<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use App\Models\InventoryTransaction;
use App\Models\Product;
use App\Models\ProductUnit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InventoryApiController extends Controller
{
    /**
     * Remove stock from inventory.
     */
    public function remove(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => [
                'required',
                'integer',
                'exists:products,id',
            ],

            'location_id' => [
                'required',
                'integer',
                'exists:locations,id',
            ],

            'product_unit_id' => [
                'required',
                'integer',
                'exists:product_units,id',
            ],

            'quantity' => [
                'required',
                'numeric',
                'gt:0',
            ],

            'reference' => [
                'nullable',
                'string',
                'max:255',
            ],

            'notes' => [
                'nullable',
                'string',
            ],
        ]);

        $product = Product::findOrFail(
            $validated['product_id']
        );

        $productUnit = ProductUnit::whereKey(
            $validated['product_unit_id']
        )
            ->where(
                'product_id',
                $validated['product_id']
            )
            ->first();

        if (!$productUnit) {
            throw ValidationException::withMessages([
                'product_unit_id' =>
                    'The selected unit does not belong to this product.',
            ]);
        }

        $conversionFactor =
            (float) $productUnit->conversion_factor;

        if ($conversionFactor <= 0) {
            throw ValidationException::withMessages([
                'product_unit_id' =>
                    'The selected unit has an invalid conversion factor.',
            ]);
        }

        $quantity =
            (float) $validated['quantity'];

        $baseQuantity =
            $quantity * $conversionFactor;

        $result = DB::transaction(function () use (
            $validated,
            $quantity,
            $baseQuantity,
            $conversionFactor,
            $productUnit
        ) {
            $inventory = Inventory::where(
                'product_id',
                $validated['product_id']
            )
                ->where(
                    'location_id',
                    $validated['location_id']
                )
                ->lockForUpdate()
                ->first();

            if (!$inventory) {
                throw ValidationException::withMessages([
                    'product_id' =>
                        'No inventory exists for this product at the selected location.',
                ]);
            }

            $currentBaseQuantity =
                $this->resolveBaseQuantity($inventory);

            $newBaseQuantity =
                $currentBaseQuantity - $baseQuantity;

            if ($newBaseQuantity < -0.0000001) {
                throw ValidationException::withMessages([
                    'quantity' =>
                        'Insufficient stock.',
                ]);
            }

            if ($newBaseQuantity < 0) {
                $newBaseQuantity = 0;
            }

            $displayConversionFactor =
                $this->getInventoryConversionFactor(
                    $inventory
                );

            $displayQuantity =
                $newBaseQuantity /
                $displayConversionFactor;

            $inventory->update([
                'quantity' => $displayQuantity,
                'base_quantity' => $newBaseQuantity,
            ]);

            $transaction =
                InventoryTransaction::create([
                    'inventory_id' => $inventory->id,
                    'product_id' => $inventory->product_id,
                    'location_id' => $inventory->location_id,
                    'type' => 'out',
                    'quantity' => $quantity,
                    'base_quantity' => $baseQuantity,
                    'product_unit_id' => $productUnit->id,
                    'conversion_factor' => $conversionFactor,
                    'reference' =>
                        $validated['reference']
                        ?? 'Inventory stock removed',
                    'notes' =>
                        $validated['notes'] ?? null,
                ]);

            return [
                'inventory' => $inventory->fresh(),
                'transaction' => $transaction,
            ];
        }, 3);

        return response()->json([
            'message' => 'Stock removed successfully.',
            'data' => $result,
        ]);
    }

    /**
     * Add stock to inventory.
     */
    public function add(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => [
                'required',
                'integer',
                'exists:products,id',
            ],

            'location_id' => [
                'required',
                'integer',
                'exists:locations,id',
            ],

            'product_unit_id' => [
                'required',
                'integer',
                'exists:product_units,id',
            ],

            'quantity' => [
                'required',
                'numeric',
                'gt:0',
            ],

            'reference' => [
                'nullable',
                'string',
                'max:255',
            ],

            'notes' => [
                'nullable',
                'string',
            ],
        ]);

        $product = Product::findOrFail(
            $validated['product_id']
        );

        if (!$product->is_active) {
            throw ValidationException::withMessages([
                'product_id' =>
                    'This product is deactivated and cannot receive new stock.',
            ]);
        }

        $productUnit = ProductUnit::whereKey(
            $validated['product_unit_id']
        )
            ->where(
                'product_id',
                $validated['product_id']
            )
            ->first();

        if (!$productUnit) {
            throw ValidationException::withMessages([
                'product_unit_id' =>
                    'The selected unit does not belong to this product.',
            ]);
        }

        $conversionFactor =
            (float) $productUnit->conversion_factor;

        if ($conversionFactor <= 0) {
            throw ValidationException::withMessages([
                'product_unit_id' =>
                    'The selected unit has an invalid conversion factor.',
            ]);
        }

        $quantity =
            (float) $validated['quantity'];

        $baseQuantity =
            $quantity * $conversionFactor;

        $result = DB::transaction(function () use (
            $validated,
            $quantity,
            $baseQuantity,
            $conversionFactor,
            $productUnit
        ) {
            $inventory = Inventory::where(
                'product_id',
                $validated['product_id']
            )
                ->where(
                    'location_id',
                    $validated['location_id']
                )
                ->lockForUpdate()
                ->first();

            if ($inventory) {
                $currentBaseQuantity =
                    $this->resolveBaseQuantity($inventory);

                $newBaseQuantity =
                    $currentBaseQuantity + $baseQuantity;

                $displayConversionFactor =
                    $this->getInventoryConversionFactor(
                        $inventory
                    );

                $displayQuantity =
                    $newBaseQuantity /
                    $displayConversionFactor;

                $inventory->update([
                    'quantity' => $displayQuantity,
                    'base_quantity' => $newBaseQuantity,
                ]);
            } else {
                $inventory = Inventory::create([
                    'product_id' => $validated['product_id'],
                    'location_id' => $validated['location_id'],
                    'product_unit_id' => $productUnit->id,
                    'conversion_factor' => $conversionFactor,
                    'quantity' => $quantity,
                    'base_quantity' => $baseQuantity,
                ]);
            }

            $transaction =
                InventoryTransaction::create([
                    'inventory_id' => $inventory->id,
                    'product_id' => $inventory->product_id,
                    'location_id' => $inventory->location_id,
                    'type' => 'in',
                    'quantity' => $quantity,
                    'base_quantity' => $baseQuantity,
                    'product_unit_id' => $productUnit->id,
                    'conversion_factor' => $conversionFactor,
                    'reference' =>
                        $validated['reference']
                        ?? 'Inventory stock added',
                    'notes' =>
                        $validated['notes'] ?? null,
                ]);

            return [
                'inventory' => $inventory->fresh(),
                'transaction' => $transaction,
            ];
        }, 3);

        return response()->json([
            'message' => 'Stock added successfully.',
            'data' => $result,
        ]);
    }

    private function resolveBaseQuantity(
        Inventory $inventory
    ): float {
        $baseQuantity =
            (float) $inventory->base_quantity;

        if ($baseQuantity > 0) {
            return $baseQuantity;
        }

        $quantity =
            (float) $inventory->quantity;

        if ($quantity <= 0) {
            return 0;
        }

        return $quantity *
            $this->getInventoryConversionFactor(
                $inventory
            );
    }

    private function getInventoryConversionFactor(
        Inventory $inventory
    ): float {
        $conversionFactor =
            (float) $inventory->conversion_factor;

        return $conversionFactor > 0
            ? $conversionFactor
            : 1;
    }
}
