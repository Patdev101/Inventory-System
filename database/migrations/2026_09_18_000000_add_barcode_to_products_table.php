<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('barcode')->nullable()->after('sku');
        });

        // A plain unique index rejects a table with more than one NULL
        // barcode on SQL Server (NULL is treated as an ordinary value
        // there, unlike MySQL/Postgres), so scope the uniqueness to rows
        // that actually have a barcode.
        DB::statement(
            'CREATE UNIQUE INDEX products_barcode_unique ON products (barcode) WHERE barcode IS NOT NULL'
        );
    }

    public function down(): void
    {
        DB::statement('DROP INDEX products_barcode_unique ON products');

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('barcode');
        });
    }
};
