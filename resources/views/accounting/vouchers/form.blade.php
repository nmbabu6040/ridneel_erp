@extends('layouts.app')

@section('title', __('app.new_voucher'))
@section('page-title', __('app.new_voucher'))

@section('content')
    <div class="stat-card" style="max-width:520px;">
        @if ($errors->any())
            <div class="alert alert-danger small">
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('accounting.vouchers.store') }}">
            @csrf

            <div class="mb-3">
                <label class="form-label">{{ __('app.direction') }}</label>
                <div class="btn-group w-100" role="group">
                    <input type="radio" class="btn-check" name="direction" id="dir-receipt" value="receipt" checked>
                    <label class="btn btn-outline-success" for="dir-receipt"><i class="bi bi-arrow-down-circle"></i> {{ __('app.receipt_in') }}</label>

                    <input type="radio" class="btn-check" name="direction" id="dir-payment" value="payment">
                    <label class="btn btn-outline-danger" for="dir-payment"><i class="bi bi-arrow-up-circle"></i> {{ __('app.payment_out') }}</label>
                </div>
                <div class="form-text">{{ __('app.voucher_direction_hint') }}</div>
            </div>

            <div class="mb-3">
                <label class="form-label">{{ __('app.partner') }}</label>
                <select name="partner_id" class="form-select @error('partner_id') is-invalid @enderror" required>
                    <option value="">{{ __('app.select') }}</option>
                    @foreach ($partners as $p)
                        <option value="{{ $p->id }}" {{ old('partner_id') == $p->id ? 'selected' : '' }}>
                            [{{ $p->typeLabel() }}] {{ $p->name }} ({{ $p->business_name }})
                        </option>
                    @endforeach
                </select>
                @error('partner_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">{{ __('app.amount') }}</label>
                <input type="number" step="0.01" min="0.01" name="amount" class="form-control @error('amount') is-invalid @enderror" value="{{ old('amount') }}" required>
                @error('amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="mb-4">
                <label class="form-label">{{ __('app.note') }}</label>
                <textarea name="note" class="form-control" rows="2">{{ old('note') }}</textarea>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> {{ __('app.save_voucher') }}</button>
                <a href="{{ route('accounting.ledger.index') }}" class="btn btn-outline-secondary">{{ __('app.cancel') }}</a>
            </div>
        </form>
    </div>
@endsection
