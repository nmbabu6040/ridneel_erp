@extends('layouts.app')

@section('title', $product->exists ? __('app.edit_product') : __('app.add_product'))
@section('page-title', $product->exists ? __('app.edit_product') : __('app.add_product'))

@section('content')
    <div class="stat-card" style="max-width:720px;">
        <form method="POST" action="{{ $product->exists ? route('inventory.products.update', $product) : route('inventory.products.store') }}">
            @csrf
            @if ($product->exists) @method('PUT') @endif

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label">{{ __('app.name') }} (English)</label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $product->name) }}" required>
                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">{{ __('app.name') }} (বাংলা)</label>
                    <input type="text" name="name_bn" class="form-control" value="{{ old('name_bn', $product->name_bn) }}">
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label">{{ __('app.category') }}</label>
                    <select name="category_id" class="form-select @error('category_id') is-invalid @enderror" required>
                        <option value="">{{ __('app.select') }}</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}" {{ (int) old('category_id', $product->category_id) === $cat->id ? 'selected' : '' }}>{{ $cat->displayName() }}</option>
                        @endforeach
                    </select>
                    @error('category_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">{{ __('app.unit') }}</label>
                    <select name="unit_id" class="form-select @error('unit_id') is-invalid @enderror" required>
                        <option value="">{{ __('app.select') }}</option>
                        @foreach ($units as $unit)
                            <option value="{{ $unit->id }}" {{ (int) old('unit_id', $product->unit_id) === $unit->id ? 'selected' : '' }}>{{ $unit->displayName() }} ({{ $unit->short_code }})</option>
                        @endforeach
                    </select>
                    @error('unit_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label">{{ __('app.sku') }}</label>
                    <input type="text" name="sku" class="form-control @error('sku') is-invalid @enderror" value="{{ old('sku', $product->sku) }}" required>
                    @error('sku') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">{{ __('app.barcode') }}</label>
                    <input type="text" name="barcode" class="form-control @error('barcode') is-invalid @enderror" value="{{ old('barcode', $product->barcode) }}">
                    @error('barcode') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label class="form-label">{{ __('app.purchase_price') }}</label>
                    <input type="number" step="0.01" min="0" name="purchase_price" class="form-control @error('purchase_price') is-invalid @enderror" value="{{ old('purchase_price', $product->purchase_price) }}" required>
                    @error('purchase_price') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">{{ __('app.sale_price') }}</label>
                    <input type="number" step="0.01" min="0" name="sale_price" class="form-control @error('sale_price') is-invalid @enderror" value="{{ old('sale_price', $product->sale_price) }}" required>
                    @error('sale_price') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">{{ __('app.reorder_level') }}</label>
                    <input type="number" min="0" name="reorder_level" class="form-control @error('reorder_level') is-invalid @enderror" value="{{ old('reorder_level', $product->reorder_level ?? 0) }}" required>
                    <div class="form-text">{{ __('app.reorder_level_hint') }}</div>
                    @error('reorder_level') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="mb-4 form-check form-switch">
                <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1" {{ old('is_active', $product->exists ? $product->is_active : true) ? 'checked' : '' }}>
                <label class="form-check-label" for="is_active">{{ __('app.status_active') }}</label>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> {{ __('app.save') }}</button>
                <a href="{{ route('inventory.products.index') }}" class="btn btn-outline-secondary">{{ __('app.cancel') }}</a>
            </div>
        </form>
    </div>
@endsection
