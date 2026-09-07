<?php

namespace App\Services;

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\PurchaseOrderReceipt;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Handles the purchasing workflow:
 *
 * draft
 *   -> pending_approval
 *   -> approved
 *   -> ordered
 *   -> partially_received
 *   -> completed
 *
 * Inventory is only affected when goods are actually received.
 *
 * All stock changes go through InventoryMovementService so the
 * application's normal base-unit conversion and inventory transaction
 * audit trail remain consistent.
 */
class PurchaseOrderService
{
    public function __construct(
        private readonly InventoryMovementService $movementService
    ) {
    }

    /**
     * Create a purchase order as a draft.
     *
     * Creating a PO does NOT affect inventory.
     */
    public function createDraft(
        int $companyId,
        int $supplierId,
        int $locationId,
        int $createdByUserId,
        array $items,
        ?string $reference = null,
        ?string $notes = null,
        ?string $expectedDeliveryDate = null
    ): PurchaseOrder {
        if (empty($items)) {
            throw ValidationException::withMessages([
                'items' => 'A purchase order must contain at least one item.',
            ]);
        }

        return DB::transaction(function () use (
            $companyId,
            $supplierId,
            $locationId,
            $createdByUserId,
            $items,
            $reference,
            $notes,
            $expectedDeliveryDate
        ) {
            $purchaseOrder = PurchaseOrder::create([
                'po_number' => $this->generatePoNumber(),
                'company_id' => $companyId,
                'supplier_id' => $supplierId,
                'location_id' => $locationId,
                'status' => PurchaseOrder::STATUS_DRAFT,
                'reference' => $reference,
                'notes' => $notes,
                'expected_delivery_date' => $expectedDeliveryDate,
                'created_by' => $createdByUserId,
            ]);

            foreach ($items as $item) {
                $quantity = (float) ($item['quantity_ordered'] ?? 0);
                $unitPrice = (float) ($item['unit_price'] ?? 0);

                if ($quantity <= 0) {
                    throw ValidationException::withMessages([
                        'items' => 'Each item quantity must be greater than zero.',
                    ]);
                }

                if ($unitPrice < 0) {
                    throw ValidationException::withMessages([
                        'items' => 'Unit price cannot be negative.',
                    ]);
                }

                PurchaseOrderItem::create([
                    'purchase_order_id' => $purchaseOrder->id,
                    'product_id' => (int) $item['product_id'],
                    'product_unit_id' => (int) $item['product_unit_id'],
                    'quantity_ordered' => $quantity,
                    'quantity_received' => 0,
                    'unit_price' => $unitPrice,
                ]);
            }

            return $purchaseOrder->fresh([
                'items',
            ]);
        });
    }

    /**
     * Submit a draft PO for approval.
     *
     * draft -> pending_approval
     */
    public function submitForApproval(
        PurchaseOrder $purchaseOrder
    ): PurchaseOrder {
        if (!$purchaseOrder->isEditable()) {
            throw ValidationException::withMessages([
                'status' =>
                    'Only draft purchase orders can be submitted for approval.',
            ]);
        }

        if ($purchaseOrder->items()->count() === 0) {
            throw ValidationException::withMessages([
                'items' =>
                    'Cannot submit a purchase order with no items.',
            ]);
        }

        $purchaseOrder->update([
            'status' => PurchaseOrder::STATUS_PENDING_APPROVAL,
        ]);

        return $purchaseOrder->fresh();
    }

    /**
     * Approve a PO.
     *
     * pending_approval -> approved
     */
    public function approve(
        PurchaseOrder $purchaseOrder,
        int $approverUserId,
        ?string $notes = null
    ): PurchaseOrder {
        if (!$purchaseOrder->isPendingApproval()) {
            throw ValidationException::withMessages([
                'status' =>
                    'Only purchase orders pending approval can be approved.',
            ]);
        }

        $purchaseOrder->update([
            'status' => PurchaseOrder::STATUS_APPROVED,
            'approved_by' => $approverUserId,
            'approved_at' => now(),
            'approval_notes' => $notes,
            'rejection_reason' => null,
        ]);

        return $purchaseOrder->fresh();
    }

