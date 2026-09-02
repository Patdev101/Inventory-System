<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inventories', function (Blueprint $table) {
            $table->dropUnique(
                'inventories_product_id_location_id_product_unit_id_unique'
            );

            $table->unique(
                ['product_id', 'location_id'],
                'inventories_product_location_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('inventories', function (Blueprint $table) {
            $table->dropUnique(
                'inventories_product_location_unique'
            );

            $table->unique(
                [
                    'product_id',
                    'location_id',
                    'product_unit_id',
                ],
                'inventories_product_id_location_id_product_unit_id_unique'
            );
        });
    }
};
