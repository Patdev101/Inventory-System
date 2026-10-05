<?php

namespace Tests\Feature;

use App\Models\Inventory;
use App\Models\InventoryTransfer;
use App\Models\PurchaseOrder;
use App\Models\StockAlert;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesInventoryFixtures;
use Tests\TestCase;

/**
 * Every staff member and manager works at one company and one location and
 * only sees and changes that location's data.
 *
 *   Company A: location A1 (the user's own), location A2 (a sibling)
 *   Company B: location B1
 */
class LocationAccessTest extends TestCase
{
    use RefreshDatabase;
    use CreatesInventoryFixtures;

    private $companyA;
    private $companyB;
    private $locA1;
    private $locA2;
    private $locB1;
    private array $stock = [];

    protected function setUp(): void
    {
        parent::setUp();

        config(['access.restrict_users_to_location' => true, 'services.pos.api_token' => 'test-token']);

        $this->companyA = $this->makeCompany(['name' => 'Company A']);
        $this->companyB = $this->makeCompany(['name' => 'Company B']);
        $this->locA1 = $this->makeLocation($this->companyA, ['name' => 'Location A1']);
        $this->locA2 = $this->makeLocation($this->companyA, ['name' => 'Location A2']);
        $this->locB1 = $this->makeLocation($this->companyB, ['name' => 'Location B1']);

        $this->stock['A1'] = $this->stockAt($this->companyA, $this->locA1, 'ALPHAONE', 10);
        $this->stock['A2'] = $this->stockAt($this->companyA, $this->locA2, 'ALPHATWO', 10);
        $this->stock['B1'] = $this->stockAt($this->companyB, $this->locB1, 'BRAVOONE', 10);
    }

    private function stockAt($company, $location, string $name, float $qty): array
    {
        $unit = $this->makeUnit();
        $product = $this->makeProduct($company, $this->makeCategory(), $unit, ['name' => $name, 'sku' => $name]);
        $productUnit = $this->makeProductUnit($product, $unit);

        return [
            'product' => $product,
            'unit' => $productUnit,
            'inventory' => $this->makeInventory($product, $location, $productUnit, $qty, $qty),
        ];
    }

    private function userAt($location, string $role = User::ROLE_STAFF, array $extra = []): User
    {
        return $this->makeUser($role, array_merge(['company_id' => $location->company_id, 'location_id' => $location->id], $extra));
    }

    public function test_a_user_only_sees_stock_at_their_own_location(): void
    {
        $this->actingAs($this->userAt($this->locA1))->get(route('inventories.index'))
            ->assertOk()
            ->assertSee('ALPHAONE')
            ->assertDontSee('ALPHATWO')
            ->assertDontSee('BRAVOONE');
    }

    public function test_opening_stock_at_another_location_or_company_is_not_found(): void
    {
        $user = $this->actingAs($this->userAt($this->locA1));

        $user->get(route('inventories.show', $this->stock['A1']['inventory']))->assertOk();
        $user->get(route('inventories.show', $this->stock['A2']['inventory']))->assertNotFound();
        $user->get(route('inventories.show', $this->stock['B1']['inventory']))->assertNotFound();
    }

    public function test_an_admin_sees_every_location(): void
    {
        $this->actingAs($this->makeUser(User::ROLE_ADMIN))->get(route('inventories.index'))
            ->assertOk()->assertSee('ALPHAONE')->assertSee('ALPHATWO')->assertSee('BRAVOONE');
    }

    public function test_a_user_with_no_location_sees_nothing(): void
    {
        $this->actingAs($this->makeUser(User::ROLE_STAFF))->get(route('inventories.index'))
            ->assertOk()
            ->assertDontSee('ALPHAONE')->assertDontSee('BRAVOONE')
            ->assertSee('assigned to a location yet', false);
    }

    public function test_other_companies_are_invisible_but_sibling_locations_are_listed(): void
    {
        $this->actingAs($this->userAt($this->locA1))->get(route('locations.index'))
            ->assertOk()
            ->assertSee('LOCATION A1')->assertSee('LOCATION A2')
            ->assertDontSee('LOCATION B1');

        $this->actingAs($this->userAt($this->locA1))->get(route('companies.index'))
            ->assertOk()->assertSee('COMPANY A')->assertDontSee('COMPANY B');
    }

