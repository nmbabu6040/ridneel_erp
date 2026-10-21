<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\Unit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with(['category', 'unit'])->latest();

        if ($search = $request->get('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        if ($request->get('low_stock') === '1') {
            // Evaluated in PHP (not SQL) since stock is a derived sum —
            // fine at Phase-2 data volumes; a materialized stock summary
            // table can replace this filter later if the catalogue grows large.
            $products = $query->get()->filter->isLowStock();
            $products = new \Illuminate\Pagination\LengthAwarePaginator(
                $products->forPage($request->get('page', 1), 15)->values(),
                $products->count(),
                15,
                $request->get('page', 1),
                ['path' => $request->url(), 'query' => $request->query()]
            );
        } else {
            $products = $query->paginate(15)->withQueryString();
        }

        return view('inventory.products.index', compact('products'));
    }

    /**
     * Printable barcode label sheet for one product — renders the
     * product's barcode (falling back to its SKU if no barcode was
     * entered, so every product can always get a scannable label even
     * before a "real" barcode is assigned) as a Code128 symbol via
     * JsBarcode, loaded client-side from a CDN. No server-side barcode
     * library needed; the browser's own Print dialog is the delivery
     * mechanism (Save as PDF or print directly to label paper).
     */
    public function barcodeLabel(Request $request, Product $product)
    {
        $qty = max(1, min(200, (int) $request->get('qty', 20)));

        return view('inventory.products.barcode-label', [
            'product' => $product,
            'qty'     => $qty,
            'code'    => $product->barcode ?: $product->sku,
        ]);
    }

    public function create()
    {
        return view('inventory.products.form', [
            'product'    => new Product(),
            'categories' => Category::where('is_active', true)->orderBy('name')->get(),
            'units'      => Unit::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Product::create($this->validated($request));

        return redirect()->route('inventory.products.index')->with('success', __('app.product_created'));
    }

    public function edit(Product $product)
    {
        return view('inventory.products.form', [
            'product'    => $product,
            'categories' => Category::where('is_active', true)->orderBy('name')->get(),
            'units'      => Unit::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $product->update($this->validated($request, $product->id));

        return redirect()->route('inventory.products.index')->with('success', __('app.product_updated'));
    }

    public function destroy(Product $product): RedirectResponse
    {
        if ($product->stockMovements()->exists()) {
            return back()->with('error', __('app.product_in_use'));
        }

        $product->delete();

        return redirect()->route('inventory.products.index')->with('success', __('app.product_deleted'));
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        $data = $request->validate([
            'category_id'    => ['required', 'exists:categories,id'],
            'unit_id'        => ['required', 'exists:units,id'],
            'name'           => ['required', 'string', 'max:200'],
            'name_bn'        => ['nullable', 'string', 'max:200'],
            'sku'            => ['required', 'string', 'max:50', Rule::unique('products', 'sku')->ignore($ignoreId)],
            'barcode'        => ['nullable', 'string', 'max:50', Rule::unique('products', 'barcode')->ignore($ignoreId)],
            'purchase_price' => ['required', 'numeric', 'min:0'],
            'sale_price'     => ['required', 'numeric', 'min:0'],
            'reorder_level'  => ['required', 'integer', 'min:0'],
            'is_active'      => ['nullable', 'boolean'],
        ]);

        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
