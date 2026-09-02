<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryTransfer extends Model
{
    use HasFactory;

    protected $table = 'inventory_transfers';

    protected $fillable = [
        'source_inventory_id',
        'destination_inventory_id',
        'product_id',
        'product_unit_id',
        'conversion_factor',
        'quantity',
        'base_quantity',
        'reference',
        'notes',
    ];

    protected $casts = [
        'conversion_factor' => 'decimal:4',
        'quantity' => 'decimal:4',
        'base_quantity' => 'decimal:4',
    ];

    public function sourceInventory(): BelongsTo
    {
        return $this->belongsTo(
            Inventory::class,
            'source_inventory_id'
        );
    }

    public function destinationInventory(): BelongsTo
    {
        return $this->belongsTo(
            Inventory::class,
            'destination_inventory_id'
        );
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(
            Product::class,
            'product_id'
        );
    }

    public function productUnit(): BelongsTo
    {
        return $this->belongsTo(
            ProductUnit::class,
            'product_unit_id'
        );
    }
}
