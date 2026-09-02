<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'address',
        'phone',
        'email',
    ];

    /**
     * Locations belonging to this company.
     */
    public function locations(): HasMany
    {
        return $this->hasMany(
            Location::class
        );
    }

    /**
     * Products belonging to this company.
     */
    public function products(): HasMany
    {
        return $this->hasMany(
            Product::class
        );
    }
}
