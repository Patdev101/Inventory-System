<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inventory_transfers', function (Blueprint $table) {
            if (!Schema::hasColumn('inventory_transfers', 'receiver_id')) {
                $table->foreignId('receiver_id')->nullable()->after('status');
            }

            if (!Schema::hasColumn('inventory_transfers', 'receiver_role')) {
                $table->string('receiver_role', 20)->nullable()->after('receiver_id');
            }

            if (!Schema::hasColumn('inventory_transfers', 'audit_status')) {
                $table->string('audit_status', 20)->default('pending')->after('receiver_role');
            }

            if (!Schema::hasColumn('inventory_transfers', 'audited_by')) {
                $table->foreignId('audited_by')->nullable()->after('audit_status');
            }

            if (!Schema::hasColumn('inventory_transfers', 'audited_at')) {
                $table->timestamp('audited_at')->nullable()->after('audited_by');
            }

            if (!Schema::hasColumn('inventory_transfers', 'audit_notes')) {
                $table->text('audit_notes')->nullable()->after('audited_at');
            }
        });

        // Add FKs separately with NO ACTION to avoid SQL Server's
        // multiple-cascade-path restriction (received_by already
        // cascades SET NULL to users).
        Schema::table('inventory_transfers', function (Blueprint $table) {
            $table->foreign('receiver_id')
                ->references('id')->on('users')
                ->onDelete('no action')
                ->onUpdate('no action');

            $table->foreign('audited_by')
                ->references('id')->on('users')
                ->onDelete('no action')
                ->onUpdate('no action');
        });
    }

    public function down(): void
    {
        Schema::table('inventory_transfers', function (Blueprint $table) {
            if (Schema::hasColumn('inventory_transfers', 'receiver_id')) {
                $table->dropForeign(['receiver_id']);
            }
            if (Schema::hasColumn('inventory_transfers', 'audited_by')) {
                $table->dropForeign(['audited_by']);
            }

            $table->dropColumn([
                'receiver_id',
                'receiver_role',
                'audit_status',
                'audited_by',
                'audited_at',
                'audit_notes',
            ]);
        });
    }
};