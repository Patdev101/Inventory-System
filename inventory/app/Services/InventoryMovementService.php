<?php

namespace App\Services;

use App\Models\Inventory;
use App\Models\InventoryTransaction;
use App\Models\InventoryTransfer;
use App\Models\Product;
use App\Models\ProductUnit;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InventoryMovementService
{
    public function addStock(
        int $productId,
        int $locationId,
        int $productUnitId,
        float $quantity
    ): Inventory {
        $product = Product::findOrFail($productId);

        if (!$product->is_active) {
            throw ValidationException::withMessages([
                'product_id' => 'This product is deactivated and cannot receive new stock.',
            ]);
        }

        $productUnit = $this->getValidProductUnit($productId, $productUnitId);
        $movementConversionFactor = (float) $productUnit->conversion_factor;
        $movementBaseQuantity = $quantity * $movementConversionFactor;

        return DB::transaction(function () use (
            $productId, $locationId, $productUnit, $quantity, $movementBaseQuantity, $movementConversionFactor
        ) {
            $inventory = Inventory::where('product_id', $productId)
                ->where('location_id', $locationId)
                ->lockForUpdate()
                ->first();

            if ($inventory) {
                $currentBaseQuantity = $this->resolveBaseQuantity($inventory);
                $newBaseQuantity = $currentBaseQuantity + $movementBaseQuantity;
                $displayConversionFactor = $this->getInventoryConversionFactor($inventory);

                $inventory->update([
                    'quantity' => $newBaseQuantity / $displayConversionFactor,
                    'base_quantity' => $newBaseQuantity,
                ]);
            } else {
                $inventory = Inventory::create([
                    'product_id' => $productId,
                    'location_id' => $locationId,
                    'product_unit_id' => $productUnit->id,
                    'conversion_factor' => $movementConversionFactor,
                    'quantity' => $quantity,
                    'base_quantity' => $movementBaseQuantity,
                ]);
            }

            $this->createTransaction(
                inventory: $inventory,
                type: 'in',
                quantity: $quantity,
                baseQuantity: $movementBaseQuantity,
                productUnitId: $productUnit->id,
                conversionFactor: $movementConversionFactor,
                reference: 'Inventory addition'
            );

            return $inventory;
        });
    }

    public function moveStock(
        Inventory $inventory,
        int $productId,
        int $productUnitId,
        string $movementType,
        float $quantity
    ): Inventory {
        if (!in_array($movementType, ['in', 'out'], true)) {
            throw new \InvalidArgumentException('Invalid movement type.');
        }

        $product = Product::findOrFail($productId);

        if (!$product->is_active && $movementType === 'in') {
            throw ValidationException::withMessages([
                'product_id' => 'This product is deactivated and cannot receive new stock.',
            ]);
        }

        $productUnit = $this->getValidProductUnit($productId, $productUnitId);
        $movementConversionFactor = (float) $productUnit->conversion_factor;
        $movementBaseQuantity = $quantity * $movementConversionFactor;

        return DB::transaction(function () use (
            $inventory, $productUnit, $movementType, $quantity, $movementBaseQuantity, $movementConversionFactor
        ) {
            $inventory = Inventory::whereKey($inventory->id)->lockForUpdate()->firstOrFail();
            $currentBaseQuantity = $this->resolveBaseQuantity($inventory);

            $newBaseQuantity = $movementType === 'in'
                ? $currentBaseQuantity + $movementBaseQuantity
                : $currentBaseQuantity - $movementBaseQuantity;

            if ($newBaseQuantity < -0.0000001) {
                throw ValidationException::withMessages([
                    'quantity' => 'Insufficient stock. You cannot remove more stock than is currently available.',
                ]);
            }

            if ($newBaseQuantity < 0) {
                $newBaseQuantity = 0;
            }

            $displayConversionFactor = $this->getInventoryConversionFactor($inventory);

            $inventory->update([
                'quantity' => $newBaseQuantity / $displayConversionFactor,
                'base_quantity' => $newBaseQuantity,
            ]);

            $this->createTransaction(
                inventory: $inventory,
                type: $movementType,
                quantity: $quantity,
                baseQuantity: $movementBaseQuantity,
                productUnitId: $productUnit->id,
                conversionFactor: $movementConversionFactor,
                reference: $movementType === 'in' ? 'Inventory stock added' : 'Inventory stock removed'
            );

            return $inventory;
        });
    }

    public function transferStock(
        int $sourceInventoryId,
        int $destinationInventoryId,
        int $productUnitId,
        float $quantity,
        ?string $reference = null,
        ?string $notes = null
    ): InventoryTransfer {
        return DB::transaction(function () use (
            $sourceInventoryId, $destinationInventoryId, $productUnitId, $quantity, $reference, $notes
        ) {
            $inventoryIds = [$sourceInventoryId, $destinationInventoryId];
            sort($inventoryIds);

            $lockedInventories = Inventory::with(['product', 'location', 'productUnit.unitOfMeasure'])
                ->whereIn('id', $inventoryIds)
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $sourceInventory = $lockedInventories->get($sourceInventoryId);
            $destinationInventory = $lockedInventories->get($destinationInventoryId);

            if (!$sourceInventory || !$destinationInventory) {
                throw ValidationException::withMessages([
                    'source_inventory_id' => 'The selected inventory records could not be found.',
                ]);
            }

            if ((int) $sourceInventory->product_id !== (int) $destinationInventory->product_id) {
                throw ValidationException::withMessages([
                    'destination_inventory_id' => 'The source and destination must contain the same product.',
                ]);
            }

            $productUnit = $this->getValidProductUnit($sourceInventory->product_id, $productUnitId);
            $conversionFactor = (float) $productUnit->conversion_factor;
            $baseQuantity = $quantity * $conversionFactor;

            $sourceBaseQuantity = $this->resolveBaseQuantity($sourceInventory);

            if ($sourceBaseQuantity - $baseQuantity < -0.0000001) {
                throw ValidationException::withMessages([
                    'quantity' => 'Insufficient stock at the source location.',
                ]);
            }

            $newSourceBaseQuantity = max(0, $sourceBaseQuantity - $baseQuantity);
            $destinationBaseQuantity = $this->resolveBaseQuantity($destinationInventory);
            $newDestinationBaseQuantity = $destinationBaseQuantity + $baseQuantity;
            $sourceConversionFactor = $this->getInventoryConversionFactor($sourceInventory);

            $sourceInventory->update([
                'quantity' => $newSourceBaseQuantity / $sourceConversionFactor,
                'base_quantity' => $newSourceBaseQuantity,
            ]);

            $destinationInventory->update([
                'product_unit_id' => $productUnit->id,
                'conversion_factor' => $conversionFactor,
                'quantity' => $newDestinationBaseQuantity / $conversionFactor,
                'base_quantity' => $newDestinationBaseQuantity,
            ]);

           $transfer = InventoryTransfer::create([
    'source_inventory_id' => $sourceInventory->id,
    'destination_inventory_id' => $destinationInventory->id,
    'product_id' => $sourceInventory->product_id,
    'product_unit_id' => $productUnit->id,
    'conversion_factor' => $conversionFactor,
    'quantity' => $quantity,
    'base_quantity' => $baseQuantity,
    'reference' => $reference ?? 'Inventory transfer',
    'notes' => $notes,
    'status' => 'completed',
]);

            $this->createTransaction(
                inventory: $sourceInventory,
                type: 'out',
                quantity: $quantity,
                baseQuantity: $baseQuantity,
                productUnitId: $productUnit->id,
                conversionFactor: $conversionFactor,
                reference: 'Transfer #' . $transfer->id . ' to ' . $destinationInventory->location->name,
                notes: $notes
            );

            $this->createTransaction(
                inventory: $destinationInventory,
                type: 'in',
                quantity: $quantity,
                baseQuantity: $baseQuantity,
                productUnitId: $productUnit->id,
                conversionFactor: $conversionFactor,
                reference: 'Transfer #' . $transfer->id . ' from ' . $sourceInventory->location->name,
                notes: $notes
            );

            return $transfer;
        });
    }

    public function initiateTransfer(
        int $sourceInventoryId,
        int $destinationLocationId,
        int $productUnitId,
        float $quantity,
        int $receiverId,
        string $receiverRole,
        ?string $reference = null,
        ?string $notes = null
    ): InventoryTransfer {
        return DB::transaction(function () use (
            $sourceInventoryId, $destinationLocationId, $productUnitId, $quantity, $receiverId, $receiverRole, $reference, $notes
        ) {
            $sourceInventory = Inventory::with(['product', 'location'])
                ->whereKey($sourceInventoryId)
                ->lockForUpdate()
                ->firstOrFail();

            if ((int) $sourceInventory->location_id === (int) $destinationLocationId) {
                throw ValidationException::withMessages([
                    'destination_location_id' => 'The destination must be a different location from the source.',
                ]);
            }

            $productUnit = $this->getValidProductUnit($sourceInventory->product_id, $productUnitId);
            $conversionFactor = (float) $productUnit->conversion_factor;
            $baseQuantity = $quantity * $conversionFactor;

            $sourceBaseQuantity = $this->resolveBaseQuantity($sourceInventory);

            if ($sourceBaseQuantity - $baseQuantity < -0.0000001) {
                throw ValidationException::withMessages([
                    'quantity' => 'Insufficient stock at the source location.',
                ]);
            }

            $newSourceBaseQuantity = max(0, $sourceBaseQuantity - $baseQuantity);
            $sourceConversionFactor = $this->getInventoryConversionFactor($sourceInventory);

            $sourceInventory->update([
                'quantity' => $newSourceBaseQuantity / $sourceConversionFactor,
                'base_quantity' => $newSourceBaseQuantity,
            ]);

            // NOTE: firstOrCreate is not fully race-safe against two
            // concurrent transfers creating a brand-new destination at the
            // same instant; add a unique index on (product_id, location_id)
            // if you want the DB itself to guarantee this.
            $destinationInventory = Inventory::firstOrCreate(
                [
                    'product_id' => $sourceInventory->product_id,
                    'location_id' => $destinationLocationId,
                ],
                [
                    'product_unit_id' => $productUnit->id,
                    'conversion_factor' => $conversionFactor,
                    'quantity' => 0,
                    'base_quantity' => 0,
                ]
            );

            $transfer = InventoryTransfer::create([
                'source_inventory_id' => $sourceInventory->id,
                'destination_inventory_id' => $destinationInventory->id,
                'product_id' => $sourceInventory->product_id,
                'product_unit_id' => $productUnit->id,
                'conversion_factor' => $conversionFactor,
                'quantity' => $quantity,
                'base_quantity' => $baseQuantity,
                'reference' => $reference ?? 'Inventory transfer',
                'notes' => $notes,
                'status' => 'pending',
                'receiver_id' => $receiverId,
                'receiver_role' => $receiverRole,
                'audit_status' => 'pending',
            ]);

            $this->createTransaction(
                inventory: $sourceInventory,
                type: 'out',
                quantity: $quantity,
                baseQuantity: $baseQuantity,
                productUnitId: $productUnit->id,
                conversionFactor: $conversionFactor,
                reference: 'Transfer #' . $transfer->id . ' (pending receipt) to ' . $destinationInventory->location->name,
                notes: $notes
            );

            return $transfer;
        });
    }

    public function completeTransferReceipt(
        InventoryTransfer $transfer,
        int $receivedByUserId
    ): InventoryTransfer {
        if ($transfer->status === 'completed') {
            throw ValidationException::withMessages([
                'status' => 'This transfer has already been received.',
            ]);
        }

        if ($transfer->audit_status !== 'passed') {
            throw ValidationException::withMessages([
                'audit_status' => 'This transfer must pass audit before it can be received.',
            ]);
        }

        return DB::transaction(function () use ($transfer, $receivedByUserId) {
            // Lock the transfer row itself (not just the destination
            // inventory) so two concurrent "Mark Received" clicks can't
            // both pass the status checks above and both credit stock.
            $lockedTransfer = InventoryTransfer::whereKey($transfer->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedTransfer->status === 'completed') {
                throw ValidationException::withMessages([
                    'status' => 'This transfer has already been received.',
                ]);
            }

            $destinationInventory = Inventory::with('location')
                ->whereKey($lockedTransfer->destination_inventory_id)
                ->lockForUpdate()
                ->firstOrFail();

            $destinationBaseQuantity = $this->resolveBaseQuantity($destinationInventory);
            $newDestinationBaseQuantity = $destinationBaseQuantity + (float) $lockedTransfer->base_quantity;

            // Use the transfer's own unit/conversion factor (the unit this
            // shipment was actually sent and received in), not whatever
            // unit the destination happened to be displaying before —
            // otherwise a destination that previously stocked this product
            // in a different unit would show the wrong quantity/unit label
            // even though base_quantity itself stays correct.
            $conversionFactor = (float) $lockedTransfer->conversion_factor;

            $destinationInventory->update([
                'product_unit_id' => $lockedTransfer->product_unit_id,
                'conversion_factor' => $conversionFactor,
                'quantity' => $newDestinationBaseQuantity / $conversionFactor,
                'base_quantity' => $newDestinationBaseQuantity,
            ]);

            $this->createTransaction(
                inventory: $destinationInventory,
                type: 'in',
                quantity: (float) $lockedTransfer->quantity,
                baseQuantity: (float) $lockedTransfer->base_quantity,
                productUnitId: $lockedTransfer->product_unit_id,
                conversionFactor: (float) $lockedTransfer->conversion_factor,
                reference: 'Transfer #' . $lockedTransfer->id . ' received into ' . $destinationInventory->location->name,
                notes: $lockedTransfer->notes
            );

            $lockedTransfer->update([
                'status' => 'completed',
                'received_by' => $receivedByUserId,
                'received_at' => now(),
            ]);

            return $lockedTransfer->fresh();
        });
    }

    public function reverseTransfer(
        InventoryTransfer $transfer,
        int $auditedByUserId,
        ?string $auditNotes = null
    ): InventoryTransfer {
        if ($transfer->status === 'completed') {
            throw ValidationException::withMessages([
                'status' => 'This transfer has already been received and cannot be rejected.',
            ]);
        }

        if ($transfer->status === 'rejected') {
            throw ValidationException::withMessages([
                'status' => 'This transfer has already been rejected.',
            ]);
        }

        return DB::transaction(function () use ($transfer, $auditedByUserId, $auditNotes) {
            // Same reasoning as completeTransferReceipt(): lock the
            // transfer row itself before re-checking status, so two
            // concurrent audit submissions can't both reverse the stock.
            $lockedTransfer = InventoryTransfer::whereKey($transfer->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (in_array($lockedTransfer->status, ['completed', 'rejected'], true)) {
                throw ValidationException::withMessages([
                    'status' => 'This transfer has already been finalized.',
                ]);
            }

            $sourceInventory = Inventory::with('location')
                ->whereKey($lockedTransfer->source_inventory_id)
                ->lockForUpdate()
                ->firstOrFail();

            $sourceBaseQuantity = $this->resolveBaseQuantity($sourceInventory);
            $newSourceBaseQuantity = $sourceBaseQuantity + (float) $lockedTransfer->base_quantity;
            $conversionFactor = $this->getInventoryConversionFactor($sourceInventory);

            $sourceInventory->update([
                'quantity' => $newSourceBaseQuantity / $conversionFactor,
                'base_quantity' => $newSourceBaseQuantity,
            ]);

            $this->createTransaction(
                inventory: $sourceInventory,
                type: 'in',
                quantity: (float) $lockedTransfer->quantity,
                baseQuantity: (float) $lockedTransfer->base_quantity,
                productUnitId: $lockedTransfer->product_unit_id,
                conversionFactor: (float) $lockedTransfer->conversion_factor,
                reference: 'Transfer #' . $lockedTransfer->id . ' failed audit — stock returned to ' . $sourceInventory->location->name,
                notes: $auditNotes
            );

            $lockedTransfer->update([
                'status' => 'rejected',
                'audit_status' => 'failed',
                'audited_by' => $auditedByUserId,
                'audited_at' => now(),
                'audit_notes' => $auditNotes,
            ]);

            return $lockedTransfer->fresh();
        });
    }

    public function getValidProductUnit(int $productId, int $productUnitId): ProductUnit
    {
        $productUnit = ProductUnit::with('unitOfMeasure')
            ->whereKey($productUnitId)
            ->where('product_id', $productId)
            ->first();

        if (!$productUnit) {
            throw ValidationException::withMessages([
                'product_unit_id' => 'The selected unit does not belong to this product.',
            ]);
        }

        $conversionFactor = (float) $productUnit->conversion_factor;

        if ($conversionFactor <= 0) {
            throw ValidationException::withMessages([
                'product_unit_id' => 'The selected unit has an invalid conversion factor.',
            ]);
        }

        return $productUnit;
    }

    public function resolveBaseQuantity(Inventory $inventory): float
    {
        $baseQuantity = (float) $inventory->base_quantity;

        if ($baseQuantity > 0) {
            return $baseQuantity;
        }

        $quantity = (float) $inventory->quantity;

        if ($quantity <= 0) {
            return 0;
        }

        return $quantity * $this->getInventoryConversionFactor($inventory);
    }

    public function getInventoryConversionFactor(Inventory $inventory): float
    {
        $conversionFactor = (float) $inventory->conversion_factor;

        return $conversionFactor > 0 ? $conversionFactor : 1;
    }

    private function createTransaction(
        Inventory $inventory,
        string $type,
        float $quantity,
        float $baseQuantity,
        ?int $productUnitId = null,
        ?float $conversionFactor = null,
        ?string $reference = null,
        ?string $notes = null
    ): InventoryTransaction {
        if (!in_array($type, ['in', 'out'], true)) {
            throw new \InvalidArgumentException('Invalid inventory transaction type.');
        }

        if ($quantity <= 0) {
            throw new \InvalidArgumentException('Transaction quantity must be greater than zero.');
        }

        if ($baseQuantity <= 0) {
            throw new \InvalidArgumentException('Transaction base quantity must be greater than zero.');
        }

        if ($conversionFactor === null || $conversionFactor <= 0) {
            throw new \InvalidArgumentException('Transaction conversion factor must be greater than zero.');
        }

        return InventoryTransaction::create([
            'inventory_id' => $inventory->id,
            'product_id' => $inventory->product_id,
            'location_id' => $inventory->location_id,
            'type' => $type,
            'quantity' => $quantity,
            'base_quantity' => $baseQuantity,
            'product_unit_id' => $productUnitId,
            'conversion_factor' => $conversionFactor,
            'reference' => $reference,
            'notes' => $notes,
        ]);
    }
}