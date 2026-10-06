@extends('layouts.app')

@section('title', __('app.chart_of_accounts'))
@section('page-title', __('app.chart_of_accounts'))

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-bold mb-0">{{ __('app.chart_of_accounts') }}</h5>
        @can('accounting.manage')
            <a href="{{ route('accounting.account-heads.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> {{ __('app.add_account_head') }}</a>
        @endcan
    </div>

    <div class="stat-card p-0">
        <div class="table-responsive-card">
            <table class="table table-hover mb-0 align-middle">
                <thead>
                    <tr>
                        <th>{{ __('app.code') }}</th>
                        <th>{{ __('app.name') }}</th>
                        <th>{{ __('app.account_type') }}</th>
                        <th>{{ __('app.status') }}</th>
                        <th class="text-end">{{ __('app.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($accountHeads as $head)
                        <tr>
                            <td><code>{{ $head->code }}</code></td>
                            <td>{{ $head->displayName() }}</td>
                            <td><span class="badge text-bg-secondary">{{ $head->typeLabel() }}</span></td>
                            <td><span class="badge {{ $head->is_active ? 'badge-status-active' : 'badge-status-inactive' }}">{{ $head->is_active ? __('app.status_active') : __('app.status_inactive') }}</span></td>
                            <td class="text-end">
                                @can('accounting.manage')
                                    <a href="{{ route('accounting.account-heads.edit', $head) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
                                    <form action="{{ route('accounting.account-heads.destroy', $head) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('app.confirm_delete') }}');">
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
    <div class="mt-3">{{ $accountHeads->links() }}</div>
@endsection
