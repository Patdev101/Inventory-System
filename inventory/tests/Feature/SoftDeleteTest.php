<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Location;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\UnitOfMeasure;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesInventoryFixtures;
use Tests\TestCase;

class SoftDeleteTest extends TestCase
{
    use RefreshDatabase;
    use CreatesInventoryFixtures;

    public function test_deleting_a_product_with_no_history_soft_deletes_it(): void
    {
        $admin = $this->makeUser(User::ROLE_ADMIN);

        $company = $this->makeCompany();
        $category = $this->makeCategory();
        $unit = $this->makeUnit();
        $product = $this->makeProduct($company, $category, $unit);

        $this->actingAs($admin)
            ->delete(route('products.destroy', $product))
            ->assertRedirect(route('products.index'));

        // The row still exists (soft-deleted), not gone.
        $this->assertDatabaseHas('products', ['id' => $product->id]);
        $this->assertNotNull($product->fresh()->deleted_at);

        // But it no longer shows up in normal queries.
        $this->assertNull(Product::find($product->id));
        $this->assertNotNull(Product::withTrashed()->find($product->id));
    }

    public function test_deleting_a_location_with_inventory_is_blocked(): void
    {
        $admin = $this->makeUser(User::ROLE_ADMIN);

        $company = $this->makeCompany();
        $category = $this->makeCategory();
        $unit = $this->makeUnit();
        $product = $this->makeProduct($company, $category, $unit);
        $productUnit = $this->makeProductUnit($product, $unit);
        $location = $this->makeLocation($company);
        $this->makeInventory($product, $location, $productUnit, 5, 5);

        $this->actingAs($admin)
            ->delete(route('locations.destroy', $location))
            ->assertRedirect(route('locations.index'));

        $this->assertDatabaseHas('locations', [
            'id' => $location->id,
            'deleted_at' => null,
        ]);
    }

    public function test_deleting_an_empty_location_soft_deletes_it(): void
    {
        $admin = $this->makeUser(User::ROLE_ADMIN);

        $company = $this->makeCompany();
        $location = $this->makeLocation($company);

        $this->actingAs($admin)
            ->delete(route('locations.destroy', $location))
            ->assertRedirect(route('locations.index'));

        $this->assertNotNull($location->fresh()->deleted_at);
        $this->assertNull(Location::find($location->id));
    }

    public function test_deleting_a_company_with_products_is_blocked(): void
    {
        $admin = $this->makeUser(User::ROLE_ADMIN);

        $company = $this->makeCompany();
        $category = $this->makeCategory();
        $unit = $this->makeUnit();
        $this->makeProduct($company, $category, $unit);

        $this->actingAs($admin)
            ->delete(route('companies.destroy', $company))
            ->assertRedirect(route('companies.index'));

        $this->assertDatabaseHas('companies', [
            'id' => $company->id,
            'deleted_at' => null,
        ]);
    }

    public function test_deleting_an_empty_company_soft_deletes_it(): void
    {
        $admin = $this->makeUser(User::ROLE_ADMIN);

        $company = $this->makeCompany();

        $this->actingAs($admin)
            ->delete(route('companies.destroy', $company))
            ->assertRedirect(route('companies.index'));

        $this->assertNotNull($company->fresh()->deleted_at);
        $this->assertNull(Company::find($company->id));
    }

    public function test_a_deleted_location_can_be_restored(): void
    {
        $admin = $this->makeUser(User::ROLE_ADMIN);

        $company = $this->makeCompany();
        $location = $this->makeLocation($company);

        $this->actingAs($admin)->delete(route('locations.destroy', $location));
        $this->assertNull(Location::find($location->id));

        $this->actingAs($admin)
            ->get(route('locations.trashed'))
            ->assertOk()
            ->assertSee($location->name);

        $this->actingAs($admin)
            ->patch(route('locations.restore', $location->id))
            ->assertRedirect(route('locations.trashed'));

        $this->assertNotNull(Location::find($location->id));
        $this->assertNull($location->fresh()->deleted_at);
    }

    public function test_a_deleted_company_can_be_restored(): void
    {
        $admin = $this->makeUser(User::ROLE_ADMIN);
        $company = $this->makeCompany();

        $this->actingAs($admin)->delete(route('companies.destroy', $company));
        $this->assertNull(Company::find($company->id));

        $this->actingAs($admin)
            ->patch(route('companies.restore', $company->id))
            ->assertRedirect(route('companies.trashed'));

        $this->assertNotNull(Company::find($company->id));
    }

    public function test_deleting_a_product_category_with_products_is_blocked(): void
    {
        $admin = $this->makeUser(User::ROLE_ADMIN);

        $company = $this->makeCompany();
        $category = $this->makeCategory();
        $unit = $this->makeUnit();
        $this->makeProduct($company, $category, $unit);

        $this->actingAs($admin)
            ->delete(route('product-categories.destroy', $category))
            ->assertRedirect(route('product-categories.index'));

        $this->assertDatabaseHas('product_categories', [
            'id' => $category->id,
            'deleted_at' => null,
        ]);
    }

    public function test_an_empty_product_category_can_be_deleted_and_restored(): void
    {
        $admin = $this->makeUser(User::ROLE_ADMIN);
        $category = $this->makeCategory();

        $this->actingAs($admin)
            ->delete(route('product-categories.destroy', $category))
            ->assertRedirect(route('product-categories.index'));

        $this->assertNull(ProductCategory::find($category->id));

        $this->actingAs($admin)
            ->get(route('product-categories.trashed'))
            ->assertOk()
            ->assertSee($category->name);

        $this->actingAs($admin)
            ->patch(route('product-categories.restore', $category->id))
            ->assertRedirect(route('product-categories.trashed'));

        $this->assertNotNull(ProductCategory::find($category->id));
    }

    public function test_deleting_a_unit_of_measure_in_use_is_blocked(): void
    {
        $admin = $this->makeUser(User::ROLE_ADMIN);

        $company = $this->makeCompany();
        $category = $this->makeCategory();
        $unit = $this->makeUnit();
        $this->makeProduct($company, $category, $unit);

        $this->actingAs($admin)
            ->delete(route('units-of-measure.destroy', ['units_of_measure' => $unit->id]))
            ->assertRedirect(route('units-of-measure.index'));

        $this->assertDatabaseHas('units_of_measure', [
            'id' => $unit->id,
            'deleted_at' => null,
        ]);
    }

    public function test_an_unused_unit_of_measure_can_be_deleted_and_restored(): void
    {
        $admin = $this->makeUser(User::ROLE_ADMIN);
        $unit = $this->makeUnit();

        $this->actingAs($admin)
            ->delete(route('units-of-measure.destroy', ['units_of_measure' => $unit->id]))
            ->assertRedirect(route('units-of-measure.index'));

        $this->assertNull(UnitOfMeasure::find($unit->id));

        $this->actingAs($admin)
            ->patch(route('units-of-measure.restore', ['units_of_measure' => $unit->id]))
            ->assertRedirect(route('units-of-measure.trashed'));

        $this->assertNotNull(UnitOfMeasure::find($unit->id));
    }

    public function test_staff_cannot_access_trashed_or_restore_endpoints(): void
    {
        $staff = $this->makeUser(User::ROLE_STAFF);
        $company = $this->makeCompany();

        $this->actingAs($staff)
            ->get(route('locations.trashed'))
            ->assertForbidden();

        $this->actingAs($staff)
            ->patch(route('companies.restore', $company->id))
            ->assertForbidden();
    }
}
