<?php

namespace App\Services;

use App\Mail\PurchaseOrderMail;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderEmail;
use App\Models\PurchaseOrderItem;
use App\Models\PurchaseOrderReceipt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
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
 * All stock changes go through InventoryMovementService.
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

            $this->logActivity(
                purchaseOrder: $purchaseOrder,
                userId: $createdByUserId,
                action: 'created',
                description: 'Purchase order created as a draft.',
                metadata: [
                    'status' => PurchaseOrder::STATUS_DRAFT,
                    'item_count' => count($items),
                ]
            );

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
        PurchaseOrder $purchaseOrder,
        int $userId
    ): PurchaseOrder {
        DB::transaction(function () use (
            $purchaseOrder,
            $userId
        ) {
            $locked = PurchaseOrder::query()
                ->whereKey($purchaseOrder->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (!$locked->isEditable()) {
                throw ValidationException::withMessages([
                    'status' =>
                        'Only draft purchase orders can be submitted for approval.',
                ]);
            }

            if ($locked->items()->count() === 0) {
                throw ValidationException::withMessages([
                    'items' =>
                        'Cannot submit a purchase order with no items.',
                ]);
            }

            $oldStatus = $locked->status;

            $locked->update([
                'status' => PurchaseOrder::STATUS_PENDING_APPROVAL,
            ]);

            $this->logActivity(
                purchaseOrder: $locked,
                userId: $userId,
                action: 'submitted_for_approval',
                description: 'Purchase order submitted for approval.',
                metadata: [
                    'from_status' => $oldStatus,
                    'to_status' => PurchaseOrder::STATUS_PENDING_APPROVAL,
                ]
            );
        });

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
        DB::transaction(function () use (
            $purchaseOrder,
            $approverUserId,
            $notes
        ) {
            $locked = PurchaseOrder::query()
                ->whereKey($purchaseOrder->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (!$locked->isPendingApproval()) {
                throw ValidationException::withMessages([
                    'status' =>
                        'Only purchase orders pending approval can be approved.',
                ]);
            }

            $locked->update([
                'status' => PurchaseOrder::STATUS_APPROVED,
                'approved_by' => $approverUserId,
                'approved_at' => now(),
                'approval_notes' => $notes,
                'rejection_reason' => null,
            ]);

            $this->logActivity(
                purchaseOrder: $locked,
                userId: $approverUserId,
                action: 'approved',
                description: 'Purchase order approved.',
                metadata: [
                    'from_status' => PurchaseOrder::STATUS_PENDING_APPROVAL,
                    'to_status' => PurchaseOrder::STATUS_APPROVED,
                    'approval_notes' => $notes,
                ]
            );
        });

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

        if ($reason === '') {
            throw ValidationException::withMessages([
                'rejection_reason' =>
                    'A rejection reason is required.',
            ]);
        }

        DB::transaction(function () use (
            $purchaseOrder,
            $approverUserId,
            $reason
        ) {
            $locked = PurchaseOrder::query()
                ->whereKey($purchaseOrder->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (!$locked->isPendingApproval()) {
                throw ValidationException::withMessages([
                    'status' =>
                        'Only purchase orders pending approval can be rejected.',
                ]);
            }

            $locked->update([
                'status' => PurchaseOrder::STATUS_REJECTED,
                'approved_by' => $approverUserId,
                'approved_at' => now(),
                'rejection_reason' => $reason,
            ]);

            $this->logActivity(
                purchaseOrder: $locked,
                userId: $approverUserId,
                action: 'rejected',
                description: 'Purchase order rejected.',
                metadata: [
                    'from_status' => PurchaseOrder::STATUS_PENDING_APPROVAL,
                    'to_status' => PurchaseOrder::STATUS_REJECTED,
                    'rejection_reason' => $reason,
                ]
            );
        });

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
        PurchaseOrder $purchaseOrder,
        int $userId
    ): PurchaseOrder {
        DB::transaction(function () use (
            $purchaseOrder,
            $userId
        ) {
            $locked = PurchaseOrder::query()
                ->whereKey($purchaseOrder->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (!$locked->isApproved()) {
                throw ValidationException::withMessages([
                    'status' =>
                        'Only approved purchase orders can be marked as ordered.',
                ]);
            }

            $locked->update([
                'status' => PurchaseOrder::STATUS_ORDERED,
            ]);

            $this->logActivity(
                purchaseOrder: $locked,
                userId: $userId,
                action: 'ordered',
                description: 'Purchase order marked as ordered.',
                metadata: [
                    'from_status' => PurchaseOrder::STATUS_APPROVED,
                    'to_status' => PurchaseOrder::STATUS_ORDERED,
                ]
            );
        });

        // Sent after the transaction commits — a mail failure should
        // never roll back the status change, and the sync mailer
        // shouldn't run inside an open DB transaction.
        $this->sendOrderRequestEmailToSupplier($purchaseOrder, $userId);

        return $purchaseOrder->fresh();
    }

    /**
     * Automatically email the supplier a purchase order request the
     * moment it's marked as ordered — the same PDF/log/activity trail as
     * the manual "Send Email" action, just fired without a user having
     * to click it. If the supplier has no email on file, this is skipped
     * (logged, not thrown) — marking a PO as ordered must never fail
     * just because a supplier's email is missing.
     */
    private function sendOrderRequestEmailToSupplier(
        PurchaseOrder $purchaseOrder,
        int $userId
    ): void {
        $purchaseOrder->load([
            'supplier',
            'location.company',
            'createdBy',
            'approvedBy',
            'items.product',
            'items.productUnit.unitOfMeasure',
        ]);

        $toEmail = $purchaseOrder->supplier?->email;

        if (empty($toEmail)) {
            Log::info(
                'Purchase order ' . $purchaseOrder->po_number
                . ' marked as ordered but its supplier has no email on file — '
                . 'automatic order-request email skipped.'
            );

            return;
        }

        $subject = 'Purchase Order from '
            . ($purchaseOrder->location?->company?->name ?? config('app.name'))
            . ' (' . $purchaseOrder->po_number . ')';

        $body = "Dear " . ($purchaseOrder->supplier?->name ?? 'Supplier') . ",\n\n"
            . "Please find attached purchase order {$purchaseOrder->po_number}. "
            . "Kindly go through it and confirm the order.\n\n"
            . "We look forward to working with you.\n\nRegards,";

        try {
            Mail::to($toEmail)->send(new PurchaseOrderMail(
                purchaseOrder: $purchaseOrder,
                emailSubject: $subject,
                messageBody: $body
            ));
        } catch (\Throwable $e) {
            Log::warning(
                'Failed to auto-send order-request email for purchase order '
                . $purchaseOrder->po_number . ': ' . $e->getMessage()
            );

            return;
        }

        PurchaseOrderEmail::create([
            'purchase_order_id' => $purchaseOrder->id,
            'sent_by' => $userId,
            'to_email' => $toEmail,
            'subject' => $subject,
            'body' => $body,
            'sent_at' => now(),
        ]);

        $this->logActivity(
            purchaseOrder: $purchaseOrder,
            userId: $userId,
            action: 'emailed_to_supplier',
            description: 'Order request automatically emailed to ' . $toEmail . ' when marked as ordered.',
            metadata: [
                'to_email' => $toEmail,
                'subject' => $subject,
                'automatic' => true,
            ]
        );
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
     * 5. Create the normal inventory transaction.
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

            $oldStatus = $purchaseOrder->status;

            $receipt = PurchaseOrderReceipt::create([
                'purchase_order_id' => $purchaseOrder->id,
                'received_by' => $receivedByUserId,
                'received_at' => now(),
                'notes' => $notes,
            ]);

            $receivedLines = [];

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

                $remaining = round(
                    $quantityOrdered - $quantityAlreadyReceived,
                    4
                );

                if ($remaining <= 0.0000001) {
                    throw ValidationException::withMessages([
                        'lines' =>
                            "Product ID {$item->product_id} has already been fully received.",
                    ]);
                }

                if (
                    $quantityReceivedNow - $remaining >
                    0.0000001
                ) {
                    throw ValidationException::withMessages([
                        'lines' =>
                            "Cannot receive more than the remaining quantity for product ID {$item->product_id}. Remaining quantity: {$remaining}.",
                    ]);
                }

                $receipt->items()->create([
                    'purchase_order_item_id' => $item->id,
                    'quantity_received' => $quantityReceivedNow,
                    'notes' => $line['notes'] ?? null,
                ]);

                $newReceivedQuantity = round(
                    $quantityAlreadyReceived +
                    $quantityReceivedNow,
                    4
                );

                $item->update([
                    'quantity_received' => $newReceivedQuantity,
                ]);

                $this->movementService->addStock(
                    productId: (int) $item->product_id,
                    locationId: (int) $purchaseOrder->location_id,
                    productUnitId: (int) $item->product_unit_id,
                    quantity: $quantityReceivedNow,
                    reference: 'PO #' . $purchaseOrder->po_number
                );

                $receivedLines[] = [
                    'purchase_order_item_id' => $item->id,
                    'product_id' => (int) $item->product_id,
                    'quantity_received' => $quantityReceivedNow,
                ];
            }

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

            $newStatus = $allFullyReceived
                ? PurchaseOrder::STATUS_COMPLETED
                : PurchaseOrder::STATUS_PARTIALLY_RECEIVED;

            $purchaseOrder->update([
                'status' => $newStatus,
            ]);

            $this->logActivity(
                purchaseOrder: $purchaseOrder,
                userId: $receivedByUserId,
                action: 'received',
                description: $allFullyReceived
                    ? 'Purchase order fully received and completed.'
                    : 'Purchase order receiving recorded.',
                metadata: [
                    'receipt_id' => $receipt->id,
                    'from_status' => $oldStatus,
                    'to_status' => $newStatus,
                    'lines' => $receivedLines,
                    'notes' => $notes,
                ]
            );

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
     */
    private function generatePoNumber(): string
    {
        $lastNumber = PurchaseOrder::query()
            ->selectRaw(
                "MAX(CAST(SUBSTRING(po_number, 4, 20) AS INT)) as max_number"
            )
            ->value('max_number');

        $nextNumber = ((int) $lastNumber) + 1;

        return 'PO-' .
            str_pad(
                (string) $nextNumber,
                6,
                '0',
                STR_PAD_LEFT
            );
    }

    /**
     * Record an activity/audit log for a purchase order.
     */
    private function logActivity(
        PurchaseOrder $purchaseOrder,
        int $userId,
        string $action,
        string $description,
        array $metadata = []
    ): void {
        $purchaseOrder->activityLogs()->create([
            'user_id' => $userId,
            'action' => $action,
            'description' => $description,
            'metadata' => $metadata ?: null,
        ]);
    }
}
