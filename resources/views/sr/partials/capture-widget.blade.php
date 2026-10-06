<div class="text-center mb-3">
    <video class="cap-video rounded border w-100" autoplay muted playsinline style="max-width:320px; background:#000;"></video>
</div>

<div class="cap-status text-center small text-secondary mb-3">{{ __('app.loading_models') }}</div>

<div class="d-flex justify-content-center">
    <button type="button" class="cap-btn btn btn-primary" disabled>
        <i class="bi bi-camera-fill"></i> {{ __('app.verify_and_submit') }}
    </button>
</div>

<form method="POST" class="cap-form d-none">
    @csrf
    <input type="hidden" class="cap-lat" name="lat">
    <input type="hidden" class="cap-lng" name="lng">
    <input type="hidden" class="cap-photo" name="photo">
    <input type="hidden" class="cap-distance" name="distance">
</form>
