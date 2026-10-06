@extends('layouts.app')

@section('title', __('app.kpi_leaderboard'))
@section('page-title', __('app.kpi_leaderboard'))

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <form method="GET" class="d-flex">
            <input type="month" name="month" value="{{ $month }}" class="form-control form-control-sm" onchange="this.form.submit()">
        </form>
        @can('kpi.manage')
            <a href="{{ route('kpi.targets.index', ['month' => $month]) }}" class="btn btn-outline-primary btn-sm"><i class="bi bi-bullseye"></i> {{ __('app.manage_targets') }}</a>
        @endcan
    </div>

    <div class="stat-card p-0 mb-4">
        <div class="p-3 border-bottom fw-bold small"><i class="bi bi-trophy-fill text-warning"></i> {{ __('app.sr_leaderboard') }}</div>
        <div class="table-responsive-card">
            <table class="table table-hover mb-0 align-middle">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>{{ __('app.sr_name') }}</th>
                        <th class="text-end">{{ __('app.target_amount') }}</th>
                        <th class="text-end">{{ __('app.achieved') }}</th>
                        <th>{{ __('app.progress') }}</th>
                        @if ($isManagerView)<th class="text-end">{{ __('app.commission') }}</th>@endif
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rows as $i => $row)
                        <tr>
                            <td>
                                @if ($i === 0) <i class="bi bi-trophy-fill text-warning"></i>
                                @elseif ($i === 1) <i class="bi bi-trophy-fill text-secondary"></i>
                                @elseif ($i === 2) <i class="bi bi-trophy-fill" style="color:#cd7f32"></i>
                                @else {{ $i + 1 }}
                                @endif
                            </td>
                            <td class="fw-semibold">{{ $row->sr?->name }}</td>
                            <td class="text-end">{{ number_format($row->target->target_amount, 2) }}</td>
                            <td class="text-end">{{ number_format($row->achievement, 2) }}</td>
                            <td style="min-width:140px">
                                <div class="progress" style="height:8px;">
                                    <div class="progress-bar {{ $row->percent >= 100 ? 'bg-success' : 'bg-primary' }}" style="width: {{ min($row->percent, 100) }}%"></div>
                                </div>
                                <div class="small text-secondary">{{ $row->percent }}%</div>
                            </td>
                            @if ($isManagerView)
                                <td class="text-end">{{ $row->commission !== null ? number_format($row->commission, 2) : '—' }}</td>
                            @endif
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-secondary py-4">{{ __('app.no_targets_set') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if ($isManagerView && $dealerRows->count())
        <div class="stat-card p-0">
            <div class="p-3 border-bottom fw-bold small"><i class="bi bi-shop"></i> {{ __('app.dealer_targets') }}</div>
            <div class="table-responsive-card">
                <table class="table table-sm mb-0 align-middle">
                    <thead>
                        <tr>
                            <th>{{ __('app.dealer') }}</th>
                            <th class="text-end">{{ __('app.target_amount') }}</th>
                            <th class="text-end">{{ __('app.achieved') }}</th>
                            <th>{{ __('app.progress') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($dealerRows as $row)
                            <tr>
                                <td>{{ $row->dealer?->name }}</td>
                                <td class="text-end">{{ number_format($row->target->target_amount, 2) }}</td>
                                <td class="text-end">{{ number_format($row->achievement, 2) }}</td>
                                <td style="min-width:140px">
                                    <div class="progress" style="height:8px;">
                                        <div class="progress-bar {{ $row->percent >= 100 ? 'bg-success' : 'bg-primary' }}" style="width: {{ min($row->percent, 100) }}%"></div>
                                    </div>
                                    <div class="small text-secondary">{{ $row->percent }}%</div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
@endsection
