<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds columns that App\Models\InventoryTransfer and
 * App\Services\InventoryMovementService (transferStock(), initiateTransfer(),
 * completeTransferReceipt(), reverseTransfer()) have always assumed exist —
 * the model's own fillable list even comments "pre-existing columns (added
 * by an earlier migration)". No such migration exists in this repository,
 * so every fresh install/test run was missing `status`, `received_by`, and
 * `received_at` on `inventory_transfers`, breaking every inventory-transfer
 * code path (both the legacy immediate transfer and the newer audited
 * receive workflow) with "no column named status".
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inventory_transfers', function (Blueprint $table) {
            if (!Schema::hasColumn('inventory_transfers', 'status')) {
                // Existing rows created before this fix (if any, on an
                // environment where the column was added by hand) are
                // assumed completed, matching transferStock()'s immediate
                // behavior — the only path capable of creating a transfer
                // before the audited workflow existed.
                $table->string('status', 20)->default('completed');
            }

            if (!Schema::hasColumn('inventory_transfers', 'received_by')) {
                $table->foreignId('received_by')->nullable();
            }

            if (!Schema::hasColumn('inventory_transfers', 'received_at')) {
                $table->timestamp('received_at')->nullable();
            }
        });

        // Separate FK call, consistent with this codebase's established
        // pattern for avoiding SQL Server's multiple-cascade-path
        // restriction on users.id.
        if (
            Schema::hasColumn('inventory_transfers', 'received_by')
            && !$this->hasForeignKey('inventory_transfers', 'received_by')
        ) {
            Schema::table('inventory_transfers', function (Blueprint $table) {
                $table->foreign('received_by')
                    ->references('id')->on('users')
                    ->onDelete('no action')
                    ->onUpdate('no action');
            });
        }
    }

    public function down(): void
    {
        Schema::table('inventory_transfers', function (Blueprint $table) {
            if ($this->hasForeignKey('inventory_transfers', 'received_by')) {
                $table->dropForeign(['received_by']);
            }

            $table->dropColumn(['status', 'received_by', 'received_at']);
        });
    }

    /**
     * SQLite's schema introspection doesn't expose named foreign key
     * constraints the same way SQL Server does, so guard the FK-add step
     * defensively rather than assuming Schema::hasColumn is enough.
     */
    private function hasForeignKey(string $table, string $column): bool
    {
        try {
            foreach (Schema::getForeignKeys($table) as $foreignKey) {
                if (in_array($column, $foreignKey['columns'] ?? [], true)) {
                    return true;
                }
            }
        } catch (\Throwable) {
            return false;
        }

        return false;
    }
};
