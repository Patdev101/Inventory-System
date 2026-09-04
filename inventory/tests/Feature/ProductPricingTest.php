<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesInventoryFixtures;
use Tests\TestCase;

class ProductPricingTest extends TestCase
{
    use RefreshDatabase;
    use CreatesInventoryFixtures;

    private function baseFormPayload(array $overrides = []): array
    {
        $company = $this->makeCompany();
        $category = $this->makeCategory();
        $unit = $this->makeUnit();

        return array_merge([
            'product_category_id' => $category->id,
            'company_id' => $company->id,
            'name' => 'Test Product',
            'base_unit_id' => $unit->id,
            'reorder_point' => 0,
            'is_active' => '1',
            'units' => [
                [
                    'unit_of_measure_id' => $unit->id,
                    'conversion_factor' => 1,
                ],
            ],
        ], $overrides);
    }

    public function test_create_and_edit_pages_render_the_vat_breakdown(): void
    {
        $admin = $this->makeUser(User::ROLE_ADMIN);

        $this->actingAs($admin)
            ->get(route('products.create'))
            ->assertOk()
            ->assertSee('vat-breakdown', false);

        $company = $this->makeCompany();
        $category = $this->makeCategory();
        $unit = $this->makeUnit();

        $product = $this->makeProduct($company, $category, $unit, [
            'cost_price' => 80,
            'selling_price' => 112,
            'pricing_method' => 'manual',
        ]);

        $this->actingAs($admin)
            ->get(route('products.edit', $product))
            ->assertOk()
            ->assertSee('vat-breakdown', false);
    }

    public function test_manual_pricing_stores_the_entered_selling_price(): void
    {
        $admin = $this->makeUser(User::ROLE_ADMIN);

        $payload = $this->baseFormPayload([
            'pricing_method' => 'manual',
            'cost_price' => 60,
            'selling_price' => 150,
        ]);

        $this->actingAs($admin)
            ->post(route('products.store'), $payload)
            ->assertRedirect(route('products.index'));

        $product = Product::where('name', 'Test Product')->firstOrFail();

        $this->assertSame('manual', $product->pricing_method);
        $this->assertSame('150.00', $product->selling_price);
        $this->assertSame('60.0000', $product->cost_price);
        $this->assertNull($product->markup_percentage);
    }

    public function test_markup_pricing_computes_selling_price_server_side(): void
    {
        $admin = $this->makeUser(User::ROLE_ADMIN);

        $payload = $this->baseFormPayload([
            'pricing_method' => 'markup',
            'cost_price' => 100,
            'markup_percentage' => 25,
            // A tampered/incorrect client-supplied selling price must be
            // ignored — the server always recalculates it.
            'selling_price' => 999999,
        ]);

        $this->actingAs($admin)
            ->post(route('products.store'), $payload)
            ->assertRedirect(route('products.index'));

        $product = Product::where('name', 'Test Product')->firstOrFail();

        $this->assertSame('markup', $product->pricing_method);
        $this->assertSame('125.00', $product->selling_price);
        $this->assertSame('100.0000', $product->cost_price);
        $this->assertSame('25.0000', $product->markup_percentage);
    }

    public function test_profit_and_margin_are_calculated_correctly(): void
    {
        $company = $this->makeCompany();
        $category = $this->makeCategory();
        $unit = $this->makeUnit();

        $product = $this->makeProduct($company, $category, $unit, [
            'cost_price' => 80,
            'selling_price' => 100,
            'pricing_method' => 'manual',
        ]);

        $this->assertSame(20.0, $product->profit);
        $this->assertSame(20.0, $product->profit_margin);
    }

    public function test_profit_margin_avoids_division_by_zero(): void
    {
        $company = $this->makeCompany();
        $category = $this->makeCategory();
        $unit = $this->makeUnit();

        $product = $this->makeProduct($company, $category, $unit, [
            'cost_price' => 50,
            'selling_price' => 0,
            'pricing_method' => 'manual',
        ]);

        $this->assertSame(-50.0, $product->profit);
        $this->assertNull($product->profit_margin);
    }

    public function test_profit_is_null_without_a_cost_price(): void
    {
        $company = $this->makeCompany();
        $category = $this->makeCategory();
        $unit = $this->makeUnit();

        $product = $this->makeProduct($company, $category, $unit, [
            'cost_price' => null,
            'selling_price' => 100,
            'pricing_method' => 'manual',
        ]);

        $this->assertNull($product->profit);
        $this->assertNull($product->profit_margin);
    }

