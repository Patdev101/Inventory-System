<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_transfers', function (Blueprint $table) {
            $table->id();

            /*
             * Source inventory record.
             */
            $table->foreignId('source_inventory_id')
                ->constrained('inventories');

            /*
             * Destination inventory record.
             */
            $table->foreignId('destination_inventory_id')
                ->constrained('inventories');

            /*
             * Product being transferred.
             */
            $table->foreignId('product_id')
                ->constrained('products');

            /*
             * Unit selected for this transfer.
             */
            $table->foreignId('product_unit_id')
                ->constrained('product_units');

            /*
             * Historical conversion factor used
             * for this specific transfer.
             */
            $table->decimal('conversion_factor', 18, 4)
                ->default(1);

            /*
             * Quantity entered by the user.
             *
             * Example:
             * 2 BOX
             */
            $table->decimal('quantity', 18, 4);

            /*
             * Normalized quantity in base units.
             *
             * Example:
             * 2 BOX × 12 = 24 base units
             */
            $table->decimal('base_quantity', 18, 4);

            /*
             * Optional transfer reference.
             */
            $table->string('reference')
                ->nullable();

            /*
             * Optional notes.
             */
            $table->text('notes')
                ->nullable();

            $table->timestamps();

            /*
             * Useful indexes.
             */
            $table->index([
                'product_id',
                'created_at',
            ]);

            $table->index('source_inventory_id');
            $table->index('destination_inventory_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_transfers');
    }
};
