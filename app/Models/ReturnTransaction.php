<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Named ReturnTransaction (not "Return") because "Return" collides
 * with PHP's reserved-ish conventions and reads poorly as a class name
 * next to Sale/Purchase — the table itself is still "returns".
 */
class ReturnTransaction extends Model
{
    use HasFactory;

    protected $table = 'returns';

    protected $fillable = [
        'return_no', 'type', 'partner_id', 'warehouse_id',
        'reference_type', 'reference_id', 'return_date', 'reason',
        'total', 'status', 'note', 'created_by',
    ];

    protected $casts = [
        'return_date' => 'date',
        'total'       => 'decimal:2',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(ReturnItem::class, 'return_id');
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

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function typeLabel(): string
    {
        if ($this->type === 'sales_return') {
            return app()->getLocale() === 'bn' ? 'সেলস রিটার্ন' : 'Sales Return';
        }
        return app()->getLocale() === 'bn' ? 'পারচেজ রিটার্ন' : 'Purchase Return';
    }
}
