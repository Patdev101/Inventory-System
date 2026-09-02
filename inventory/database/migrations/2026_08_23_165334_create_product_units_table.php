<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_units', function (Blueprint $table) {
            $table->id();

            $table->foreignId('product_id')
                ->constrained('products')
                ->cascadeOnDelete();

            $table->foreignId('unit_of_measure_id')
                ->constrained('units_of_measure')
                ->noActionOnDelete();

            $table->decimal('conversion_factor', 18, 4);
            $table->boolean('is_default')->default(false);

            $table->timestamps();

            $table->unique(['product_id', 'unit_of_measure_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_units');
    }
};