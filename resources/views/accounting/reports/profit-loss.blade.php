@extends('layouts.app')

@section('title', __('app.profit_loss'))
@section('page-title', __('app.profit_loss'))

@section('content')
    <form method="GET" class="row g-2 mb-3">
        <div class="col-sm-4 col-md-3">
            <label class="form-label small mb-1">{{ __('app.from') }}</label>
            <input type="date" name="from" value="{{ $from }}" class="form-control form-control-sm">
        </div>
        <div class="col-sm-4 col-md-3">
            <label class="form-label small mb-1">{{ __('app.to') }}</label>
            <input type="date" name="to" value="{{ $to }}" class="form-control form-control-sm">
        </div>
        <div class="col-sm-4 col-md-3">
            <label class="form-label small mb-1">{{ __('app.sales_type') }}</label>
            <select name="type" class="form-select form-select-sm">
                <option value="all" {{ $type === 'all' ? 'selected' : '' }}>{{ __('app.all_types') }}</option>
                <option value="primary" {{ $type === 'primary' ? 'selected' : '' }}>{{ __('app.primary_sales') }}</option>
                <option value="secondary" {{ $type === 'secondary' ? 'selected' : '' }}>{{ __('app.secondary_sales') }}</option>
            </select>
        </div>
        <div class="col-sm-4 col-md-3 d-flex align-items-end">
            <button type="submit" class="btn btn-primary btn-sm w-100">{{ __('app.apply') }}</button>
        </div>
    </form>

    <div class="alert alert-light border small mb-3">
        <i class="bi bi-info-circle me-1"></i> {{ __('app.pl_cogs_note') }}
    </div>

    <div class="stat-card" style="max-width:560px;">
        <div class="d-flex justify-content-between py-2 border-bottom">
            <span>{{ __('app.revenue') }}</span>
            <strong>{{ number_format($revenue, 2) }}</strong>
        </div>
        <div class="d-flex justify-content-between py-2 border-bottom">
            <span>{{ __('app.cogs') }}</span>
            <strong class="text-danger">({{ number_format($cogs, 2) }})</strong>
        </div>
        <div class="d-flex justify-content-between py-2 border-bottom fw-bold">
            <span>{{ __('app.gross_profit') }}</span>
            <strong class="{{ $grossProfit >= 0 ? 'text-success' : 'text-danger' }}">{{ number_format($grossProfit, 2) }}</strong>
        </div>
        <div class="d-flex justify-content-between py-2 border-bottom">
            <span>{{ __('app.total_expenses') }}</span>
            <strong class="text-danger">({{ number_format($expenses, 2) }})</strong>
        </div>
        <div class="d-flex justify-content-between py-3 fs-5 fw-bold">
            <span>{{ __('app.net_profit') }}</span>
            <strong class="{{ $netProfit >= 0 ? 'text-success' : 'text-danger' }}">{{ number_format($netProfit, 2) }}</strong>
        </div>
    </div>

    @if ($expenseBreakdown->count())
        <div class="stat-card p-0 mt-3" style="max-width:560px;">
            <div class="p-3 border-bottom fw-bold small">{{ __('app.expense_breakdown') }}</div>
            <table class="table table-sm mb-0">
                <tbody>
                    @foreach ($expenseBreakdown as $row)
                        <tr>
                            <td>{{ $row->accountHead?->displayName() }}</td>
                            <td class="text-end">{{ number_format($row->total, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
@endsection
