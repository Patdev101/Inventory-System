<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\StockMovementRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesInventoryFixtures;
use Tests\TestCase;

class InventoryTransferRequestTest extends TestCase
{
    use RefreshDatabase;
    use CreatesInventoryFixtures;

    public function test_staff_request_for_out_of_stock_item_is_queued_for_approval(): void
    {
        $staff = $this->makeUser(User::ROLE_STAFF);

        $company = $this->makeCompany();
        $category = $this->makeCategory();
        $unit = $this->makeUnit();
        $product = $this->makeProduct($company, $category, $unit);
        $productUnit = $this->makeProductUnit($product, $unit);

        $emptyLocation = $this->makeLocation($company, ['name' => 'Empty Branch']);
        $stockedLocation = $this->makeLocation($company, ['name' => 'Stocked Branch']);

        $emptyInventory = $this->makeInventory($product, $emptyLocation, $productUnit, 0, 0);
        $this->makeInventory($product, $stockedLocation, $productUnit, 50, 50);

        $this->actingAs($staff)
            ->post(route('inventories.request-transfer', $emptyInventory), [
                'source_location_id' => $stockedLocation->id,
                'product_unit_id' => $productUnit->id,
                'quantity' => 10,
            ])
            ->assertRedirect(route('inventories.show', $emptyInventory));

        $this->assertDatabaseHas('stock_movement_requests', [
            'type' => StockMovementRequest::TYPE_TRANSFER,
            'location_id' => $stockedLocation->id,
            'destination_location_id' => $emptyLocation->id,
            'product_id' => $product->id,
            'status' => StockMovementRequest::STATUS_PENDING,
        ]);

        // Nothing moved yet — still pending approval.
        $this->assertDatabaseHas('inventories', [
            'id' => $emptyInventory->id,
            'base_quantity' => 0,
        ]);
    }

    public function test_manager_request_executes_the_transfer_immediately(): void
    {
        $manager = $this->makeUser(User::ROLE_MANAGER);

        $company = $this->makeCompany();
        $category = $this->makeCategory();
        $unit = $this->makeUnit();
        $product = $this->makeProduct($company, $category, $unit);
        $productUnit = $this->makeProductUnit($product, $unit);

        $emptyLocation = $this->makeLocation($company, ['name' => 'Empty Branch']);
        $stockedLocation = $this->makeLocation($company, ['name' => 'Stocked Branch']);

        $emptyInventory = $this->makeInventory($product, $emptyLocation, $productUnit, 0, 0);
        $this->makeInventory($product, $stockedLocation, $productUnit, 50, 50);

        $this->actingAs($manager)
            ->post(route('inventories.request-transfer', $emptyInventory), [
                'source_location_id' => $stockedLocation->id,
                'product_unit_id' => $productUnit->id,
                'quantity' => 10,
            ])
            ->assertRedirect(route('inventories.show', $emptyInventory));

        $this->assertDatabaseHas('inventories', [
            'id' => $emptyInventory->id,
            'base_quantity' => 10,
        ]);

        $this->assertDatabaseCount('stock_movement_requests', 0);
        $this->assertDatabaseCount('inventory_transfers', 1);
    }

    public function test_source_location_from_a_different_company_is_rejected(): void
    {
        $manager = $this->makeUser(User::ROLE_MANAGER);

        $company = $this->makeCompany();
        $otherCompany = $this->makeCompany(['name' => 'Other Co', 'code' => 'OTH-' . uniqid()]);

        $category = $this->makeCategory();
        $unit = $this->makeUnit();
        $product = $this->makeProduct($company, $category, $unit);
        $productUnit = $this->makeProductUnit($product, $unit);

        $emptyLocation = $this->makeLocation($company, ['name' => 'Empty Branch']);
        $foreignLocation = $this->makeLocation($otherCompany, ['name' => 'Foreign Branch']);

        $emptyInventory = $this->makeInventory($product, $emptyLocation, $productUnit, 0, 0);
        $this->makeInventory($product, $foreignLocation, $productUnit, 50, 50);

        $this->actingAs($manager)
            ->post(route('inventories.request-transfer', $emptyInventory), [
                'source_location_id' => $foreignLocation->id,
                'product_unit_id' => $productUnit->id,
                'quantity' => 10,
            ])
            ->assertSessionHasErrors('source_location_id');

        $this->assertDatabaseHas('inventories', [
            'id' => $emptyInventory->id,
            'base_quantity' => 0,
        ]);
    }

    public function test_source_location_with_no_stock_is_rejected(): void
    {
        $manager = $this->makeUser(User::ROLE_MANAGER);

        $company = $this->makeCompany();
        $category = $this->makeCategory();
        $unit = $this->makeUnit();
        $product = $this->makeProduct($company, $category, $unit);
        $productUnit = $this->makeProductUnit($product, $unit);

        $emptyLocation = $this->makeLocation($company, ['name' => 'Empty Branch']);
        $alsoEmptyLocation = $this->makeLocation($company, ['name' => 'Also Empty Branch']);

        $emptyInventory = $this->makeInventory($product, $emptyLocation, $productUnit, 0, 0);
        $this->makeInventory($product, $alsoEmptyLocation, $productUnit, 0, 0);

        $this->actingAs($manager)
            ->post(route('inventories.request-transfer', $emptyInventory), [
                'source_location_id' => $alsoEmptyLocation->id,
                'product_unit_id' => $productUnit->id,
                'quantity' => 10,
            ])
            ->assertSessionHasErrors('source_location_id');
    }

    public function test_approving_a_transfer_request_moves_the_stock(): void
    {
        $staff = $this->makeUser(User::ROLE_STAFF);
        $manager = $this->makeUser(User::ROLE_MANAGER);

        $company = $this->makeCompany();
        $category = $this->makeCategory();
        $unit = $this->makeUnit();
        $product = $this->makeProduct($company, $category, $unit);
        $productUnit = $this->makeProductUnit($product, $unit);

        $emptyLocation = $this->makeLocation($company, ['name' => 'Empty Branch']);
        $stockedLocation = $this->makeLocation($company, ['name' => 'Stocked Branch']);

        $emptyInventory = $this->makeInventory($product, $emptyLocation, $productUnit, 0, 0);
        $this->makeInventory($product, $stockedLocation, $productUnit, 50, 50);

        $this->actingAs($staff)->post(route('inventories.request-transfer', $emptyInventory), [
            'source_location_id' => $stockedLocation->id,
            'product_unit_id' => $productUnit->id,
            'quantity' => 10,
        ]);

        $stockMovementRequest = StockMovementRequest::firstOrFail();

        $this->actingAs($manager)
            ->patch(route('stock-movement-requests.approve', $stockMovementRequest))
            ->assertRedirect();

        $this->assertDatabaseHas('inventories', [
            'id' => $emptyInventory->id,
            'base_quantity' => 10,
        ]);

        $this->assertDatabaseHas('inventories', [
            'product_id' => $product->id,
            'location_id' => $stockedLocation->id,
            'base_quantity' => 40,
        ]);

        $stockMovementRequest->refresh();
        $this->assertSame(StockMovementRequest::STATUS_APPROVED, $stockMovementRequest->status);
    }
}
