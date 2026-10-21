<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_key', 'label', 'label_bn',
        'email_enabled', 'extra_recipients',
        'sms_enabled', 'sms_extra_recipients',
    ];

    protected $casts = [
        'email_enabled' => 'boolean',
        'sms_enabled'   => 'boolean',
    ];

    public function displayLabel(): string
    {
        return app()->getLocale() === 'bn' && $this->label_bn ? $this->label_bn : $this->label;
    }

    /** Extra recipient emails as a clean array (trims blanks/whitespace). */
    public function extraRecipientList(): array
    {
        return $this->splitList($this->extra_recipients);
    }

    /** Extra recipient phone numbers (E.164-ish, whatever the SMS gateway expects) as a clean array. */
    public function smsExtraRecipientList(): array
    {
        return $this->splitList($this->sms_extra_recipients);
    }

    private function splitList(?string $value): array
    {
        if (!$value) {
            return [];
        }

        return collect(explode(',', $value))
            ->map(fn ($e) => trim($e))
            ->filter()
            ->values()
            ->all();
    }
}
