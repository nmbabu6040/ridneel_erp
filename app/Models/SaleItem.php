<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaleItem extends Model
{
    use HasFactory;

    protected $fillable = ['sale_id', 'product_id', 'unit_id', 'quantity', 'base_quantity', 'batch_no', 'unit_price', 'discount', 'subtotal'];

    protected $casts = [
        'quantity'   => 'integer',
        'unit_price' => 'decimal:2',
        'discount'   => 'decimal:2',
        'subtotal'   => 'decimal:2',
    ];

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /** The unit this line was sold in — null means the product's base unit. */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    /** Display-friendly unit label — falls back to the product's base unit when this line didn't use a secondary unit. */
    public function unitLabel(): ?string
    {
        return $this->unit?->short_code ?? $this->product?->unit?->short_code;
    }
}
