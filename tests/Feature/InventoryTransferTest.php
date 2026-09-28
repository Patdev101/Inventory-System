<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesInventoryFixtures;
use Tests\TestCase;

/**
 * Covers InventoryTransferController::store(), which creates one pending,
 * audited transfer per checklist item via
 * InventoryMovementService::initiateTransfer() — see SYSTEM_DOCUMENTATION.md
 * Section 9.1. Stock leaves the source immediately; it only reaches the
 * destination once the assigned receiver passes audit and marks it
 * received (covered separately in InventoryTransferReceivingTest).
 */
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

    public function test_manager_can_initiate_a_transfer_and_stock_leaves_the_source_immediately(): void
    {
        $manager = $this->makeUser(User::ROLE_MANAGER);
        $receiver = $this->makeUser(User::ROLE_STAFF);

        $company = $this->makeCompany();
        $locationA = $this->makeLocation($company, ['code' => 'A']);
        $locationB = $this->makeLocation($company, ['code' => 'B']);
        $category = $this->makeCategory();
        $unit = $this->makeUnit();
        $product = $this->makeProduct($company, $category, $unit);
        $productUnit = $this->makeProductUnit($product, $unit);

        $source = $this->makeInventory($product, $locationA, $productUnit, 100, 100);

        $this->actingAs($manager)->post(route('inventory-transfers.store'), [
            'destination_location_id' => $locationB->id,
            'receiver_id' => $receiver->id,
            'receiver_role' => User::ROLE_STAFF,
            'items' => [
                [
                    'source_inventory_id' => $source->id,
                    'product_unit_id' => $productUnit->id,
                    'quantity' => 40,
                ],
            ],
        ])->assertRedirect(route('inventory-transfers.index'));

        // Stock leaves the source right away...
        $this->assertSame(60.0, (float) $source->fresh()->base_quantity);

        // ...but is not yet credited to the destination — it's pending
        // the receiver's audit, not an immediate transfer.
        $this->assertDatabaseHas('inventory_transfers', [
            'source_inventory_id' => $source->id,
            'base_quantity' => 40,
            'status' => 'pending',
            'audit_status' => 'pending',
            'receiver_id' => $receiver->id,
        ]);

        $destinationInventory = \App\Models\Inventory::where('product_id', $product->id)
            ->where('location_id', $locationB->id)
            ->firstOrFail();

        $this->assertSame(0.0, (float) $destinationInventory->base_quantity);

        $this->assertDatabaseHas('inventory_transactions', [
            'inventory_id' => $source->id,
            'type' => 'out',
            'base_quantity' => 40,
        ]);
    }

    public function test_transferring_to_a_location_with_no_existing_stock_creates_the_inventory_record(): void
    {
        // A location that has never stocked the product before is a valid
        // destination — the inventory record is created (at zero, pending
        // receipt) rather than requiring one to already exist.
        $manager = $this->makeUser(User::ROLE_MANAGER);
        $receiver = $this->makeUser(User::ROLE_STAFF);

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
            'destination_location_id' => $locationB->id,
            'receiver_id' => $receiver->id,
            'receiver_role' => User::ROLE_STAFF,
            'items' => [
                [
                    'source_inventory_id' => $source->id,
                    'product_unit_id' => $productUnit->id,
                    'quantity' => 25,
                ],
            ],
        ])->assertRedirect(route('inventory-transfers.index'));

        $this->assertSame(75.0, (float) $source->fresh()->base_quantity);

        // Created, but still at zero until the receiver marks it received.
        $this->assertDatabaseHas('inventories', [
            'product_id' => $product->id,
            'location_id' => $locationB->id,
            'base_quantity' => 0,
        ]);
    }

    public function test_transfer_is_rejected_when_source_has_insufficient_stock(): void
    {
        $manager = $this->makeUser(User::ROLE_MANAGER);
        $receiver = $this->makeUser(User::ROLE_STAFF);

        $company = $this->makeCompany();
        $locationA = $this->makeLocation($company, ['code' => 'A']);
        $locationB = $this->makeLocation($company, ['code' => 'B']);
        $category = $this->makeCategory();
        $unit = $this->makeUnit();
        $product = $this->makeProduct($company, $category, $unit);
        $productUnit = $this->makeProductUnit($product, $unit);

        $source = $this->makeInventory($product, $locationA, $productUnit, 5, 5);

        $this->actingAs($manager)->post(route('inventory-transfers.store'), [
            'destination_location_id' => $locationB->id,
            'receiver_id' => $receiver->id,
            'receiver_role' => User::ROLE_STAFF,
            'items' => [
                [
                    'source_inventory_id' => $source->id,
                    'product_unit_id' => $productUnit->id,
                    'quantity' => 40,
                ],
            ],
        ])->assertSessionHasErrors();

        $this->assertSame(5.0, (float) $source->fresh()->base_quantity);
    }

    public function test_transfer_is_rejected_when_destination_is_the_same_as_the_source(): void
    {
        $manager = $this->makeUser(User::ROLE_MANAGER);
        $receiver = $this->makeUser(User::ROLE_STAFF);

        $company = $this->makeCompany();
        $locationA = $this->makeLocation($company, ['code' => 'A']);
        $category = $this->makeCategory();
        $unit = $this->makeUnit();
        $product = $this->makeProduct($company, $category, $unit);
        $productUnit = $this->makeProductUnit($product, $unit);

        $source = $this->makeInventory($product, $locationA, $productUnit, 100, 100);

        $this->actingAs($manager)->post(route('inventory-transfers.store'), [
            'destination_location_id' => $locationA->id,
            'receiver_id' => $receiver->id,
            'receiver_role' => User::ROLE_STAFF,
            'items' => [
                [
                    'source_inventory_id' => $source->id,
                    'product_unit_id' => $productUnit->id,
                    'quantity' => 10,
                ],
            ],
        ])->assertSessionHasErrors();

        $this->assertSame(100.0, (float) $source->fresh()->base_quantity);
    }

    public function test_staff_cannot_create_a_transfer(): void
    {
        $staff = $this->makeUser(User::ROLE_STAFF);
        $receiver = $this->makeUser(User::ROLE_STAFF);

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
            'destination_location_id' => $locationB->id,
            'receiver_id' => $receiver->id,
            'receiver_role' => User::ROLE_STAFF,
            'items' => [
                [
                    'source_inventory_id' => $source->id,
                    'product_unit_id' => $productUnit->id,
                    'quantity' => 10,
                ],
            ],
        ])->assertForbidden();
    }
}
