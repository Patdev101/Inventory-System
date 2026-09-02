<?php

namespace App\Services;

use App\Models\Inventory;
use App\Models\InventoryTransaction;
use App\Models\Product;
use App\Models\ProductUnit;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Applies stock movements against Inventory + InventoryTransaction.
 *
 * Extracted from InventoryController so the same base_quantity math is
 * used whether a movement is applied immediately (admin/manager) or
 * applied later on approval of a StockMovementRequest (staff).
 */
class InventoryMovementService
{
    /**
     * Add stock for a product at a location (creates the inventory
     * record if one does not already exist).
     */
    public function addStock(
        int $productId,
        int $locationId,
        int $productUnitId,
        float $quantity
    ): Inventory {
        $product = Product::findOrFail($productId);

        if (!$product->is_active) {
            throw ValidationException::withMessages([
                'product_id' =>
                    'This product is deactivated and cannot receive new stock.',
            ]);
        }

        $productUnit = $this->getValidProductUnit($productId, $productUnitId);

        $movementConversionFactor = (float) $productUnit->conversion_factor;
        $movementBaseQuantity = $quantity * $movementConversionFactor;

        return DB::transaction(function () use (
            $productId,
            $locationId,
            $productUnit,
            $quantity,
            $movementBaseQuantity,
            $movementConversionFactor
        ) {
            $inventory = Inventory::where('product_id', $productId)
                ->where('location_id', $locationId)
                ->lockForUpdate()
                ->first();

            if ($inventory) {
                $currentBaseQuantity = $this->resolveBaseQuantity($inventory);
                $newBaseQuantity = $currentBaseQuantity + $movementBaseQuantity;

                $displayConversionFactor = $this->getInventoryConversionFactor($inventory);
                $displayQuantity = $newBaseQuantity / $displayConversionFactor;

                $inventory->update([
                    'quantity' => $displayQuantity,
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

    /**
     * Apply a stock-in or stock-out movement against an existing
     * inventory record.
     */
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
                'product_id' =>
                    'This product is deactivated and cannot receive new stock.',
            ]);
        }

        $productUnit = $this->getValidProductUnit($productId, $productUnitId);

        $movementConversionFactor = (float) $productUnit->conversion_factor;
        $movementBaseQuantity = $quantity * $movementConversionFactor;

        return DB::transaction(function () use (
            $inventory,
            $productUnit,
            $movementType,
            $quantity,
            $movementBaseQuantity,
            $movementConversionFactor
        ) {
            $inventory = Inventory::whereKey($inventory->id)
                ->lockForUpdate()
                ->firstOrFail();

            $currentBaseQuantity = $this->resolveBaseQuantity($inventory);

            $newBaseQuantity = $movementType === 'in'
                ? $currentBaseQuantity + $movementBaseQuantity
                : $currentBaseQuantity - $movementBaseQuantity;

            if ($newBaseQuantity < -0.0000001) {
                throw ValidationException::withMessages([
                    'quantity' =>
                        'Insufficient stock. You cannot remove more stock than is currently available.',
                ]);
            }

            if ($newBaseQuantity < 0) {
                $newBaseQuantity = 0;
            }

            $displayConversionFactor = $this->getInventoryConversionFactor($inventory);
            $displayQuantity = $newBaseQuantity / $displayConversionFactor;

            $inventory->update([
                'quantity' => $displayQuantity,
                'base_quantity' => $newBaseQuantity,
            ]);

            $this->createTransaction(
                inventory: $inventory,
                type: $movementType,
                quantity: $quantity,
                baseQuantity: $movementBaseQuantity,
                productUnitId: $productUnit->id,
                conversionFactor: $movementConversionFactor,
                reference: $movementType === 'in'
                    ? 'Inventory stock added'
                    : 'Inventory stock removed'
            );

            return $inventory;
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
                'product_unit_id' =>
                    'The selected unit does not belong to this product.',
            ]);
        }

        $conversionFactor = (float) $productUnit->conversion_factor;

        if ($conversionFactor <= 0) {
            throw ValidationException::withMessages([
                'product_unit_id' =>
                    'The selected unit has an invalid conversion factor.',
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
