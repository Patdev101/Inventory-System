<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesInventoryFixtures;
use Tests\TestCase;

class InventoryCompanyScopingTest extends TestCase
{
    use RefreshDatabase;
    use CreatesInventoryFixtures;

    public function test_adding_stock_for_a_product_and_location_in_different_companies_is_rejected(): void
    {
        $admin = $this->makeUser(User::ROLE_ADMIN);

        $companyA = $this->makeCompany(['name' => 'Company A', 'code' => 'CO-A-' . uniqid()]);
        $companyB = $this->makeCompany(['name' => 'Company B', 'code' => 'CO-B-' . uniqid()]);

        $category = $this->makeCategory();
        $unit = $this->makeUnit();

        $product = $this->makeProduct($companyA, $category, $unit);
        $productUnit = $this->makeProductUnit($product, $unit);

        $locationInOtherCompany = $this->makeLocation($companyB);

        $this->actingAs($admin)
            ->post(route('inventories.store'), [
                'product_id' => $product->id,
                'location_id' => $locationInOtherCompany->id,
                'product_unit_id' => $productUnit->id,
                'quantity' => 10,
            ])
            ->assertSessionHasErrors('location_id');

        $this->assertDatabaseMissing('inventories', [
            'product_id' => $product->id,
            'location_id' => $locationInOtherCompany->id,
        ]);
    }

    public function test_adding_stock_for_a_product_and_location_in_the_same_company_succeeds(): void
    {
        $admin = $this->makeUser(User::ROLE_ADMIN);

        $company = $this->makeCompany();
        $category = $this->makeCategory();
        $unit = $this->makeUnit();

        $product = $this->makeProduct($company, $category, $unit);
        $productUnit = $this->makeProductUnit($product, $unit);
        $location = $this->makeLocation($company);

        $this->actingAs($admin)
            ->post(route('inventories.store'), [
                'product_id' => $product->id,
                'location_id' => $location->id,
                'product_unit_id' => $productUnit->id,
                'quantity' => 10,
            ])
            ->assertSessionDoesntHaveErrors();

        $this->assertDatabaseHas('inventories', [
            'product_id' => $product->id,
            'location_id' => $location->id,
            'base_quantity' => 10,
        ]);
    }
}
