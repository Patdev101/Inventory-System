<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Expiry dates are optional on every stock-in, so products no longer have a
 * "this product expires" setting. Removes it from databases that got it from
 * an earlier version of the expiry migration.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('products', 'tracks_expiry')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn('tracks_expiry');
            });
        }
    }

    public function down(): void
    {
        // Nothing to restore: the setting is no longer used.
    }
};