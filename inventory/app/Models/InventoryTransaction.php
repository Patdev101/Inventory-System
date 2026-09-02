<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
    'inventory_id',
    'product_id',
    'location_id',
    'type',
    'quantity',
    'base_quantity',
    'product_unit_id',
    'conversion_factor',
    'reference',
    'notes',
];


    protected $casts = [
    'quantity' => 'decimal:4',
    'base_quantity' => 'decimal:4',
    'conversion_factor' => 'decimal:4',
];


    /**
     * Inventory may be NULL when the inventory
     * record has been deleted.
     *
     * The transaction itself remains as an audit record.
     */
    public function inventory(): BelongsTo
    {
        return $this->belongsTo(
            Inventory::class,
            'inventory_id'
        );
    }

    /**
     * Product stored directly on the transaction.
     *
     * This allows the audit record to retain its
     * product reference even after the inventory
     * record itself has been deleted.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(
            Product::class,
            'product_id'
        );
    }

    /**
     * Location stored directly on the transaction.
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(
            Location::class,
            'location_id'
        );
    }

    /**
     * Product unit used for this transaction.
     */
    public function productUnit(): BelongsTo
    {
        return $this->belongsTo(
            ProductUnit::class,
            'product_unit_id'
        );
    }

    /**
     * Get the signed base quantity.
     *
     * IN  = positive
     * OUT = negative
     *
     * Example:
     *
     * IN  10  -> +10
     * OUT  3  -> -3
     */
    public function getSignedBaseQuantity(): float
    {
        $quantity = (float) $this->base_quantity;

        return $this->type === 'out'
            ? -$quantity
            : $quantity;
    }

    /**
     * Determine whether this transaction is an IN movement.
     */
    public function isIn(): bool
    {
        return $this->type === 'in';
    }

    /**
     * Determine whether this transaction is an OUT movement.
     */
    public function isOut(): bool
    {
        return $this->type === 'out';
    }

    /**
     * Get a human-readable transaction direction.
     */
    public function getDirectionLabelAttribute(): string
    {
        return $this->isIn()
            ? 'Stock In'
            : 'Stock Out';
    }

    /**
     * Get signed quantity for display.
     */
    public function getSignedQuantity(): float
    {
        $quantity = (float) $this->quantity;

        return $this->isOut()
            ? -$quantity
            : $quantity;
    }
}
