@extends('layouts.app')

@section('title', $expense->exists ? __('app.edit_expense') : __('app.add_expense'))
@section('page-title', $expense->exists ? __('app.edit_expense') : __('app.add_expense'))

@section('content')
    <div class="stat-card" style="max-width:560px;">
        <form method="POST" action="{{ $expense->exists ? route('accounting.expenses.update', $expense) : route('accounting.expenses.store') }}">
            @csrf
            @if ($expense->exists) @method('PUT') @endif

            <div class="mb-3">
                <label class="form-label">{{ __('app.account_head') }}</label>
                <select name="account_head_id" class="form-select @error('account_head_id') is-invalid @enderror" required>
                    <option value="">{{ __('app.select') }}</option>
                    @foreach ($heads as $h)
                        <option value="{{ $h->id }}" {{ (int) old('account_head_id', $expense->account_head_id) === $h->id ? 'selected' : '' }}>{{ $h->displayName() }}</option>
                    @endforeach
                </select>
                @error('account_head_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label">{{ __('app.date') }}</label>
                    <input type="date" name="expense_date" class="form-control @error('expense_date') is-invalid @enderror" value="{{ old('expense_date', optional($expense->expense_date)->format('Y-m-d') ?? now()->format('Y-m-d')) }}" required>
                    @error('expense_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">{{ __('app.amount') }}</label>
                    <input type="number" step="0.01" min="0.01" name="amount" class="form-control @error('amount') is-invalid @enderror" value="{{ old('amount', $expense->amount) }}" required>
                    @error('amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">{{ __('app.paid_to') }} ({{ __('app.optional') }})</label>
                <input type="text" name="paid_to" class="form-control" value="{{ old('paid_to', $expense->paid_to) }}">
            </div>

            <div class="mb-4">
                <label class="form-label">{{ __('app.note') }}</label>
                <textarea name="note" class="form-control" rows="2">{{ old('note', $expense->note) }}</textarea>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> {{ __('app.save') }}</button>
                <a href="{{ route('accounting.expenses.index') }}" class="btn btn-outline-secondary">{{ __('app.cancel') }}</a>
            </div>
        </form>
    </div>
@endsection
