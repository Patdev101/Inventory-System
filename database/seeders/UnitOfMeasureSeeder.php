<?php

namespace Database\Seeders;

use App\Models\UnitOfMeasure;
use App\Support\DefaultUnits;
use Illuminate\Database\Seeder;

class UnitOfMeasureSeeder extends Seeder
{
    /**
     * The standard units are also installed by a migration, so they exist
     * without seeding; this keeps `db:seed` working and in step with it.
     */
    public function run(): void
    {
        foreach (DefaultUnits::rows() as $unit) {
            UnitOfMeasure::withTrashed()->updateOrCreate(['code' => $unit['code']], $unit);
        }
    }
}
