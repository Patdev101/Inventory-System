<?php

namespace Tests\Feature;

use App\Models\Inventory;
use App\Models\InventoryTransaction;
use App\Models\InventoryTransfer;
use App\Models\User;
use App\Services\ExpiryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesInventoryFixtures;
use Tests\TestCase;

/**
 * Products that expire: every stock-in records its expiry date, the oldest
 * stock is assumed to go out first, and stock close to (or past) its expiry
 * is flagged on the dashboard and the Expiring Stock report.
 */
class ExpiryTrackingTest extends TestCase
{
    use RefreshDatabase;
    use CreatesInventoryFixtures;

    private $company;
    private $location;
    private $product;
    private $unit;
    private Inventory $inventory;
    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = $this->makeCompany();
        $this->location = $this->makeLocation($this->company);
        $uom = $this->makeUnit();
        $this->product = $this->makeProduct($this->company, $this->makeCategory(), $uom, ['name' => 'MILK']);
        $this->unit = $this->makeProductUnit($this->product, $uom);
        $this->inventory = $this->makeInventory($this->product, $this->location, $this->unit, 0, 0);
        $this->admin = $this->makeUser(User::ROLE_ADMIN);
    }

    private function move(string $type, float $quantity, ?string $expiry = null, array $extra = [])
    {
        return $this->actingAs($this->admin)->put(route('inventories.update', $this->inventory), array_merge([
            'product_unit_id' => $this->unit->id,
            'movement_type' => $type,
            'quantity' => $quantity,
            'remarks' => 'Test',
            'expiry_date' => $expiry,
        ], $extra));
    }

    public function test_the_expiry_date_is_optional_and_saved_when_given(): void
    {
        $this->move('in', 5)->assertSessionHasNoErrors();
        $this->assertNull(InventoryTransaction::sole()->expiry_date);

        $this->move('in', 5, now()->addDays(60)->toDateString())->assertSessionHasNoErrors();
        $this->assertSame(now()->addDays(60)->toDateString(), InventoryTransaction::latest('id')->first()->expiry_date->toDateString());
    }

    public function test_stock_going_out_does_not_need_an_expiry_date(): void
    {
        $this->move('in', 5, now()->addDays(60)->toDateString());

        $this->move('out', 2)->assertSessionHasNoErrors();
    }

    public function test_the_oldest_stock_goes_out_first(): void
    {
        $this->move('in', 10, now()->addDays(5)->toDateString());   // older delivery
        $this->move('in', 10, now()->addDays(90)->toDateString());  // newer delivery
        $this->move('out', 12);                                     // takes all of the old one + 2 of the new

        $batches = app(ExpiryService::class)->remainingBatches($this->inventory->fresh());

        $this->assertCount(1, $batches);
        $this->assertEquals(8, $batches[0]['base_quantity']);
        $this->assertSame(now()->addDays(90)->toDateString(), $batches[0]['expiry_date']->toDateString());
    }

    public function test_expiring_and_expired_stock_is_flagged_on_the_dashboard_and_report(): void
    {
        $this->move('in', 3, now()->subDay()->toDateString());
        $this->move('in', 4, now()->addDays(10)->toDateString());
        $this->move('in', 5, now()->addDays(200)->toDateString());

        $this->actingAs($this->admin)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('1 batch has expired')
            ->assertSee('1 batch expires within 30 days');

        $this->actingAs($this->admin)->get(route('reports.expiring'))
            ->assertOk()
            ->assertSee('MILK')
            ->assertSee('Expired 1 day ago')
            ->assertSee('10 days left')
            ->assertDontSee(now()->addDays(200)->format('m/d/Y'));
    }

    public function test_a_transfer_carries_the_expiry_date_to_the_destination(): void
    {
        $this->move('in', 10, now()->addDays(40)->toDateString());

        $destination = $this->makeLocation($this->company, ['name' => 'BRANCH 2']);
        $receiver = $this->makeUser(User::ROLE_STAFF, ['company_id' => $this->company->id, 'location_id' => $destination->id]);

        $this->actingAs($this->admin)->post(route('inventory-transfers.store'), [
            'destination_location_id' => $destination->id,
            'receiver_id' => $receiver->id,
            'items' => [['source_inventory_id' => $this->inventory->id, 'product_unit_id' => $this->unit->id, 'quantity' => 4]],
        ])->assertSessionHasNoErrors();

        $transfer = InventoryTransfer::sole();
        $this->assertSame(now()->addDays(40)->toDateString(), $transfer->expiry_date->toDateString());

        $this->actingAs($receiver)->patch(route('inventory-transfers.audit', $transfer), ['result' => 'pass'])->assertSessionHasNoErrors();
        $this->actingAs($receiver)->patch(route('inventory-transfers.receive', $transfer), ['received_quantity' => 4])->assertSessionHasNoErrors();

        $arrival = InventoryTransaction::where('type', 'in')->where('location_id', $destination->id)->sole();
        $this->assertSame(now()->addDays(40)->toDateString(), $arrival->expiry_date->toDateString());
    }

    public function test_stock_without_an_expiry_date_is_never_flagged(): void
    {
        $this->move('in', 6)->assertSessionHasNoErrors();

        $this->actingAs($this->admin)->get(route('reports.expiring'))
            ->assertOk()
            ->assertDontSee('MILK');

        $this->actingAs($this->admin)->get(route('inventories.show', $this->inventory))
            ->assertOk()
            ->assertDontSee('Expiry Dates');
    }
}