@extends('layouts.app')

@section('title', __('app.live_tracking'))
@section('page-title', __('app.live_tracking'))

@section('content')
    <div class="stat-card p-0 mb-3">
        <div id="live-map" style="height:520px; border-radius:.75rem; overflow:hidden;"></div>
    </div>

    <div class="stat-card p-0">
        <div class="table-responsive-card">
            <table class="table table-sm mb-0 align-middle">
                <thead>
                    <tr>
                        <th>{{ __('app.sr_name') }}</th>
                        <th>{{ __('app.status') }}</th>
                        <th>{{ __('app.last_seen') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($locations as $loc)
                        @php $att = $todayAttendance->get($loc->user_id); @endphp
                        <tr>
                            <td>{{ $loc->user?->name }}</td>
                            <td>
                                @if ($att?->isCheckedIn())
                                    <span class="badge badge-status-active">{{ __('app.on_duty') }}</span>
                                @elseif ($att?->isComplete())
                                    <span class="badge text-bg-secondary">{{ __('app.checked_out') }}</span>
                                @else
                                    <span class="badge text-bg-danger">{{ __('app.not_checked_in') }}</span>
                                @endif
                            </td>
                            <td>{{ $loc->recorded_at->diffForHumans() }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center text-secondary py-4">{{ __('app.no_location_data') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css">
@endpush


@php
    $mapPoints = $locations
        ->map(function ($loc) use ($todayAttendance) {
            $att = $todayAttendance->get($loc->user_id);
            return [
                'name' => $loc->user?->name,
                'lat' => (float) $loc->latitude,
                'lng' => (float) $loc->longitude,
                'time' => $loc->recorded_at->format('h:i A'),
                'onDuty' => $att?->isCheckedIn() ?? false,
            ];
        })
        ->values();
@endphp

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>
    <script>
        (function() {
            var points = @json($mapPoints);

            var defaultCenter = points.length ? [points[0].lat, points[0].lng] : [23.8103, 90.4125]; // Dhaka fallback
            var map = L.map('live-map').setView(defaultCenter, points.length ? 12 : 7);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors',
                maxZoom: 19,
            }).addTo(map);

            var bounds = [];
            points.forEach(function(p) {
                var color = p.onDuty ? '#198754' : '#6c757d';
                var marker = L.circleMarker([p.lat, p.lng], {
                    radius: 9,
                    fillColor: color,
                    color: '#fff',
                    weight: 2,
                    fillOpacity: 0.9,
                }).addTo(map);
                marker.bindPopup('<strong>' + p.name + '</strong><br>' + p.time);
                bounds.push([p.lat, p.lng]);
            });

            if (bounds.length > 1) {
                map.fitBounds(bounds, {
                    padding: [40, 40]
                });
            }
        })();
    </script>
@endpush