    public function test_adding_stock_or_ordering_at_another_location_is_refused(): void
    {
        $manager = $this->userAt($this->locA1, User::ROLE_MANAGER);

        $this->actingAs($manager)->post(route('inventories.store'), [
            'product_id' => $this->stock['A2']['product']->id, 'location_id' => $this->locA2->id,
            'product_unit_id' => $this->stock['A2']['unit']->id, 'quantity' => 5,
        ])->assertForbidden();

        $supplier = Supplier::create(['company_id' => $this->companyA->id, 'name' => 'Sup', 'email' => 's@example.com', 'is_active' => true]);
        $this->actingAs($manager)->post(route('purchase-orders.store'), [
            'supplier_id' => $supplier->id, 'location_id' => $this->locA2->id,
            'items' => [['product_id' => $this->stock['A1']['product']->id, 'product_unit_id' => $this->stock['A1']['unit']->id, 'quantity_ordered' => 5, 'unit_price' => 5]],
        ])->assertForbidden();

        $this->assertSame(0, PurchaseOrder::withoutGlobalScopes()->count());
        $this->assertEquals(10, (float) Inventory::withoutGlobalScopes()->find($this->stock['A2']['inventory']->id)->base_quantity);
    }

    public function test_a_transfer_can_go_to_a_sibling_location_but_not_another_company(): void
    {
        $manager = $this->userAt($this->locA1, User::ROLE_MANAGER);
        $receiver = $this->userAt($this->locA2);
        $item = ['source_inventory_id' => $this->stock['A1']['inventory']->id, 'product_unit_id' => $this->stock['A1']['unit']->id, 'quantity' => 4];

        $this->actingAs($manager)->post(route('inventory-transfers.store'), [
            'destination_location_id' => $this->locA2->id, 'receiver_id' => $receiver->id, 'items' => [$item],
        ])->assertSessionHasNoErrors()->assertRedirect(route('inventory-transfers.index'));

        $this->assertSame(1, InventoryTransfer::withoutGlobalScopes()->count());
        $this->assertEquals(6, (float) Inventory::withoutGlobalScopes()->find($this->stock['A1']['inventory']->id)->base_quantity);

        // Only the stock row at the destination was created: no second row at A1.
        $this->assertSame(1, Inventory::withoutGlobalScopes()->where('location_id', $this->locA1->id)->where('product_id', $this->stock['A1']['product']->id)->count());

        $this->actingAs($manager)->post(route('inventory-transfers.store'), [
            'destination_location_id' => $this->locB1->id, 'receiver_id' => $receiver->id, 'items' => [$item],
        ])->assertForbidden();

        $this->assertSame(1, InventoryTransfer::withoutGlobalScopes()->count());
    }

    public function test_a_transfer_cannot_take_stock_from_another_location(): void
    {
        $manager = $this->userAt($this->locA1, User::ROLE_MANAGER);

        $this->actingAs($manager)->post(route('inventory-transfers.store'), [
            'destination_location_id' => $this->locA2->id, 'receiver_id' => $this->userAt($this->locA2)->id,
            'items' => [['source_inventory_id' => $this->stock['A2']['inventory']->id, 'product_unit_id' => $this->stock['A2']['unit']->id, 'quantity' => 4]],
        ])->assertForbidden();

        $this->assertEquals(10, (float) Inventory::withoutGlobalScopes()->find($this->stock['A2']['inventory']->id)->base_quantity);
    }

    public function test_the_receiver_must_work_at_the_destination(): void
    {
        $manager = $this->userAt($this->locA1, User::ROLE_MANAGER);

        $this->actingAs($manager)->post(route('inventory-transfers.store'), [
            'destination_location_id' => $this->locA2->id, 'receiver_id' => $this->userAt($this->locA1)->id,
            'items' => [['source_inventory_id' => $this->stock['A1']['inventory']->id, 'product_unit_id' => $this->stock['A1']['unit']->id, 'quantity' => 1]],
        ])->assertSessionHasErrors('receiver_id');
    }

