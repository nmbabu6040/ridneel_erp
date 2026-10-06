<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Unit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UnitController extends Controller
{
    public function index()
    {
        $units = Unit::withCount('products')->orderBy('name')->paginate(15);

        return view('inventory.units.index', compact('units'));
    }

    public function create()
    {
        return view('inventory.units.form', ['unit' => new Unit()]);
    }

    public function store(Request $request): RedirectResponse
    {
        Unit::create($this->validated($request));

        return redirect()->route('inventory.units.index')->with('success', __('app.unit_created'));
    }

    public function edit(Unit $unit)
    {
        return view('inventory.units.form', compact('unit'));
    }

    public function update(Request $request, Unit $unit): RedirectResponse
    {
        $unit->update($this->validated($request, $unit->id));

        return redirect()->route('inventory.units.index')->with('success', __('app.unit_updated'));
    }

    public function destroy(Unit $unit): RedirectResponse
    {
        if ($unit->products()->exists()) {
            return back()->with('error', __('app.unit_in_use'));
        }

        $unit->delete();

        return redirect()->route('inventory.units.index')->with('success', __('app.unit_deleted'));
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'name'       => ['required', 'string', 'max:100'],
            'name_bn'    => ['nullable', 'string', 'max:100'],
            'short_code' => ['required', 'string', 'max:10', Rule::unique('units', 'short_code')->ignore($ignoreId)],
        ]);
    }
}
