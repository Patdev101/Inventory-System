<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesInventoryFixtures;
use Tests\TestCase;

class InventoryUnitConversionTest extends TestCase
{
    use RefreshDatabase;
    use CreatesInventoryFixtures;

    public function test_adding_stock_in_an_alternate_unit_converts_to_base_quantity(): void
    {
        $manager = $this->makeUser(User::ROLE_MANAGER);

        $company = $this->makeCompany();
        $location = $this->makeLocation($company);
        $category = $this->makeCategory();
        $kg = $this->makeUnit(['name' => 'Kilogram', 'code' => 'KG']);
        $product = $this->makeProduct($company, $category, $kg);

        $bag = $this->makeUnit(['name' => 'Bag', 'code' => 'BAG']);
        $bagUnit = $this->makeProductUnit($product, $bag, conversionFactor: 25, isDefault: false);

        $this->actingAs($manager)->post(route('inventories.store'), [
            'product_id' => $product->id,
            'location_id' => $location->id,
            'product_unit_id' => $bagUnit->id,
            'quantity' => 2,
        ])->assertRedirect(route('inventories.index'));

        $this->assertDatabaseHas('inventories', [
            'product_id' => $product->id,
            'location_id' => $location->id,
            'quantity' => 2,
            'base_quantity' => 50,
        ]);
    }

    public function test_a_product_unit_belonging_to_a_different_product_is_rejected(): void
    {
        $manager = $this->makeUser(User::ROLE_MANAGER);

        $company = $this->makeCompany();
        $location = $this->makeLocation($company);
        $category = $this->makeCategory();
        $unit = $this->makeUnit();

        $productA = $this->makeProduct($company, $category, $unit, ['name' => 'Product A']);

        $productB = $this->makeProduct($company, $category, $unit, ['name' => 'Product B']);
        $productBUnit = $this->makeProductUnit($productB, $unit);

        $this->actingAs($manager)->post(route('inventories.store'), [
            'product_id' => $productA->id,
            'location_id' => $location->id,
            'product_unit_id' => $productBUnit->id,
            'quantity' => 5,
        ])->assertSessionHasErrors('product_unit_id');

        $this->assertDatabaseMissing('inventories', [
            'product_id' => $productA->id,
        ]);
    }

    public function test_stock_out_cannot_create_negative_inventory(): void
    {
        $manager = $this->makeUser(User::ROLE_MANAGER);

        $company = $this->makeCompany();
        $location = $this->makeLocation($company);
        $category = $this->makeCategory();
        $unit = $this->makeUnit();
        $product = $this->makeProduct($company, $category, $unit);
        $productUnit = $this->makeProductUnit($product, $unit);
        $inventory = $this->makeInventory($product, $location, $productUnit, 5, 5);

        $this->actingAs($manager)->patch(route('inventories.update', $inventory), [
            'product_id' => $product->id,
            'location_id' => $location->id,
            'product_unit_id' => $productUnit->id,
            'movement_type' => 'out',
            'quantity' => 999,
        ])->assertSessionHasErrors('quantity');

        $this->assertSame(5.0, (float) $inventory->fresh()->base_quantity);
    }
}