    /**
     * Reject a PO.
     *
     * pending_approval -> rejected
     */
    public function reject(
        PurchaseOrder $purchaseOrder,
        int $approverUserId,
        string $reason
    ): PurchaseOrder {
        $reason = trim($reason);

        if (!$purchaseOrder->isPendingApproval()) {
            throw ValidationException::withMessages([
                'status' =>
                    'Only purchase orders pending approval can be rejected.',
            ]);
        }

        if ($reason === '') {
            throw ValidationException::withMessages([
                'rejection_reason' =>
                    'A rejection reason is required.',
            ]);
        }

        $purchaseOrder->update([
            'status' => PurchaseOrder::STATUS_REJECTED,
            'approved_by' => $approverUserId,
            'approved_at' => now(),
            'rejection_reason' => $reason,
        ]);

        return $purchaseOrder->fresh();
    }

    /**
     * Mark an approved PO as ordered.
     *
     * approved -> ordered
     *
     * This does not affect inventory.
     */
    public function markOrdered(
        PurchaseOrder $purchaseOrder
    ): PurchaseOrder {
        if (!$purchaseOrder->isApproved()) {
            throw ValidationException::withMessages([
                'status' =>
                    'Only approved purchase orders can be marked as ordered.',
            ]);
        }

        $purchaseOrder->update([
            'status' => PurchaseOrder::STATUS_ORDERED,
        ]);

        return $purchaseOrder->fresh();
    }

