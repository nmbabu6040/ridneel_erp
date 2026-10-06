<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Models\Partner;
use App\Models\PartnerTransaction;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SalesTarget;
use App\Models\StockMovement;
use App\Models\Warehouse;
use App\Services\NotificationDispatcher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class SaleController extends Controller
{
    public function index(Request $request)
    {
        $type = $request->get('type', 'primary');
        abort_unless(in_array($type, ['primary', 'secondary'], true), 404);

        $query = Sale::with(['partner', 'warehouse'])->where('type', $type)->latest('sale_date')->latest('id');

        if ($partnerId = $request->get('partner_id')) {
            $query->where('partner_id', $partnerId);
        }
        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        $sales    = $query->paginate(15)->withQueryString();
        $partners = Partner::type($type === 'primary' ? 'dealer' : 'retailer')->orderBy('name')->get();

        return view('sales.index', compact('sales', 'type', 'partners'));
    }

    public function create(Request $request)
    {
        $type = $request->get('type', 'primary');
        $this->authorizeType($request, $type);

        $partnerType = $type === 'primary' ? 'dealer' : 'retailer';
        $products    = Product::with('unit')->where('is_active', true)->orderBy('name')->get();
        $warehouses  = Warehouse::where('is_active', true)->orderBy('name')->get();

        // Small pre-computed product x warehouse stock map so the create
        // form can show/validate available quantity client-side without
        // a round-trip per keystroke. Fine at Phase-3 catalogue sizes;
        // swap for a cached stock-summary table if this grows large.
        $stockMap = [];
        foreach ($products as $product) {
            foreach ($warehouses as $warehouse) {
                $stockMap["{$product->id}_{$warehouse->id}"] = $product->currentStock($warehouse->id);
            }
        }

        return view('sales.create', [
            'type'       => $type,
            'partners'   => Partner::type($partnerType)->where('is_active', true)->orderBy('name')->get(),
            'warehouses' => $warehouses,
            'products'   => $products,
            'stockMap'   => $stockMap,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $type = $request->input('type');
        $this->authorizeType($request, $type);

        $data = $request->validate([
            'type'                  => ['required', Rule::in(['primary', 'secondary'])],
            'partner_id'            => ['required', 'exists:partners,id'],
            'warehouse_id'          => ['required', 'exists:warehouses,id'],
            'sale_date'             => ['required', 'date'],
            'discount'              => ['nullable', 'numeric', 'min:0'],
            'paid_amount'           => ['nullable', 'numeric', 'min:0'],
            'note'                  => ['nullable', 'string', 'max:255'],
            'product_id'            => ['required', 'array', 'min:1'],
            'product_id.*'          => ['required', 'exists:products,id'],
            'quantity'              => ['required', 'array', 'min:1'],
            'quantity.*'            => ['required', 'integer', 'min:1'],
            'unit_price'            => ['required', 'array', 'min:1'],
            'unit_price.*'          => ['required', 'numeric', 'min:0'],
            'item_discount'         => ['nullable', 'array'],
            'item_discount.*'       => ['nullable', 'numeric', 'min:0'],
        ]);

        // Stock sufficiency check happens BEFORE anything is written —
        // partial invoices (some items ok, some not) must never happen.
        $shortages = [];
        foreach ($data['product_id'] as $i => $productId) {
            $product   = Product::findOrFail($productId);
            $requested = (int) $data['quantity'][$i];
            $available = $product->currentStock($data['warehouse_id']);

            if ($requested > $available) {
                $shortages[] = "{$product->name}: {$available} " . __('app.available') . ", {$requested} " . __('app.requested');
            }
        }

        if (!empty($shortages)) {
            throw ValidationException::withMessages(['quantity' => $shortages]);
        }

        $sale = DB::transaction(function () use ($data, $type, $request) {
            $subtotal = 0;
            $lineData = [];

            foreach ($data['product_id'] as $i => $productId) {
                $qty      = (int) $data['quantity'][$i];
                $price    = (float) $data['unit_price'][$i];
                $lineDisc = (float) ($data['item_discount'][$i] ?? 0);
                $lineSub  = ($qty * $price) - $lineDisc;
                $subtotal += $lineSub;

                $lineData[] = [
                    'product_id' => $productId,
                    'quantity'   => $qty,
                    'unit_price' => $price,
                    'discount'   => $lineDisc,
                    'subtotal'   => $lineSub,
                ];
            }

            $discount   = (float) ($data['discount'] ?? 0);
            $total      = max(0, $subtotal - $discount);
            $paidAmount = min((float) ($data['paid_amount'] ?? 0), $total);
            $dueAmount  = $total - $paidAmount;

            $sale = Sale::create([
                'invoice_no'   => $this->generateInvoiceNo($type),
                'type'         => $type,
                'partner_id'   => $data['partner_id'],
                'warehouse_id' => $data['warehouse_id'],
                'sale_date'    => $data['sale_date'],
                'subtotal'     => $subtotal,
                'discount'     => $discount,
                'total'        => $total,
                'paid_amount'  => $paidAmount,
                'due_amount'   => $dueAmount,
                'status'       => 'confirmed',
                'note'         => $data['note'] ?? null,
                'created_by'   => $request->user()->id,
            ]);

            foreach ($lineData as $line) {
                SaleItem::create($line + ['sale_id' => $sale->id]);

                StockMovement::create([
                    'product_id'   => $line['product_id'],
                    'warehouse_id' => $data['warehouse_id'],
                    'type'         => 'sales_out',
                    'quantity'     => -abs($line['quantity']),
                    'reference_no' => $sale->invoice_no,
                    'note'         => __('app.sold_via_invoice') . ' ' . $sale->invoice_no,
                    'created_by'   => $request->user()->id,
                ]);
            }

            PartnerTransaction::create([
                'partner_id'      => $sale->partner_id,
                'type'            => 'sale',
                'amount'          => $total,
                'reference_type'  => 'Sale',
                'reference_id'    => $sale->id,
                'note'            => __('app.invoice') . ' ' . $sale->invoice_no,
                'created_by'      => $request->user()->id,
            ]);

            if ($paidAmount > 0) {
                PartnerTransaction::create([
                    'partner_id'     => $sale->partner_id,
                    'type'           => 'payment',
                    'amount'         => -$paidAmount,
                    'reference_type' => 'Sale',
                    'reference_id'   => $sale->id,
                    'note'           => __('app.payment_at_invoice') . ' ' . $sale->invoice_no,
                    'created_by'     => $request->user()->id,
                ]);
            }

            return $sale;
        });

        $this->notifyNewInvoice($sale);
        $this->notifyLowStockIfAny($sale);
        $this->notifyTargetAchievedIfCrossed($sale);

        return redirect()->route('sales.show', $sale)->with('success', __('app.invoice_created'));
    }

    public function show(Sale $sale)
    {
        $sale->load(['items.product', 'partner', 'warehouse', 'createdBy']);

        return view('sales.show', compact('sale'));
    }

    public function cancel(Request $request, Sale $sale): RedirectResponse
    {
        $this->authorizeType($request, $sale->type);

        if ($sale->isCancelled()) {
            return back()->with('error', __('app.already_cancelled'));
        }

        DB::transaction(function () use ($sale, $request) {
            foreach ($sale->items as $item) {
                StockMovement::create([
                    'product_id'   => $item->product_id,
                    'warehouse_id' => $sale->warehouse_id,
                    'type'         => 'adjustment_in',
                    'quantity'     => abs($item->quantity),
                    'reference_no' => $sale->invoice_no,
                    'note'         => __('app.cancelled_invoice') . ' ' . $sale->invoice_no,
                    'created_by'   => $request->user()->id,
                ]);
            }

            // Net balance contribution of this invoice was exactly
            // due_amount (total owed minus what was already paid at
            // sale time) — one reversing entry cancels it out cleanly.
            if ((float) $sale->due_amount !== 0.0) {
                PartnerTransaction::create([
                    'partner_id'     => $sale->partner_id,
                    'type'           => 'adjustment',
                    'amount'         => -$sale->due_amount,
                    'reference_type' => 'Sale',
                    'reference_id'   => $sale->id,
                    'note'           => __('app.cancelled_invoice') . ' ' . $sale->invoice_no,
                    'created_by'     => $request->user()->id,
                ]);
            }

            $sale->update(['status' => 'cancelled']);
        });

        return redirect()->route('sales.show', $sale)->with('success', __('app.invoice_cancelled'));
    }

    private function notifyNewInvoice(Sale $sale): void
    {
        $sale->load(['partner', 'createdBy']);

        NotificationDispatcher::send(
            eventKey: 'new_invoice',
            subject: __('app.email_new_invoice_subject') . ' ' . $sale->invoice_no,
            view: 'emails.new-invoice',
            data: [
                'invoiceNo'  => $sale->invoice_no,
                'typeLabel'  => $sale->typeLabel(),
                'partnerName' => $sale->partner?->name,
                'total'      => number_format($sale->total, 2),
                'paid'       => number_format($sale->paid_amount, 2),
                'due'        => (float) $sale->due_amount,
                'createdBy'  => $sale->createdBy?->name,
                'url'        => route('sales.show', $sale),
            ],
            defaultRecipients: NotificationDispatcher::emailsWithPermission('sales.view')
        );
    }

    /**
     * Checks only the products just sold in this invoice (not the whole
     * catalogue) — cheap, and exactly matches what Product::isLowStock()
     * already does, just triggered right after the stock-decreasing
     * event instead of on a schedule.
     */
    private function notifyLowStockIfAny(Sale $sale): void
    {
        $sale->loadMissing('items.product', 'warehouse');

        foreach ($sale->items as $item) {
            $product = $item->product;
            if ($product && $product->isLowStock()) {
                NotificationDispatcher::send(
                    eventKey: 'low_stock',
                    subject: __('app.email_low_stock_subject') . ' ' . $product->name,
                    view: 'emails.low-stock',
                    data: [
                        'productName'   => $product->name,
                        'sku'           => $product->sku,
                        'warehouseName' => $sale->warehouse?->name,
                        'currentStock'  => $product->currentStock($sale->warehouse_id),
                        'reorderLevel'  => $product->reorder_level,
                        'unit'          => $product->unit?->short_code,
                    ],
                    defaultRecipients: NotificationDispatcher::emailsWithPermission('inventory.manage')
                );
            }
        }
    }

    /** Fires once per target, the first time it crosses 100% — see sales_targets.notified_at. */
    private function notifyTargetAchievedIfCrossed(Sale $sale): void
    {
        $month = $sale->sale_date->format('Y-m') . '-01';

        $target = $sale->type === 'secondary'
            ? SalesTarget::where('target_type', 'sr')->where('target_ref_id', $sale->created_by)->where('period_month', $month)->first()
            : SalesTarget::where('target_type', 'dealer')->where('target_ref_id', $sale->partner_id)->where('period_month', $month)->first();

        if (!$target || $target->notified_at) {
            return;
        }

        if ($target->achievementPercent() >= 100) {
            NotificationDispatcher::send(
                eventKey: 'target_achieved',
                subject: __('app.email_target_achieved_subject') . ' ' . $target->subjectName(),
                view: 'emails.target-achieved',
                data: [
                    'subjectName'  => $target->subjectName(),
                    'month'        => $target->period_month->format('F Y'),
                    'targetAmount' => number_format($target->target_amount, 2),
                    'achieved'     => number_format($target->achievement(), 2),
                    'percent'      => $target->achievementPercent(),
                ],
                defaultRecipients: NotificationDispatcher::emailsWithPermission('kpi.manage')
            );

            $target->update(['notified_at' => now()]);
        }
    }

    private function authorizeType(Request $request, string $type): void
    {
        abort_unless(in_array($type, ['primary', 'secondary'], true), 404);
        abort_unless($request->user()->hasPermission("sales.$type"), 403, __('app.no_permission'));
    }

    private function generateInvoiceNo(string $type): string
    {
        $prefix = $type === 'primary' ? 'PS' : 'SS';
        $today  = now()->format('Ymd');

        // Sequence resets daily per type. Wrapped in the same DB
        // transaction as the insert, so a same-millisecond race is the
        // only realistic collision risk — acceptable for Phase 3;
        // a dedicated sequence table can replace this if volume grows.
        $countToday = Sale::where('type', $type)->whereDate('created_at', now()->toDateString())->count();

        return "{$prefix}-{$today}-" . str_pad($countToday + 1, 4, '0', STR_PAD_LEFT);
    }
}
