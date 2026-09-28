<?php

namespace Tests\Feature;

use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\User;
use App\Notifications\StockMovementRequestSubmitted;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\Concerns\CreatesInventoryFixtures;
use Tests\TestCase;

class PurchaseOrderApprovalTest extends TestCase
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

    public function test_manager_can_submit_approve_and_mark_a_purchase_order_ordered(): void
    {
        $manager = $this->makeUser(User::ROLE_MANAGER);
        $purchaseOrder = $this->createDraftPurchaseOrder($manager);

        $this->actingAs($manager)
            ->patch(route('purchase-orders.submit', $purchaseOrder))
            ->assertRedirect();

        $this->assertSame('pending_approval', $purchaseOrder->fresh()->status);

        $this->actingAs($manager)
            ->patch(route('purchase-orders.approve', $purchaseOrder), [
                'approval_notes' => 'Looks good.',
            ])
            ->assertRedirect();

        $this->assertSame('approved', $purchaseOrder->fresh()->status);

        $this->actingAs($manager)
            ->patch(route('purchase-orders.mark-ordered', $purchaseOrder))
            ->assertRedirect();

        $this->assertSame('ordered', $purchaseOrder->fresh()->status);
    }

    public function test_manager_can_reject_a_pending_purchase_order_with_a_reason(): void
    {
        $manager = $this->makeUser(User::ROLE_MANAGER);
        $purchaseOrder = $this->createDraftPurchaseOrder($manager);

        $this->actingAs($manager)
            ->patch(route('purchase-orders.submit', $purchaseOrder))
            ->assertRedirect();

        $this->actingAs($manager)
            ->patch(route('purchase-orders.reject', $purchaseOrder), [
                'rejection_reason' => 'Wrong pricing.',
            ])
            ->assertRedirect();

        $this->assertSame('rejected', $purchaseOrder->fresh()->status);
    }

    public function test_rejecting_a_purchase_order_without_a_reason_is_rejected(): void
    {
        $manager = $this->makeUser(User::ROLE_MANAGER);
        $purchaseOrder = $this->createDraftPurchaseOrder($manager);

        $this->actingAs($manager)
            ->patch(route('purchase-orders.submit', $purchaseOrder));

        $this->actingAs($manager)
            ->patch(route('purchase-orders.reject', $purchaseOrder), [])
            ->assertSessionHasErrors('rejection_reason');

        $this->assertSame('pending_approval', $purchaseOrder->fresh()->status);
    }

    public function test_approving_an_already_approved_purchase_order_is_rejected(): void
    {
        $manager = $this->makeUser(User::ROLE_MANAGER);
        $purchaseOrder = $this->createDraftPurchaseOrder($manager);

        $this->actingAs($manager)->patch(route('purchase-orders.submit', $purchaseOrder));
        $this->actingAs($manager)->patch(route('purchase-orders.approve', $purchaseOrder));

        // Second approval attempt on the now-approved PO must fail cleanly,
        // not silently re-apply or throw an unhandled error.
        $this->actingAs($manager)
            ->patch(route('purchase-orders.approve', $purchaseOrder), [
                'approval_notes' => 'Approving again.',
            ])
            ->assertSessionHasErrors('status');

        $this->assertSame('approved', $purchaseOrder->fresh()->status);
    }

    public function test_marking_an_already_ordered_purchase_order_ordered_again_is_rejected(): void
    {
        $manager = $this->makeUser(User::ROLE_MANAGER);
        $purchaseOrder = $this->createDraftPurchaseOrder($manager);

        $this->actingAs($manager)->patch(route('purchase-orders.submit', $purchaseOrder));
        $this->actingAs($manager)->patch(route('purchase-orders.approve', $purchaseOrder));
        $this->actingAs($manager)->patch(route('purchase-orders.mark-ordered', $purchaseOrder));

        $this->actingAs($manager)
            ->patch(route('purchase-orders.mark-ordered', $purchaseOrder))
            ->assertSessionHasErrors('status');

        $this->assertSame('ordered', $purchaseOrder->fresh()->status);
    }

    public function test_staff_cannot_approve_or_reject_a_purchase_order(): void
    {
        $manager = $this->makeUser(User::ROLE_MANAGER);
        $staff = $this->makeUser(User::ROLE_STAFF);

        $purchaseOrder = $this->createDraftPurchaseOrder($manager);

        $this->actingAs($manager)
            ->patch(route('purchase-orders.submit', $purchaseOrder));

        $this->actingAs($staff)
            ->patch(route('purchase-orders.approve', $purchaseOrder))
            ->assertForbidden();

        $this->assertSame('pending_approval', $purchaseOrder->fresh()->status);
    }

    public function test_submitting_a_staff_stock_request_notifies_admins_and_managers_only(): void
    {
        Notification::fake();

        $admin = $this->makeUser(User::ROLE_ADMIN);
        $manager = $this->makeUser(User::ROLE_MANAGER);
        $otherStaff = $this->makeUser(User::ROLE_STAFF);
        $staff = $this->makeUser(User::ROLE_STAFF);

        $company = $this->makeCompany();
        $location = $this->makeLocation($company);
        $category = $this->makeCategory();
        $unit = $this->makeUnit();
        $product = $this->makeProduct($company, $category, $unit);
        $productUnit = $this->makeProductUnit($product, $unit);

        $this->actingAs($staff)->post(route('inventories.store'), [
            'product_id' => $product->id,
            'location_id' => $location->id,
            'product_unit_id' => $productUnit->id,
            'quantity' => 5,
        ]);

        Notification::assertSentTo($admin, StockMovementRequestSubmitted::class);
        Notification::assertSentTo($manager, StockMovementRequestSubmitted::class);
        Notification::assertNotSentTo($otherStaff, StockMovementRequestSubmitted::class);
        Notification::assertNotSentTo($staff, StockMovementRequestSubmitted::class);
    }
}
