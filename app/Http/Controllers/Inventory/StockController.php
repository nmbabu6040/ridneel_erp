<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Warehouse;
use App\Support\CsvExporter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class StockController extends Controller
{
    /** Stock ledger — filterable list of every movement. */
    public function index(Request $request)
    {
        $query = $this->filteredQuery($request);

        $movements  = $query->paginate(20)->withQueryString();
        $products   = Product::orderBy('name')->get();
        $warehouses = Warehouse::orderBy('name')->get();

        return view('inventory.stock.index', compact('movements', 'products', 'warehouses'));
    }

    public function export(Request $request)
    {
        $rows = $this->filteredQuery($request)->get()->map(fn (StockMovement $m) => [
            $m->created_at->format('Y-m-d H:i'), $m->product?->name, $m->warehouse?->name,
            $m->typeLabel(), $m->quantity, $m->reference_no, $m->createdBy?->name,
        ]);

        return CsvExporter::stream(
            'stock-ledger-' . now()->format('Y-m-d') . '.csv',
            ['Date', 'Product', 'Warehouse', 'Type', 'Quantity', 'Reference', 'By'],
            $rows
        );
    }

    private function filteredQuery(Request $request)
    {
        $query = StockMovement::with(['product', 'warehouse', 'createdBy'])->latest();

        if ($productId = $request->get('product_id')) {
            $query->where('product_id', $productId);
        }
        if ($warehouseId = $request->get('warehouse_id')) {
            $query->where('warehouse_id', $warehouseId);
        }
        if ($type = $request->get('type')) {
            $query->where('type', $type);
        }

        return $query;
    }

    /** Form to record Opening Stock or a manual Adjustment. */
    public function create()
    {
        return view('inventory.stock.form', [
            'products'   => Product::where('is_active', true)->orderBy('name')->get(),
            'warehouses' => Warehouse::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'product_id'   => ['required', 'exists:products,id'],
            'warehouse_id' => ['required', 'exists:warehouses,id'],
            'type'         => ['required', Rule::in(['opening', 'adjustment_in', 'adjustment_out'])],
            'quantity'     => ['required', 'integer', 'min:1'],
            'unit_cost'    => ['nullable', 'numeric', 'min:0'],
            'batch_no'     => ['nullable', 'string', 'max:50'],
            'expiry_date'  => ['nullable', 'date'],
            'note'         => ['nullable', 'string', 'max:255'],
        ]);

        // Enforce the sign convention centrally, here, once — every other
        // module (Sales, Purchase, Returns in later phases) must go
        // through this same rule so the ledger sum is always trustworthy.
        $signedQuantity = in_array($data['type'], StockMovement::OUT_TYPES, true)
            ? -abs($data['quantity'])
            : abs($data['quantity']);

        StockMovement::create([
            'product_id'   => $data['product_id'],
            'warehouse_id' => $data['warehouse_id'],
            'type'         => $data['type'],
            'quantity'     => $signedQuantity,
            'unit_cost'    => $data['unit_cost'] ?? null,
            'batch_no'     => $data['batch_no'] ?? null,
            'expiry_date'  => $data['expiry_date'] ?? null,
            'note'         => $data['note'] ?? null,
            'created_by'   => $request->user()->id,
        ]);

        return redirect()->route('inventory.stock.index')->with('success', __('app.stock_entry_saved'));
    }

    /**
     * Every batch still in stock, across all batch-tracked products,
     * soonest-expiry-first — the page a warehouse manager checks daily
     * to decide what to discount/prioritize selling or pull off shelves.
     */
    public function expiryReport(Request $request)
    {
        $days = (int) $request->get('days', 30);

        $products = Product::where('tracks_batches', true)->where('is_active', true)->orderBy('name')->get();

        $rows = collect();
        foreach ($products as $product) {
            foreach ($product->availableBatches() as $batch) {
                $rows->push((object) [
                    'product'     => $product,
                    'batch_no'    => $batch->batch_no,
                    'expiry_date' => $batch->expiry_date,
                    'remaining'   => $batch->remaining,
                    'days_left'   => $batch->expiry_date ? now()->diffInDays($batch->expiry_date, false) : null,
                ]);
            }
        }

        $rows = $rows->sortBy(fn ($r) => $r->days_left ?? PHP_INT_MAX)->values();

        if ($request->get('filter') === 'expiring') {
            $rows = $rows->filter(fn ($r) => $r->days_left !== null && $r->days_left <= $days)->values();
        }

        return view('inventory.stock.expiry-report', compact('rows', 'days'));
    }
}
