@extends('layouts.app')

@section('title', $sale->invoice_no)
@section('page-title', __('app.invoice') . ' ' . $sale->invoice_no)

@section('content')
    <div class="d-flex justify-content-between align-items-start mb-3 flex-wrap gap-2 no-print">
        <div>
            <span class="badge {{ $sale->status === 'confirmed' ? 'badge-status-active' : 'badge-status-suspended' }}">
                {{ __('app.status_' . $sale->status) }}
            </span>
            <span class="badge text-bg-secondary ms-1">{{ $sale->typeLabel() }}</span>
        </div>
        <div class="d-flex gap-2">
            <button onclick="window.print()" class="btn btn-outline-secondary btn-sm"><i class="bi bi-printer"></i> {{ __('app.print') }}</button>
            @if (!$sale->isCancelled())
                @can(($sale->type === 'primary' ? 'sales.primary' : 'sales.secondary'))
                    <form method="POST" action="{{ route('sales.cancel', $sale) }}" onsubmit="return confirm('{{ __('app.confirm_cancel_invoice') }}');">
                        @csrf
                        <button type="submit" class="btn btn-outline-danger btn-sm"><i class="bi bi-x-circle"></i> {{ __('app.cancel_invoice') }}</button>
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
                <div><strong>{{ __('app.invoice_no') }}:</strong> {{ $sale->invoice_no }}</div>
                <div><strong>{{ __('app.date') }}:</strong> {{ $sale->sale_date->format('d M Y') }}</div>
                <div><strong>{{ __('app.warehouse') }}:</strong> {{ $sale->warehouse?->name }}</div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-6">
                <div class="text-secondary small mb-1">{{ $sale->type === 'primary' ? __('app.dealer') : __('app.retailer') }}</div>
                <div class="fw-semibold">{{ $sale->partner?->name }}</div>
                <div class="small">{{ $sale->partner?->business_name }}</div>
                <div class="small">{{ $sale->partner?->phone }}</div>
                <div class="small">{{ $sale->partner?->address }}</div>
            </div>
        </div>

        <div class="table-responsive-card mb-3">
            <table class="table table-bordered mb-0">
                <thead>
                    <tr>
                        <th>{{ __('app.product') }}</th>
                        <th class="text-end">{{ __('app.quantity') }}</th>
                        <th class="text-end">{{ __('app.unit_price') }}</th>
                        <th class="text-end">{{ __('app.discount') }}</th>
                        <th class="text-end">{{ __('app.subtotal') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($sale->items as $item)
                        <tr>
                            <td>{{ $item->product?->name }}</td>
                            <td class="text-end">{{ $item->quantity }}</td>
                            <td class="text-end">{{ number_format($item->unit_price, 2) }}</td>
                            <td class="text-end">{{ number_format($item->discount, 2) }}</td>
                            <td class="text-end">{{ number_format($item->subtotal, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="row justify-content-end">
            <div class="col-md-4">
                <div class="d-flex justify-content-between"><span>{{ __('app.subtotal') }}</span><span>{{ number_format($sale->subtotal, 2) }}</span></div>
                <div class="d-flex justify-content-between"><span>{{ __('app.overall_discount') }}</span><span>{{ number_format($sale->discount, 2) }}</span></div>
                <div class="d-flex justify-content-between fs-5"><strong>{{ __('app.total') }}</strong><strong>{{ number_format($sale->total, 2) }}</strong></div>
                <div class="d-flex justify-content-between"><span>{{ __('app.paid_amount') }}</span><span>{{ number_format($sale->paid_amount, 2) }}</span></div>
                <div class="d-flex justify-content-between text-danger"><strong>{{ __('app.due') }}</strong><strong>{{ number_format($sale->due_amount, 2) }}</strong></div>
            </div>
        </div>

        @if ($sale->note)
            <div class="mt-3 small text-secondary"><strong>{{ __('app.note') }}:</strong> {{ $sale->note }}</div>
        @endif

        <div class="mt-4 small text-secondary no-print">{{ __('app.created_by') }}: {{ $sale->createdBy?->name }} · {{ $sale->created_at->format('d M Y, h:i A') }}</div>
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
