<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_no', 'type', 'partner_id', 'warehouse_id', 'sale_date',
        'subtotal', 'discount', 'total', 'paid_amount', 'due_amount',
        'status', 'note', 'created_by',
    ];

    protected $casts = [
        'sale_date'   => 'date',
        'subtotal'    => 'decimal:2',
        'discount'    => 'decimal:2',
        'total'       => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'due_amount'  => 'decimal:2',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function typeLabel(): string
    {
        if ($this->type === 'primary') {
            return app()->getLocale() === 'bn' ? 'প্রাইমারি সেলস' : 'Primary Sale';
        }
        return app()->getLocale() === 'bn' ? 'সেকেন্ডারি সেলস' : 'Secondary Sale';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }
}
