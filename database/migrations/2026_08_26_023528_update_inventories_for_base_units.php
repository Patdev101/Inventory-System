<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /*
         * The inventory unique index was already changed
         * by the earlier inventory migrations.
         *
         * The final intended rule is:
         *
         * Product + Location = one inventory record.
         *
         * ProductUnit is only the current display unit.
         */

        /*
         * The unique index this migration wants
         * (product_id + location_id) was already created by the
         * earlier change_inventory_unique_product_location migration,
         * on every driver.
         *
         * This migration only exists to defensively (re)create that
         * index on SQL Server environments whose migration history
         * predates that earlier migration. Laravel's Schema builder
         * does not provide a portable hasIndex() method, so the
         * existence check below is SQL Server specific and this
         * migration is a no-op everywhere else.
         */

        if (\DB::getDriverName() !== 'sqlsrv') {
            return;
        }

        $indexExists = \DB::selectOne("
            SELECT 1 AS [exists]
            FROM sys.indexes
            WHERE name = 'inventories_product_location_unique'
              AND object_id = OBJECT_ID('dbo.inventories')
        ");

        if (!$indexExists) {
            Schema::table('inventories', function (Blueprint $table) {
                $table->unique(
                    [
                        'product_id',
                        'location_id',
                    ],
                    'inventories_product_location_unique'
                );
            });
        }
    }

    public function down(): void
    {
        if (\DB::getDriverName() !== 'sqlsrv') {
            return;
        }

        $indexExists = \DB::selectOne("
            SELECT 1 AS [exists]
            FROM sys.indexes
            WHERE name = 'inventories_product_location_unique'
              AND object_id = OBJECT_ID('dbo.inventories')
        ");

        if ($indexExists) {
            Schema::table('inventories', function (Blueprint $table) {
                $table->dropUnique(
                    'inventories_product_location_unique'
                );
            });
        }
    }
};
