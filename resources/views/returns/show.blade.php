@extends('layouts.app')

@section('title', $return->return_no)
@section('page-title', $return->typeLabel() . ' ' . $return->return_no)

@section('content')
    <div class="d-flex justify-content-between align-items-start mb-3 flex-wrap gap-2 no-print">
        <div>
            <span class="badge {{ $return->status === 'confirmed' ? 'badge-status-active' : 'badge-status-suspended' }}">{{ __('app.status_' . $return->status) }}</span>
            <span class="badge text-bg-secondary ms-1">{{ $return->typeLabel() }}</span>
        </div>
        <div class="d-flex gap-2">
            <button onclick="window.print()" class="btn btn-outline-secondary btn-sm"><i class="bi bi-printer"></i> {{ __('app.print') }}</button>
            @if (!$return->isCancelled())
                @can('returns.manage')
                    <form method="POST" action="{{ route('returns.cancel', $return) }}" onsubmit="return confirm('{{ __('app.confirm_cancel_return') }}');">
                        @csrf
                        <button type="submit" class="btn btn-outline-danger btn-sm"><i class="bi bi-x-circle"></i> {{ __('app.cancel_return') }}</button>
                    </form>
                @endcan
            @endif
        </div>
    </div>

    <div class="stat-card">
        <div class="row mb-4">
            <div class="col-md-6">
                <h5 class="fw-bold mb-1">{{ __('app.app_name') }}</h5>
                <div class="small text-secondary">Ridneel Trade International</div>
            </div>
            <div class="col-md-6 text-md-end">
                <div><strong>{{ __('app.return_no') }}:</strong> {{ $return->return_no }}</div>
                <div><strong>{{ __('app.date') }}:</strong> {{ $return->return_date->format('d M Y') }}</div>
                <div><strong>{{ __('app.warehouse') }}:</strong> {{ $return->warehouse?->name }}</div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-6">
                <div class="text-secondary small mb-1">{{ __('app.partner') }}</div>
                <div class="fw-semibold">{{ $return->partner?->name }}</div>
                <div class="small">{{ $return->partner?->phone }}</div>
            </div>
            <div class="col-md-6 text-md-end">
                @if ($return->reason)<div><strong>{{ __('app.reason') }}:</strong> {{ $return->reason }}</div>@endif
            </div>
        </div>

        <div class="table-responsive-card mb-3">
            <table class="table table-bordered mb-0">
                <thead>
                    <tr>
                        <th>{{ __('app.product') }}</th>
                        <th class="text-end">{{ __('app.quantity') }}</th>
                        <th class="text-end">{{ __('app.unit_price') }}</th>
                        <th class="text-end">{{ __('app.subtotal') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($return->items as $item)
                        <tr>
                            <td>{{ $item->product?->name }}</td>
                            <td class="text-end">{{ $item->quantity }}</td>
                            <td class="text-end">{{ number_format($item->unit_price, 2) }}</td>
                            <td class="text-end">{{ number_format($item->subtotal, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3" class="text-end fw-bold">{{ __('app.total') }}</td>
                        <td class="text-end fw-bold">{{ number_format($return->total, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        @if ($return->note)
            <div class="small text-secondary"><strong>{{ __('app.note') }}:</strong> {{ $return->note }}</div>
        @endif

        <div class="mt-4 small text-secondary no-print">{{ __('app.created_by') }}: {{ $return->createdBy?->name }} · {{ $return->created_at->format('d M Y, h:i A') }}</div>
    </div>
@endsection

@push('styles')
<style>
    @media print {
        .sidebar, .topbar, .no-print { display: none !important; }
        .main-content { margin-left: 0 !important; }
        .page-body { padding: 0 !important; }
    }
</style>
@endpush
