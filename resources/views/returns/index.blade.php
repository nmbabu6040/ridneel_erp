@extends('layouts.app')

@section('title', __('app.returns'))
@section('page-title', __('app.returns'))

@section('content')
    <ul class="nav nav-pills mb-3">
        <li class="nav-item">
            <a class="nav-link {{ $type === 'sales_return' ? 'active' : '' }}" href="{{ route('returns.index', ['type' => 'sales_return']) }}">{{ __('app.sales_return') }}</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $type === 'purchase_return' ? 'active' : '' }}" href="{{ route('returns.index', ['type' => 'purchase_return']) }}">{{ __('app.purchase_return') }}</a>
        </li>
    </ul>

    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <form method="GET" class="d-flex">
            <input type="hidden" name="type" value="{{ $type }}">
            <select name="partner_id" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">{{ __('app.all_partners') }}</option>
                @foreach ($partners as $p)
                    <option value="{{ $p->id }}" {{ (string) request('partner_id') === (string) $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                @endforeach
            </select>
        </form>
        @can('returns.manage')
            <a href="{{ route('returns.create', ['type' => $type]) }}" class="btn btn-primary btn-sm text-nowrap"><i class="bi bi-plus-lg"></i> {{ __('app.new_return') }}</a>
        @endcan
    </div>

    <div class="stat-card p-0">
        <div class="table-responsive-card">
            <table class="table table-hover mb-0 align-middle">
                <thead>
                    <tr>
                        <th>{{ __('app.return_no') }}</th>
                        <th>{{ __('app.date') }}</th>
                        <th>{{ __('app.partner') }}</th>
                        <th>{{ __('app.reason') }}</th>
                        <th class="text-end">{{ __('app.total') }}</th>
                        <th>{{ __('app.status') }}</th>
                        <th class="text-end">{{ __('app.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($returns as $r)
                        <tr>
                            <td><a href="{{ route('returns.show', $r) }}"><code>{{ $r->return_no }}</code></a></td>
                            <td>{{ $r->return_date->format('d M Y') }}</td>
                            <td>{{ $r->partner?->name }}</td>
                            <td>{{ $r->reason ?: '—' }}</td>
                            <td class="text-end fw-semibold">{{ number_format($r->total, 2) }}</td>
                            <td><span class="badge {{ $r->status === 'confirmed' ? 'badge-status-active' : 'badge-status-suspended' }}">{{ __('app.status_' . $r->status) }}</span></td>
                            <td class="text-end"><a href="{{ route('returns.show', $r) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-eye"></i></a></td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-secondary py-4">{{ __('app.no_records') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">{{ $returns->links() }}</div>
@endsection