    public function test_markup_pricing_requires_cost_price_and_markup_percentage(): void
    {
        $admin = $this->makeUser(User::ROLE_ADMIN);

        $payload = $this->baseFormPayload([
            'pricing_method' => 'markup',
        ]);

        $this->actingAs($admin)
            ->post(route('products.store'), $payload)
            ->assertSessionHasErrors(['cost_price', 'markup_percentage']);

        $this->assertDatabaseMissing('products', ['name' => 'Test Product']);
    }

    public function test_manual_pricing_requires_a_selling_price(): void
    {
        $admin = $this->makeUser(User::ROLE_ADMIN);

        $payload = $this->baseFormPayload([
            'pricing_method' => 'manual',
        ]);

        $this->actingAs($admin)
            ->post(route('products.store'), $payload)
            ->assertSessionHasErrors(['selling_price']);

        $this->assertDatabaseMissing('products', ['name' => 'Test Product']);
    }

    public function test_negative_prices_and_markup_are_rejected(): void
    {
        $admin = $this->makeUser(User::ROLE_ADMIN);

        $payload = $this->baseFormPayload([
            'pricing_method' => 'markup',
            'cost_price' => -10,
            'markup_percentage' => -5,
        ]);

        $this->actingAs($admin)
            ->post(route('products.store'), $payload)
            ->assertSessionHasErrors(['cost_price', 'markup_percentage']);

        $this->assertDatabaseMissing('products', ['name' => 'Test Product']);
    }

    public function test_negative_selling_price_is_rejected(): void
    {
        $admin = $this->makeUser(User::ROLE_ADMIN);

        $payload = $this->baseFormPayload([
            'pricing_method' => 'manual',
            'selling_price' => -25,
        ]);

        $this->actingAs($admin)
            ->post(route('products.store'), $payload)
            ->assertSessionHasErrors(['selling_price']);

        $this->assertDatabaseMissing('products', ['name' => 'Test Product']);
    }

    public function test_updating_unrelated_fields_preserves_an_existing_manual_selling_price(): void
    {
        $admin = $this->makeUser(User::ROLE_ADMIN);

        $company = $this->makeCompany();
        $category = $this->makeCategory();
        $unit = $this->makeUnit();

        // Simulates a product that already existed before pricing fields
        // were introduced: it only ever had a selling_price set.
        $product = $this->makeProduct($company, $category, $unit, [
            'selling_price' => 249.99,
            'cost_price' => null,
            'markup_percentage' => null,
            'pricing_method' => 'manual',
        ]);

        $this->makeProductUnit($product, $unit);

        $payload = [
            'product_category_id' => $category->id,
            'company_id' => $company->id,
            'name' => 'Renamed Existing Product',
            'base_unit_id' => $unit->id,
            'reorder_point' => 0,
            'is_active' => '1',
            'pricing_method' => 'manual',
            'selling_price' => 249.99,
            'units' => [
                [
                    'unit_of_measure_id' => $unit->id,
                    'conversion_factor' => 1,
                ],
            ],
        ];

        $this->actingAs($admin)
            ->put(route('products.update', $product), $payload)
            ->assertRedirect(route('products.index'));

        $product->refresh();

        $this->assertSame('249.99', $product->selling_price);
        $this->assertNull($product->cost_price);
    }

    public function test_inventory_api_returns_pricing_fields_and_keeps_selling_price_unchanged(): void
    {
        config(['services.pos.api_token' => 'test-token']);

        $company = $this->makeCompany();
        $category = $this->makeCategory();
        $unit = $this->makeUnit();

        $product = $this->makeProduct($company, $category, $unit, [
            'cost_price' => 40,
            'markup_percentage' => 50,
            'pricing_method' => 'markup',
            'selling_price' => 60,
        ]);

        $this->makeProductUnit($product, $unit);

        $response = $this->withHeader('Authorization', 'Bearer test-token')
            ->getJson('/api/products');

        $response->assertOk();

        $data = collect($response->json())->firstWhere('id', $product->id);

        $this->assertNotNull($data);
        $this->assertEquals('60.00', $data['selling_price']);
        $this->assertEquals('40.0000', $data['cost_price']);
        $this->assertEquals('50.0000', $data['markup_percentage']);
        $this->assertSame('markup', $data['pricing_method']);
        $this->assertEquals(20, $data['profit']);
        $this->assertEqualsWithDelta(33.33, $data['profit_margin'], 0.01);
    }
}
