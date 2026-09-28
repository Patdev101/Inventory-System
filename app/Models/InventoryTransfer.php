<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\InventoryTransferReceipt;

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

        // Partial receiving
        'received_quantity',
        'received_base_quantity',

        'reference',
        'notes',

        // Transfer status
        'status',
        'received_at',
        'received_by',

        // Receiver workflow
        'receiver_id',
        'receiver_role',

        // Audit workflow
        'audit_status',
        'audited_by',
        'audited_at',
        'audit_notes',
    ];

    protected $casts = [
        'conversion_factor' => 'decimal:4',
        'quantity' => 'decimal:4',
        'base_quantity' => 'decimal:4',

        // Partial receiving
        'received_quantity' => 'decimal:4',
        'received_base_quantity' => 'decimal:4',

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

    public function receipts()
    {
        return $this->hasMany(
            InventoryTransferReceipt::class,
            'inventory_transfer_id'
        );
    }
}
