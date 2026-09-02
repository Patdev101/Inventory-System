<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /*
         * Remove the old foreign key if one exists.
         *
         * The original create_inventory_transactions_table migration
         * never defines this foreign key, so it can only be present
         * on SQL Server environments whose migration history predates
         * that migration. Laravel's Schema builder does not provide a
         * portable "does this foreign key exist" check, so this
         * lookup is SQL Server specific and skipped everywhere else.
         */
        if (DB::getDriverName() === 'sqlsrv') {
            $foreignKeys = DB::select("
                SELECT fk.name
                FROM sys.foreign_keys fk
                INNER JOIN sys.tables t
                    ON fk.parent_object_id = t.object_id
                WHERE t.name = 'inventory_transactions'
                  AND fk.name = 'inventory_transactions_inventory_id_foreign'
            ");

            if (!empty($foreignKeys)) {
                Schema::table('inventory_transactions', function (Blueprint $table) {
                    $table->dropForeign(
                        'inventory_transactions_inventory_id_foreign'
                    );
                });
            }
        }

        /*
         * inventory_id must be nullable because
         * transactions must survive inventory deletion.
         */
        Schema::table('inventory_transactions', function (Blueprint $table) {
            $table->unsignedBigInteger('inventory_id')
                ->nullable()
                ->change();
        });

        /*
         * Re-create the foreign key.
         *
         * When inventory is deleted:
         *
         * inventory_id -> NULL
         *
         * Transaction remains.
         */
        Schema::table('inventory_transactions', function (Blueprint $table) {
            $table->foreign('inventory_id')
                ->references('id')
                ->on('inventories')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('inventory_transactions', function (Blueprint $table) {
            $table->dropForeign([
                'inventory_id'
            ]);
        });

        Schema::table('inventory_transactions', function (Blueprint $table) {
            $table->unsignedBigInteger('inventory_id')
                ->nullable(false)
                ->change();
        });

        Schema::table('inventory_transactions', function (Blueprint $table) {
            $table->foreign('inventory_id')
                ->references('id')
                ->on('inventories')
                ->cascadeOnDelete();
        });
    }
};