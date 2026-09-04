<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Product extends Model
{
    use HasFactory;

    public const PRICING_METHOD_MANUAL = 'manual';
    public const PRICING_METHOD_MARKUP = 'markup';

    protected $fillable = [
        'product_category_id',
        'name',
        'sku',
        'description',
        'base_unit_id',
        'company_id',
        'reorder_point',
        'is_active',
        'selling_price',
        'cost_price',
        'markup_percentage',
        'pricing_method',
    ];

    protected $casts = [
        'reorder_point' => 'decimal:4',
        'selling_price' => 'decimal:2',
        'cost_price' => 'decimal:4',
        'markup_percentage' => 'decimal:4',
        'is_active' => 'boolean',
    ];

    protected $appends = [
        'profit',
        'profit_margin',
    ];

    /**
     * selling_price - cost_price. Null when there is no cost price to
     * compare against, rather than silently treating it as zero.
     */
    public function getProfitAttribute(): ?float
    {
        if ($this->cost_price === null) {
            return null;
        }

        return round(
            (float) $this->selling_price - (float) $this->cost_price,
            4
        );
    }

    /**
     * ((selling_price - cost_price) / selling_price) * 100.
     * Null when there is no cost price, or when selling_price is 0
     * (division by zero is never performed).
     */
    public function getProfitMarginAttribute(): ?float
    {
        $profit = $this->getProfitAttribute();

        if ($profit === null) {
            return null;
        }

        $sellingPrice = (float) $this->selling_price;

        if ($sellingPrice <= 0) {
            return null;
        }

        return round(($profit / $sellingPrice) * 100, 4);
    }

    /**
     * cost_price + (cost_price * markup_percentage / 100).
     * Used server-side so the client's calculation is never trusted.
     */
    public static function calculateMarkupSellingPrice(
        float $costPrice,
        float $markupPercentage
    ): float {
        return round(
            $costPrice + ($costPrice * $markupPercentage / 100),
            2
        );
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(
            ProductCategory::class,
            'product_category_id'
        );
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(
            Company::class,
            'company_id'
        );
    }

    public function baseUnit(): BelongsTo
    {
        return $this->belongsTo(
            UnitOfMeasure::class,
            'base_unit_id'
        );
    }

    public function productUnits(): HasMany
    {
        return $this->hasMany(
            ProductUnit::class
        );
    }

    public function defaultProductUnit(): HasOne
    {
        return $this->hasOne(
            ProductUnit::class
        )->where(
            'is_default',
            true
        );
    }

    public function inventories(): HasMany
    {
        return $this->hasMany(
            Inventory::class
        );
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(
            InventoryTransaction::class
        );
    }

    public function getReorderPointValue(): float
    {
        return (float) $this->reorder_point;
    }

    public function hasReorderPoint(): bool
    {
        return $this->getReorderPointValue() > 0;
    }
}
