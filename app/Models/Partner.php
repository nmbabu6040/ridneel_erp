<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Partner extends Model
{
    use HasFactory;

    protected $fillable = [
        'type', 'name', 'business_name', 'phone', 'email', 'address',
        'territory', 'linked_dealer_id', 'opening_balance', 'credit_limit', 'is_active',
    ];

    protected $casts = [
        'opening_balance' => 'decimal:2',
        'credit_limit'    => 'decimal:2',
        'is_active'       => 'boolean',
    ];

    public function linkedDealer(): BelongsTo
    {
        return $this->belongsTo(Partner::class, 'linked_dealer_id');
    }

    public function retailers(): HasMany
    {
        return $this->hasMany(Partner::class, 'linked_dealer_id');
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class, 'supplier_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(PartnerTransaction::class);
    }

    public function scopeType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Live running balance: opening_balance is the fixed starting point
     * set when the partner was created; every sale/payment/return after
     * that is a signed row in partner_transactions (never mutates
     * opening_balance itself) — same non-drift principle as
     * Product::currentStock(). Positive = partner owes us.
     */
    public function currentBalance(): float
    {
        return (float) $this->opening_balance + (float) $this->transactions()->sum('amount');
    }

    public function typeLabel(): string
    {
        $labels = [
            'dealer'   => app()->getLocale() === 'bn' ? 'ডিলার' : 'Dealer',
            'retailer' => app()->getLocale() === 'bn' ? 'রিটেইলার' : 'Retailer',
            'mechanic' => app()->getLocale() === 'bn' ? 'মেকানিক' : 'Mechanic',
            'supplier' => app()->getLocale() === 'bn' ? 'সাপ্লায়ার' : 'Supplier',
        ];

        return $labels[$this->type] ?? $this->type;
    }
}
