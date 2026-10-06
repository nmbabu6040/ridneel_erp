@extends('layouts.app')

@section('title', $unit->exists ? __('app.edit_unit') : __('app.add_unit'))
@section('page-title', $unit->exists ? __('app.edit_unit') : __('app.add_unit'))

@section('content')
    <div class="stat-card" style="max-width:560px;">
        <form method="POST" action="{{ $unit->exists ? route('inventory.units.update', $unit) : route('inventory.units.store') }}">
            @csrf
            @if ($unit->exists) @method('PUT') @endif

            <div class="mb-3">
                <label class="form-label">{{ __('app.name') }} (English)</label>
                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $unit->name) }}" required placeholder="e.g. Piece">
                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="mb-3">
                <label class="form-label">{{ __('app.name') }} (বাংলা)</label>
                <input type="text" name="name_bn" class="form-control" value="{{ old('name_bn', $unit->name_bn) }}" placeholder="যেমন পিস">
            </div>
            <div class="mb-4">
                <label class="form-label">{{ __('app.short_code') }}</label>
                <input type="text" name="short_code" class="form-control @error('short_code') is-invalid @enderror" value="{{ old('short_code', $unit->short_code) }}" required placeholder="e.g. pcs, kg, ltr">
                @error('short_code') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> {{ __('app.save') }}</button>
                <a href="{{ route('inventory.units.index') }}" class="btn btn-outline-secondary">{{ __('app.cancel') }}</a>
            </div>
        </form>
    </div>
@endsection
