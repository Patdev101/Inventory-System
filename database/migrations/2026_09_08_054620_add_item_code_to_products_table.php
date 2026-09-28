<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('item_code', 100)
                ->nullable()
                ->after('sku');
        });

        DB::statement(
            'CREATE UNIQUE INDEX products_item_code_unique ON products (item_code) WHERE item_code IS NOT NULL'
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP INDEX products_item_code_unique ON products');

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('item_code');
        });
    }
};
