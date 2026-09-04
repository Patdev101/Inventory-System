<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesInventoryFixtures;
use Tests\TestCase;

class InventoryTransferTest extends TestCase
{
    use RefreshDatabase;
    use CreatesInventoryFixtures;

    public function test_transfer_form_renders_for_a_manager(): void
    {
        $manager = $this->makeUser(User::ROLE_MANAGER);

        $company = $this->makeCompany();
        $location = $this->makeLocation($company);
        $category = $this->makeCategory();
        $unit = $this->makeUnit();
        $product = $this->makeProduct($company, $category, $unit);
        $productUnit = $this->makeProductUnit($product, $unit);

        $this->makeInventory($product, $location, $productUnit, 10, 10);

        $this->actingAs($manager)
            ->get(route('inventory-transfers.create'))
            ->assertOk()
            ->assertSee('Destination Location');
    }

    public function test_manager_can_transfer_stock_between_locations(): void
    {
        $manager = $this->makeUser(User::ROLE_MANAGER);

        $company = $this->makeCompany();
        $locationA = $this->makeLocation($company, ['code' => 'A']);
        $locationB = $this->makeLocation($company, ['code' => 'B']);
        $category = $this->makeCategory();
        $unit = $this->makeUnit();
        $product = $this->makeProduct($company, $category, $unit);
        $productUnit = $this->makeProductUnit($product, $unit);

        $source = $this->makeInventory($product, $locationA, $productUnit, 100, 100);
        $destination = $this->makeInventory($product, $locationB, $productUnit, 10, 10);

        $this->actingAs($manager)->post(route('inventory-transfers.store'), [
            'source_inventory_id' => $source->id,
            'destination_location_id' => $locationB->id,
            'product_unit_id' => $productUnit->id,
            'quantity' => 40,
        ])->assertRedirect(route('inventory-transfers.index'));

        $this->assertSame(60.0, (float) $source->fresh()->base_quantity);
        $this->assertSame(50.0, (float) $destination->fresh()->base_quantity);

        $this->assertDatabaseHas('inventory_transfers', [
            'source_inventory_id' => $source->id,
            'destination_inventory_id' => $destination->id,
            'base_quantity' => 40,
        ]);

        $this->assertDatabaseHas('inventory_transactions', [
            'inventory_id' => $source->id,
            'type' => 'out',
            'base_quantity' => 40,
        ]);

        $this->assertDatabaseHas('inventory_transactions', [
            'inventory_id' => $destination->id,
            'type' => 'in',
            'base_quantity' => 40,
        ]);
    }

    public function test_transferring_to_a_location_with_no_existing_stock_creates_the_inventory_record(): void
    {
        // This is the exact scenario that used to be a dead end: a
        // location that has never stocked the product before had no way
        // to be picked as a destination at all.
        $manager = $this->makeUser(User::ROLE_MANAGER);

        $company = $this->makeCompany();
        $locationA = $this->makeLocation($company, ['code' => 'A']);
        $locationB = $this->makeLocation($company, ['code' => 'B']);
        $category = $this->makeCategory();
        $unit = $this->makeUnit();
        $product = $this->makeProduct($company, $category, $unit);
        $productUnit = $this->makeProductUnit($product, $unit);

        $source = $this->makeInventory($product, $locationA, $productUnit, 100, 100);

        $this->assertDatabaseMissing('inventories', [
            'product_id' => $product->id,
            'location_id' => $locationB->id,
        ]);

        $this->actingAs($manager)->post(route('inventory-transfers.store'), [
            'source_inventory_id' => $source->id,
            'destination_location_id' => $locationB->id,
            'product_unit_id' => $productUnit->id,
            'quantity' => 25,
        ])->assertRedirect(route('inventory-transfers.index'));

        $this->assertSame(75.0, (float) $source->fresh()->base_quantity);

        $this->assertDatabaseHas('inventories', [
            'product_id' => $product->id,
            'location_id' => $locationB->id,
            'base_quantity' => 25,
        ]);
    }

    public function test_transfer_is_rejected_when_source_has_insufficient_stock(): void
    {
        $manager = $this->makeUser(User::ROLE_MANAGER);

        $company = $this->makeCompany();
        $locationA = $this->makeLocation($company, ['code' => 'A']);
        $locationB = $this->makeLocation($company, ['code' => 'B']);
        $category = $this->makeCategory();
        $unit = $this->makeUnit();
        $product = $this->makeProduct($company, $category, $unit);
        $productUnit = $this->makeProductUnit($product, $unit);

        $source = $this->makeInventory($product, $locationA, $productUnit, 5, 5);
        $destination = $this->makeInventory($product, $locationB, $productUnit, 10, 10);

        $this->actingAs($manager)->post(route('inventory-transfers.store'), [
            'source_inventory_id' => $source->id,
            'destination_location_id' => $locationB->id,
            'product_unit_id' => $productUnit->id,
            'quantity' => 40,
        ])->assertSessionHasErrors('quantity');

        $this->assertSame(5.0, (float) $source->fresh()->base_quantity);
        $this->assertSame(10.0, (float) $destination->fresh()->base_quantity);
    }

    public function test_transfer_is_rejected_when_destination_is_the_same_as_the_source(): void
    {
        $manager = $this->makeUser(User::ROLE_MANAGER);

        $company = $this->makeCompany();
        $locationA = $this->makeLocation($company, ['code' => 'A']);
        $category = $this->makeCategory();
        $unit = $this->makeUnit();
        $product = $this->makeProduct($company, $category, $unit);
        $productUnit = $this->makeProductUnit($product, $unit);

        $source = $this->makeInventory($product, $locationA, $productUnit, 100, 100);

        $this->actingAs($manager)->post(route('inventory-transfers.store'), [
            'source_inventory_id' => $source->id,
            'destination_location_id' => $locationA->id,
            'product_unit_id' => $productUnit->id,
            'quantity' => 10,
        ])->assertSessionHasErrors('destination_location_id');

        $this->assertSame(100.0, (float) $source->fresh()->base_quantity);
    }

    public function test_staff_cannot_create_a_transfer(): void
    {
        $staff = $this->makeUser(User::ROLE_STAFF);

        $company = $this->makeCompany();
        $locationA = $this->makeLocation($company, ['code' => 'A']);
        $locationB = $this->makeLocation($company, ['code' => 'B']);
        $category = $this->makeCategory();
        $unit = $this->makeUnit();
        $product = $this->makeProduct($company, $category, $unit);
        $productUnit = $this->makeProductUnit($product, $unit);

        $source = $this->makeInventory($product, $locationA, $productUnit, 100, 100);
        $this->makeInventory($product, $locationB, $productUnit, 10, 10);

        $this->actingAs($staff)->post(route('inventory-transfers.store'), [
            'source_inventory_id' => $source->id,
            'destination_location_id' => $locationB->id,
            'product_unit_id' => $productUnit->id,
            'quantity' => 10,
        ])->assertForbidden();
    }
}
