<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventories', function (Blueprint $table) {
            $table->id();

            $table->foreignId('product_id')
                ->constrained('products')
                ->cascadeOnDelete();

            $table->foreignId('location_id')
                ->constrained('locations')
                ->cascadeOnDelete();

            $table->foreignId('product_unit_id')
                ->constrained('product_units');

            $table->decimal('quantity', 18, 4);

            $table->timestamps();

            $table->unique([
                'product_id',
                'location_id',
                'product_unit_id',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventories');
    }
};