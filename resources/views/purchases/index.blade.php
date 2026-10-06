@extends('layouts.app')

@section('title', __('app.purchases'))
@section('page-title', __('app.purchases'))

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <form method="GET" class="d-flex gap-2">
            <select name="supplier_id" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">{{ __('app.all_suppliers') }}</option>
                @foreach ($suppliers as $s)
                    <option value="{{ $s->id }}" {{ (string) request('supplier_id') === (string) $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                @endforeach
            </select>
            <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">{{ __('app.all_status') }}</option>
                <option value="confirmed" {{ request('status') === 'confirmed' ? 'selected' : '' }}>{{ __('app.status_confirmed') }}</option>
                <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>{{ __('app.status_cancelled') }}</option>
            </select>
        </form>
        @can('accounting.manage')
            <a href="{{ route('purchases.create') }}" class="btn btn-primary btn-sm text-nowrap"><i class="bi bi-plus-lg"></i> {{ __('app.new_purchase') }}</a>
        @endcan
    </div>

    <div class="stat-card p-0">
        <div class="table-responsive-card">
            <table class="table table-hover mb-0 align-middle">
                <thead>
                    <tr>
                        <th>{{ __('app.invoice_no') }}</th>
                        <th>{{ __('app.date') }}</th>
                        <th>{{ __('app.supplier') }}</th>
                        <th class="text-end">{{ __('app.total') }}</th>
                        <th class="text-end">{{ __('app.paid') }}</th>
                        <th class="text-end">{{ __('app.due') }}</th>
                        <th>{{ __('app.status') }}</th>
                        <th class="text-end">{{ __('app.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($purchases as $p)
                        <tr>
                            <td><a href="{{ route('purchases.show', $p) }}"><code>{{ $p->invoice_no }}</code></a></td>
                            <td>{{ $p->purchase_date->format('d M Y') }}</td>
                            <td>{{ $p->supplier?->name }}</td>
                            <td class="text-end">{{ number_format($p->total, 2) }}</td>
                            <td class="text-end">{{ number_format($p->paid_amount, 2) }}</td>
                            <td class="text-end {{ $p->due_amount > 0 ? 'text-danger fw-semibold' : '' }}">{{ number_format($p->due_amount, 2) }}</td>
                            <td><span class="badge {{ $p->status === 'confirmed' ? 'badge-status-active' : 'badge-status-suspended' }}">{{ __('app.status_' . $p->status) }}</span></td>
                            <td class="text-end"><a href="{{ route('purchases.show', $p) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-eye"></i></a></td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-secondary py-4">{{ __('app.no_records') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">{{ $purchases->links() }}</div>
@endsection
