<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationSetting extends Model
{
    use HasFactory;

    protected $fillable = ['event_key', 'label', 'label_bn', 'email_enabled', 'extra_recipients'];

    protected $casts = ['email_enabled' => 'boolean'];

    public function displayLabel(): string
    {
        return app()->getLocale() === 'bn' && $this->label_bn ? $this->label_bn : $this->label;
    }

    /** Extra recipient emails as a clean array (trims blanks/whitespace). */
    public function extraRecipientList(): array
    {
        if (!$this->extra_recipients) {
            return [];
        }

        return collect(explode(',', $this->extra_recipients))
            ->map(fn ($e) => trim($e))
            ->filter()
            ->values()
            ->all();
    }
}
