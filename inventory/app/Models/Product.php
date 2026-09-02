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
    ];

    protected $casts = [
        'reorder_point' => 'decimal:4',
        'selling_price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

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
