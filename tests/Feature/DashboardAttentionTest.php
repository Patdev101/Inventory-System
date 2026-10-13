<?php

namespace Tests\Feature;

use App\Models\StockMovementRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesInventoryFixtures;
use Tests\TestCase;

/**
 * The "Needs attention" list at the top of the dashboard only names work the
 * signed-in person can act on.
 */
class DashboardAttentionTest extends TestCase
{
    use RefreshDatabase;
    use CreatesInventoryFixtures;

    private $location;
    private array $stock;

    protected function setUp(): void
    {
        parent::setUp();

        $company = $this->makeCompany();
        $this->location = $this->makeLocation($company);

        $unit = $this->makeUnit();
        $product = $this->makeProduct($company, $this->makeCategory(), $unit, ['name' => 'COLA', 'sku' => 'COLA']);
        $productUnit = $this->makeProductUnit($product, $unit);

        $this->stock = [
            'product' => $product,
            'unit' => $productUnit,
            'inventory' => $this->makeInventory($product, $this->location, $productUnit, 50, 50),
        ];
    }

    private function request(User $by): StockMovementRequest
    {
        return StockMovementRequest::create([
            'type' => 'in',
            'status' => StockMovementRequest::STATUS_PENDING,
            'product_id' => $this->stock['product']->id,
            'location_id' => $this->location->id,
            'product_unit_id' => $this->stock['unit']->id,
            'quantity' => 1,
            'requested_by' => $by->id,
        ]);
    }

    public function test_nothing_to_do_says_so(): void
    {
        $this->actingAs($this->makeUser(User::ROLE_ADMIN))->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Needs attention')
            ->assertSee('all caught up', false);
    }

    public function test_out_of_stock_is_listed_with_the_right_action_for_each_role(): void
    {
        $this->stock['inventory']->update(['base_quantity' => 0, 'quantity' => 0]);

        $this->actingAs($this->makeUser(User::ROLE_ADMIN))->get(route('dashboard'))
            ->assertOk()
            ->assertSee('1 item is out of stock')
            ->assertSee('Create purchase order');

        $this->actingAs($this->makeUser(User::ROLE_STAFF))->get(route('dashboard'))
            ->assertOk()
            ->assertSee('1 item is out of stock')
            ->assertDontSee('Create purchase order');
    }

    public function test_an_approver_is_told_about_requests_and_staff_only_about_their_own(): void
    {
        $staff = $this->makeUser(User::ROLE_STAFF);
        $otherStaff = $this->makeUser(User::ROLE_STAFF);

        $this->request($staff);
        $this->request($otherStaff);

        $this->actingAs($this->makeUser(User::ROLE_MANAGER))->get(route('dashboard'))
            ->assertOk()
            ->assertSee('2 stock requests need your approval');

        $this->actingAs($staff)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('1 of your stock request is waiting for approval')
            ->assertDontSee('need your approval');
    }
}
