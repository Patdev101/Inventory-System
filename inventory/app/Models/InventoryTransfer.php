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

        // pre-existing columns (added by an earlier migration, now
        // wired up here for the first time)
        'status',
        'received_at',
        'received_by',

        // new audit/receiver workflow columns
        'receiver_id',
        'receiver_role',
        'audit_status',
        'audited_by',
        'audited_at',
        'audit_notes',
    ];

    protected $casts = [
        'conversion_factor' => 'decimal:4',
        'quantity' => 'decimal:4',
        'base_quantity' => 'decimal:4',
        'received_at' => 'datetime',
        'audited_at' => 'datetime',
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

    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'received_by'
        );
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'receiver_id'
        );
    }

    public function auditedBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'audited_by'
        );
    }
}