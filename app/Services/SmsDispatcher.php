<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * SMS has no single universal API the way SMTP email does — every
 * gateway (bulksmsbd.net, Elitbuzz, Grameenphone, Twilio, etc.) has
 * its own request shape. This class is written against a GET-based
 * "api_key + sender_id + number + message" pattern, which is the most
 * common shape among Bangladeshi bulk-SMS resellers — but you will
 * very likely need to adjust the request in send() to match your
 * actual provider's API documentation. Everything else in this system
 * (NotificationDispatcher, the settings page, the event wiring) does
 * NOT need to change when you do that — this one method is the only
 * place gateway-specific details live.
 */
class SmsDispatcher
{
    public static function isConfigured(): bool
    {
        return (bool) config('services.sms.enabled') && (bool) config('services.sms.api_key');
    }

    /**
     * @param array<string> $numbers phone numbers, in whatever format your gateway expects (e.g. 8801XXXXXXXXX)
     * @return array{sent: array<string>, failed: array<string>}
     */
    public static function send(array $numbers, string $message): array
    {
        $sent = [];
        $failed = [];

        if (!self::isConfigured()) {
            Log::info('SmsDispatcher: SMS not configured (services.sms.enabled/api_key) — skipping.');
            return ['sent' => $sent, 'failed' => $numbers];
        }

        foreach (array_unique($numbers) as $number) {
            try {
                // ↓↓↓ ADJUST THIS to your actual SMS provider's API ↓↓↓
                $response = Http::timeout(10)->get(config('services.sms.url'), [
                    'api_key'   => config('services.sms.api_key'),
                    'senderid'  => config('services.sms.sender_id'),
                    'number'    => $number,
                    'message'   => $message,
                ]);
                // ↑↑↑ ADJUST THIS to your actual SMS provider's API ↑↑↑

                if ($response->successful()) {
                    $sent[] = $number;
                } else {
                    $failed[] = $number;
                    Log::warning("SmsDispatcher: send to {$number} failed — HTTP {$response->status()}: {$response->body()}");
                }
            } catch (\Throwable $e) {
                $failed[] = $number;
                Log::error("SmsDispatcher: send to {$number} threw — " . $e->getMessage());
            }
        }

        return ['sent' => $sent, 'failed' => $failed];
    }
}
