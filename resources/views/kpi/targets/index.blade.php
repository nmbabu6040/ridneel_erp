@extends('layouts.app')

@section('title', __('app.targets'))
@section('page-title', __('app.targets'))

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <form method="GET" class="d-flex">
            <input type="month" name="month" value="{{ $month }}" class="form-control form-control-sm" onchange="this.form.submit()">
        </form>
        <a href="{{ route('kpi.targets.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> {{ __('app.set_target') }}</a>
    </div>

    <div class="stat-card p-0">
        <div class="table-responsive-card">
            <table class="table table-hover mb-0 align-middle">
                <thead>
                    <tr>
                        <th>{{ __('app.target_type') }}</th>
                        <th>{{ __('app.name') }}</th>
                        <th class="text-end">{{ __('app.target_amount') }}</th>
                        <th class="text-end">{{ __('app.achieved') }}</th>
                        <th>{{ __('app.progress') }}</th>
                        <th class="text-end">{{ __('app.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($targets as $t)
                        @php $pct = $t->achievementPercent(); @endphp
                        <tr>
                            <td><span class="badge text-bg-secondary">{{ $t->target_type === 'sr' ? __('app.sr') : __('app.dealer') }}</span></td>
                            <td>{{ $t->subjectName() }}</td>
                            <td class="text-end">{{ number_format($t->target_amount, 2) }}</td>
                            <td class="text-end">{{ number_format($t->achievement(), 2) }}</td>
                            <td style="min-width:140px">
                                <div class="progress" style="height:8px;">
                                    <div class="progress-bar {{ $pct >= 100 ? 'bg-success' : 'bg-primary' }}" style="width: {{ min($pct, 100) }}%"></div>
                                </div>
                                <div class="small text-secondary">{{ $pct }}%</div>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('kpi.targets.edit', $t) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
                                <form action="{{ route('kpi.targets.destroy', $t) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('app.confirm_delete') }}');">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-secondary py-4">{{ __('app.no_records') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
