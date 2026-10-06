@extends('layouts.app')

@section('title', $category->exists ? __('app.edit_category') : __('app.add_category'))
@section('page-title', $category->exists ? __('app.edit_category') : __('app.add_category'))

@section('content')
    <div class="stat-card" style="max-width:560px;">
        <form method="POST" action="{{ $category->exists ? route('inventory.categories.update', $category) : route('inventory.categories.store') }}">
            @csrf
            @if ($category->exists) @method('PUT') @endif

            <div class="mb-3">
                <label class="form-label">{{ __('app.name') }} (English)</label>
                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $category->name) }}" required>
                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="mb-3">
                <label class="form-label">{{ __('app.name') }} (বাংলা)</label>
                <input type="text" name="name_bn" class="form-control" value="{{ old('name_bn', $category->name_bn) }}">
            </div>
            <div class="mb-4 form-check form-switch">
                <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1" {{ old('is_active', $category->exists ? $category->is_active : true) ? 'checked' : '' }}>
                <label class="form-check-label" for="is_active">{{ __('app.status_active') }}</label>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> {{ __('app.save') }}</button>
                <a href="{{ route('inventory.categories.index') }}" class="btn btn-outline-secondary">{{ __('app.cancel') }}</a>
            </div>
        </form>
    </div>
@endsection
