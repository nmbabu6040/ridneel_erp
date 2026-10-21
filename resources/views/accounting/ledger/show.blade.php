@extends('layouts.app')

@section('title', $partner->name)
@section('page-title', __('app.partner_ledger'))

@section('content')
    <div class="stat-card mb-3">
        <div class="row">
            <div class="col-md-6">
                <h5 class="fw-bold mb-1">{{ $partner->name }}</h5>
                <div class="small text-secondary">{{ $partner->business_name }} · {{ $partner->typeLabel() }}</div>
                <div class="small">{{ $partner->phone }}</div>
                <a href="{{ route('accounting.ledger.export', $partner) }}" class="btn btn-outline-secondary btn-sm mt-2"><i class="bi bi-file-earmark-spreadsheet"></i> {{ __('app.export_csv') }}</a>
            </div>
            <div class="col-md-6 text-md-end">
                <div class="small text-secondary">{{ __('app.opening_balance') }}: {{ number_format($openingBalance, 2) }}</div>
                <div class="fs-4 fw-bold {{ $currentBalance > 0 ? 'text-danger' : ($currentBalance < 0 ? 'text-warning' : 'text-success') }}">
                    {{ number_format($currentBalance, 2) }}
                </div>
                <div class="small text-secondary">
                    {{ $currentBalance > 0 ? __('app.partner_owes_us') : ($currentBalance < 0 ? __('app.we_owe_partner') : __('app.settled')) }}
                </div>
            </div>
        </div>
    </div>

    <div class="stat-card p-0">
        <div class="table-responsive-card">
            <table class="table table-sm mb-0 align-middle">
                <thead>
                    <tr>
                        <th>{{ __('app.date') }}</th>
                        <th>{{ __('app.type') }}</th>
                        <th>{{ __('app.note') }}</th>
                        <th class="text-end">{{ __('app.amount') }}</th>
                        <th class="text-end">{{ __('app.running_balance') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rows as $tx)
                        <tr>
                            <td class="text-nowrap">{{ $tx->created_at->format('d M Y, h:i A') }}</td>
                            <td><span class="badge text-bg-secondary">{{ $tx->typeLabel() }}</span></td>
                            <td class="small">{{ $tx->note }}</td>
                            <td class="text-end {{ $tx->amount >= 0 ? 'text-success' : 'text-danger' }}">
                                {{ $tx->amount >= 0 ? '+' : '' }}{{ number_format($tx->amount, 2) }}
                            </td>
                            <td class="text-end fw-semibold">{{ number_format($tx->running_balance, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-secondary py-4">{{ __('app.no_records') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
