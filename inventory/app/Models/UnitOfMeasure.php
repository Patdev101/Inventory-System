<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UnitOfMeasure extends Model
{
    use HasFactory;

    protected $table = 'units_of_measure';

    protected $fillable = [
        'name',
        'code',
        'description',
    ];

    /**
     * Products using this unit.
     */
    public function productUnits(): HasMany
    {
        return $this->hasMany(
            ProductUnit::class,
            'unit_of_measure_id'
        );
    }

    /**
     * Products using this as their base unit.
     */
    public function baseUnitProducts(): HasMany
    {
        return $this->hasMany(
            Product::class,
            'base_unit_id'
        );
    }
}
