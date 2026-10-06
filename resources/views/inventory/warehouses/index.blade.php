@extends('layouts.app')

@section('title', __('app.warehouses'))
@section('page-title', __('app.warehouses'))

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-bold mb-0">{{ __('app.warehouses') }}</h5>
        @can('inventory.manage')
            <a href="{{ route('inventory.warehouses.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> {{ __('app.add_warehouse') }}</a>
        @endcan
    </div>

    <div class="stat-card p-0">
        <div class="table-responsive-card">
            <table class="table table-hover mb-0 align-middle">
                <thead>
                    <tr>
                        <th>{{ __('app.name') }}</th>
                        <th>{{ __('app.phone') }}</th>
                        <th>{{ __('app.default') }}</th>
                        <th>{{ __('app.status') }}</th>
                        <th class="text-end">{{ __('app.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($warehouses as $w)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $w->name }}</div>
                                @if ($w->name_bn)<div class="small text-secondary">{{ $w->name_bn }}</div>@endif
                            </td>
                            <td>{{ $w->phone ?: '—' }}</td>
                            <td>@if($w->is_default)<span class="badge text-bg-primary">{{ __('app.default') }}</span>@endif</td>
                            <td><span class="badge {{ $w->is_active ? 'badge-status-active' : 'badge-status-inactive' }}">{{ $w->is_active ? __('app.status_active') : __('app.status_inactive') }}</span></td>
                            <td class="text-end">
                                @can('inventory.manage')
                                    <a href="{{ route('inventory.warehouses.edit', $w) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
                                    <form action="{{ route('inventory.warehouses.destroy', $w) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('app.confirm_delete') }}');">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-secondary py-4">{{ __('app.no_records') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">{{ $warehouses->links() }}</div>
@endsection
