<?php

namespace Database\Seeders;

use App\Models\UnitOfMeasure;
use Illuminate\Database\Seeder;

class UnitOfMeasureSeeder extends Seeder
{
    /**
     * Seed the application's measurement units.
     */
    public function run(): void
    {
        $units = [
            // Count / packaging
            [
                'name' => 'Piece',
                'code' => 'PCS',
                'description' => 'Individual piece',
            ],
            [
                'name' => 'Pack',
                'code' => 'PACK',
                'description' => 'Pack of items',
            ],
            [
                'name' => 'Box',
                'code' => 'BOX',
                'description' => 'Box of items',
            ],
            [
                'name' => 'Dozen',
                'code' => 'DOZ',
                'description' => 'Twelve items',
            ],
            [
                'name' => 'Half Dozen',
                'code' => 'HDZ',
                'description' => 'Six items',
            ],
            [
                'name' => 'Pair',
                'code' => 'PAIR',
                'description' => 'Two items',
            ],
            [
                'name' => 'Set',
                'code' => 'SET',
                'description' => 'Set of items',
            ],
            [
                'name' => 'Bundle',
                'code' => 'BUNDLE',
                'description' => 'Bundle of items',
            ],
            [
                'name' => 'Bag',
                'code' => 'BAG',
                'description' => 'Bag of items',
            ],
            [
                'name' => 'Carton',
                'code' => 'CTN',
                'description' => 'Carton of items',
            ],
            [
                'name' => 'Case',
                'code' => 'CASE',
                'description' => 'Case of items',
            ],
            [
                'name' => 'Pallet',
                'code' => 'PAL',
                'description' => 'Pallet of items',
            ],
            [
                'name' => 'Roll',
                'code' => 'ROLL',
                'description' => 'Roll of material',
            ],
            [
                'name' => 'Ream',
                'code' => 'REAM',
                'description' => 'Five hundred sheets',
            ],

            // Length
            [
                'name' => 'Meter',
                'code' => 'M',
                'description' => 'Meter',
            ],
            [
                'name' => 'Centimeter',
                'code' => 'CM',
                'description' => 'Centimeter',
            ],
            [
                'name' => 'Millimeter',
                'code' => 'MM',
                'description' => 'Millimeter',
            ],
            [
                'name' => 'Kilometer',
                'code' => 'KM',
                'description' => 'Kilometer',
            ],
            [
                'name' => 'Inch',
                'code' => 'IN',
                'description' => 'Inch',
            ],
            [
                'name' => 'Foot',
                'code' => 'FT',
                'description' => 'Foot',
            ],
            [
                'name' => 'Yard',
                'code' => 'YD',
                'description' => 'Yard',
            ],

            // Weight / Mass
            [
                'name' => 'Kilogram',
                'code' => 'KG',
                'description' => 'Kilogram',
            ],
            [
                'name' => 'Gram',
                'code' => 'G',
                'description' => 'Gram',
            ],
            [
                'name' => 'Milligram',
                'code' => 'MG',
                'description' => 'Milligram',
            ],
            [
                'name' => 'Metric Ton',
                'code' => 'TON',
                'description' => 'Metric ton',
            ],
            [
                'name' => 'Pound',
                'code' => 'LB',
                'description' => 'Pound',
            ],
            [
                'name' => 'Ounce',
                'code' => 'OZ',
                'description' => 'Ounce',
            ],

            // Volume
            [
                'name' => 'Liter',
                'code' => 'L',
                'description' => 'Liter',
            ],
            [
                'name' => 'Milliliter',
                'code' => 'ML',
                'description' => 'Milliliter',
            ],
            [
                'name' => 'Gallon',
                'code' => 'GAL',
                'description' => 'Gallon',
            ],
            [
                'name' => 'Quart',
                'code' => 'QT',
                'description' => 'Quart',
            ],
            [
                'name' => 'Pint',
                'code' => 'PT',
                'description' => 'Pint',
            ],
        ];

        foreach ($units as $unit) {
            UnitOfMeasure::updateOrCreate(
                [
                    'code' => $unit['code'],
                ],
                $unit
            );
        }
    }
}
