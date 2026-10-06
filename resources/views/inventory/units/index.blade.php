@extends('layouts.app')

@section('title', __('app.units'))
@section('page-title', __('app.units'))

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-bold mb-0">{{ __('app.units') }}</h5>
        @can('inventory.manage')
            <a href="{{ route('inventory.units.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> {{ __('app.add_unit') }}</a>
        @endcan
    </div>

    <div class="stat-card p-0">
        <div class="table-responsive-card">
            <table class="table table-hover mb-0 align-middle">
                <thead>
                    <tr>
                        <th>{{ __('app.name') }}</th>
                        <th>{{ __('app.short_code') }}</th>
                        <th>{{ __('app.products') }}</th>
                        <th class="text-end">{{ __('app.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($units as $unit)
                        <tr>
                            <td>{{ $unit->name }} @if($unit->name_bn)<span class="text-secondary small">/ {{ $unit->name_bn }}</span>@endif</td>
                            <td><code>{{ $unit->short_code }}</code></td>
                            <td>{{ $unit->products_count }}</td>
                            <td class="text-end">
                                @can('inventory.manage')
                                    <a href="{{ route('inventory.units.edit', $unit) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
                                    <form action="{{ route('inventory.units.destroy', $unit) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('app.confirm_delete') }}');">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-secondary py-4">{{ __('app.no_records') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">{{ $units->links() }}</div>
@endsection
