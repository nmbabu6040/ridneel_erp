<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id', 'warehouse_id', 'type', 'quantity',
        'unit_cost', 'reference_no', 'note', 'created_by',
    ];

    protected $casts = [
        'quantity'  => 'integer',
        'unit_cost' => 'decimal:2',
    ];

    /** Movement types whose quantity must be stored as positive (stock in). */
    public const IN_TYPES = ['opening', 'purchase_in', 'return_in', 'adjustment_in', 'transfer_in'];

    /** Movement types whose quantity must be stored as negative (stock out). */
    public const OUT_TYPES = ['sales_out', 'return_out', 'adjustment_out', 'transfer_out'];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
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
        $labels = [
            'opening'        => ['en' => 'Opening Stock', 'bn' => 'ওপেনিং স্টক'],
            'purchase_in'    => ['en' => 'Purchase In', 'bn' => 'ক্রয় ইন'],
            'sales_out'      => ['en' => 'Sales Out', 'bn' => 'বিক্রয় আউট'],
            'return_in'      => ['en' => 'Return In', 'bn' => 'রিটার্ন ইন'],
            'return_out'     => ['en' => 'Return Out', 'bn' => 'রিটার্ন আউট'],
            'adjustment_in'  => ['en' => 'Adjustment In', 'bn' => 'অ্যাডজাস্টমেন্ট ইন'],
            'adjustment_out' => ['en' => 'Adjustment Out', 'bn' => 'অ্যাডজাস্টমেন্ট আউট'],
            'transfer_in'    => ['en' => 'Transfer In', 'bn' => 'ট্রান্সফার ইন'],
            'transfer_out'   => ['en' => 'Transfer Out', 'bn' => 'ট্রান্সফার আউট'],
        ];

        return $labels[$this->type][app()->getLocale()] ?? $this->type;
    }
}
