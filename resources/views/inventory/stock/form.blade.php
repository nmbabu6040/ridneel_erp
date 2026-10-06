@extends('layouts.app')

@section('title', __('app.new_stock_entry'))
@section('page-title', __('app.new_stock_entry'))

@section('content')
    <div class="stat-card" style="max-width:560px;">
        <form method="POST" action="{{ route('inventory.stock.store') }}">
            @csrf

            <div class="mb-3">
                <label class="form-label">{{ __('app.entry_type') }}</label>
                <select name="type" class="form-select @error('type') is-invalid @enderror" required>
                    <option value="opening" {{ old('type') === 'opening' ? 'selected' : '' }}>{{ __('app.opening_stock') }}</option>
                    <option value="adjustment_in" {{ old('type') === 'adjustment_in' ? 'selected' : '' }}>{{ __('app.adjustment_in') }}</option>
                    <option value="adjustment_out" {{ old('type') === 'adjustment_out' ? 'selected' : '' }}>{{ __('app.adjustment_out') }}</option>
                </select>
                @error('type') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">{{ __('app.product') }}</label>
                <select name="product_id" class="form-select @error('product_id') is-invalid @enderror" required>
                    <option value="">{{ __('app.select') }}</option>
                    @foreach ($products as $p)
                        <option value="{{ $p->id }}" {{ (int) old('product_id') === $p->id ? 'selected' : '' }}>{{ $p->name }} ({{ $p->sku }})</option>
                    @endforeach
                </select>
                @error('product_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">{{ __('app.warehouse') }}</label>
                <select name="warehouse_id" class="form-select @error('warehouse_id') is-invalid @enderror" required>
                    <option value="">{{ __('app.select') }}</option>
                    @foreach ($warehouses as $w)
                        <option value="{{ $w->id }}" {{ (int) old('warehouse_id') === $w->id ? 'selected' : '' }}>{{ $w->name }}</option>
                    @endforeach
                </select>
                @error('warehouse_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label">{{ __('app.quantity') }}</label>
                    <input type="number" min="1" name="quantity" class="form-control @error('quantity') is-invalid @enderror" value="{{ old('quantity') }}" required>
                    <div class="form-text">{{ __('app.quantity_hint') }}</div>
                    @error('quantity') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">{{ __('app.unit_cost') }}</label>
                    <input type="number" step="0.01" min="0" name="unit_cost" class="form-control" value="{{ old('unit_cost') }}">
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label">{{ __('app.note') }}</label>
                <textarea name="note" class="form-control" rows="2">{{ old('note') }}</textarea>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> {{ __('app.save') }}</button>
                <a href="{{ route('inventory.stock.index') }}" class="btn btn-outline-secondary">{{ __('app.cancel') }}</a>
            </div>
        </form>
    </div>
@endsection
