<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();

            $table->string('po_number', 30)->unique();

            $table->foreignId('company_id')
                ->constrained('companies')
                ->cascadeOnDelete();

            $table->foreignId('supplier_id')
                ->constrained('suppliers');

            /*
             * The warehouse location this PO will be received into.
             */
            $table->foreignId('location_id')
                ->constrained('locations');

            /*
             * draft, pending_approval, approved, rejected, ordered,
             * partially_received, received, completed, cancelled
             */
            $table->string('status', 30)->default('draft');

            $table->string('reference', 255)->nullable();
            $table->text('notes')->nullable();
            $table->date('expected_delivery_date')->nullable();

            $table->foreignId('created_by')->constrained('users');

            $table->foreignId('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->text('approval_notes')->nullable();
            $table->text('rejection_reason')->nullable();

            $table->timestamps();

            $table->index(['company_id', 'status']);
        });

        // Separate FK call to avoid SQL Server's multiple-cascade-path
        // restriction, consistent with how receiver_id/audited_by were
        // added to inventory_transfers earlier.
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->foreign('approved_by')
                ->references('id')->on('users')
                ->onDelete('no action')
                ->onUpdate('no action');
        });
    }

    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropForeign(['approved_by']);
        });

        Schema::dropIfExists('purchase_orders');
    }
};