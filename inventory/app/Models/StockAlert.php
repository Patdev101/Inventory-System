<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockAlert extends Model
{
    use HasFactory;

    protected $fillable = [
        'inventory_id',
        'acknowledged_by',
        'severity',
        'status',
        'base_quantity',
        'reorder_point',
        'acknowledged_at',
        'resolved_at',
    ];

    protected $casts = [
        'base_quantity' => 'decimal:4',
        'reorder_point' => 'decimal:4',
        'acknowledged_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    public function inventory(): BelongsTo
    {
        return $this->belongsTo(Inventory::class);
    }

    public function acknowledgedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'acknowledged_by');
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['open', 'acknowledged']);
    }

    public function isActive(): bool
    {
        return in_array($this->status, ['open', 'acknowledged'], true);
    }
}
