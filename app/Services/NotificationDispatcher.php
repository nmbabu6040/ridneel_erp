<?php

namespace App\Services;

use App\Mail\SystemNotificationMail;
use App\Models\NotificationLog;
use App\Models\NotificationSetting;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Single choke point every email in the system passes through. This is
 * deliberate: a new feature that wants to notify someone by email calls
 * NotificationDispatcher::send(...) rather than calling Mail::to()
 * directly, which is what makes the Notification Settings admin page
 * (toggle on/off, add extra recipients) actually govern every email —
 * and what makes the notification_logs audit trail complete rather
 * than partial.
 */
class NotificationDispatcher
{
    /**
     * @param string $eventKey matches notification_settings.event_key
     * @param string $subject email subject line
     * @param string $view Blade view name for the email body (resources/views/emails/*)
     * @param array $data data passed to that view
     * @param array $defaultRecipients emails that should get this notification
     *        regardless of extra_recipients config (e.g. managers for low stock)
     */
    public static function send(string $eventKey, string $subject, string $view, array $data, array $defaultRecipients = []): void
    {
        $setting = NotificationSetting::where('event_key', $eventKey)->first();

        // Unknown event key or explicitly disabled by admin → skip silently.
        if ($setting && !$setting->email_enabled) {
            return;
        }

        $recipients = collect($defaultRecipients)
            ->merge($setting?->extraRecipientList() ?? [])
            ->filter()
            ->unique()
            ->values();

        if ($recipients->isEmpty()) {
            return;
        }

        try {
            Mail::to($recipients->all())->queue(new SystemNotificationMail($subject, $view, $data));

            NotificationLog::create([
                'event_key'  => $eventKey,
                'subject'    => $subject,
                'recipients' => $recipients->implode(', '),
                'status'     => 'sent',
            ]);
        } catch (Throwable $e) {
            // Never let a broken mail server break the business action
            // (sale/purchase/return) that triggered this notification —
            // log it instead of throwing, both here and in Laravel's own log.
            Log::error("NotificationDispatcher [{$eventKey}] failed: " . $e->getMessage());

            NotificationLog::create([
                'event_key'  => $eventKey,
                'subject'    => $subject,
                'recipients' => $recipients->implode(', '),
                'status'     => 'failed',
                'error'      => $e->getMessage(),
            ]);
        }
    }

    /** Email addresses of every active user who holds the given permission key. */
    public static function emailsWithPermission(string $permission): array
    {
        return User::with('role')
            ->where('status', 'active')
            ->get()
            ->filter(fn (User $u) => $u->hasPermission($permission))
            ->pluck('email')
            ->filter()
            ->unique()
            ->values()
            ->all();
    }
}