    public function test_the_pos_api_still_sees_every_location(): void
    {
        $names = collect($this->getJson('/api/products', ['Authorization' => 'Bearer test-token'])->assertOk()->json())->pluck('name');

        $this->assertTrue($names->contains('ALPHAONE') && $names->contains('ALPHATWO') && $names->contains('BRAVOONE'));
    }

    public function test_stock_alerts_cover_every_location_whoever_opens_the_dashboard(): void
    {
        foreach (['A1', 'B1'] as $key) {
            Inventory::withoutGlobalScopes()->whereKey($this->stock[$key]['inventory']->id)->update(['base_quantity' => 0, 'quantity' => 0]);
        }

        $this->actingAs($this->userAt($this->locA1))->get(route('dashboard'))->assertOk();

        $this->assertSame(2, StockAlert::withoutGlobalScopes()->count());
        $this->assertSame(1, StockAlert::count(), 'The signed-in user should only see the alert for their own location');
    }

    public function test_a_manager_adds_people_at_their_own_location_and_an_admin_chooses_one(): void
    {
        $password = ['password' => 'Password1!xx', 'password_confirmation' => 'Password1!xx'];

        $this->actingAs($this->userAt($this->locA1, User::ROLE_MANAGER))->post(route('users.store'), [
            'name' => 'New Staff', 'email' => 'new.staff@example.com', 'role' => 'staff',
            'location_id' => $this->locB1->id,
        ] + $password)->assertSessionHasNoErrors();

        $created = User::where('email', 'new.staff@example.com')->firstOrFail();
        $this->assertSame($this->locA1->id, (int) $created->location_id);
        $this->assertSame($this->companyA->id, (int) $created->company_id);

        $admin = $this->makeUser(User::ROLE_ADMIN);
        $this->actingAs($admin)->post(route('users.store'), [
            'name' => 'No Place', 'email' => 'no.place@example.com', 'role' => 'staff',
        ] + $password)->assertSessionHasErrors('location_id');

        $this->actingAs($admin)->post(route('users.store'), [
            'name' => 'At B', 'email' => 'at.b@example.com', 'role' => 'manager', 'location_id' => $this->locB1->id,
        ] + $password)->assertSessionHasNoErrors();

        $this->assertSame($this->companyB->id, (int) User::where('email', 'at.b@example.com')->value('company_id'));
    }

    public function test_a_manager_only_manages_staff_at_their_own_location(): void
    {
        $manager = $this->userAt($this->locA1, User::ROLE_MANAGER);
        $mine = $this->userAt($this->locA1);
        $other = $this->userAt($this->locB1);

        $this->actingAs($manager)->get(route('users.index'))->assertOk()
            ->assertSee($mine->email)->assertDontSee($other->email);

        $this->actingAs($manager)->patch(route('users.deactivate', $other))->assertForbidden();
        $this->actingAs($manager)->patch(route('users.deactivate', $mine))->assertRedirect();
    }

    public function test_approvers_are_only_the_admins_and_managers_of_that_location(): void
    {
        $adminUser = $this->makeUser(User::ROLE_ADMIN);
        $managerHere = $this->userAt($this->locA1, User::ROLE_MANAGER);
        $managerSibling = $this->userAt($this->locA2, User::ROLE_MANAGER);
        $managerOtherCompany = $this->userAt($this->locB1, User::ROLE_MANAGER);

        $this->actingAs($this->userAt($this->locA1))->post(route('inventories.store'), [
            'product_id' => $this->stock['A1']['product']->id, 'location_id' => $this->locA1->id,
            'product_unit_id' => $this->stock['A1']['unit']->id, 'quantity' => 2,
        ])->assertSessionHasNoErrors();

        $this->assertSame(1, $adminUser->notifications()->count());
        $this->assertSame(1, $managerHere->notifications()->count());
        $this->assertSame(0, $managerSibling->notifications()->count());
        $this->assertSame(0, $managerOtherCompany->notifications()->count());
    }
}
