<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesInventoryFixtures;
use Tests\TestCase;

class ReportsTest extends TestCase
{
    use RefreshDatabase;
    use CreatesInventoryFixtures;

    public function test_any_signed_in_user_can_view_reports(): void
    {
        $staff = $this->makeUser(User::ROLE_STAFF);

        $this->actingAs($staff)->get(route('reports.index'))->assertOk();
        $this->actingAs($staff)->get(route('reports.stock-movements'))->assertOk();
        $this->actingAs($staff)->get(route('reports.transfers'))->assertOk();
        $this->actingAs($staff)->get(route('reports.low-stock'))->assertOk();
    }

    public function test_stock_movement_report_reflects_recorded_transactions(): void
    {
        $manager = $this->makeUser(User::ROLE_MANAGER);

        $company = $this->makeCompany();
        $location = $this->makeLocation($company);
        $category = $this->makeCategory();
        $unit = $this->makeUnit();
        $product = $this->makeProduct($company, $category, $unit, ['name' => 'Widget']);
        $productUnit = $this->makeProductUnit($product, $unit);

        $this->actingAs($manager)->post(route('inventories.store'), [
            'product_id' => $product->id,
            'location_id' => $location->id,
            'product_unit_id' => $productUnit->id,
            'quantity' => 20,
        ]);

        $response = $this->actingAs($manager)->get(route('reports.stock-movements', ['search' => 'Widget']));

        $response->assertOk();
        $response->assertSee('Widget');
    }

    public function test_low_stock_report_filters_by_status(): void
    {
        $manager = $this->makeUser(User::ROLE_MANAGER);

        $company = $this->makeCompany();
        $location = $this->makeLocation($company);
        $category = $this->makeCategory();
        $unit = $this->makeUnit();
        $product = $this->makeProduct($company, $category, $unit, ['name' => 'OutOfStockThing', 'reorder_point' => 100]);
        $productUnit = $this->makeProductUnit($product, $unit);
        $this->makeInventory($product, $location, $productUnit, 0, 0);

        $response = $this->actingAs($manager)->get(route('reports.low-stock', ['status' => 'out_of_stock']));

        $response->assertOk();
        $response->assertSee('OutOfStockThing');
    }
}
