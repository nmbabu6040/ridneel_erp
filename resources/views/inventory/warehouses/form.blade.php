@extends('layouts.app')

@section('title', $warehouse->exists ? __('app.edit_warehouse') : __('app.add_warehouse'))
@section('page-title', $warehouse->exists ? __('app.edit_warehouse') : __('app.add_warehouse'))

@section('content')
    <div class="stat-card" style="max-width:560px;">
        <form method="POST" action="{{ $warehouse->exists ? route('inventory.warehouses.update', $warehouse) : route('inventory.warehouses.store') }}">
            @csrf
            @if ($warehouse->exists) @method('PUT') @endif

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label">{{ __('app.name') }} (English)</label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $warehouse->name) }}" required>
                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">{{ __('app.name') }} (বাংলা)</label>
                    <input type="text" name="name_bn" class="form-control" value="{{ old('name_bn', $warehouse->name_bn) }}">
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">{{ __('app.address') }}</label>
                <input type="text" name="address" class="form-control" value="{{ old('address', $warehouse->address) }}">
            </div>
            <div class="mb-3">
                <label class="form-label">{{ __('app.phone') }}</label>
                <input type="text" name="phone" class="form-control" value="{{ old('phone', $warehouse->phone) }}">
            </div>

            <div class="mb-2 form-check form-switch">
                <input type="checkbox" class="form-check-input" id="is_default" name="is_default" value="1" {{ old('is_default', $warehouse->is_default) ? 'checked' : '' }}>
                <label class="form-check-label" for="is_default">{{ __('app.set_default_warehouse') }}</label>
            </div>
            <div class="mb-4 form-check form-switch">
                <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1" {{ old('is_active', $warehouse->exists ? $warehouse->is_active : true) ? 'checked' : '' }}>
                <label class="form-check-label" for="is_active">{{ __('app.status_active') }}</label>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> {{ __('app.save') }}</button>
                <a href="{{ route('inventory.warehouses.index') }}" class="btn btn-outline-secondary">{{ __('app.cancel') }}</a>
            </div>
        </form>
    </div>
@endsection
