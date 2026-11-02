<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class CompanySetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'name_bn',
        'tagline',
        'logo_path',
        'favicon_emoji',
        'address',
        'phone',
        'email',
        'website',
        'currency_symbol',
        'currency_code',
        'invoice_footer_note',
    ];

    /**
     * The one row every part of the UI reads from — cached briefly
     * since it's read on nearly every request (sidebar, invoices,
     * emails). Guarded with Schema::hasTable() so a fresh install
     * running `php artisan migrate` for the first time (before this
     * table exists yet) doesn't crash AppServiceProvider's view
     * composer — it just falls back to an in-memory default instead.
     */
    public static function current(): self
    {
        return Cache::remember('company_settings', 300, function () {
            if (!Schema::hasTable('company_settings')) {
                return new self([
                    'name' => 'My Company',
                    'currency_symbol' => '৳',
                    'favicon_emoji' => '🏢',
                ]);
            }

            return self::first() ?? self::create([
                'name' => 'My Company',
                'currency_symbol' => '৳',
                'currency_code' => 'BDT',
                'favicon_emoji' => '',
            ]);
        });
    }

    public static function forget(): void
    {
        Cache::forget('company_settings');
    }

    public function displayName(): string
    {
        return app()->getLocale() === 'bn' && $this->name_bn ? $this->name_bn : $this->name;
    }
}
