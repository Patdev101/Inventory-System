<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();

            $table->foreignId('product_category_id')
                ->constrained('product_categories')
                ->noActionOnDelete();

            $table->foreignId('company_id')
                ->constrained('companies')
                ->noActionOnDelete();

            $table->string('name', 200);

            $table->string('sku', 100)
                ->nullable()
                ->unique();

            $table->text('description')
                ->nullable();

            $table->foreignId('base_unit_id')
                ->constrained('units_of_measure')
                ->noActionOnDelete();

            $table->decimal(
                'reorder_point',
                20,
                4
            )->default(0);

            $table->boolean('is_active')
                ->default(true);

            $table->timestamps();

            $table->index('product_category_id');
            $table->index('company_id');
            $table->index('base_unit_id');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
