<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PartnerTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'partner_id', 'type', 'amount', 'reference_type', 'reference_id', 'note', 'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function typeLabel(): string
    {
        $labels = [
            'sale'       => ['en' => 'Sale (Invoice)', 'bn' => 'বিক্রয় (ইনভয়েস)'],
            'payment'    => ['en' => 'Payment Received', 'bn' => 'পেমেন্ট গৃহীত'],
            'return'     => ['en' => 'Return', 'bn' => 'রিটার্ন'],
            'adjustment' => ['en' => 'Adjustment', 'bn' => 'অ্যাডজাস্টমেন্ট'],
        ];

        return $labels[$this->type][app()->getLocale()] ?? $this->type;
    }
}