    /**
     * Receive goods against a purchase order.
     *
     * Allowed states:
     *
     * ordered
     * partially_received
     *
     * Receiving stock will:
     *
     * 1. Create a purchase-order receipt.
     * 2. Create receipt line items.
     * 3. Increase PurchaseOrderItem.quantity_received.
     * 4. Add the received quantity to inventory.
     * 5. Create the normal inventory transaction through
     *    InventoryMovementService.
     *
     * If every line is fully received:
     *
     * partially_received/ordered -> completed
     *
     * Otherwise:
     *
     * ordered -> partially_received
     */
    public function receiveItems(
        PurchaseOrder $purchaseOrder,
        int $receivedByUserId,
        array $lines,
        ?string $notes = null
    ): PurchaseOrder {
        if (!in_array($purchaseOrder->status, [
            PurchaseOrder::STATUS_ORDERED,
            PurchaseOrder::STATUS_PARTIALLY_RECEIVED,
        ], true)) {
            throw ValidationException::withMessages([
                'status' =>
                    'This purchase order is not open for receiving.',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Keep only lines with a positive received quantity
        |--------------------------------------------------------------------------
        */

        $nonZeroLines = array_filter(
            $lines,
            static function (array $line): bool {
                return (float) ($line['quantity_received'] ?? 0) > 0;
            }
        );

        if (empty($nonZeroLines)) {
            throw ValidationException::withMessages([
                'lines' =>
                    'Enter a received quantity for at least one item.',
            ]);
        }

        return DB::transaction(function () use (
            $purchaseOrder,
            $receivedByUserId,
            $nonZeroLines,
            $notes
        ) {
            /*
            |--------------------------------------------------------------------------
            | Lock the PO
            |--------------------------------------------------------------------------
            |
            | This prevents two receiving requests from modifying the same
            | purchase order simultaneously.
            |
            */

            $purchaseOrder = PurchaseOrder::query()
                ->whereKey($purchaseOrder->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (!in_array($purchaseOrder->status, [
                PurchaseOrder::STATUS_ORDERED,
                PurchaseOrder::STATUS_PARTIALLY_RECEIVED,
            ], true)) {
                throw ValidationException::withMessages([
                    'status' =>
                        'This purchase order is no longer open for receiving.',
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Create receipt header
            |--------------------------------------------------------------------------
            */

            $receipt = PurchaseOrderReceipt::create([
                'purchase_order_id' => $purchaseOrder->id,
                'received_by' => $receivedByUserId,
                'received_at' => now(),
                'notes' => $notes,
            ]);

            /*
            |--------------------------------------------------------------------------
            | Process each received line
            |--------------------------------------------------------------------------
            */

            foreach ($nonZeroLines as $line) {
                $purchaseOrderItemId =
                    (int) ($line['purchase_order_item_id'] ?? 0);

                $quantityReceivedNow =
                    (float) ($line['quantity_received'] ?? 0);

                if ($purchaseOrderItemId <= 0) {
                    throw ValidationException::withMessages([
                        'lines' =>
                            'A valid purchase order item is required.',
                    ]);
                }

                if ($quantityReceivedNow <= 0) {
                    continue;
                }

                /*
                |--------------------------------------------------------------------------
                | Lock the PO item
                |--------------------------------------------------------------------------
                */

                $item = PurchaseOrderItem::query()
                    ->where('purchase_order_id', $purchaseOrder->id)
                    ->whereKey($purchaseOrderItemId)
                    ->lockForUpdate()
                    ->first();

                if (!$item) {
                    throw ValidationException::withMessages([
                        'lines' =>
                            'One of the selected purchase order items does not belong to this purchase order.',
                    ]);
                }

                $quantityOrdered =
                    (float) $item->quantity_ordered;

                $quantityAlreadyReceived =
                    (float) $item->quantity_received;

                $remaining =
                    round(
                        $quantityOrdered -
                        $quantityAlreadyReceived,
                        4
                    );

                /*
                |--------------------------------------------------------------------------
                | Prevent over-receiving
                |--------------------------------------------------------------------------
                */

                if ($remaining <= 0.0000001) {
                    throw ValidationException::withMessages([
                        'lines' =>
                            "Product ID {$item->product_id} has already been fully received.",
                    ]);
                }

                if (
                    $quantityReceivedNow -
                    $remaining >
                    0.0000001
                ) {
                    throw ValidationException::withMessages([
                        'lines' =>
                            "Cannot receive more than the remaining quantity for product ID {$item->product_id}. Remaining quantity: {$remaining}.",
                    ]);
                }

                /*
                |--------------------------------------------------------------------------
                | Create receipt line
                |--------------------------------------------------------------------------
                */

                $receipt->items()->create([
                    'purchase_order_item_id' => $item->id,
                    'quantity_received' => $quantityReceivedNow,
                    'notes' => $line['notes'] ?? null,
                ]);

                /*
                |--------------------------------------------------------------------------
                | Update PO received quantity
                |--------------------------------------------------------------------------
                */

                $newReceivedQuantity =
                    round(
                        $quantityAlreadyReceived +
                        $quantityReceivedNow,
                        4
                    );

                $item->update([
                    'quantity_received' =>
                        $newReceivedQuantity,
                ]);

                /*
                |--------------------------------------------------------------------------
                | Add received stock
                |--------------------------------------------------------------------------
                |
                | InventoryMovementService is the single place responsible
                | for converting the selected product unit into base quantity
                | and recording the inventory transaction.
                |
                */

                $this->movementService->addStock(
                    productId: (int) $item->product_id,
                    locationId: (int) $purchaseOrder->location_id,
                    productUnitId: (int) $item->product_unit_id,
                    quantity: $quantityReceivedNow,
                    reference: 'PO #' . $purchaseOrder->po_number
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Recalculate PO status
            |--------------------------------------------------------------------------
            */

            $purchaseOrder->load([
                'items',
            ]);

            $allFullyReceived =
                $purchaseOrder->items->isNotEmpty()
                && $purchaseOrder->items->every(
                    static function (
                        PurchaseOrderItem $item
                    ): bool {
                        return $item->isFullyReceived();
                    }
                );

            $purchaseOrder->update([
                'status' => $allFullyReceived
                    ? PurchaseOrder::STATUS_COMPLETED
                    : PurchaseOrder::STATUS_PARTIALLY_RECEIVED,
            ]);

            return $purchaseOrder->fresh([
                'items',
                'receipts.items',
            ]);
        });
    }

    /**
     * Generate the next purchase-order number.
     *
     * Example:
     *
     * PO-000001
     * PO-000002
     * PO-000003
     *
     * The numeric suffix is based on the highest existing PO number,
     * rather than COUNT(), so deleted POs do not cause duplicates.
     */
    private function generatePoNumber(): string
    {
        $lastNumber = PurchaseOrder::query()
            ->selectRaw(
                "MAX(CAST(SUBSTRING(po_number, 4, 20) AS INT)) as max_number"
            )
            ->value('max_number');

        $nextNumber =
            ((int) $lastNumber) + 1;

        return 'PO-' .
            str_pad(
                (string) $nextNumber,
                6,
                '0',
                STR_PAD_LEFT
            );
    }
}
