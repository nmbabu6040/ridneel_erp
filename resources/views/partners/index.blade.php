@extends('layouts.app')

@section('title', __('app.partners'))
@section('page-title', __('app.partners'))

@section('content')
    <ul class="nav nav-pills mb-3">
        @foreach (['dealer' => __('app.dealers'), 'retailer' => __('app.retailers'), 'mechanic' => __('app.mechanics'), 'supplier' => __('app.suppliers')] as $t => $label)
            <li class="nav-item">
                <a class="nav-link {{ $type === $t ? 'active' : '' }}" href="{{ route('partners.index', ['type' => $t]) }}">{{ $label }}</a>
            </li>
        @endforeach
    </ul>

    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <form method="GET" class="d-flex">
            <input type="hidden" name="type" value="{{ $type }}">
            <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="{{ __('app.search_placeholder') }}">
        </form>
        @can('partners.manage')
            <a href="{{ route('partners.create', ['type' => $type]) }}" class="btn btn-primary btn-sm text-nowrap">
                <i class="bi bi-plus-lg"></i> {{ __('app.add') }} {{ __('app.' . $type) }}
            </a>
        @endcan
    </div>

    <div class="stat-card p-0">
        <div class="table-responsive-card">
            <table class="table table-hover mb-0 align-middle">
                <thead>
                    <tr>
                        <th>{{ __('app.name') }}</th>
                        <th>{{ __('app.phone') }}</th>
                        <th>{{ __('app.territory') }}</th>
                        @if ($type === 'retailer')<th>{{ __('app.linked_dealer') }}</th>@endif
                        <th>{{ __('app.balance') }}</th>
                        <th>{{ __('app.status') }}</th>
                        <th class="text-end">{{ __('app.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($partners as $p)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $p->name }}</div>
                                @if ($p->business_name)<div class="small text-secondary">{{ $p->business_name }}</div>@endif
                            </td>
                            <td>{{ $p->phone }}</td>
                            <td>{{ $p->territory ?: '—' }}</td>
                            @if ($type === 'retailer')<td>{{ $p->linkedDealer?->name ?: '—' }}</td>@endif
                            <td class="{{ $p->opening_balance > 0 ? 'text-danger' : 'text-success' }}">{{ number_format($p->opening_balance, 2) }}</td>
                            <td><span class="badge {{ $p->is_active ? 'badge-status-active' : 'badge-status-inactive' }}">{{ $p->is_active ? __('app.status_active') : __('app.status_inactive') }}</span></td>
                            <td class="text-end">
                                @can('partners.manage')
                                    <a href="{{ route('partners.edit', $p) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
                                    <form action="{{ route('partners.destroy', $p) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('app.confirm_delete') }}');">
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
    <div class="mt-3">{{ $partners->links() }}</div>
@endsection
