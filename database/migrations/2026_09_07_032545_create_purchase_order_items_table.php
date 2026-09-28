<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_order_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('purchase_order_id')
                ->constrained('purchase_orders')
                ->cascadeOnDelete();

            $table->foreignId('product_id')
                ->constrained('products');

            $table->foreignId('product_unit_id')
                ->constrained('product_units');

            $table->decimal('quantity_ordered', 18, 4);

            /*
             * Denormalized running total, kept in sync by
             * PurchaseOrderReceiptItem rows created during receiving.
             * Convenient for "ordered vs received vs remaining" display
             * without summing every receipt each time.
             */
            $table->decimal('quantity_received', 18, 4)->default(0);

            $table->decimal('unit_price', 18, 4);

            $table->timestamps();

            $table->index('purchase_order_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_order_items');
    }
};