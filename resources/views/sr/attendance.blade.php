@extends('layouts.app')

@section('title', __('app.attendance'))
@section('page-title', __('app.attendance'))

@section('content')
    @if (!$profile)
        <div class="stat-card text-center" style="max-width:480px;">
            <i class="bi bi-person-bounding-box fs-1 text-primary mb-2"></i>
            <h6 class="fw-bold">{{ __('app.enroll_face_first') }}</h6>
            <p class="text-secondary small">{{ __('app.enroll_face_desc') }}</p>
            <a href="{{ route('sr.face.create') }}" class="btn btn-primary"><i class="bi bi-camera-fill"></i> {{ __('app.enroll_now') }}</a>
        </div>
    @else
        <div class="row g-3 mb-3">
            <div class="col-md-6">
                <div class="stat-card h-100">
                    <div class="small text-secondary">{{ __('app.check_in') }}</div>
                    @if ($today->check_in_at)
                        <div class="fs-5 fw-bold text-success"><i class="bi bi-check-circle-fill"></i> {{ $today->check_in_at->format('h:i A') }}</div>
                        <div class="small text-secondary">{{ __('app.status') }}: {{ __('app.status_' . $today->status) }}</div>
                    @else
                        <div class="fs-6 text-secondary">{{ __('app.not_checked_in') }}</div>
                    @endif
                </div>
            </div>
            <div class="col-md-6">
                <div class="stat-card h-100">
                    <div class="small text-secondary">{{ __('app.check_out') }}</div>
                    @if ($today->check_out_at)
                        <div class="fs-5 fw-bold text-success"><i class="bi bi-check-circle-fill"></i> {{ $today->check_out_at->format('h:i A') }}</div>
                    @else
                        <div class="fs-6 text-secondary">{{ __('app.not_checked_out') }}</div>
                    @endif
                </div>
            </div>
        </div>

        @if (!$today->check_in_at)
            <div class="stat-card mb-4" style="max-width:480px;" id="capture-card" data-mode="checkin" data-action="{{ route('sr.attendance.checkin') }}">
                <h6 class="fw-bold mb-2"><i class="bi bi-box-arrow-in-right"></i> {{ __('app.check_in') }}</h6>
                @include('sr.partials.capture-widget')
            </div>
        @elseif (!$today->check_out_at)
            <div class="stat-card mb-4" style="max-width:480px;" id="capture-card" data-mode="checkout" data-action="{{ route('sr.attendance.checkout') }}">
                <h6 class="fw-bold mb-2"><i class="bi bi-box-arrow-right"></i> {{ __('app.check_out') }}</h6>
                @include('sr.partials.capture-widget')
            </div>
            <div class="alert alert-light border small">
                <i class="bi bi-geo-alt-fill text-primary"></i> {{ __('app.live_tracking_active') }}
            </div>
        @else
            <div class="alert alert-success">{{ __('app.attendance_complete_today') }}</div>
        @endif

        <div class="stat-card p-0">
            <div class="p-3 border-bottom fw-bold small">{{ __('app.recent_history') }}</div>
            <div class="table-responsive-card">
                <table class="table table-sm mb-0 align-middle">
                    <thead>
                        <tr>
                            <th>{{ __('app.date') }}</th>
                            <th>{{ __('app.check_in') }}</th>
                            <th>{{ __('app.check_out') }}</th>
                            <th>{{ __('app.status') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($history as $h)
                            <tr>
                                <td>{{ $h->date->format('d M Y') }}</td>
                                <td>{{ $h->check_in_at?->format('h:i A') ?: '—' }}</td>
                                <td>{{ $h->check_out_at?->format('h:i A') ?: '—' }}</td>
                                <td><span class="badge {{ $h->status === 'present' ? 'badge-status-active' : 'text-bg-warning' }}">{{ __('app.status_' . $h->status) }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-secondary py-3">{{ __('app.no_records') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif
@endsection

@if ($profile && (!$today->check_in_at || !$today->check_out_at))
    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
    <script>
    (function () {
        var MODEL_URL = 'https://cdn.jsdelivr.net/gh/justadudewhohacks/face-api.js@master/weights';
        var referenceDescriptor = @json($profile->descriptor);

        var card = document.getElementById('capture-card');
        var mode = card.dataset.mode;
        var actionUrl = card.dataset.action;

        var video = card.querySelector('.cap-video');
        var statusLine = card.querySelector('.cap-status');
        var captureBtn = card.querySelector('.cap-btn');
        var form = card.querySelector('.cap-form');

        function euclideanDistance(a, b) {
            var sum = 0;
            for (var i = 0; i < a.length; i++) sum += Math.pow(a[i] - b[i], 2);
            return Math.sqrt(sum);
        }

        function getPosition() {
            return new Promise(function (resolve, reject) {
                if (!navigator.geolocation) { reject(new Error('{{ __("app.geolocation_unsupported") }}')); return; }
                navigator.geolocation.getCurrentPosition(resolve, reject, { enableHighAccuracy: true, timeout: 10000 });
            });
        }

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

            var distance = euclideanDistance(Array.from(detection.descriptor), referenceDescriptor);
            if (distance > 0.6) {
                statusLine.textContent = '{{ __("app.face_mismatch") }} (Δ=' + distance.toFixed(3) + ')';
                captureBtn.disabled = false;
                return;
            }

            statusLine.textContent = '{{ __("app.face_matched_locating") }}';

            var position;
            try {
                position = await getPosition();
            } catch (err) {
                statusLine.textContent = '{{ __("app.location_error") }}: ' + err.message;
                captureBtn.disabled = false;
                return;
            }

            var canvas = document.createElement('canvas');
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            canvas.getContext('2d').drawImage(video, 0, 0);

            form.querySelector('.cap-lat').value = position.coords.latitude;
            form.querySelector('.cap-lng').value = position.coords.longitude;
            form.querySelector('.cap-photo').value = canvas.toDataURL('image/jpeg', 0.85);
            form.querySelector('.cap-distance').value = distance.toFixed(4);
            form.action = actionUrl;

            statusLine.textContent = '{{ __("app.submitting") }}';
            form.submit();
        });

        init();

        // Live GPS ping loop — only meaningful for the check-out card
        // (i.e. the SR is already checked in for today).
        if (mode === 'checkout') {
            function sendPing() {
                if (!navigator.geolocation) return;
                navigator.geolocation.getCurrentPosition(function (pos) {
                    fetch("{{ route('sr.tracking.ping') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        },
                        body: JSON.stringify({
                            lat: pos.coords.latitude,
                            lng: pos.coords.longitude,
                            accuracy: pos.coords.accuracy,
                        }),
                    }).catch(function () { /* network hiccup — next tick will retry */ });
                }, function () {}, { enableHighAccuracy: true, timeout: 10000 });
            }
            sendPing();
            setInterval(sendPing, 90000); // every 90 seconds while this page stays open
        }
    })();
    </script>
    @endpush
@endif
