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
 * Single choke point every email AND SMS in the system passes through.
 * This is deliberate: a new feature that wants to notify someone calls
 * NotificationDispatcher::send(...) rather than calling Mail::to() or
 * SmsDispatcher::send() directly, which is what makes the Notification
 * Settings admin page (toggle each channel on/off per event, add extra
 * recipients) actually govern every notification — and what makes the
 * notification_logs audit trail complete rather than partial.
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
     * @param string|null $smsText short plain-text version for SMS — pass this for
     *        events urgent enough to also want SMS (late check-in, low stock, etc.);
     *        leave null to skip SMS entirely for this call, even if the event has
     *        SMS enabled (not every email also makes sense as an SMS)
     * @param array $defaultSmsPhones phone numbers that should get the SMS,
     *        regardless of sms_extra_recipients config
     */
    public static function send(
        string $eventKey,
        string $subject,
        string $view,
        array $data,
        array $defaultRecipients = [],
        ?string $smsText = null,
        array $defaultSmsPhones = []
    ): void {
        $setting = NotificationSetting::where('event_key', $eventKey)->first();

        self::sendEmail($eventKey, $subject, $view, $data, $defaultRecipients, $setting);

        if ($smsText !== null) {
            self::sendSms($eventKey, $subject, $smsText, $defaultSmsPhones, $setting);
        }
    }

    private static function sendEmail(string $eventKey, string $subject, string $view, array $data, array $defaultRecipients, ?NotificationSetting $setting): void
    {
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
            Log::error("NotificationDispatcher [{$eventKey}] email failed: " . $e->getMessage());

            NotificationLog::create([
                'event_key'  => $eventKey,
                'subject'    => $subject,
                'recipients' => $recipients->implode(', '),
                'status'     => 'failed',
                'error'      => $e->getMessage(),
            ]);
        }
    }

    private static function sendSms(string $eventKey, string $subject, string $smsText, array $defaultSmsPhones, ?NotificationSetting $setting): void
    {
        // SMS defaults to OFF per event (unlike email, which defaults
        // to on) — SMS usually costs money per message, so an admin
        // has to deliberately opt an event into it from the settings page.
        if (!$setting || !$setting->sms_enabled) {
            return;
        }

        $phones = collect($defaultSmsPhones)
            ->merge($setting->smsExtraRecipientList())
            ->filter()
            ->unique()
            ->values();

        if ($phones->isEmpty() || !SmsDispatcher::isConfigured()) {
            return;
        }

        $result = SmsDispatcher::send($phones->all(), $smsText);

        NotificationLog::create([
            'event_key'  => $eventKey,
            'subject'    => '[SMS] ' . $subject,
            'recipients' => implode(', ', $result['sent']) ?: implode(', ', $result['failed']),
            'status'     => empty($result['failed']) ? 'sent' : (empty($result['sent']) ? 'failed' : 'sent'),
            'error'      => empty($result['failed']) ? null : ('Failed for: ' . implode(', ', $result['failed'])),
        ]);
    }

    /** Email addresses of every active user who holds the given permission key. */
    public static function emailsWithPermission(string $permission): array
    {
        return self::usersWithPermission($permission)->pluck('email')->filter()->unique()->values()->all();
    }

    /** Phone numbers of every active user who holds the given permission key. */
    public static function phonesWithPermission(string $permission): array
    {
        return self::usersWithPermission($permission)->pluck('phone')->filter()->unique()->values()->all();
    }

    private static function usersWithPermission(string $permission)
    {
        return User::with('role')
            ->where('status', 'active')
            ->get()
            ->filter(fn (User $u) => $u->hasPermission($permission));
    }
}
