@extends('layouts.app')

@section('title', __('app.stock_ledger'))
@section('page-title', __('app.stock_ledger'))

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <h5 class="fw-bold mb-0">{{ __('app.stock_ledger') }}</h5>
        @can('stock.adjust')
            <a href="{{ route('inventory.stock.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> {{ __('app.new_stock_entry') }}</a>
        @endcan
    </div>

    <form method="GET" class="row g-2 mb-3">
        <div class="col-sm-4 col-md-3">
            <select name="product_id" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">{{ __('app.all_products') }}</option>
                @foreach ($products as $p)
                    <option value="{{ $p->id }}" {{ (string) request('product_id') === (string) $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-sm-4 col-md-3">
            <select name="warehouse_id" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">{{ __('app.all_warehouses') }}</option>
                @foreach ($warehouses as $w)
                    <option value="{{ $w->id }}" {{ (string) request('warehouse_id') === (string) $w->id ? 'selected' : '' }}>{{ $w->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-sm-4 col-md-3">
            <select name="type" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">{{ __('app.all_types') }}</option>
                @foreach (['opening','purchase_in','sales_out','return_in','return_out','adjustment_in','adjustment_out','transfer_in','transfer_out'] as $t)
                    <option value="{{ $t }}" {{ request('type') === $t ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $t)) }}</option>
                @endforeach
            </select>
        </div>
    </form>

    <div class="stat-card p-0">
        <div class="table-responsive-card">
            <table class="table table-hover mb-0 align-middle">
                <thead>
                    <tr>
                        <th>{{ __('app.date') }}</th>
                        <th>{{ __('app.product') }}</th>
                        <th>{{ __('app.warehouse') }}</th>
                        <th>{{ __('app.type') }}</th>
                        <th class="text-end">{{ __('app.quantity') }}</th>
                        <th>{{ __('app.reference') }}</th>
                        <th>{{ __('app.by') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($movements as $m)
                        <tr>
                            <td class="text-nowrap">{{ $m->created_at->format('d M Y, h:i A') }}</td>
                            <td>{{ $m->product?->name }}</td>
                            <td>{{ $m->warehouse?->name }}</td>
                            <td><span class="badge {{ $m->quantity >= 0 ? 'text-bg-success' : 'text-bg-danger' }}">{{ $m->typeLabel() }}</span></td>
                            <td class="text-end fw-semibold {{ $m->quantity >= 0 ? 'text-success' : 'text-danger' }}">
                                {{ $m->quantity >= 0 ? '+' : '' }}{{ $m->quantity }}
                            </td>
                            <td>{{ $m->reference_no ?: ($m->note ?: '—') }}</td>
                            <td>{{ $m->createdBy?->name ?: '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-secondary py-4">{{ __('app.no_records') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">{{ $movements->links() }}</div>
@endsection
