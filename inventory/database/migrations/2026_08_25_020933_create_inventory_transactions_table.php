<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_transactions', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('inventory_id');

            $table->unsignedBigInteger('product_id');

            $table->unsignedBigInteger('location_id');

            $table->unsignedBigInteger('product_unit_id')->nullable();

            $table->decimal('quantity', 18, 4);

            $table->decimal('base_quantity', 18, 4);

            /*
             * IN
             * OUT
             * ADJUSTMENT
             */
            $table->string('type', 30);

            $table->string('reference')->nullable();

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index([
                'product_id',
                'location_id',
            ]);

            $table->index('inventory_id');

            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_transactions');
    }
};