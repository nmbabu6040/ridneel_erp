<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id', 'unit_id', 'name', 'name_bn', 'sku', 'barcode',
        'purchase_price', 'sale_price', 'reorder_level', 'image', 'is_active',
    ];

    protected $casts = [
        'purchase_price' => 'decimal:2',
        'sale_price'     => 'decimal:2',
        'reorder_level'  => 'integer',
        'is_active'      => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function displayName(): string
    {
        return app()->getLocale() === 'bn' && $this->name_bn ? $this->name_bn : $this->name;
    }

    /**
     * Current on-hand quantity, either across all warehouses (default)
     * or for one specific warehouse. Always derived live from the
     * ledger — see stock_movements migration for why.
     */
    public function currentStock(?int $warehouseId = null): int
    {
        $query = $this->stockMovements();

        if ($warehouseId) {
            $query->where('warehouse_id', $warehouseId);
        }

        return (int) $query->sum('quantity');
    }

    public function isLowStock(): bool
    {
        return $this->reorder_level > 0 && $this->currentStock() <= $this->reorder_level;
    }
}
