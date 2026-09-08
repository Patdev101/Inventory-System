<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_transfer_receipts', function (Blueprint $table) {
            $table->id();

            $table->foreignId('inventory_transfer_id')
                ->constrained('inventory_transfers')
                ->cascadeOnDelete();

            $table->foreignId('received_by')
                ->constrained('users');

            $table->foreignId('product_unit_id')
                ->constrained('product_units');

            $table->decimal('quantity', 15, 4);

            $table->decimal('base_quantity', 15, 4);

            $table->decimal('conversion_factor', 15, 4);

            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_transfer_receipts');
    }
};
