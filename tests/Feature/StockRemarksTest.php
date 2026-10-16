<?php

namespace Tests\Feature;

use App\Models\InventoryTransaction;
use App\Models\StockMovementRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesInventoryFixtures;
use Tests\TestCase;

/**
 * Every manual stock movement says why it happened, and the reason stays
 * with the movement (including when a staff request is approved later).
 */
class StockRemarksTest extends TestCase
{
    use RefreshDatabase;
    use CreatesInventoryFixtures;

    private array $stock;

    protected function setUp(): void
    {
        parent::setUp();

        $company = $this->makeCompany();
        $location = $this->makeLocation($company);
        $unit = $this->makeUnit();
        $product = $this->makeProduct($company, $this->makeCategory(), $unit);
        $productUnit = $this->makeProductUnit($product, $unit);

        $this->stock = [
            'location' => $location,
            'unit' => $productUnit,
            'inventory' => $this->makeInventory($product, $location, $productUnit, 20, 20),
        ];
    }

    private function pullOut(User $user, array $extra = [])
    {
        return $this->actingAs($user)->put(route('inventories.update', $this->stock['inventory']), array_merge([
            'product_unit_id' => $this->stock['unit']->id,
            'movement_type' => 'out',
            'quantity' => 2,
        ], $extra));
    }

    public function test_a_stock_movement_without_remarks_is_refused(): void
    {
        $this->pullOut($this->makeUser(User::ROLE_ADMIN))->assertSessionHasErrors('remarks');

        $this->assertSame(0, InventoryTransaction::count());
    }

    public function test_remarks_are_saved_on_the_transaction(): void
    {
        $this->pullOut($this->makeUser(User::ROLE_ADMIN), ['remarks' => 'Damaged in storage'])
            ->assertSessionHasNoErrors();

        $this->assertSame('Damaged in storage', InventoryTransaction::sole()->notes);
    }

    public function test_a_staff_requests_remarks_carry_through_to_the_approved_movement(): void
    {
        $staff = $this->makeUser(User::ROLE_STAFF, ['company_id' => $this->stock['location']->company_id, 'location_id' => $this->stock['location']->id]);

        $this->pullOut($staff, ['remarks' => 'Pulled out for display'])->assertSessionHasNoErrors();

        $request = StockMovementRequest::sole();
        $this->assertSame('Pulled out for display', $request->notes);

        $this->actingAs($this->makeUser(User::ROLE_ADMIN))
            ->get(route('stock-movement-requests.index'))
            ->assertSee('Pulled out for display');

        $this->actingAs($this->makeUser(User::ROLE_ADMIN))
            ->patch(route('stock-movement-requests.approve', $request))
            ->assertSessionHasNoErrors();

        $this->assertSame('Pulled out for display', InventoryTransaction::sole()->notes);
    }
}
