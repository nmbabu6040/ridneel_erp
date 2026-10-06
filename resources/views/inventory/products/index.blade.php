@extends('layouts.app')

@section('title', __('app.products'))
@section('page-title', __('app.products'))

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <h5 class="fw-bold mb-0">{{ __('app.products') }}</h5>
        <div class="d-flex gap-2">
            <form method="GET" class="d-flex gap-2">
                <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="{{ __('app.search_placeholder') }}">
                <select name="low_stock" class="form-select form-select-sm" onchange="this.form.submit()" style="width:auto">
                    <option value="">{{ __('app.all_products') }}</option>
                    <option value="1" {{ request('low_stock') === '1' ? 'selected' : '' }}>{{ __('app.low_stock_only') }}</option>
                </select>
            </form>
            @can('inventory.manage')
                <a href="{{ route('inventory.products.create') }}" class="btn btn-primary btn-sm text-nowrap"><i class="bi bi-plus-lg"></i> {{ __('app.add_product') }}</a>
            @endcan
        </div>
    </div>

    <div class="stat-card p-0">
        <div class="table-responsive-card">
            <table class="table table-hover mb-0 align-middle">
                <thead>
                    <tr>
                        <th>{{ __('app.product') }}</th>
                        <th>{{ __('app.sku') }}</th>
                        <th>{{ __('app.category') }}</th>
                        <th>{{ __('app.sale_price') }}</th>
                        <th>{{ __('app.stock') }}</th>
                        <th>{{ __('app.status') }}</th>
                        <th class="text-end">{{ __('app.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($products as $product)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $product->name }}</div>
                                @if ($product->name_bn)<div class="small text-secondary">{{ $product->name_bn }}</div>@endif
                            </td>
                            <td><code>{{ $product->sku }}</code></td>
                            <td>{{ $product->category?->displayName() }}</td>
                            <td>{{ number_format($product->sale_price, 2) }}</td>
                            <td>
                                @php $stock = $product->currentStock(); @endphp
                                <span class="badge {{ $product->isLowStock() ? 'text-bg-danger' : 'text-bg-secondary' }}">
                                    {{ $stock }} {{ $product->unit?->short_code }}
                                </span>
                                @if ($product->isLowStock())
                                    <i class="bi bi-exclamation-triangle-fill text-danger ms-1" title="{{ __('app.low_stock_warning') }}"></i>
                                @endif
                            </td>
                            <td><span class="badge {{ $product->is_active ? 'badge-status-active' : 'badge-status-inactive' }}">{{ $product->is_active ? __('app.status_active') : __('app.status_inactive') }}</span></td>
                            <td class="text-end">
                                @can('inventory.manage')
                                    <a href="{{ route('inventory.products.edit', $product) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
                                    <form action="{{ route('inventory.products.destroy', $product) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('app.confirm_delete') }}');">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-secondary py-4">{{ __('app.no_records') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">{{ $products->links() }}</div>
@endsection
