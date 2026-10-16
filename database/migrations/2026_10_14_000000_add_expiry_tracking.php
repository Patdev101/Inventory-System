<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Expiry dates.
 *
 * Any stock coming in (added, adjusted IN, received from a purchase order or
 * a transfer) can carry an optional expiry date, kept on that movement. The
 * app works out which of those deliveries are still on the shelf (oldest
 * stock goes out first) and warns before they expire.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inventory_transactions', function (Blueprint $table) {
            $table->date('expiry_date')->nullable();
            $table->index(['inventory_id', 'type', 'expiry_date']);
        });

        Schema::table('stock_movement_requests', function (Blueprint $table) {
            $table->date('expiry_date')->nullable();
        });

        Schema::table('inventory_transfers', function (Blueprint $table) {
            $table->date('expiry_date')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('inventory_transfers', function (Blueprint $table) {
            $table->dropColumn('expiry_date');
        });

        Schema::table('stock_movement_requests', function (Blueprint $table) {
            $table->dropColumn('expiry_date');
        });

        Schema::table('inventory_transactions', function (Blueprint $table) {
            $table->dropIndex(['inventory_id', 'type', 'expiry_date']);
            $table->dropColumn('expiry_date');
        });
    }
};
