@extends('layouts.app')

@section('title', $accountHead->exists ? __('app.edit_account_head') : __('app.add_account_head'))
@section('page-title', $accountHead->exists ? __('app.edit_account_head') : __('app.add_account_head'))

@section('content')
    <div class="stat-card" style="max-width:560px;">
        <form method="POST" action="{{ $accountHead->exists ? route('accounting.account-heads.update', $accountHead) : route('accounting.account-heads.store') }}">
            @csrf
            @if ($accountHead->exists) @method('PUT') @endif

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label">{{ __('app.name') }} (English)</label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $accountHead->name) }}" required>
                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">{{ __('app.name') }} (বাংলা)</label>
                    <input type="text" name="name_bn" class="form-control" value="{{ old('name_bn', $accountHead->name_bn) }}">
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label">{{ __('app.code') }}</label>
                    <input type="text" name="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code', $accountHead->code) }}" required>
                    @error('code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">{{ __('app.account_type') }}</label>
                    <select name="type" class="form-select @error('type') is-invalid @enderror" required>
                        @foreach (['asset', 'liability', 'income', 'expense', 'equity'] as $t)
                            <option value="{{ $t }}" {{ old('type', $accountHead->type) === $t ? 'selected' : '' }}>{{ ucfirst($t) }}</option>
                        @endforeach
                    </select>
                    @error('type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="mb-4 form-check form-switch">
                <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1" {{ old('is_active', $accountHead->exists ? $accountHead->is_active : true) ? 'checked' : '' }}>
                <label class="form-check-label" for="is_active">{{ __('app.status_active') }}</label>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> {{ __('app.save') }}</button>
                <a href="{{ route('accounting.account-heads.index') }}" class="btn btn-outline-secondary">{{ __('app.cancel') }}</a>
            </div>
        </form>
    </div>
@endsection
