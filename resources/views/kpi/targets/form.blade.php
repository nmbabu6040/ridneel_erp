@extends('layouts.app')

@section('title', __('app.set_target'))
@section('page-title', __('app.set_target'))

@section('content')
    @if ($errors->any())
        <div class="alert alert-danger small">
            <ul class="mb-0 ps-3">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="stat-card" style="max-width:480px;">
        <form method="POST" action="{{ route('kpi.targets.store') }}">
            @csrf

            <div class="mb-3">
                <label class="form-label">{{ __('app.target_type') }}</label>
                <select name="target_type" id="target_type" class="form-select" required>
                    <option value="sr" {{ old('target_type') === 'sr' ? 'selected' : '' }}>{{ __('app.sr') }} ({{ __('app.secondary_sales') }})</option>
                    <option value="dealer" {{ old('target_type') === 'dealer' ? 'selected' : '' }}>{{ __('app.dealer') }} ({{ __('app.primary_sales') }})</option>
                </select>
            </div>

            <div class="mb-3" id="sr-field">
                <label class="form-label">{{ __('app.sr') }}</label>
                <select name="target_ref_id" class="form-select sr-select" required>
                    <option value="">{{ __('app.select') }}</option>
                    @foreach ($srUsers as $sr)
                        <option value="{{ $sr->id }}">{{ $sr->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3 d-none" id="dealer-field">
                <label class="form-label">{{ __('app.dealer') }}</label>
                <select name="target_ref_id_dealer" class="form-select dealer-select">
                    <option value="">{{ __('app.select') }}</option>
                    @foreach ($dealers as $d)
                        <option value="{{ $d->id }}">{{ $d->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label">{{ __('app.month') }}</label>
                    <input type="month" name="period_month" class="form-control" value="{{ old('period_month', now()->format('Y-m')) }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">{{ __('app.target_amount') }}</label>
                    <input type="number" step="0.01" min="0.01" name="target_amount" class="form-control" value="{{ old('target_amount') }}" required>
                </div>
            </div>

            <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> {{ __('app.save') }}</button>
            <a href="{{ route('kpi.targets.index') }}" class="btn btn-outline-secondary">{{ __('app.cancel') }}</a>
        </form>
    </div>
@endsection

@push('scripts')
<script>
(function () {
    var typeSelect = document.getElementById('target_type');
    var srField = document.getElementById('sr-field');
    var dealerField = document.getElementById('dealer-field');
    var srSelect = document.querySelector('.sr-select');
    var dealerSelect = document.querySelector('.dealer-select');

    function toggle() {
        var isSr = typeSelect.value === 'sr';
        srField.classList.toggle('d-none', !isSr);
        dealerField.classList.toggle('d-none', isSr);
        srSelect.name = isSr ? 'target_ref_id' : '';
        dealerSelect.name = isSr ? '' : 'target_ref_id';
    }

    typeSelect.addEventListener('change', toggle);
    toggle();
})();
</script>
@endpush
