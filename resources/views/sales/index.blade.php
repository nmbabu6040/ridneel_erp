@extends('layouts.app')

@section('title', __('app.sales'))
@section('page-title', __('app.sales'))

@section('content')
    <ul class="nav nav-pills mb-3">
        <li class="nav-item">
            <a class="nav-link {{ $type === 'primary' ? 'active' : '' }}" href="{{ route('sales.index', ['type' => 'primary']) }}">{{ __('app.primary_sales') }}</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $type === 'secondary' ? 'active' : '' }}" href="{{ route('sales.index', ['type' => 'secondary']) }}">{{ __('app.secondary_sales') }}</a>
        </li>
    </ul>

    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <form method="GET" class="d-flex gap-2">
            <input type="hidden" name="type" value="{{ $type }}">
            <select name="partner_id" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">{{ $type === 'primary' ? __('app.all_dealers') : __('app.all_retailers') }}</option>
                @foreach ($partners as $p)
                    <option value="{{ $p->id }}" {{ (string) request('partner_id') === (string) $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                @endforeach
            </select>
            <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">{{ __('app.all_status') }}</option>
                <option value="confirmed" {{ request('status') === 'confirmed' ? 'selected' : '' }}>{{ __('app.status_confirmed') }}</option>
                <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>{{ __('app.status_cancelled') }}</option>
            </select>
        </form>
        @can(($type === 'primary' ? 'sales.primary' : 'sales.secondary'))
            <a href="{{ route('sales.create', ['type' => $type]) }}" class="btn btn-primary btn-sm text-nowrap">
                <i class="bi bi-plus-lg"></i> {{ __('app.new_invoice') }}
            </a>
        @endcan
    </div>

    <div class="stat-card p-0">
        <div class="table-responsive-card">
            <table class="table table-hover mb-0 align-middle">
                <thead>
                    <tr>
                        <th>{{ __('app.invoice_no') }}</th>
                        <th>{{ __('app.date') }}</th>
                        <th>{{ $type === 'primary' ? __('app.dealer') : __('app.retailer') }}</th>
                        <th class="text-end">{{ __('app.total') }}</th>
                        <th class="text-end">{{ __('app.paid') }}</th>
                        <th class="text-end">{{ __('app.due') }}</th>
                        <th>{{ __('app.status') }}</th>
                        <th class="text-end">{{ __('app.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($sales as $sale)
                        <tr>
                            <td><a href="{{ route('sales.show', $sale) }}"><code>{{ $sale->invoice_no }}</code></a></td>
                            <td>{{ $sale->sale_date->format('d M Y') }}</td>
                            <td>{{ $sale->partner?->name }}</td>
                            <td class="text-end">{{ number_format($sale->total, 2) }}</td>
                            <td class="text-end">{{ number_format($sale->paid_amount, 2) }}</td>
                            <td class="text-end {{ $sale->due_amount > 0 ? 'text-danger fw-semibold' : '' }}">{{ number_format($sale->due_amount, 2) }}</td>
                            <td>
                                <span class="badge {{ $sale->status === 'confirmed' ? 'badge-status-active' : 'badge-status-suspended' }}">
                                    {{ __('app.status_' . $sale->status) }}
                                </span>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('sales.show', $sale) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-eye"></i></a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-secondary py-4">{{ __('app.no_records') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">{{ $sales->links() }}</div>
@endsection
