@extends('layouts.app')

@section('title', __('app.attendance_report'))
@section('page-title', __('app.attendance_report'))

@section('content')
    <form method="GET" class="row g-2 mb-3">
        <div class="col-auto">
            <input type="date" name="date" value="{{ $date }}" class="form-control form-control-sm" onchange="this.form.submit()">
        </div>
    </form>

    <div class="stat-card p-0">
        <div class="table-responsive-card">
            <table class="table table-hover mb-0 align-middle">
                <thead>
                    <tr>
                        <th>{{ __('app.sr_name') }}</th>
                        <th>{{ __('app.check_in') }}</th>
                        <th>{{ __('app.check_in_location') }}</th>
                        <th>{{ __('app.check_out') }}</th>
                        <th>{{ __('app.worked_hours') }}</th>
                        <th>{{ __('app.status') }}</th>
                        <th>{{ __('app.photo') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($srUsers as $sr)
                        @php $a = $records->get($sr->id); @endphp
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $sr->name }}</div>
                                <div class="small text-secondary">{{ $sr->phone }}</div>
                            </td>
                            <td>{{ $a?->check_in_at?->format('h:i A') ?: '—' }}</td>
                            <td>
                                @if ($a?->check_in_lat)
                                    <a href="https://www.openstreetmap.org/?mlat={{ $a->check_in_lat }}&mlon={{ $a->check_in_lng }}#map=17/{{ $a->check_in_lat }}/{{ $a->check_in_lng }}" target="_blank" rel="noopener">
                                        <i class="bi bi-geo-alt-fill"></i> {{ __('app.view_map') }}
                                    </a>
                                @else — @endif
                            </td>
                            <td>{{ $a?->check_out_at?->format('h:i A') ?: '—' }}</td>
                            <td>
                                @if ($a?->workedMinutes())
                                    {{ intdiv($a->workedMinutes(), 60) }}h {{ $a->workedMinutes() % 60 }}m
                                @else — @endif
                            </td>
                            <td>
                                @if (!$a || !$a->check_in_at)
                                    <span class="badge text-bg-danger">{{ __('app.status_absent') }}</span>
                                @else
                                    <span class="badge {{ $a->status === 'present' ? 'badge-status-active' : 'text-bg-warning' }}">{{ __('app.status_' . $a->status) }}</span>
                                @endif
                            </td>
                            <td>
                                @if ($a?->check_in_photo)
                                    <a href="{{ asset('storage/' . $a->check_in_photo) }}" target="_blank" rel="noopener">
                                        <img src="{{ asset('storage/' . $a->check_in_photo) }}" alt="" style="width:36px;height:36px;object-fit:cover;border-radius:6px;">
                                    </a>
                                @else — @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-secondary py-4">{{ __('app.no_records') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
