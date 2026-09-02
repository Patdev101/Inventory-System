<?php

namespace Tests\Concerns;

use App\Models\Company;
use App\Models\Inventory;
use App\Models\Location;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductUnit;
use App\Models\UnitOfMeasure;
use App\Models\User;

trait CreatesInventoryFixtures
{
    protected function makeUser(string $role): User
    {
        return User::factory()->create(['role' => $role]);
    }

    protected function makeCompany(array $attributes = []): Company
    {
        return Company::create(array_merge([
            'name' => 'Test Company',
            'code' => 'CO-' . uniqid(),
        ], $attributes));
    }

    protected function makeLocation(Company $company, array $attributes = []): Location
    {
        return Location::create(array_merge([
            'company_id' => $company->id,
            'name' => 'Test Location',
            'code' => 'LOC-' . uniqid(),
        ], $attributes));
    }

    protected function makeCategory(array $attributes = []): ProductCategory
    {
        return ProductCategory::create(array_merge([
            'name' => 'Test Category',
            'code' => 'CAT-' . uniqid(),
        ], $attributes));
    }

    protected function makeUnit(array $attributes = []): UnitOfMeasure
    {
        return UnitOfMeasure::create(array_merge([
            'name' => 'Kilogram',
            'code' => 'KG-' . uniqid(),
        ], $attributes));
    }

    protected function makeProduct(
        Company $company,
        ProductCategory $category,
        UnitOfMeasure $baseUnit,
        array $attributes = []
    ): Product {
        return Product::create(array_merge([
            'product_category_id' => $category->id,
            'company_id' => $company->id,
            'base_unit_id' => $baseUnit->id,
            'name' => 'Test Product',
            'reorder_point' => 0,
            'is_active' => true,
        ], $attributes));
    }

    protected function makeProductUnit(
        Product $product,
        UnitOfMeasure $unit,
        float $conversionFactor = 1,
        bool $isDefault = true
    ): ProductUnit {
        return ProductUnit::create([
            'product_id' => $product->id,
            'unit_of_measure_id' => $unit->id,
            'conversion_factor' => $conversionFactor,
            'is_default' => $isDefault,
        ]);
    }

    protected function makeInventory(
        Product $product,
        Location $location,
        ProductUnit $productUnit,
        float $quantity,
        float $baseQuantity
    ): Inventory {
        return Inventory::create([
            'product_id' => $product->id,
            'location_id' => $location->id,
            'product_unit_id' => $productUnit->id,
            'conversion_factor' => $productUnit->conversion_factor,
            'quantity' => $quantity,
            'base_quantity' => $baseQuantity,
        ]);
    }
}
