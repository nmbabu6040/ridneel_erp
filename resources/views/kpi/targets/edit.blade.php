@extends('layouts.app')

@section('title', __('app.edit_target'))
@section('page-title', __('app.edit_target'))

@section('content')
    <div class="stat-card" style="max-width:420px;">
        <div class="mb-3">
            <div class="small text-secondary">{{ $target->target_type === 'sr' ? __('app.sr') : __('app.dealer') }}</div>
            <div class="fw-bold">{{ $target->subjectName() }}</div>
            <div class="small text-secondary">{{ $target->period_month->format('F Y') }}</div>
        </div>

        <form method="POST" action="{{ route('kpi.targets.update', $target) }}">
            @csrf
            @method('PUT')

            <div class="mb-4">
                <label class="form-label">{{ __('app.target_amount') }}</label>
                <input type="number" step="0.01" min="0.01" name="target_amount" class="form-control @error('target_amount') is-invalid @enderror" value="{{ old('target_amount', $target->target_amount) }}" required>
                @error('target_amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> {{ __('app.save') }}</button>
            <a href="{{ route('kpi.targets.index', ['month' => $target->period_month->format('Y-m')]) }}" class="btn btn-outline-secondary">{{ __('app.cancel') }}</a>
        </form>
    </div>
@endsection
