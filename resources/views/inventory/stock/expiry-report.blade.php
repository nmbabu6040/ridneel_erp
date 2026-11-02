@extends('layouts.app')

@section('title', __('app.expiry_report'))
@section('page-title', __('app.expiry_report'))

@section('content')
    <form method="GET" class="row g-2 mb-3">
        <div class="col-auto">
            <select name="filter" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">{{ __('app.all_batches') }}</option>
                <option value="expiring" {{ request('filter') === 'expiring' ? 'selected' : '' }}>{{ __('app.expiring_within') }}</option>
            </select>
        </div>
        @if (request('filter') === 'expiring')
            <div class="col-auto">
                <input type="number" name="days" value="{{ $days }}" class="form-control form-control-sm" style="width:90px" onchange="this.form.submit()">
            </div>
        @endif
    </form>

    <div class="stat-card p-0">
        <div class="table-responsive-card">
            <table class="table table-hover mb-0 align-middle">
                <thead>
                    <tr>
                        <th>{{ __('app.product') }}</th>
                        <th>{{ __('app.batch_no') }}</th>
                        <th class="text-end">{{ __('app.remaining_qty') }}</th>
                        <th>{{ __('app.expiry_date') }}</th>
                        <th>{{ __('app.status') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rows as $row)
                        <tr>
                            <td>{{ $row->product->name }}</td>
                            <td><code>{{ $row->batch_no }}</code></td>
                            <td class="text-end">{{ $row->remaining }} {{ $row->product->unit?->short_code }}</td>
                            <td>{{ $row->expiry_date ? \Carbon\Carbon::parse($row->expiry_date)->format('d M Y') : '—' }}</td>
                            <td>
                                @if ($row->days_left === null)
                                    <span class="badge text-bg-secondary">{{ __('app.no_expiry') }}</span>
                                @elseif ($row->days_left < 0)
                                    <span class="badge text-bg-danger">{{ __('app.expired') }}</span>
                                @elseif ($row->days_left <= 30)
                                    <span class="badge text-bg-warning">{{ __('app.expiring_soon') }} ({{ $row->days_left }}d)</span>
                                @else
                                    <span class="badge badge-status-active">{{ __('app.status_active') }}</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-secondary py-4">{{ __('app.no_records') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
