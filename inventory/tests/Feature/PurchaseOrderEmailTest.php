<?php

namespace Tests\Feature;

use App\Mail\PurchaseOrderMail;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\Concerns\CreatesInventoryFixtures;
use Tests\TestCase;

class PurchaseOrderEmailTest extends TestCase
{
    use RefreshDatabase;
    use CreatesInventoryFixtures;

    private function createDraftPurchaseOrder(User $creator): PurchaseOrder
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
            'email' => 'supplier@example.com',
            'is_active' => true,
        ]);

        $this->actingAs($creator)->post(route('purchase-orders.store'), [
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

        return PurchaseOrder::query()->latest('id')->firstOrFail();
    }

    public function test_sending_a_purchase_order_email_logs_it_and_sends_mail(): void
    {
        Mail::fake();

        $admin = $this->makeUser(User::ROLE_ADMIN);
        $purchaseOrder = $this->createDraftPurchaseOrder($admin);

        $this->actingAs($admin)
            ->post(route('purchase-orders.email.send', $purchaseOrder), [
                'to_email' => 'supplier@example.com',
                'subject' => 'Purchase Order ' . $purchaseOrder->po_number,
                'body' => 'Please see attached purchase order.',
            ])
            ->assertRedirect(route('purchase-orders.show', $purchaseOrder));

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

    public function test_sending_a_purchase_order_email_requires_a_valid_recipient(): void
    {
        Mail::fake();

        $admin = $this->makeUser(User::ROLE_ADMIN);
        $purchaseOrder = $this->createDraftPurchaseOrder($admin);

        $this->actingAs($admin)
            ->post(route('purchase-orders.email.send', $purchaseOrder), [
                'to_email' => 'not-an-email',
                'subject' => 'Subject',
                'body' => 'Body',
            ])
            ->assertSessionHasErrors('to_email');

        Mail::assertNothingSent();
    }
}
