<?php

namespace App\Http\Controllers\Returns;

use App\Http\Controllers\Controller;
use App\Models\Partner;
use App\Models\PartnerTransaction;
use App\Models\Product;
use App\Models\ReturnItem;
use App\Models\ReturnTransaction;
use App\Models\StockMovement;
use App\Models\Warehouse;
use App\Services\NotificationDispatcher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ReturnController extends Controller
{
    public function index(Request $request)
    {
        $type = $request->get('type', 'sales_return');
        abort_unless(in_array($type, ['sales_return', 'purchase_return'], true), 404);

        $query = ReturnTransaction::with(['partner', 'warehouse'])->where('type', $type)->latest('return_date')->latest('id');

        if ($partnerId = $request->get('partner_id')) {
            $query->where('partner_id', $partnerId);
        }

        $returns  = $query->paginate(15)->withQueryString();
        $partners = Partner::type($type === 'sales_return' ? 'dealer' : 'supplier')->orderBy('name')->get();
        // Sales returns can come from either a dealer or a retailer —
        // merge both lists for the filter dropdown.
        if ($type === 'sales_return') {
            $partners = $partners->merge(Partner::type('retailer')->orderBy('name')->get())->sortBy('name')->values();
        }

        return view('returns.index', compact('returns', 'type', 'partners'));
    }

    public function create(Request $request)
    {
        $type = $request->get('type', 'sales_return');
        abort_unless(in_array($type, ['sales_return', 'purchase_return'], true), 404);

        if ($type === 'sales_return') {
            $partners = Partner::type('dealer')->where('is_active', true)->orderBy('name')->get()
                ->merge(Partner::type('retailer')->where('is_active', true)->orderBy('name')->get())
                ->sortBy('name')->values();
        } else {
            $partners = Partner::type('supplier')->where('is_active', true)->orderBy('name')->get();
        }

        return view('returns.create', [
            'type'       => $type,
            'partners'   => $partners,
            'warehouses' => Warehouse::where('is_active', true)->orderBy('name')->get(),
            'products'   => Product::with('unit')->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'type'              => ['required', Rule::in(['sales_return', 'purchase_return'])],
            'partner_id'        => ['required', 'exists:partners,id'],
            'warehouse_id'      => ['required', 'exists:warehouses,id'],
            'return_date'       => ['required', 'date'],
            'reason'            => ['nullable', 'string', 'max:150'],
            'reference_no_text' => ['nullable', 'string', 'max:100'], // free-text original invoice number, for human reference only
            'note'              => ['nullable', 'string', 'max:255'],
            'product_id'        => ['required', 'array', 'min:1'],
            'product_id.*'      => ['required', 'exists:products,id'],
            'quantity'          => ['required', 'array', 'min:1'],
            'quantity.*'        => ['required', 'integer', 'min:1'],
            'unit_price'        => ['required', 'array', 'min:1'],
            'unit_price.*'      => ['required', 'numeric', 'min:0'],
        ]);

        $return = DB::transaction(function () use ($data, $request) {
            $total = 0;
            $lineData = [];

            foreach ($data['product_id'] as $i => $productId) {
                $qty   = (int) $data['quantity'][$i];
                $price = (float) $data['unit_price'][$i];
                $sub   = $qty * $price;
                $total += $sub;

                $lineData[] = ['product_id' => $productId, 'quantity' => $qty, 'unit_price' => $price, 'subtotal' => $sub];
            }

            $return = ReturnTransaction::create([
                'return_no'    => $this->generateReturnNo($data['type']),
                'type'         => $data['type'],
                'partner_id'   => $data['partner_id'],
                'warehouse_id' => $data['warehouse_id'],
                'return_date'  => $data['return_date'],
                'reason'       => $data['reason'] ?? null,
                'total'        => $total,
                'status'       => 'confirmed',
                'note'         => trim(($data['reference_no_text'] ?? '') . ' ' . ($data['note'] ?? '')),
                'created_by'   => $request->user()->id,
            ]);

            $stockType = $data['type'] === 'sales_return' ? 'return_in' : 'return_out';

            foreach ($lineData as $line) {
                ReturnItem::create($line + ['return_id' => $return->id]);

                StockMovement::create([
                    'product_id'   => $line['product_id'],
                    'warehouse_id' => $data['warehouse_id'],
                    'type'         => $stockType,
                    'quantity'     => $stockType === 'return_in' ? abs($line['quantity']) : -abs($line['quantity']),
                    'reference_no' => $return->return_no,
                    'note'         => __('app.return_ref') . ' ' . $return->return_no,
                    'created_by'   => $request->user()->id,
                ]);
            }

            // Sign mirrors Sale/Purchase exactly: a sales_return credits
            // the dealer/retailer (reduces what they owe us → negative);
            // a purchase_return credits US from the supplier (reduces
            // what we owe them → positive). See Partner::currentBalance().
            PartnerTransaction::create([
                'partner_id'     => $return->partner_id,
                'type'           => 'return',
                'amount'         => $data['type'] === 'sales_return' ? -$total : $total,
                'reference_type' => 'ReturnTransaction',
                'reference_id'   => $return->id,
                'note'           => __('app.return_ref') . ' ' . $return->return_no,
                'created_by'     => $request->user()->id,
            ]);

            return $return;
        });

        $return->load(['partner', 'createdBy']);
        NotificationDispatcher::send(
            eventKey: 'new_return',
            subject: __('app.email_new_return_subject') . ' ' . $return->return_no,
            view: 'emails.new-return',
            data: [
                'returnNo'    => $return->return_no,
                'typeLabel'   => $return->typeLabel(),
                'partnerName' => $return->partner?->name,
                'reason'      => $return->reason,
                'total'       => number_format($return->total, 2),
                'createdBy'   => $return->createdBy?->name,
                'url'         => route('returns.show', $return),
            ],
            defaultRecipients: NotificationDispatcher::emailsWithPermission('returns.view')
        );

        return redirect()->route('returns.show', $return)->with('success', __('app.return_created'));
    }

    public function show(ReturnTransaction $return)
    {
        $return->load(['items.product', 'partner', 'warehouse', 'createdBy']);

        return view('returns.show', compact('return'));
    }

    public function cancel(Request $request, ReturnTransaction $return): RedirectResponse
    {
        if ($return->isCancelled()) {
            return back()->with('error', __('app.already_cancelled'));
        }

        DB::transaction(function () use ($return, $request) {
            $reverseType = $return->type === 'sales_return' ? 'adjustment_out' : 'adjustment_in';

            foreach ($return->items as $item) {
                StockMovement::create([
                    'product_id'   => $item->product_id,
                    'warehouse_id' => $return->warehouse_id,
                    'type'         => $reverseType,
                    'quantity'     => $reverseType === 'adjustment_in' ? abs($item->quantity) : -abs($item->quantity),
                    'reference_no' => $return->return_no,
                    'note'         => __('app.cancelled_return') . ' ' . $return->return_no,
                    'created_by'   => $request->user()->id,
                ]);
            }

            PartnerTransaction::create([
                'partner_id'     => $return->partner_id,
                'type'           => 'adjustment',
                'amount'         => $return->type === 'sales_return' ? $return->total : -$return->total,
                'reference_type' => 'ReturnTransaction',
                'reference_id'   => $return->id,
                'note'           => __('app.cancelled_return') . ' ' . $return->return_no,
                'created_by'     => $request->user()->id,
            ]);

            $return->update(['status' => 'cancelled']);
        });

        return redirect()->route('returns.show', $return)->with('success', __('app.return_cancelled'));
    }

    private function generateReturnNo(string $type): string
    {
        $prefix = $type === 'sales_return' ? 'SRN' : 'PRN';
        $today  = now()->format('Ymd');
        $countToday = ReturnTransaction::where('type', $type)->whereDate('created_at', now()->toDateString())->count();

        return "{$prefix}-{$today}-" . str_pad($countToday + 1, 4, '0', STR_PAD_LEFT);
    }
}
