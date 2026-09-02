<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductUnit extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'unit_of_measure_id',
        'conversion_factor',
        'is_default',
    ];

    protected $casts = [
        'conversion_factor' => 'decimal:4',
        'is_default' => 'boolean',
    ];

    /**
     * Product.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(
            Product::class
        );
    }

    /**
     * Unit of measure.
     */
    public function unitOfMeasure(): BelongsTo
    {
        return $this->belongsTo(
            UnitOfMeasure::class,
            'unit_of_measure_id'
        );
    }
}
