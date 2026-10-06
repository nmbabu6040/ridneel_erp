<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Warehouse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
// (No Rule import needed here — warehouse validation has no unique/exists rules.)

class WarehouseController extends Controller
{
    public function index()
    {
        $warehouses = Warehouse::orderBy('name')->paginate(15);

        return view('inventory.warehouses.index', compact('warehouses'));
    }

    public function create()
    {
        return view('inventory.warehouses.form', ['warehouse' => new Warehouse()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $this->handleDefaultFlag($data);

        Warehouse::create($data);

        return redirect()->route('inventory.warehouses.index')->with('success', __('app.warehouse_created'));
    }

    public function edit(Warehouse $warehouse)
    {
        return view('inventory.warehouses.form', compact('warehouse'));
    }

    public function update(Request $request, Warehouse $warehouse): RedirectResponse
    {
        $data = $this->validated($request);
        $this->handleDefaultFlag($data, $warehouse->id);

        $warehouse->update($data);

        return redirect()->route('inventory.warehouses.index')->with('success', __('app.warehouse_updated'));
    }

    public function destroy(Warehouse $warehouse): RedirectResponse
    {
        if ($warehouse->stockMovements()->exists()) {
            return back()->with('error', __('app.warehouse_in_use'));
        }

        $warehouse->delete();

        return redirect()->route('inventory.warehouses.index')->with('success', __('app.warehouse_deleted'));
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name'       => ['required', 'string', 'max:150'],
            'name_bn'    => ['nullable', 'string', 'max:150'],
            'address'    => ['nullable', 'string', 'max:255'],
            'phone'      => ['nullable', 'string', 'max:20'],
            'is_default' => ['nullable', 'boolean'],
            'is_active'  => ['nullable', 'boolean'],
        ]);

        $data['is_default'] = $request->boolean('is_default');
        $data['is_active']  = $request->boolean('is_active');

        return $data;
    }

    /** Only one warehouse may be the default at a time. */
    private function handleDefaultFlag(array &$data, ?int $ignoreId = null): void
    {
        if (!empty($data['is_default'])) {
            DB::table('warehouses')
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->update(['is_default' => false]);
        }
    }
}
