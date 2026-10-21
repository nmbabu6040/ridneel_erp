<?php

namespace App\Http\Controllers\Purchases;

use App\Http\Controllers\Controller;
use App\Models\Partner;
use App\Models\PartnerTransaction;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\StockMovement;
use App\Models\Warehouse;
use App\Services\NotificationDispatcher;
use App\Support\CsvExporter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseController extends Controller
{
    public function index(Request $request)
    {
        $query = $this->filteredQuery($request);

        $purchases = $query->paginate(15)->withQueryString();
        $suppliers = Partner::type('supplier')->orderBy('name')->get();

        return view('purchases.index', compact('purchases', 'suppliers'));
    }

    public function export(Request $request)
    {
        $rows = $this->filteredQuery($request)->get()->map(fn (Purchase $p) => [
            $p->invoice_no, $p->purchase_date->format('Y-m-d'), $p->supplier?->name,
            $p->subtotal, $p->discount, $p->total, $p->paid_amount, $p->due_amount, $p->status,
        ]);

        return CsvExporter::stream(
            'purchases-' . now()->format('Y-m-d') . '.csv',
            ['Invoice No', 'Date', 'Supplier', 'Subtotal', 'Discount', 'Total', 'Paid', 'Due', 'Status'],
            $rows
        );
    }

    private function filteredQuery(Request $request)
    {
        $query = Purchase::with(['supplier', 'warehouse'])->latest('purchase_date')->latest('id');

        if ($supplierId = $request->get('supplier_id')) {
            $query->where('supplier_id', $supplierId);
        }
        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        return $query;
    }

    public function create()
    {
        $products = Product::with('unit')->where('is_active', true)->orderBy('name')->get();
        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();

        return view('purchases.create', [
            'suppliers'  => Partner::type('supplier')->where('is_active', true)->orderBy('name')->get(),
            'warehouses' => $warehouses,
            'products'   => $products,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'supplier_id'          => ['required', 'exists:partners,id'],
            'warehouse_id'         => ['required', 'exists:warehouses,id'],
            'purchase_date'        => ['required', 'date'],
            'supplier_invoice_no'  => ['nullable', 'string', 'max:100'],
            'discount'             => ['nullable', 'numeric', 'min:0'],
            'paid_amount'          => ['nullable', 'numeric', 'min:0'],
            'note'                 => ['nullable', 'string', 'max:255'],
            'product_id'           => ['required', 'array', 'min:1'],
            'product_id.*'         => ['required', 'exists:products,id'],
            'quantity'             => ['required', 'array', 'min:1'],
            'quantity.*'           => ['required', 'integer', 'min:1'],
            'unit_cost'            => ['required', 'array', 'min:1'],
            'unit_cost.*'          => ['required', 'numeric', 'min:0'],
            'item_discount'        => ['nullable', 'array'],
            'item_discount.*'      => ['nullable', 'numeric', 'min:0'],
        ]);

        $purchase = DB::transaction(function () use ($data, $request) {
            $subtotal = 0;
            $lineData = [];

            foreach ($data['product_id'] as $i => $productId) {
                $qty      = (int) $data['quantity'][$i];
                $cost     = (float) $data['unit_cost'][$i];
                $lineDisc = (float) ($data['item_discount'][$i] ?? 0);
                $lineSub  = ($qty * $cost) - $lineDisc;
                $subtotal += $lineSub;

                $lineData[] = [
                    'product_id' => $productId,
                    'quantity'   => $qty,
                    'unit_cost'  => $cost,
                    'discount'   => $lineDisc,
                    'subtotal'   => $lineSub,
                ];
            }

            $discount   = (float) ($data['discount'] ?? 0);
            $total      = max(0, $subtotal - $discount);
            $paidAmount = min((float) ($data['paid_amount'] ?? 0), $total);
            $dueAmount  = $total - $paidAmount;

            $purchase = Purchase::create([
                'invoice_no'           => $this->generateInvoiceNo(),
                'supplier_invoice_no'  => $data['supplier_invoice_no'] ?? null,
                'supplier_id'          => $data['supplier_id'],
                'warehouse_id'         => $data['warehouse_id'],
                'purchase_date'        => $data['purchase_date'],
                'subtotal'             => $subtotal,
                'discount'             => $discount,
                'total'                => $total,
                'paid_amount'          => $paidAmount,
                'due_amount'           => $dueAmount,
                'status'               => 'confirmed',
                'note'                 => $data['note'] ?? null,
                'created_by'           => $request->user()->id,
            ]);

            foreach ($lineData as $line) {
                PurchaseItem::create($line + ['purchase_id' => $purchase->id]);

                StockMovement::create([
                    'product_id'   => $line['product_id'],
                    'warehouse_id' => $data['warehouse_id'],
                    'type'         => 'purchase_in',
                    'quantity'     => abs($line['quantity']),
                    'unit_cost'    => $line['unit_cost'],
                    'reference_no' => $purchase->invoice_no,
                    'note'         => __('app.purchased_via') . ' ' . $purchase->invoice_no,
                    'created_by'   => $request->user()->id,
                ]);
            }

            // Sign convention (see Partner::currentBalance): positive =
            // partner owes us. A purchase means WE now owe the supplier
            // more, so it's recorded negative here — the mirror image
            // of how Sale::store() records a sale as positive.
            PartnerTransaction::create([
                'partner_id'     => $purchase->supplier_id,
                'type'           => 'purchase',
                'amount'         => -$total,
                'reference_type' => 'Purchase',
                'reference_id'   => $purchase->id,
                'note'           => __('app.purchase_invoice') . ' ' . $purchase->invoice_no,
                'created_by'     => $request->user()->id,
            ]);

            if ($paidAmount > 0) {
                PartnerTransaction::create([
                    'partner_id'     => $purchase->supplier_id,
                    'type'           => 'payment',
                    'amount'         => $paidAmount, // paying the supplier reduces what we owe them
                    'reference_type' => 'Purchase',
                    'reference_id'   => $purchase->id,
                    'note'           => __('app.payment_at_purchase') . ' ' . $purchase->invoice_no,
                    'created_by'     => $request->user()->id,
                ]);
            }

            return $purchase;
        });

        $purchase->load(['supplier', 'createdBy']);
        NotificationDispatcher::send(
            eventKey: 'new_purchase',
            subject: __('app.email_new_purchase_subject') . ' ' . $purchase->invoice_no,
            view: 'emails.new-purchase',
            data: [
                'invoiceNo'    => $purchase->invoice_no,
                'supplierName' => $purchase->supplier?->name,
                'total'        => number_format($purchase->total, 2),
                'paid'         => number_format($purchase->paid_amount, 2),
                'due'          => (float) $purchase->due_amount,
                'createdBy'    => $purchase->createdBy?->name,
                'url'          => route('purchases.show', $purchase),
            ],
            defaultRecipients: NotificationDispatcher::emailsWithPermission('accounting.view')
        );

        return redirect()->route('purchases.show', $purchase)->with('success', __('app.purchase_created'));
    }

    public function show(Purchase $purchase)
    {
        $purchase->load(['items.product', 'supplier', 'warehouse', 'createdBy']);

        return view('purchases.show', compact('purchase'));
    }

    public function cancel(Request $request, Purchase $purchase): RedirectResponse
    {
        if ($purchase->isCancelled()) {
            return back()->with('error', __('app.already_cancelled'));
        }

        DB::transaction(function () use ($purchase, $request) {
            foreach ($purchase->items as $item) {
                StockMovement::create([
                    'product_id'   => $item->product_id,
                    'warehouse_id' => $purchase->warehouse_id,
                    'type'         => 'adjustment_out',
                    'quantity'     => -abs($item->quantity),
                    'reference_no' => $purchase->invoice_no,
                    'note'         => __('app.cancelled_purchase') . ' ' . $purchase->invoice_no,
                    'created_by'   => $request->user()->id,
                ]);
            }

            // Net balance contribution of this purchase was -due_amount
            // (see store()); one reversing entry of +due_amount cancels
            // it out — mirror of Sale::cancel()'s -due_amount reversal.
            if ((float) $purchase->due_amount !== 0.0) {
                PartnerTransaction::create([
                    'partner_id'     => $purchase->supplier_id,
                    'type'           => 'adjustment',
                    'amount'         => $purchase->due_amount,
                    'reference_type' => 'Purchase',
                    'reference_id'   => $purchase->id,
                    'note'           => __('app.cancelled_purchase') . ' ' . $purchase->invoice_no,
                    'created_by'     => $request->user()->id,
                ]);
            }

            $purchase->update(['status' => 'cancelled']);
        });

        return redirect()->route('purchases.show', $purchase)->with('success', __('app.purchase_cancelled'));
    }

    private function generateInvoiceNo(): string
    {
        $today = now()->format('Ymd');
        $countToday = Purchase::whereDate('created_at', now()->toDateString())->count();

        return "PO-{$today}-" . str_pad($countToday + 1, 4, '0', STR_PAD_LEFT);
    }
}
