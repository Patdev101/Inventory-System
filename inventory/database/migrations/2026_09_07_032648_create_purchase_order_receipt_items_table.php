<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_order_receipt_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('purchase_order_receipt_id')
                ->constrained('purchase_order_receipts')
                ->cascadeOnDelete();

            $table->foreignId('purchase_order_item_id');

            /*
             * Quantity actually received in THIS delivery event, for
             * THIS line item. Sum of these across all receipts for a
             * given purchase_order_item equals its quantity_received.
             */
            $table->decimal('quantity_received', 18, 4);

            /*
             * Per-line discrepancy note, e.g. "damaged", "wrong item",
             * separate from the receipt-level general notes.
             */
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index('purchase_order_receipt_id');
            $table->index('purchase_order_item_id');
        });

        // Separate FK with NO ACTION to avoid SQL Server's
        // multiple-cascade-path restriction (this table already
        // cascades to purchase_order_receipts, which itself cascades
        // to purchase_orders — a second cascade path through
        // purchase_order_items is not allowed).
        Schema::table('purchase_order_receipt_items', function (Blueprint $table) {
            $table->foreign('purchase_order_item_id')
                ->references('id')->on('purchase_order_items')
                ->onDelete('no action')
                ->onUpdate('no action');
        });
    }

    public function down(): void
    {
        Schema::table('purchase_order_receipt_items', function (Blueprint $table) {
            $table->dropForeign(['purchase_order_item_id']);
        });

        Schema::dropIfExists('purchase_order_receipt_items');
    }
};