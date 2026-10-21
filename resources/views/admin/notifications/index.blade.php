@extends('layouts.app')

@section('title', __('app.notification_settings'))
@section('page-title', __('app.notification_settings'))

@section('content')
    <div class="stat-card mb-4" style="max-width:520px;">
        <h6 class="fw-bold mb-2"><i class="bi bi-envelope-check"></i> {{ __('app.send_test_email') }}</h6>
        <form method="POST" action="{{ route('admin.notifications.test') }}" class="d-flex gap-2">
            @csrf
            <input type="email" name="test_email" class="form-control form-control-sm" placeholder="you@example.com" required>
            <button type="submit" class="btn btn-outline-primary btn-sm text-nowrap"><i class="bi bi-send"></i> {{ __('app.send') }}</button>
        </form>
        <div class="form-text">{{ __('app.send_test_hint') }}</div>
    </div>

    @unless ($smsConfigured)
        <div class="alert alert-light border small mb-4" style="max-width:700px;">
            <i class="bi bi-info-circle"></i> {{ __('app.sms_not_configured') }}
        </div>
    @endunless

    {{-- Each row submits to a different URL, so each gets its own <form> —
         but a <form> can't legally be a child of <tr> in HTML. Instead,
         each form is declared empty here (outside the table) and every
         field/button inside the table points at it via form="rowId",
         which is valid HTML5 and works in every modern browser. --}}
    @foreach ($settings as $setting)
        <form method="POST" action="{{ route('admin.notifications.update', $setting) }}" id="notif-form-{{ $setting->id }}">
            @csrf @method('PUT')
        </form>
    @endforeach

    <div class="stat-card p-0 mb-4">
        <div class="p-3 border-bottom fw-bold small">{{ __('app.event_list') }}</div>
        <div class="table-responsive-card">
            <table class="table mb-0 align-middle">
                <thead>
                    <tr>
                        <th>{{ __('app.event') }}</th>
                        <th style="width:70px">{{ __('app.email') }}</th>
                        <th>{{ __('app.extra_recipients') }}</th>
                        <th style="width:70px">{{ __('app.sms') }}</th>
                        <th>{{ __('app.sms_extra_recipients') }}</th>
                        <th style="width:90px"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($settings as $setting)
                        @php $formId = 'notif-form-' . $setting->id; @endphp
                        <tr>
                            <td class="align-middle">
                                <div class="fw-semibold">{{ $setting->displayLabel() }}</div>
                                <div class="small text-secondary"><code>{{ $setting->event_key }}</code></div>
                            </td>
                            <td>
                                <div class="form-check form-switch">
                                    <input type="checkbox" class="form-check-input" form="{{ $formId }}" name="email_enabled" value="1" {{ $setting->email_enabled ? 'checked' : '' }}>
                                </div>
                            </td>
                            <td>
                                <input type="text" form="{{ $formId }}" name="extra_recipients" class="form-control form-control-sm" value="{{ $setting->extra_recipients }}" placeholder="{{ __('app.extra_recipients_placeholder') }}">
                            </td>
                            <td>
                                <div class="form-check form-switch">
                                    <input type="checkbox" class="form-check-input" form="{{ $formId }}" name="sms_enabled" value="1" {{ $setting->sms_enabled ? 'checked' : '' }}>
                                </div>
                            </td>
                            <td>
                                <input type="text" form="{{ $formId }}" name="sms_extra_recipients" class="form-control form-control-sm" value="{{ $setting->sms_extra_recipients }}" placeholder="{{ __('app.sms_extra_recipients_placeholder') }}">
                            </td>
                            <td class="text-end">
                                <button type="submit" form="{{ $formId }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-check-lg"></i></button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="stat-card p-0">
        <div class="p-3 border-bottom fw-bold small">{{ __('app.recent_email_log') }}</div>
        <div class="table-responsive-card">
            <table class="table table-sm mb-0 align-middle">
                <thead>
                    <tr>
                        <th>{{ __('app.date') }}</th>
                        <th>{{ __('app.event') }}</th>
                        <th>{{ __('app.subject') }}</th>
                        <th>{{ __('app.recipients') }}</th>
                        <th>{{ __('app.status') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($recentLogs as $log)
                        <tr>
                            <td class="text-nowrap">{{ $log->created_at->format('d M, h:i A') }}</td>
                            <td><code>{{ $log->event_key }}</code></td>
                            <td class="small">{{ $log->subject }}</td>
                            <td class="small">{{ $log->recipients }}</td>
                            <td>
                                <span class="badge {{ $log->status === 'sent' ? 'badge-status-active' : 'badge-status-suspended' }}" title="{{ $log->error }}">
                                    {{ $log->status === 'sent' ? __('app.sent') : __('app.failed') }}
                                </span>
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
