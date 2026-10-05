<?php

use App\Support\DefaultUnits;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * 1. Set-up names and codes are stored in capitals from now on, so
     *    existing records are capitalised to match.
     * 2. Every installation gets the standard units of measure, so a new
     *    system is never handed over with an empty unit list.
     */
    public function up(): void
    {
        $columns = [
            'companies' => ['name', 'code'],
            'locations' => ['name', 'code'],
            'product_categories' => ['name', 'code'],
            'units_of_measure' => ['name', 'code'],
            'suppliers' => ['name'],
            'products' => ['name', 'sku', 'item_code'],
        ];

        foreach ($columns as $table => $fields) {
            foreach ($fields as $field) {
                DB::table($table)->whereNotNull($field)->update([$field => DB::raw("UPPER({$field})")]);
            }
        }

        $existing = DB::table('units_of_measure')->pluck('code')->map(fn ($code) => strtoupper((string) $code))->all();
        $now = now();

        foreach (DefaultUnits::rows() as $unit) {
            if (in_array($unit['code'], $existing, true)) {
                continue;
            }

            DB::table('units_of_measure')->insert($unit + ['created_at' => $now, 'updated_at' => $now]);
        }
    }

    public function down(): void
    {
        // Capitalisation cannot be undone, and the units may already be in use.
    }
};
