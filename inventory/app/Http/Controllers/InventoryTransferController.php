<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\InventoryTransaction;
use App\Models\InventoryTransfer;
use App\Models\ProductUnit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InventoryTransferController extends Controller
{
    /**
     * Show transfer history.
     */
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search'));

        $transfers = InventoryTransfer::with([
            'product',
            'sourceInventory.location',
            'destinationInventory.location',
            'productUnit.unitOfMeasure',
        ])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {

                    $query->where(
                        'reference',
                        'like',
                        '%' . $search . '%'
                    )

                    ->orWhere(
                        'notes',
                        'like',
                        '%' . $search . '%'
                    )

                    ->orWhereHas(
                        'product',
                        function ($query) use ($search) {
                            $query->where(
                                'name',
                                'like',
                                '%' . $search . '%'
                            );
                        }
                    )

                    ->orWhereHas(
                        'sourceInventory.location',
                        function ($query) use ($search) {
                            $query->where(
                                'name',
                                'like',
                                '%' . $search . '%'
                            )
                            ->orWhere(
                                'code',
                                'like',
                                '%' . $search . '%'
                            );
                        }
                    )

                    ->orWhereHas(
                        'destinationInventory.location',
                        function ($query) use ($search) {
                            $query->where(
                                'name',
                                'like',
                                '%' . $search . '%'
                            )
                            ->orWhere(
                                'code',
                                'like',
                                '%' . $search . '%'
                            );
                        }
                    );
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view(
            'inventory-transfers.index',
            compact(
                'transfers',
                'search'
            )
        );
    }

    /**
     * Show the transfer form.
     */
    public function create()
    {
        $inventories = Inventory::with([
            'product',
            'product.productUnits.unitOfMeasure',
            'location',
            'productUnit.unitOfMeasure',
        ])
            ->where('base_quantity', '>', 0)
            ->orderBy('id')
            ->get();

        return view(
            'inventory-transfers.create',
            compact('inventories')
        );
    }

    /**
     * Show a single inventory transfer.
     */
    public function show(InventoryTransfer $transfer)
    {
        $transfer->load([
            'product',
            'productUnit.unitOfMeasure',
            'sourceInventory.product',
            'sourceInventory.location',
            'sourceInventory.productUnit.unitOfMeasure',
            'destinationInventory.product',
            'destinationInventory.location',
            'destinationInventory.productUnit.unitOfMeasure',
        ]);

        return view(
            'inventory-transfers.show',
            compact('transfer')
        );
    }

    /**
     * Transfer stock between locations.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'source_inventory_id' => [
                'required',
                'integer',
                'exists:inventories,id',
            ],

            'destination_inventory_id' => [
                'required',
                'integer',
                'exists:inventories,id',
                'different:source_inventory_id',
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

        DB::transaction(function () use ($validated) {

            $inventoryIds = [
                (int) $validated['source_inventory_id'],
                (int) $validated['destination_inventory_id'],
            ];

            sort($inventoryIds);

            $lockedInventories = Inventory::with([
                'product',
                'location',
                'productUnit.unitOfMeasure',
            ])
                ->whereIn('id', $inventoryIds)
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $sourceInventory = $lockedInventories->get(
                (int) $validated['source_inventory_id']
            );

            $destinationInventory = $lockedInventories->get(
                (int) $validated['destination_inventory_id']
            );

            if (!$sourceInventory || !$destinationInventory) {
                throw ValidationException::withMessages([
                    'source_inventory_id' =>
                        'The selected inventory records could not be found.',
                ]);
            }

            if (
                (int) $sourceInventory->product_id !==
                (int) $destinationInventory->product_id
            ) {
                throw ValidationException::withMessages([
                    'destination_inventory_id' =>
                        'The source and destination must contain the same product.',
                ]);
            }

            $productUnit = ProductUnit::with(
                'unitOfMeasure'
            )
                ->whereKey(
                    $validated['product_unit_id']
                )
                ->where(
                    'product_id',
                    $sourceInventory->product_id
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

            $sourceBaseQuantity =
                $this->resolveBaseQuantity(
                    $sourceInventory
                );

            if (
                $sourceBaseQuantity
                - $baseQuantity
                < -0.0000001
            ) {
                throw ValidationException::withMessages([
                    'quantity' =>
                        'Insufficient stock at the source location.',
                ]);
            }

            $newSourceBaseQuantity =
                $sourceBaseQuantity
                - $baseQuantity;

            if ($newSourceBaseQuantity < 0) {
                $newSourceBaseQuantity = 0;
            }

            $destinationBaseQuantity =
                $this->resolveBaseQuantity(
                    $destinationInventory
                );

            $newDestinationBaseQuantity =
                $destinationBaseQuantity
                + $baseQuantity;

            $sourceConversionFactor =
                $this->getInventoryConversionFactor(
                    $sourceInventory
                );

            $sourceDisplayQuantity =
                $newSourceBaseQuantity
                / $sourceConversionFactor;

            $destinationDisplayQuantity =
                $newDestinationBaseQuantity
                / $conversionFactor;

            $sourceInventory->update([
                'quantity' =>
                    $sourceDisplayQuantity,

                'base_quantity' =>
                    $newSourceBaseQuantity,
            ]);

            $destinationInventory->update([
                'product_unit_id' =>
                    $productUnit->id,

                'conversion_factor' =>
                    $conversionFactor,

                'quantity' =>
                    $destinationDisplayQuantity,

                'base_quantity' =>
                    $newDestinationBaseQuantity,
            ]);

            $transfer = InventoryTransfer::create([
                'source_inventory_id' =>
                    $sourceInventory->id,

                'destination_inventory_id' =>
                    $destinationInventory->id,

                'product_id' =>
                    $sourceInventory->product_id,

                'product_unit_id' =>
                    $productUnit->id,

                'conversion_factor' =>
                    $conversionFactor,

                'quantity' =>
                    $quantity,

                'base_quantity' =>
                    $baseQuantity,

                'reference' =>
                    $validated['reference']
                    ?? 'Inventory transfer',

                'notes' =>
                    $validated['notes']
                    ?? null,
            ]);

            $this->createTransaction(
                inventory: $sourceInventory,
                type: 'out',
                quantity: $quantity,
                baseQuantity: $baseQuantity,
                productUnitId: $productUnit->id,
                conversionFactor: $conversionFactor,
                reference:
                    'Transfer #' . $transfer->id
                    . ' to '
                    . $destinationInventory->location->name,
                notes:
                    $validated['notes'] ?? null
            );

            $this->createTransaction(
                inventory: $destinationInventory,
                type: 'in',
                quantity: $quantity,
                baseQuantity: $baseQuantity,
                productUnitId: $productUnit->id,
                conversionFactor: $conversionFactor,
                reference:
                    'Transfer #' . $transfer->id
                    . ' from '
                    . $sourceInventory->location->name,
                notes:
                    $validated['notes'] ?? null
            );
        });

        return redirect()
            ->route('inventory-transfers.index')
            ->with(
                'success',
                'Inventory transferred successfully.'
            );
    }

    /**
     * Resolve inventory stock in base units.
     */
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

        $conversionFactor =
            $this->getInventoryConversionFactor(
                $inventory
            );

        return $quantity * $conversionFactor;
    }

    /**
     * Get current inventory display conversion factor.
     */
    private function getInventoryConversionFactor(
        Inventory $inventory
    ): float {
        $conversionFactor =
            (float) $inventory->conversion_factor;

        return $conversionFactor > 0
            ? $conversionFactor
            : 1;
    }

    /**
     * Create inventory transaction.
     */
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

        if (!in_array(
            $type,
            ['in', 'out'],
            true
        )) {
            throw new \InvalidArgumentException(
                'Invalid inventory transaction type.'
            );
        }

        if ($quantity <= 0) {
            throw new \InvalidArgumentException(
                'Transaction quantity must be greater than zero.'
            );
        }

        if ($baseQuantity <= 0) {
            throw new \InvalidArgumentException(
                'Transaction base quantity must be greater than zero.'
            );
        }

        if (
            $conversionFactor === null ||
            $conversionFactor <= 0
        ) {
            throw new \InvalidArgumentException(
                'Transaction conversion factor must be greater than zero.'
            );
        }

        return InventoryTransaction::create([
            'inventory_id' =>
                $inventory->id,

            'product_id' =>
                $inventory->product_id,

            'location_id' =>
                $inventory->location_id,

            'type' =>
                $type,

            'quantity' =>
                $quantity,

            'base_quantity' =>
                $baseQuantity,

            'product_unit_id' =>
                $productUnitId,

            'conversion_factor' =>
                $conversionFactor,

            'reference' =>
                $reference,

            'notes' =>
                $notes,
        ]);
    }
}
