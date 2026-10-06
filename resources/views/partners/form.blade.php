@extends('layouts.app')

@section('title', $partner->exists ? __('app.edit_partner') : __('app.add_partner'))
@section('page-title', $partner->exists ? __('app.edit_partner') : __('app.add_partner'))

@section('content')
    <div class="stat-card" style="max-width:640px;">
        <form method="POST" action="{{ $partner->exists ? route('partners.update', $partner) : route('partners.store') }}">
            @csrf
            @if ($partner->exists) @method('PUT') @endif
            <input type="hidden" name="type" value="{{ $type }}">

            <div class="alert alert-light border small mb-3">
                {{ __('app.partner_type_label') }}: <strong>{{ __('app.' . $type) }}</strong>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label">{{ __('app.contact_name') }}</label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $partner->name) }}" required>
                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">{{ __('app.business_name') }}</label>
                    <input type="text" name="business_name" class="form-control" value="{{ old('business_name', $partner->business_name) }}">
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label">{{ __('app.phone') }}</label>
                    <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $partner->phone) }}" required>
                    @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">{{ __('app.email') }} ({{ __('app.optional') }})</label>
                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $partner->email) }}">
                    @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label">{{ __('app.address') }}</label>
                    <input type="text" name="address" class="form-control" value="{{ old('address', $partner->address) }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">{{ __('app.territory') }}</label>
                    <input type="text" name="territory" class="form-control" value="{{ old('territory', $partner->territory) }}" placeholder="{{ __('app.territory_hint') }}">
                </div>
            </div>

            @if ($type === 'retailer')
                <div class="mb-3">
                    <label class="form-label">{{ __('app.linked_dealer') }}</label>
                    <select name="linked_dealer_id" class="form-select @error('linked_dealer_id') is-invalid @enderror" required>
                        <option value="">{{ __('app.select') }}</option>
                        @foreach ($dealers as $d)
                            <option value="{{ $d->id }}" {{ (int) old('linked_dealer_id', $partner->linked_dealer_id) === $d->id ? 'selected' : '' }}>{{ $d->name }} ({{ $d->business_name }})</option>
                        @endforeach
                    </select>
                    @error('linked_dealer_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            @endif

            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label">{{ __('app.opening_balance') }}</label>
                    <input type="number" step="0.01" name="opening_balance" class="form-control" value="{{ old('opening_balance', $partner->opening_balance ?? 0) }}">
                    <div class="form-text">{{ __('app.opening_balance_hint') }}</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">{{ __('app.credit_limit') }}</label>
                    <input type="number" step="0.01" min="0" name="credit_limit" class="form-control" value="{{ old('credit_limit', $partner->credit_limit ?? 0) }}">
                </div>
            </div>

            <div class="mb-4 form-check form-switch">
                <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1" {{ old('is_active', $partner->exists ? $partner->is_active : true) ? 'checked' : '' }}>
                <label class="form-check-label" for="is_active">{{ __('app.status_active') }}</label>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> {{ __('app.save') }}</button>
                <a href="{{ route('partners.index', ['type' => $type]) }}" class="btn btn-outline-secondary">{{ __('app.cancel') }}</a>
            </div>
        </form>
    </div>
@endsection
