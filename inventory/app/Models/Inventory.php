<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Inventory extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'location_id',
        'product_unit_id',
        'conversion_factor',
        'quantity',
        'base_quantity',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
        'conversion_factor' => 'decimal:4',
        'base_quantity' => 'decimal:4',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(
            Product::class,
            'product_id'
        );
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(
            Location::class,
            'location_id'
        );
    }

    public function productUnit(): BelongsTo
    {
        return $this->belongsTo(
            ProductUnit::class,
            'product_unit_id'
        );
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(
            InventoryTransaction::class,
            'inventory_id'
        );
    }

    public function scopeOutOfStock(Builder $query): Builder
    {
        return $query->where('base_quantity', '<=', 0);
    }

    public function scopeCriticalStock(Builder $query): Builder
    {
        return $query
            ->where('base_quantity', '>', 0)
            ->whereHas('product', function (Builder $productQuery) {
                $productQuery
                    ->whereNotNull('reorder_point')
                    ->where('reorder_point', '>', 0)
                    ->whereRaw(
                        'inventories.base_quantity <= (products.reorder_point / 2)'
                    );
            });
    }

    public function scopeLowStock(Builder $query): Builder
    {
        return $query
            ->where('base_quantity', '>', 0)
            ->whereHas('product', function (Builder $productQuery) {
                $productQuery
                    ->whereNotNull('reorder_point')
                    ->where('reorder_point', '>', 0)
                    ->whereRaw(
                        'inventories.base_quantity > (products.reorder_point / 2)'
                    )
                    ->whereColumn(
                        'inventories.base_quantity',
                        '<=',
                        'products.reorder_point'
                    );
            });
    }

    public function hasStock(): bool
    {
        return $this->getBaseQuantityValue() > 0;
    }

    public function isOutOfStock(): bool
    {
        return $this->getBaseQuantityValue() <= 0;
    }

    public function isCriticalStock(): bool
    {
        $baseQuantity = $this->getBaseQuantityValue();
        $reorderPoint = $this->getReorderPointValue();

        if ($baseQuantity <= 0 || $reorderPoint <= 0) {
            return false;
        }

        return $baseQuantity <= ($reorderPoint / 2);
    }

    public function isLowStock(): bool
    {
        $baseQuantity = $this->getBaseQuantityValue();
        $reorderPoint = $this->getReorderPointValue();

        if ($baseQuantity <= 0 || $reorderPoint <= 0) {
            return false;
        }

        return $baseQuantity <= $reorderPoint;
    }

    public function isInStock(): bool
    {
        return !$this->isOutOfStock()
            && !$this->isLowStock()
            && !$this->isCriticalStock();
    }

    public function getBaseQuantityValue(): float
    {
        return (float) $this->base_quantity;
    }

    public function getQuantityValue(): float
    {
        return (float) $this->quantity;
    }

    public function getReorderPointValue(): float
    {
        return $this->product
            ? (float) $this->product->reorder_point
            : 0.0;
    }

    public function getStockStatus(): string
    {
        if ($this->isOutOfStock()) {
            return 'Out of Stock';
        }

        if ($this->isCriticalStock()) {
            return 'Critical';
        }

        if ($this->isLowStock()) {
            return 'Low Stock';
        }

        return 'In Stock';
    }
}
