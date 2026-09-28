<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // SQL Server treats NULL as a value in a plain unique index, so only one
    // product could ever have no SKU. Scope uniqueness to real SKUs, the same
    // way barcode and item_code already are.
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'sqlsrv') {
            return;
        }

        DB::statement('DROP INDEX products_sku_unique ON products');
        DB::statement('CREATE UNIQUE INDEX products_sku_unique ON products (sku) WHERE sku IS NOT NULL');
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'sqlsrv') {
            return;
        }

        DB::statement('DROP INDEX products_sku_unique ON products');
        DB::statement('CREATE UNIQUE INDEX products_sku_unique ON products (sku)');
    }
};
