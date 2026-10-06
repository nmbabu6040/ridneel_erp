@extends('layouts.app')

@section('title', __('app.partner_ledger'))
@section('page-title', __('app.partner_ledger'))

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <ul class="nav nav-pills">
            @foreach (['dealer' => __('app.dealers'), 'retailer' => __('app.retailers'), 'mechanic' => __('app.mechanics'), 'supplier' => __('app.suppliers')] as $t => $label)
                <li class="nav-item">
                    <a class="nav-link {{ $type === $t ? 'active' : '' }}" href="{{ route('accounting.ledger.index', ['type' => $t]) }}">{{ $label }}</a>
                </li>
            @endforeach
        </ul>
        @can('accounting.manage')
            <a href="{{ route('accounting.vouchers.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> {{ __('app.new_voucher') }}</a>
        @endcan
    </div>

    <div class="stat-card p-0">
        <div class="table-responsive-card">
            <table class="table table-hover mb-0 align-middle">
                <thead>
                    <tr>
                        <th>{{ __('app.name') }}</th>
                        <th>{{ __('app.phone') }}</th>
                        <th class="text-end">{{ __('app.balance') }}</th>
                        <th class="text-end">{{ __('app.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($partners as $p)
                        <tr>
                            <td>{{ $p->name }} <span class="small text-secondary">{{ $p->business_name }}</span></td>
                            <td>{{ $p->phone }}</td>
                            <td class="text-end fw-semibold {{ $p->currentBalance() > 0 ? 'text-danger' : ($p->currentBalance() < 0 ? 'text-warning' : '') }}">
                                {{ number_format($p->currentBalance(), 2) }}
                            </td>
                            <td class="text-end">
                                <a href="{{ route('accounting.ledger.show', $p) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-journal-text"></i> {{ __('app.statement') }}</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-secondary py-4">{{ __('app.no_records') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
