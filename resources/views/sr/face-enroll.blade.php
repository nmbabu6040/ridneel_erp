@extends('layouts.app')

@section('title', __('app.face_enrollment'))
@section('page-title', __('app.face_enrollment'))

@section('content')
    <div class="stat-card" style="max-width:520px;">
        <p class="text-secondary small mb-3">{{ __('app.enroll_instructions') }}</p>

        @if ($profile)
            <div class="alert alert-info small">
                <i class="bi bi-info-circle me-1"></i>
                {{ __('app.already_enrolled_on') }} {{ $profile->enrolled_at->format('d M Y, h:i A') }}.
                {{ __('app.reenroll_hint') }}
            </div>
        @endif

        <div id="camera-wrap" class="text-center mb-3">
            <video id="video" autoplay muted playsinline class="rounded border w-100" style="max-width:360px; background:#000;"></video>
            <canvas id="overlay" style="display:none;"></canvas>
        </div>

        <div id="status-line" class="text-center small text-secondary mb-3">{{ __('app.loading_models') }}</div>

        <div class="d-flex gap-2 justify-content-center">
            <button type="button" id="capture-btn" class="btn btn-primary" disabled>
                <i class="bi bi-camera-fill"></i> {{ __('app.capture_enroll') }}
            </button>
        </div>

        <form id="enroll-form" method="POST" action="{{ route('sr.face.store') }}" class="d-none">
            @csrf
            <input type="hidden" name="descriptor" id="descriptor-input">
            <input type="hidden" name="photo" id="photo-input">
        </form>
    </div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
<script>
(function () {
    var MODEL_URL = 'https://cdn.jsdelivr.net/gh/justadudewhohacks/face-api.js@master/weights';
    var video = document.getElementById('video');
    var statusLine = document.getElementById('status-line');
    var captureBtn = document.getElementById('capture-btn');

    async function init() {
        try {
            statusLine.textContent = '{{ __("app.loading_models") }}';
            await Promise.all([
                faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL),
                faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL),
                faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL),
            ]);

            var stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user' } });
            video.srcObject = stream;

            statusLine.textContent = '{{ __("app.position_face") }}';
            captureBtn.disabled = false;
        } catch (err) {
            statusLine.textContent = '{{ __("app.camera_model_error") }}: ' + err.message;
        }
    }

    captureBtn.addEventListener('click', async function () {
        captureBtn.disabled = true;
        statusLine.textContent = '{{ __("app.detecting_face") }}';

        var detection = await faceapi
            .detectSingleFace(video, new faceapi.TinyFaceDetectorOptions())
            .withFaceLandmarks()
            .withFaceDescriptor();

        if (!detection) {
            statusLine.textContent = '{{ __("app.no_face_detected") }}';
            captureBtn.disabled = false;
            return;
        }

        var canvas = document.createElement('canvas');
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        canvas.getContext('2d').drawImage(video, 0, 0);
        var photoDataUrl = canvas.toDataURL('image/jpeg', 0.85);

        document.getElementById('descriptor-input').value = JSON.stringify(Array.from(detection.descriptor));
        document.getElementById('photo-input').value = photoDataUrl;

        statusLine.textContent = '{{ __("app.face_captured_saving") }}';
        document.getElementById('enroll-form').submit();
    });

    init();
})();
</script>
@endpush
