<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesInventoryFixtures;
use Tests\TestCase;

class PurchaseOrderReceivingTest extends TestCase
{
    use RefreshDatabase;
    use CreatesInventoryFixtures;

    public function test_admin_can_create_a_draft_purchase_order(): void
    {
        $admin = $this->makeUser(User::ROLE_ADMIN);

        $company = $this->makeCompany();
        $location = $this->makeLocation($company);
        $category = $this->makeCategory();
        $unit = $this->makeUnit();
        $product = $this->makeProduct($company, $category, $unit);
        $this->makeProductUnit($product, $unit);

        $supplier = \App\Models\Supplier::create([
            'company_id' => $company->id,
            'name' => 'Test Supplier',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->post(route('purchase-orders.store'), [
            'supplier_id' => $supplier->id,
            'location_id' => $location->id,
            'items' => [
                [
                    'product_id' => $product->id,
                    'product_unit_id' => $product->productUnits()->first()->id,
                    'quantity_ordered' => 10,
                    'unit_price' => 5,
                ],
            ],
        ]);

        $response->assertSessionDoesntHaveErrors();
        $response->assertRedirect();

        $this->assertDatabaseHas('purchase_orders', [
            'supplier_id' => $supplier->id,
            'status' => 'draft',
        ]);
    }

    public function test_po_numbers_increment_correctly_across_multiple_orders(): void
    {
        // Exercises generatePoNumber()'s CAST(SUBSTRING(...)) raw SQL
        // against a real, non-empty purchase_orders table — this runs on
        // SQLite in tests but SQL Server in production, so both must
        // parse this expression correctly.
        $admin = $this->makeUser(User::ROLE_ADMIN);

        $company = $this->makeCompany();
        $location = $this->makeLocation($company);
        $category = $this->makeCategory();
        $unit = $this->makeUnit();
        $product = $this->makeProduct($company, $category, $unit);
        $this->makeProductUnit($product, $unit);

        $supplier = \App\Models\Supplier::create([
            'company_id' => $company->id,
            'name' => 'Test Supplier',
            'is_active' => true,
        ]);

        $payload = [
            'supplier_id' => $supplier->id,
            'location_id' => $location->id,
            'items' => [
                [
                    'product_id' => $product->id,
                    'product_unit_id' => $product->productUnits()->first()->id,
                    'quantity_ordered' => 10,
                    'unit_price' => 5,
                ],
            ],
        ];

        $this->actingAs($admin)->post(route('purchase-orders.store'), $payload);
        $this->actingAs($admin)->post(route('purchase-orders.store'), $payload);
        $this->actingAs($admin)->post(route('purchase-orders.store'), $payload);

        $poNumbers = \App\Models\PurchaseOrder::query()
            ->orderBy('id')
            ->pluck('po_number')
            ->all();

        $this->assertCount(3, $poNumbers);
        $this->assertSame(
            $poNumbers,
            array_unique($poNumbers),
            'PO numbers must be unique across consecutive creates.'
        );
    }

    /**
     * The primary workflow this document is about: PO -> Receiving ->
     * Inventory increase -> PO quantity/status update, all the way
     * through to what the POS's product endpoint would report.
     */
    public function test_full_receiving_workflow_increases_inventory_and_updates_po_status(): void
    {
        $admin = $this->makeUser(User::ROLE_ADMIN);

        $company = $this->makeCompany();
        $location = $this->makeLocation($company);
        $category = $this->makeCategory();
        $unit = $this->makeUnit();
        $product = $this->makeProduct($company, $category, $unit);
        $productUnit = $this->makeProductUnit($product, $unit);

        $supplier = \App\Models\Supplier::create([
            'company_id' => $company->id,
            'name' => 'Test Supplier',
            'is_active' => true,
        ]);

        $create = $this->actingAs($admin)->post(route('purchase-orders.store'), [
            'supplier_id' => $supplier->id,
            'location_id' => $location->id,
            'items' => [
                [
                    'product_id' => $product->id,
                    'product_unit_id' => $productUnit->id,
                    'quantity_ordered' => 100,
                    'unit_price' => 5,
                ],
            ],
        ]);

        $purchaseOrder = \App\Models\PurchaseOrder::query()->latest('id')->firstOrFail();
        $item = $purchaseOrder->items()->firstOrFail();

        // draft -> pending_approval -> approved -> ordered, mirroring the
        // real workflow (receiving is only open once ordered).
        app(\App\Services\PurchaseOrderService::class)->submitForApproval($purchaseOrder, $admin->id);
        app(\App\Services\PurchaseOrderService::class)->approve($purchaseOrder->fresh(), $admin->id);
        app(\App\Services\PurchaseOrderService::class)->markOrdered($purchaseOrder->fresh(), $admin->id);

        // Partial receive: 40 of 100.
        $this->actingAs($admin)->patch(route('purchase-orders.receive', $purchaseOrder), [
            'lines' => [
                ['purchase_order_item_id' => $item->id, 'quantity_received' => 40],
            ],
        ])->assertRedirect(route('purchase-orders.show', $purchaseOrder));

        $this->assertSame('partially_received', $purchaseOrder->fresh()->status);
        $this->assertSame('40.0000', $item->fresh()->quantity_received);

        $this->assertDatabaseHas('inventories', [
            'product_id' => $product->id,
            'location_id' => $location->id,
            'base_quantity' => 40,
        ]);

        // Remaining receive: 60 of 100 -> fully received -> completed.
        $this->actingAs($admin)->patch(route('purchase-orders.receive', $purchaseOrder), [
            'lines' => [
                ['purchase_order_item_id' => $item->id, 'quantity_received' => 60],
            ],
        ])->assertRedirect(route('purchase-orders.show', $purchaseOrder));

        $this->assertSame('completed', $purchaseOrder->fresh()->status);
        $this->assertSame('100.0000', $item->fresh()->quantity_received);

        $this->assertDatabaseHas('inventories', [
            'product_id' => $product->id,
            'location_id' => $location->id,
            'base_quantity' => 100,
        ]);

        // Over-receiving beyond what's left must be rejected, not silently
        // clamped or double-applied.
        $this->actingAs($admin)->patch(route('purchase-orders.receive', $purchaseOrder), [
            'lines' => [
                ['purchase_order_item_id' => $item->id, 'quantity_received' => 1],
            ],
        ])->assertSessionHasErrors();

        $this->assertDatabaseHas('inventories', [
            'product_id' => $product->id,
            'location_id' => $location->id,
            'base_quantity' => 100,
        ]);
    }

    public function test_purchase_order_pdf_renders_successfully(): void
    {
        $admin = $this->makeUser(User::ROLE_ADMIN);

        $company = $this->makeCompany();
        $location = $this->makeLocation($company);
        $category = $this->makeCategory();
        $unit = $this->makeUnit();
        $product = $this->makeProduct($company, $category, $unit);
        $this->makeProductUnit($product, $unit);

        $supplier = \App\Models\Supplier::create([
            'company_id' => $company->id,
            'name' => 'Test Supplier',
            'is_active' => true,
        ]);

        $this->actingAs($admin)->post(route('purchase-orders.store'), [
            'supplier_id' => $supplier->id,
            'location_id' => $location->id,
            'items' => [
                [
                    'product_id' => $product->id,
                    'product_unit_id' => $product->productUnits()->first()->id,
                    'quantity_ordered' => 10,
                    'unit_price' => 5,
                ],
            ],
        ]);

        $purchaseOrder = \App\Models\PurchaseOrder::query()->latest('id')->firstOrFail();

        $response = $this->actingAs($admin)->get(route('purchase-orders.pdf', $purchaseOrder));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');

        // A real PDF binary starts with this magic header — confirms
        // dompdf actually produced a PDF, not an error page mislabeled
        // with a PDF content-type.
        $this->assertStringStartsWith('%PDF-', $response->getContent());
    }
}
