<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inventory_transfers', function (Blueprint $table) {
            $table->decimal('received_quantity', 15, 4)
                ->default(0)
                ->after('base_quantity');

            $table->decimal('received_base_quantity', 15, 4)
                ->default(0)
                ->after('received_quantity');
        });
    }

    public function down(): void
    {
        Schema::table('inventory_transfers', function (Blueprint $table) {
            $table->dropColumn([
                'received_quantity',
                'received_base_quantity',
            ]);
        });
    }
};
