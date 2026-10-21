@extends('layouts.app')

@section('title', __('app.expenses'))
@section('page-title', __('app.expenses'))

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <h5 class="fw-bold mb-0">{{ __('app.expenses') }}</h5>
        <div class="d-flex gap-2">
            <a href="{{ route('accounting.expenses.export', request()->query()) }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-file-earmark-spreadsheet"></i> {{ __('app.export_csv') }}</a>
            @can('accounting.manage')
                <a href="{{ route('accounting.expenses.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> {{ __('app.add_expense') }}</a>
            @endcan
        </div>
    </div>

    <form method="GET" class="row g-2 mb-3">
        <div class="col-sm-4 col-md-3"><input type="date" name="from" value="{{ request('from') }}" class="form-control form-control-sm" onchange="this.form.submit()"></div>
        <div class="col-sm-4 col-md-3"><input type="date" name="to" value="{{ request('to') }}" class="form-control form-control-sm" onchange="this.form.submit()"></div>
        <div class="col-sm-4 col-md-3">
            <select name="account_head_id" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">{{ __('app.all_accounts') }}</option>
                @foreach ($heads as $h)
                    <option value="{{ $h->id }}" {{ (string) request('account_head_id') === (string) $h->id ? 'selected' : '' }}>{{ $h->displayName() }}</option>
                @endforeach
            </select>
        </div>
    </form>

    <div class="stat-card p-0">
        <div class="table-responsive-card">
            <table class="table table-hover mb-0 align-middle">
                <thead>
                    <tr>
                        <th>{{ __('app.date') }}</th>
                        <th>{{ __('app.account_head') }}</th>
                        <th>{{ __('app.paid_to') }}</th>
                        <th class="text-end">{{ __('app.amount') }}</th>
                        <th>{{ __('app.by') }}</th>
                        @can('accounting.manage')<th class="text-end">{{ __('app.actions') }}</th>@endcan
                    </tr>
                </thead>
                <tbody>
                    @forelse ($expenses as $exp)
                        <tr>
                            <td>{{ $exp->expense_date->format('d M Y') }}</td>
                            <td>{{ $exp->accountHead?->displayName() }}</td>
                            <td>{{ $exp->paid_to ?: '—' }}</td>
                            <td class="text-end fw-semibold">{{ number_format($exp->amount, 2) }}</td>
                            <td>{{ $exp->createdBy?->name ?: '—' }}</td>
                            @can('accounting.manage')
                                <td class="text-end">
                                    <a href="{{ route('accounting.expenses.edit', $exp) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
                                    <form action="{{ route('accounting.expenses.destroy', $exp) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('app.confirm_delete') }}');">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                    </form>
                                </td>
                            @endcan
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-secondary py-4">{{ __('app.no_records') }}</td></tr>
                    @endforelse
                </tbody>
                @if ($expenses->count())
                    <tfoot>
                        <tr>
                            <td colspan="3" class="text-end fw-bold">{{ __('app.total') }}</td>
                            <td class="text-end fw-bold">{{ number_format($total, 2) }}</td>
                            <td colspan="2"></td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>
    <div class="mt-3">{{ $expenses->links() }}</div>
@endsection
