<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_movement_requests', function (Blueprint $table) {
            /*
             * Only used when type = 'transfer': the location that should
             * receive the stock. `location_id` continues to mean the
             * location the movement is applied against (the transfer's
             * source, in that case).
             */
            $table->foreignId('destination_location_id')
                ->nullable()
                ->after('location_id')
                ->constrained('locations')
                ->noActionOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('stock_movement_requests', function (Blueprint $table) {
            $table->dropConstrainedForeignId('destination_location_id');
        });
    }
};
