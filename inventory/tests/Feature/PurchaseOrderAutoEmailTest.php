<?php

namespace Tests\Feature;

use App\Mail\PurchaseOrderMail;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\User;
use App\Services\PurchaseOrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\Concerns\CreatesInventoryFixtures;
use Tests\TestCase;

class PurchaseOrderAutoEmailTest extends TestCase
{
    use RefreshDatabase;
    use CreatesInventoryFixtures;

    private function createApprovedPurchaseOrder(User $admin, ?string $supplierEmail): PurchaseOrder
    {
        $company = $this->makeCompany();
        $location = $this->makeLocation($company);
        $category = $this->makeCategory();
        $unit = $this->makeUnit();
        $product = $this->makeProduct($company, $category, $unit);
        $this->makeProductUnit($product, $unit);

        $supplier = Supplier::create([
            'company_id' => $company->id,
            'name' => 'Test Supplier',
            'email' => $supplierEmail,
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

        $purchaseOrder = PurchaseOrder::query()->latest('id')->firstOrFail();

        $service = app(PurchaseOrderService::class);
        $service->submitForApproval($purchaseOrder, $admin->id);
        $service->approve($purchaseOrder->fresh(), $admin->id);

        return $purchaseOrder->fresh();
    }

    public function test_marking_a_purchase_order_ordered_automatically_emails_the_supplier(): void
    {
        Mail::fake();

        $admin = $this->makeUser(User::ROLE_ADMIN);
        $purchaseOrder = $this->createApprovedPurchaseOrder($admin, 'supplier@example.com');

        app(PurchaseOrderService::class)->markOrdered($purchaseOrder, $admin->id);

        Mail::assertSent(PurchaseOrderMail::class, function (PurchaseOrderMail $mail) {
            return $mail->hasTo('supplier@example.com');
        });

        $this->assertDatabaseHas('purchase_order_emails', [
            'purchase_order_id' => $purchaseOrder->id,
            'to_email' => 'supplier@example.com',
        ]);

        $this->assertDatabaseHas('purchase_order_activity_logs', [
            'purchase_order_id' => $purchaseOrder->id,
            'action' => 'emailed_to_supplier',
        ]);
    }

    public function test_marking_ordered_with_no_supplier_email_does_not_fail_or_send_mail(): void
    {
        Mail::fake();

        $admin = $this->makeUser(User::ROLE_ADMIN);
        $purchaseOrder = $this->createApprovedPurchaseOrder($admin, null);

        $result = app(PurchaseOrderService::class)->markOrdered($purchaseOrder, $admin->id);

        $this->assertSame('ordered', $result->status);

        Mail::assertNothingSent();

        $this->assertDatabaseMissing('purchase_order_emails', [
            'purchase_order_id' => $purchaseOrder->id,
        ]);
    }
}
