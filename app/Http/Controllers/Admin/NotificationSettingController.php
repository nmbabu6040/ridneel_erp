<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\SystemNotificationMail;
use App\Models\NotificationLog;
use App\Models\NotificationSetting;
use App\Services\SmsDispatcher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class NotificationSettingController extends Controller
{
    public function index()
    {
        $settings = NotificationSetting::orderBy('event_key')->get();
        $recentLogs = NotificationLog::latest()->limit(20)->get();
        $smsConfigured = SmsDispatcher::isConfigured();

        return view('admin.notifications.index', compact('settings', 'recentLogs', 'smsConfigured'));
    }

    public function update(Request $request, NotificationSetting $notificationSetting): RedirectResponse
    {
        $data = $request->validate([
            'email_enabled'        => ['nullable', 'boolean'],
            'extra_recipients'     => ['nullable', 'string', 'max:1000'],
            'sms_enabled'          => ['nullable', 'boolean'],
            'sms_extra_recipients' => ['nullable', 'string', 'max:1000'],
        ]);

        $data['email_enabled'] = $request->boolean('email_enabled');
        $data['sms_enabled']   = $request->boolean('sms_enabled');

        $notificationSetting->update($data);

        return redirect()->route('admin.notifications.index')->with('success', __('app.notification_setting_saved'));
    }

    /** Lets an admin confirm SMTP is actually configured correctly, from the UI, without digging into logs. */
    public function sendTest(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'test_email' => ['required', 'email'],
        ]);

        try {
            Mail::to($data['test_email'])->send(new SystemNotificationMail(
                __('app.email_test_subject'),
                'emails.test',
                ['sentAt' => now()->format('d M Y, h:i A')]
            ));

            return back()->with('success', __('app.test_email_sent'));
        } catch (\Throwable $e) {
            return back()->with('error', __('app.test_email_failed') . ': ' . $e->getMessage());
        }
    }
}
