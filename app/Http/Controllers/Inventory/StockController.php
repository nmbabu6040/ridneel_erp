<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Warehouse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class StockController extends Controller
{
    /** Stock ledger — filterable list of every movement. */
    public function index(Request $request)
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

        $movements  = $query->paginate(20)->withQueryString();
        $products   = Product::orderBy('name')->get();
        $warehouses = Warehouse::orderBy('name')->get();

        return view('inventory.stock.index', compact('movements', 'products', 'warehouses'));
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
            'note'         => $data['note'] ?? null,
            'created_by'   => $request->user()->id,
        ]);

        return redirect()->route('inventory.stock.index')->with('success', __('app.stock_entry_saved'));
    }
}
