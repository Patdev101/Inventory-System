<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_movement_requests', function (Blueprint $table) {
            $table->id();

            $table->foreignId('inventory_id')
                ->nullable()
                ->constrained('inventories')
                ->nullOnDelete();

            $table->foreignId('product_id')
                ->constrained('products')
                ->onDelete('no action');

            $table->foreignId('location_id')
                ->constrained('locations')
                ->onDelete('no action');

            $table->foreignId('product_unit_id')
                ->constrained('product_units')
                ->onDelete('no action');

            $table->string('type', 10);
            $table->decimal('quantity', 18, 4);
            $table->string('status', 20)->default('pending');
            $table->text('notes')->nullable();
            $table->text('rejection_reason')->nullable();

            $table->foreignId('requested_by')
                ->constrained('users')
                ->onDelete('no action');

            $table->foreignId('reviewed_by')
                ->nullable()
                ->constrained('users')
                ->onDelete('no action');

            $table->timestamp('reviewed_at')->nullable();

            $table->timestamps();

            $table->index(['status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movement_requests');
    }
};
