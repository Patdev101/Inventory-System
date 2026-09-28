<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            /*
             * Nullable and with no default touching existing rows —
             * existing selling_price values are preserved untouched.
             */
            $table->decimal('cost_price', 18, 4)
                ->nullable()
                ->after('selling_price');

            $table->decimal('markup_percentage', 8, 4)
                ->nullable()
                ->after('cost_price');

            $table->string('pricing_method', 20)
                ->default('manual')
                ->after('markup_percentage');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'cost_price',
                'markup_percentage',
                'pricing_method',
            ]);
        });
    }
};
