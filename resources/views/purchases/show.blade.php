@extends('layouts.app')

@section('title', $purchase->invoice_no)
@section('page-title', __('app.purchase') . ' ' . $purchase->invoice_no)

@section('content')
    <div class="d-flex justify-content-between align-items-start mb-3 flex-wrap gap-2 no-print">
        <div>
            <span class="badge {{ $purchase->status === 'confirmed' ? 'badge-status-active' : 'badge-status-suspended' }}">
                {{ __('app.status_' . $purchase->status) }}
            </span>
        </div>
        <div class="d-flex gap-2">
            <button onclick="window.print()" class="btn btn-outline-secondary btn-sm"><i class="bi bi-printer"></i> {{ __('app.print') }}</button>
            @if (!$purchase->isCancelled())
                @can('accounting.manage')
                    <form method="POST" action="{{ route('purchases.cancel', $purchase) }}" onsubmit="return confirm('{{ __('app.confirm_cancel_purchase') }}');">
                        @csrf
                        <button type="submit" class="btn btn-outline-danger btn-sm"><i class="bi bi-x-circle"></i> {{ __('app.cancel_purchase') }}</button>
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
                <div><strong>{{ __('app.invoice_no') }}:</strong> {{ $purchase->invoice_no }}</div>
                @if ($purchase->supplier_invoice_no)
                    <div><strong>{{ __('app.supplier_invoice_no') }}:</strong> {{ $purchase->supplier_invoice_no }}</div>
                @endif
                <div><strong>{{ __('app.date') }}:</strong> {{ $purchase->purchase_date->format('d M Y') }}</div>
                <div><strong>{{ __('app.warehouse') }}:</strong> {{ $purchase->warehouse?->name }}</div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-6">
                <div class="text-secondary small mb-1">{{ __('app.supplier') }}</div>
                <div class="fw-semibold">{{ $purchase->supplier?->name }}</div>
                <div class="small">{{ $purchase->supplier?->business_name }}</div>
                <div class="small">{{ $purchase->supplier?->phone }}</div>
            </div>
        </div>

        <div class="table-responsive-card mb-3">
            <table class="table table-bordered mb-0">
                <thead>
                    <tr>
                        <th>{{ __('app.product') }}</th>
                        <th class="text-end">{{ __('app.quantity') }}</th>
                        <th class="text-end">{{ __('app.unit_cost') }}</th>
                        <th class="text-end">{{ __('app.discount') }}</th>
                        <th class="text-end">{{ __('app.subtotal') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($purchase->items as $item)
                        <tr>
                            <td>{{ $item->product?->name }}</td>
                            <td class="text-end">{{ $item->quantity }}</td>
                            <td class="text-end">{{ number_format($item->unit_cost, 2) }}</td>
                            <td class="text-end">{{ number_format($item->discount, 2) }}</td>
                            <td class="text-end">{{ number_format($item->subtotal, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="row justify-content-end">
            <div class="col-md-4">
                <div class="d-flex justify-content-between"><span>{{ __('app.subtotal') }}</span><span>{{ number_format($purchase->subtotal, 2) }}</span></div>
                <div class="d-flex justify-content-between"><span>{{ __('app.overall_discount') }}</span><span>{{ number_format($purchase->discount, 2) }}</span></div>
                <div class="d-flex justify-content-between fs-5"><strong>{{ __('app.total') }}</strong><strong>{{ number_format($purchase->total, 2) }}</strong></div>
                <div class="d-flex justify-content-between"><span>{{ __('app.paid_amount') }}</span><span>{{ number_format($purchase->paid_amount, 2) }}</span></div>
                <div class="d-flex justify-content-between text-danger"><strong>{{ __('app.due') }}</strong><strong>{{ number_format($purchase->due_amount, 2) }}</strong></div>
            </div>
        </div>

        @if ($purchase->note)
            <div class="mt-3 small text-secondary"><strong>{{ __('app.note') }}:</strong> {{ $purchase->note }}</div>
        @endif

        <div class="mt-4 small text-secondary no-print">{{ __('app.created_by') }}: {{ $purchase->createdBy?->name }} · {{ $purchase->created_at->format('d M Y, h:i A') }}</div>
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
