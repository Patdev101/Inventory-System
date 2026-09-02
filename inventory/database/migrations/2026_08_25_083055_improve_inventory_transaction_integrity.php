<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inventory_transactions', function (Blueprint $table) {
            /*
             * Speeds up transaction history queries.
             */
            $table->index(
                ['created_at', 'id'],
                'inventory_transactions_created_at_id_index'
            );

            /*
             * Useful for product/location movement reports.
             */
            $table->index(
                ['product_id', 'location_id', 'created_at'],
                'inventory_transactions_product_location_date_index'
            );
        });
    }

    public function down(): void
    {
        Schema::table('inventory_transactions', function (Blueprint $table) {
            $table->dropIndex(
                'inventory_transactions_created_at_id_index'
            );

            $table->dropIndex(
                'inventory_transactions_product_location_date_index'
            );
        });
    }
};
