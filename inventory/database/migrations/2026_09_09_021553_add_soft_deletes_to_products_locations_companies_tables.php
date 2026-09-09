<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('locations', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('companies', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('locations', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('companies', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
