<?php

namespace App\Support;

/**
 * The standard units of measure every installation starts with, plus what
 * is needed to suggest conversions between them.
 *
 * `family` groups units that can convert into each other. `size` is how
 * many of the family's reference unit one of this unit holds (grams,
 * metres, litres, pieces). A null size means the unit is packaging whose
 * content differs per product (a box of 12, a box of 24...), so the person
 * setting up the product types the quantity.
 */
class DefaultUnits
{
    public const UNITS = [
        // Count and packaging (reference: one piece)
        'PCS' => ['name' => 'PIECE', 'description' => 'Individual piece', 'family' => 'count', 'size' => 1],
        'PAIR' => ['name' => 'PAIR', 'description' => 'Two items', 'family' => 'count', 'size' => 2],
        'HDZ' => ['name' => 'HALF DOZEN', 'description' => 'Six items', 'family' => 'count', 'size' => 6],
        'DOZ' => ['name' => 'DOZEN', 'description' => 'Twelve items', 'family' => 'count', 'size' => 12],
        'REAM' => ['name' => 'REAM', 'description' => 'Five hundred sheets', 'family' => 'count', 'size' => 500],
        'PACK' => ['name' => 'PACK', 'description' => 'Pack of items', 'family' => 'count', 'size' => null],
        'BOX' => ['name' => 'BOX', 'description' => 'Box of items', 'family' => 'count', 'size' => null],
        'SET' => ['name' => 'SET', 'description' => 'Set of items', 'family' => 'count', 'size' => null],
        'BUNDLE' => ['name' => 'BUNDLE', 'description' => 'Bundle of items', 'family' => 'count', 'size' => null],
        'BAG' => ['name' => 'BAG', 'description' => 'Bag of items', 'family' => 'count', 'size' => null],
        'CTN' => ['name' => 'CARTON', 'description' => 'Carton of items', 'family' => 'count', 'size' => null],
        'CASE' => ['name' => 'CASE', 'description' => 'Case of items', 'family' => 'count', 'size' => null],
        'PAL' => ['name' => 'PALLET', 'description' => 'Pallet of items', 'family' => 'count', 'size' => null],
        'ROLL' => ['name' => 'ROLL', 'description' => 'Roll of material', 'family' => 'count', 'size' => null],
        'BTL' => ['name' => 'BOTTLE', 'description' => 'Bottle', 'family' => 'count', 'size' => null],
        'CAN' => ['name' => 'CAN', 'description' => 'Can', 'family' => 'count', 'size' => null],
        'SACK' => ['name' => 'SACK', 'description' => 'Sack of items', 'family' => 'count', 'size' => null],
        'TUB' => ['name' => 'TUB', 'description' => 'Tub or container', 'family' => 'count', 'size' => null],

        // Weight (reference: one gram)
        'MG' => ['name' => 'MILLIGRAM', 'description' => 'Milligram', 'family' => 'weight', 'size' => 0.001],
        'G' => ['name' => 'GRAM', 'description' => 'Gram', 'family' => 'weight', 'size' => 1],
        'KG' => ['name' => 'KILOGRAM', 'description' => 'Kilogram', 'family' => 'weight', 'size' => 1000],
        'TON' => ['name' => 'METRIC TON', 'description' => 'Metric ton', 'family' => 'weight', 'size' => 1000000],
        'OZ' => ['name' => 'OUNCE', 'description' => 'Ounce', 'family' => 'weight', 'size' => 28.3495],
        'LB' => ['name' => 'POUND', 'description' => 'Pound', 'family' => 'weight', 'size' => 453.5924],

        // Volume (reference: one litre)
        'ML' => ['name' => 'MILLILITER', 'description' => 'Milliliter', 'family' => 'volume', 'size' => 0.001],
        'L' => ['name' => 'LITER', 'description' => 'Liter', 'family' => 'volume', 'size' => 1],
        'PT' => ['name' => 'PINT', 'description' => 'Pint', 'family' => 'volume', 'size' => 0.4732],
        'QT' => ['name' => 'QUART', 'description' => 'Quart', 'family' => 'volume', 'size' => 0.9464],
        'GAL' => ['name' => 'GALLON', 'description' => 'Gallon', 'family' => 'volume', 'size' => 3.7854],

        // Length (reference: one metre)
        'MM' => ['name' => 'MILLIMETER', 'description' => 'Millimeter', 'family' => 'length', 'size' => 0.001],
        'CM' => ['name' => 'CENTIMETER', 'description' => 'Centimeter', 'family' => 'length', 'size' => 0.01],
        'IN' => ['name' => 'INCH', 'description' => 'Inch', 'family' => 'length', 'size' => 0.0254],
        'FT' => ['name' => 'FOOT', 'description' => 'Foot', 'family' => 'length', 'size' => 0.3048],
        'YD' => ['name' => 'YARD', 'description' => 'Yard', 'family' => 'length', 'size' => 0.9144],
        'M' => ['name' => 'METER', 'description' => 'Meter', 'family' => 'length', 'size' => 1],
        'KM' => ['name' => 'KILOMETER', 'description' => 'Kilometer', 'family' => 'length', 'size' => 1000],
    ];

    /** Rows ready to insert into units_of_measure. */
    public static function rows(): array
    {
        $rows = [];

        foreach (self::UNITS as $code => $unit) {
            $rows[] = ['code' => $code, 'name' => $unit['name'], 'description' => $unit['description']];
        }

        return $rows;
    }
}
