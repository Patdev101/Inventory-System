<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The company and location a user works at. Staff and managers only see
     * and change data for this location (and pick transfer destinations
     * inside this company); admins are not restricted.
     *
     * Plain indexed columns, not foreign keys: SQL Server rejects the
     * multiple cascade paths a second foreign key to locations would add.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('company_id')->nullable()->index();
            $table->unsignedBigInteger('location_id')->nullable()->index();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['company_id']);
            $table->dropIndex(['location_id']);
            $table->dropColumn(['company_id', 'location_id']);
        });
    }
};
