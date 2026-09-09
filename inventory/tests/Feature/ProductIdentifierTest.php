<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesInventoryFixtures;
use Tests\TestCase;

class ProductIdentifierTest extends TestCase
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
            'pricing_method' => 'manual',
            'selling_price' => 100,
            'units' => [
                [
                    'unit_of_measure_id' => $unit->id,
                    'conversion_factor' => 1,
                ],
            ],
        ], $overrides);
    }

    public function test_sku_and_item_code_are_auto_generated_when_left_blank(): void
    {
        $admin = $this->makeUser(User::ROLE_ADMIN);

        $this->actingAs($admin)
            ->post(route('products.store'), $this->baseFormPayload())
            ->assertRedirect(route('products.index'));

        $product = Product::where('name', 'Test Product')->firstOrFail();

        $this->assertNotNull($product->sku);
        $this->assertNotNull($product->item_code);
        $this->assertStringStartsWith('SKU-', $product->sku);
        $this->assertStringStartsWith('ITM-', $product->item_code);
    }

    public function test_manually_entered_sku_and_item_code_are_used_as_is(): void
    {
        $admin = $this->makeUser(User::ROLE_ADMIN);

        $payload = $this->baseFormPayload([
            'sku' => 'CUSTOM-SKU-1',
            'item_code' => 'CUSTOM-ITEM-1',
        ]);

        $this->actingAs($admin)
            ->post(route('products.store'), $payload)
            ->assertRedirect(route('products.index'));

        $product = Product::where('name', 'Test Product')->firstOrFail();

        $this->assertSame('CUSTOM-SKU-1', $product->sku);
        $this->assertSame('CUSTOM-ITEM-1', $product->item_code);
    }

    public function test_duplicate_sku_is_rejected(): void
    {
        $admin = $this->makeUser(User::ROLE_ADMIN);

        $this->actingAs($admin)
            ->post(route('products.store'), $this->baseFormPayload([
                'sku' => 'DUPLICATE-SKU',
                'name' => 'First Product',
            ]))
            ->assertRedirect(route('products.index'));

        $this->actingAs($admin)
            ->post(route('products.store'), $this->baseFormPayload([
                'sku' => 'DUPLICATE-SKU',
                'name' => 'Second Product',
            ]))
            ->assertSessionHasErrors('sku');

        $this->assertDatabaseCount('products', 1);
    }

    public function test_duplicate_item_code_is_rejected(): void
    {
        $admin = $this->makeUser(User::ROLE_ADMIN);

        $this->actingAs($admin)
            ->post(route('products.store'), $this->baseFormPayload([
                'item_code' => 'DUPLICATE-ITEM',
                'name' => 'First Product',
            ]))
            ->assertRedirect(route('products.index'));

        $this->actingAs($admin)
            ->post(route('products.store'), $this->baseFormPayload([
                'item_code' => 'DUPLICATE-ITEM',
                'name' => 'Second Product',
            ]))
            ->assertSessionHasErrors('item_code');

        $this->assertDatabaseCount('products', 1);
    }

    public function test_leaving_sku_blank_on_edit_preserves_the_existing_value(): void
    {
        $admin = $this->makeUser(User::ROLE_ADMIN);

        $company = $this->makeCompany();
        $category = $this->makeCategory();
        $unit = $this->makeUnit();

        $product = $this->makeProduct($company, $category, $unit, [
            'sku' => 'ORIGINAL-SKU',
            'item_code' => 'ORIGINAL-ITEM',
        ]);
        $this->makeProductUnit($product, $unit);

        $this->actingAs($admin)
            ->put(route('products.update', $product), [
                'product_category_id' => $category->id,
                'company_id' => $company->id,
                'name' => 'Test Product',
                'base_unit_id' => $unit->id,
                'reorder_point' => 0,
                'is_active' => '1',
                'pricing_method' => 'manual',
                'selling_price' => 100,
                'sku' => '',
                'item_code' => '',
                'units' => [
                    [
                        'unit_of_measure_id' => $unit->id,
                        'conversion_factor' => 1,
                        'product_unit_id' => $product->productUnits()->first()->id,
                    ],
                ],
            ])
            ->assertRedirect(route('products.index'));

        $product->refresh();

        $this->assertSame('ORIGINAL-SKU', $product->sku);
        $this->assertSame('ORIGINAL-ITEM', $product->item_code);
    }
}
