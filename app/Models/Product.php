<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id', 'unit_id', 'name', 'name_bn', 'sku', 'barcode',
        'purchase_price', 'sale_price', 'reorder_level', 'tracks_batches',
        'secondary_unit_id', 'conversion_factor', 'image', 'is_active',
    ];

    protected $casts = [
        'purchase_price'    => 'decimal:2',
        'sale_price'        => 'decimal:2',
        'reorder_level'     => 'integer',
        'tracks_batches'    => 'boolean',
        'conversion_factor' => 'decimal:4',
        'is_active'         => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    /** The product's optional secondary selling/buying unit (e.g. Carton, when the base unit is Piece). */
    public function secondaryUnit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'secondary_unit_id');
    }

    public function hasSecondaryUnit(): bool
    {
        return $this->secondary_unit_id && $this->conversion_factor > 0;
    }

    /** Converts a quantity entered in the secondary unit (e.g. "2 Carton") to the base unit (e.g. 200 Pieces). Pass false for a quantity already in the base unit (no-op, just rounds). */
    public function toBaseQuantity(float $quantity, bool $inSecondaryUnit): int
    {
        if ($inSecondaryUnit && $this->hasSecondaryUnit()) {
            return (int) round($quantity * (float) $this->conversion_factor);
        }

        return (int) round($quantity);
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

    /**
     * Every batch this product currently has stock in, with its
     * remaining quantity and expiry date — grouped straight from the
     * ledger (SUM per batch_no), same derive-don't-store principle as
     * currentStock(). Ordered soonest-expiry-first so both the Sales
     * form (manual FEFO picking) and the Expiry Report read naturally
     * top-to-bottom. Only meaningful when tracks_batches is true;
     * returns an empty collection otherwise.
     *
     * @return Collection<int, object{batch_no: string, expiry_date: ?\Illuminate\Support\Carbon, remaining: int}>
     */
    public function availableBatches(?int $warehouseId = null): Collection
    {
        if (!$this->tracks_batches) {
            return collect();
        }

        $query = $this->stockMovements()
            ->whereNotNull('batch_no')
            ->when($warehouseId, fn ($q) => $q->where('warehouse_id', $warehouseId))
            ->selectRaw('batch_no, MAX(expiry_date) as expiry_date, SUM(quantity) as remaining')
            ->groupBy('batch_no');

        return $query->get()
            ->filter(fn ($row) => $row->remaining > 0)
            ->sortBy('expiry_date')
            ->values();
    }
}
