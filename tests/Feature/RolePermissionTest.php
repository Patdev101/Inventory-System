<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesInventoryFixtures;
use Tests\TestCase;

class RolePermissionTest extends TestCase
{
    use RefreshDatabase;
    use CreatesInventoryFixtures;

    public function test_staff_cannot_create_a_company(): void
    {
        $staff = $this->makeUser(User::ROLE_STAFF);

        $this->actingAs($staff)
            ->get(route('companies.create'))
            ->assertForbidden();

        $this->actingAs($staff)
            ->post(route('companies.store'), ['name' => 'X', 'code' => 'X1'])
            ->assertForbidden();
    }

    public function test_admin_can_create_a_company(): void
    {
        $admin = $this->makeUser(User::ROLE_ADMIN);

        $this->actingAs($admin)
            ->get(route('companies.create'))
            ->assertOk();
    }

    public function test_manager_cannot_manage_master_data(): void
    {
        $manager = $this->makeUser(User::ROLE_MANAGER);

        $this->actingAs($manager)
            ->get(route('companies.create'))
            ->assertForbidden();

        $this->actingAs($manager)
            ->get(route('units-of-measure.create'))
            ->assertForbidden();
    }

    public function test_all_roles_can_view_master_data(): void
    {
        $company = $this->makeCompany();

        foreach ([User::ROLE_ADMIN, User::ROLE_MANAGER, User::ROLE_STAFF] as $role) {
            $user = $this->makeUser($role);

            $this->actingAs($user)
                ->get(route('companies.index'))
                ->assertOk();

            $this->actingAs($user)
                ->get(route('companies.show', $company))
                ->assertOk();
        }
    }

    public function test_staff_can_submit_stock_movements_for_approval_but_cannot_transfer_or_delete(): void
    {
        $staff = $this->makeUser(User::ROLE_STAFF);

        $company = $this->makeCompany();
        $location = $this->makeLocation($company);
        $category = $this->makeCategory();
        $unit = $this->makeUnit();
        $product = $this->makeProduct($company, $category, $unit);
        $productUnit = $this->makeProductUnit($product, $unit);
        $inventory = $this->makeInventory($product, $location, $productUnit, 10, 10);

        $this->actingAs($staff)
            ->get(route('inventories.create'))
            ->assertOk();

        $this->actingAs($staff)
            ->get(route('inventories.edit', $inventory))
            ->assertOk();

        $this->actingAs($staff)
            ->patch(route('inventories.update', $inventory), [
                'product_id' => $product->id,
                'location_id' => $location->id,
                'product_unit_id' => $productUnit->id,
                'movement_type' => 'in',
                'quantity' => 5,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('stock_movement_requests', [
            'inventory_id' => $inventory->id,
            'requested_by' => $staff->id,
            'status' => 'pending',
        ]);

        $this->assertEquals(10, $inventory->fresh()->base_quantity);

        $this->actingAs($staff)
            ->get(route('inventory-transfers.create'))
            ->assertForbidden();

        $this->actingAs($staff)
            ->delete(route('inventories.destroy', $inventory))
            ->assertForbidden();
    }

    public function test_manager_can_approve_a_staff_stock_movement_request(): void
    {
        $staff = $this->makeUser(User::ROLE_STAFF);
        $manager = $this->makeUser(User::ROLE_MANAGER);

        $company = $this->makeCompany();
        $location = $this->makeLocation($company);
        $category = $this->makeCategory();
        $unit = $this->makeUnit();
        $product = $this->makeProduct($company, $category, $unit);
        $productUnit = $this->makeProductUnit($product, $unit);
        $inventory = $this->makeInventory($product, $location, $productUnit, 10, 10);

        $this->actingAs($staff)->patch(route('inventories.update', $inventory), [
            'product_id' => $product->id,
            'location_id' => $location->id,
            'product_unit_id' => $productUnit->id,
            'movement_type' => 'in',
            'quantity' => 5,
        ]);

        $stockMovementRequest = \App\Models\StockMovementRequest::firstOrFail();

        $this->actingAs($manager)
            ->get(route('stock-movement-requests.index'))
            ->assertOk();

        $this->actingAs($manager)
            ->patch(route('stock-movement-requests.approve', $stockMovementRequest))
            ->assertRedirect();

        $this->assertEquals(15, $inventory->fresh()->base_quantity);
        $this->assertEquals('approved', $stockMovementRequest->fresh()->status);
    }

    public function test_manager_can_adjust_stock_but_not_delete_inventory(): void
    {
        $manager = $this->makeUser(User::ROLE_MANAGER);

        $company = $this->makeCompany();
        $location = $this->makeLocation($company);
        $category = $this->makeCategory();
        $unit = $this->makeUnit();
        $product = $this->makeProduct($company, $category, $unit);
        $productUnit = $this->makeProductUnit($product, $unit);
        $inventory = $this->makeInventory($product, $location, $productUnit, 10, 10);

        $this->actingAs($manager)
            ->get(route('inventories.edit', $inventory))
            ->assertOk();

        $this->actingAs($manager)
            ->delete(route('inventories.destroy', $inventory))
            ->assertForbidden();
    }

    public function test_staff_cannot_acknowledge_or_resolve_alerts(): void
    {
        $staff = $this->makeUser(User::ROLE_STAFF);

        $company = $this->makeCompany();
        $location = $this->makeLocation($company);
        $category = $this->makeCategory();
        $unit = $this->makeUnit();
        $product = $this->makeProduct($company, $category, $unit, ['reorder_point' => 100]);
        $productUnit = $this->makeProductUnit($product, $unit);
        $inventory = $this->makeInventory($product, $location, $productUnit, 0, 0);

        $alert = \App\Models\StockAlert::create([
            'inventory_id' => $inventory->id,
            'severity' => 'out_of_stock',
            'status' => 'open',
            'base_quantity' => 0,
            'reorder_point' => 100,
        ]);

        $this->actingAs($staff)
            ->patch(route('stock-alerts.acknowledge', $alert))
            ->assertForbidden();

        $this->actingAs($staff)
            ->patch(route('stock-alerts.resolve', $alert))
            ->assertForbidden();
    }
}
