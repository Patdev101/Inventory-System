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
         * -------------------------------------------------------------
         * Add the new ERP product fields.
         * -------------------------------------------------------------
         *
         * We initially make base_unit_id and company_id nullable
         * because existing products already exist in the database.
         *
         * After existing data has been migrated, these can be made
         * required if desired.
         */

        /*
         * On a legacy database, products still have the original
         * "code" column and this migration renames it to "sku".
         *
         * On a fresh install, create_products_table already creates
         * "sku" directly, so there is no "code" column to rename and
         * the block below (and the unique-index swap that follows)
         * is skipped entirely.
         */
        $hasLegacyCodeColumn = Schema::hasColumn('products', 'code');

        if ($hasLegacyCodeColumn) {
            Schema::table('products', function (Blueprint $table) {
                /*
                 * Rename the old "code" field to "sku".
                 *
                 * The existing values are preserved.
                 */
                $table->renameColumn('code', 'sku');
            });
        }

        /*
         * On a fresh install, create_products_table already creates
         * base_unit_id, company_id, reorder_point, and is_active
         * directly, so there is nothing left for this migration to
         * add. On a legacy database created before those columns
         * existed, this block adds them.
         */
        $needsErpColumns = !Schema::hasColumn('products', 'base_unit_id');

        if ($needsErpColumns) {
            Schema::table('products', function (Blueprint $table) {

                /*
                 * Base unit of the product.
                 */
                $table->foreignId('base_unit_id')
                    ->nullable()
                    ->after('description')
                    ->constrained('units_of_measure')
                    ->noActionOnDelete();

                /*
                 * Company that owns the product.
                 */
                $table->foreignId('company_id')
                    ->nullable()
                    ->after('base_unit_id')
                    ->constrained('companies')
                    ->noActionOnDelete();

                /*
                 * Minimum stock level before the product
                 * should be considered for reorder.
                 */
                $table->decimal('reorder_point', 18, 4)
                    ->default(0)
                    ->after('company_id');

                /*
                 * Product active/inactive status.
                 */
                $table->boolean('is_active')
                    ->default(true)
                    ->after('reorder_point');
            });
        }


        /*
         * -------------------------------------------------------------
         * Make SKU optional.
         * -------------------------------------------------------------
         *
         * The original code field was required and unique.
         * SKU should now be optional.
         *
         * We remove the old unique index first, then make the
         * field nullable.
         */

        if ($hasLegacyCodeColumn) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropUnique('products_code_unique');
            });

            Schema::table('products', function (Blueprint $table) {
                $table->string('sku', 100)
                    ->nullable()
                    ->change();

                $table->unique('sku');
            });
        }


        /*
         * -------------------------------------------------------------
         * Populate base_unit_id for existing products.
         * -------------------------------------------------------------
         *
         * Existing products already have product_units.
         *
         * We use:
         *
         * 1. The default product unit, if one exists.
         * 2. Otherwise the first product unit.
         *
         * This allows existing products to continue working.
         */

        if ($needsErpColumns) {
            $products = DB::table('products')
                ->select('id')
                ->get();

            foreach ($products as $product) {

                $productUnit = DB::table('product_units')
                    ->where('product_id', $product->id)
                    ->orderByDesc('is_default')
                    ->orderBy('id')
                    ->first();

                if ($productUnit) {

                    DB::table('products')
                        ->where('id', $product->id)
                        ->update([
                            'base_unit_id' =>
                                $productUnit->unit_of_measure_id,
                        ]);
                }
            }
        }


        /*
         * -------------------------------------------------------------
         * Assign an existing company to existing products.
         * -------------------------------------------------------------
         *
         * We do NOT guess which company owns an existing product.
         *
         * Therefore company_id remains nullable for existing records.
         *
         * New product creation will require a company.
         */
    }

    public function down(): void
    {
        /*
         * Remove foreign keys/columns added by this migration.
         */

        Schema::table('products', function (Blueprint $table) {

            $table->dropForeign([
                'base_unit_id',
            ]);

            $table->dropForeign([
                'company_id',
            ]);

            $table->dropColumn([
                'base_unit_id',
                'company_id',
                'reorder_point',
                'is_active',
            ]);
        });


        /*
         * Restore the original "code" column.
         */

        Schema::table('products', function (Blueprint $table) {

            $table->dropUnique('products_sku_unique');

            $table->renameColumn(
                'sku',
                'code'
            );
        });


        /*
         * Restore the original code requirements.
         */

        Schema::table('products', function (Blueprint $table) {

            $table->string('code', 50)
                ->nullable(false)
                ->change();

            $table->unique('code');
        });
    }
};
