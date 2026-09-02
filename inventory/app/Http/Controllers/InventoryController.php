<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\InventoryTransaction;
use App\Models\Location;
use App\Models\Product;
use App\Models\ProductUnit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InventoryController extends Controller
{
/**
* Display inventory list.
*/
public function index(Request $request)
{
$search = trim($request->input('search', ''));

    $query = Inventory::with([
        'product',
        'location',
        'productUnit.unitOfMeasure',
    ]);

    if ($search !== '') {
        $query->where(function ($q) use ($search) {
            $q->whereHas('product', function ($productQuery) use ($search) {
                $productQuery
                    ->where('name', 'like', '%' . $search . '%')
                    ->orWhere('sku', 'like', '%' . $search . '%');
            })
            ->orWhereHas('location', function ($locationQuery) use ($search) {
                $locationQuery
                    ->where('name', 'like', '%' . $search . '%')
                    ->orWhere('code', 'like', '%' . $search . '%');
            });
        });
    }

    $inventories = $query
        ->orderByDesc('id')
        ->paginate(10)
        ->withQueryString();

    return view(
        'inventories.index',
        compact('inventories', 'search')
    );
}

/**
 * Show create inventory form.
 */
public function create()
{
    $products = Product::where('is_active', true)
        ->with('productUnits.unitOfMeasure')
        ->orderBy('name')
        ->get();

    $locations = Location::with('company')
        ->orderBy('name')
        ->get();

    return view(
        'inventories.create',
        compact('products', 'locations')
    );
}

/**
 * Add inventory / stock.
 *
 * The selected product unit is used for the incoming movement.
 * If inventory already exists, the inventory's existing display
 * unit is preserved.
 */
public function store(Request $request)
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

    $productUnit = $this->getValidProductUnit(
        (int) $validated['product_id'],
        (int) $validated['product_unit_id']
    );

    $enteredQuantity = (float) $validated['quantity'];

    $movementConversionFactor =
        (float) $productUnit->conversion_factor;

    $movementBaseQuantity =
        $enteredQuantity * $movementConversionFactor;

    DB::transaction(function () use (
        $validated,
        $productUnit,
        $enteredQuantity,
        $movementBaseQuantity,
        $movementConversionFactor
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
            /*
             * base_quantity is the authoritative stock balance.
             */
            $currentBaseQuantity =
                $this->resolveBaseQuantity($inventory);

            $newBaseQuantity =
                $currentBaseQuantity + $movementBaseQuantity;

            /*
             * Preserve the inventory's existing display unit.
             */
            $displayConversionFactor =
                $this->getInventoryConversionFactor($inventory);

            $displayQuantity =
                $newBaseQuantity / $displayConversionFactor;

            $inventory->update([
                'quantity' => $displayQuantity,
                'base_quantity' => $newBaseQuantity,
            ]);
        } else {
            /*
             * For a new inventory record, the first movement's
             * product unit becomes the inventory display unit.
             */
            $inventory = Inventory::create([
                'product_id' => $validated['product_id'],
                'location_id' => $validated['location_id'],
                'product_unit_id' => $productUnit->id,
                'conversion_factor' => $movementConversionFactor,
                'quantity' => $enteredQuantity,
                'base_quantity' => $movementBaseQuantity,
            ]);
        }

        /*
         * Always record the exact unit used by this movement.
         */
        $this->createTransaction(
            inventory: $inventory,
            type: 'in',
            quantity: $enteredQuantity,
            baseQuantity: $movementBaseQuantity,
            productUnitId: $productUnit->id,
            conversionFactor: $movementConversionFactor,
            reference: 'Inventory addition'
        );
    });

    return redirect()
        ->route('inventories.index')
        ->with(
            'success',
            'Inventory added successfully.'
        );
}

/**
 * Display one inventory.
 */
public function show(Inventory $inventory)
{
    $inventory->load([
        'product',
        'location.company',
        'productUnit.unitOfMeasure',
        'transactions.product',
        'transactions.location',
        'transactions.productUnit.unitOfMeasure',
    ]);

    $runningBalance = 0;

    $transactions = $inventory->transactions
        ->sortBy(function ($transaction) {
            return [
                $transaction->created_at?->timestamp ?? 0,
                $transaction->id,
            ];
        })
        ->values();

    foreach ($transactions as $transaction) {
        $runningBalance +=
            $transaction->getSignedBaseQuantity();

        $transaction->running_balance =
            $runningBalance;
    }

    return view(
        'inventories.show',
        compact('inventory', 'transactions')
    );
}

/**
 * Show edit inventory form.
 */
public function edit(Inventory $inventory)
{
    $inventory->load([
        'product',
        'location.company',
        'productUnit.unitOfMeasure',
    ]);

    $products = Product::with(
        'productUnits.unitOfMeasure'
    )
        ->orderBy('name')
        ->get();

    $locations = Location::with('company')
        ->orderBy('name')
        ->get();

    return view(
        'inventories.edit',
        compact(
            'inventory',
            'products',
            'locations'
        )
    );
}

/**
 * Update inventory through a stock movement.
 *
 * The selected product unit belongs to the movement only.
 * It does NOT replace the inventory's display unit.
 */
public function update(
    Request $request,
    Inventory $inventory
) {
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

        'movement_type' => [
            'required',
            'in:in,out',
        ],

        'quantity' => [
            'required',
            'numeric',
            'gt:0',
        ],
    ]);

    $product = Product::findOrFail(
        $validated['product_id']
    );

    if (
        !$product->is_active &&
        $validated['movement_type'] === 'in'
    ) {
        throw ValidationException::withMessages([
            'product_id' =>
                'This product is deactivated and cannot receive new stock.',
        ]);
    }

    /*
     * Validate that the selected unit belongs to the product.
     */
    $productUnit = $this->getValidProductUnit(
        (int) $validated['product_id'],
        (int) $validated['product_unit_id']
    );

    $enteredQuantity =
        (float) $validated['quantity'];

    /*
     * This conversion factor belongs to this movement.
     */
    $movementConversionFactor =
        (float) $productUnit->conversion_factor;

    $movementBaseQuantity =
        $enteredQuantity * $movementConversionFactor;

    DB::transaction(function () use (
        $inventory,
        $validated,
        $productUnit,
        $enteredQuantity,
        $movementBaseQuantity,
        $movementConversionFactor
    ) {
        /*
         * Lock inventory so concurrent movements cannot
         * overwrite the balance.
         */
        $inventory = Inventory::whereKey(
            $inventory->id
        )
            ->lockForUpdate()
            ->firstOrFail();

        /*
         * Product + location identifies an inventory record.
         */
        $duplicate = Inventory::where(
            'product_id',
            $validated['product_id']
        )
            ->where(
                'location_id',
                $validated['location_id']
            )
            ->where(
                'id',
                '!=',
                $inventory->id
            )
            ->lockForUpdate()
            ->exists();

        if ($duplicate) {
            throw ValidationException::withMessages([
                'product_id' =>
                    'Inventory already exists for this product and location. '
                    . 'Edit the existing inventory instead.',
            ]);
        }

        /*
         * Stock is always calculated in base units.
         */
        $currentBaseQuantity =
            $this->resolveBaseQuantity($inventory);

        if ($validated['movement_type'] === 'in') {
            $newBaseQuantity =
                $currentBaseQuantity +
                $movementBaseQuantity;
        } else {
            $newBaseQuantity =
                $currentBaseQuantity -
                $movementBaseQuantity;
        }

        /*
         * Never allow negative inventory.
         */
        if ($newBaseQuantity < -0.0000001) {
            throw ValidationException::withMessages([
                'quantity' =>
                    'Insufficient stock. You cannot remove more stock than is currently available.',
            ]);
        }

        /*
         * Remove tiny floating-point errors around zero.
         */
        if ($newBaseQuantity < 0) {
            $newBaseQuantity = 0;
        }

        /*
         * Preserve the inventory's configured display unit.
         *
         * The movement unit is not used to calculate the displayed
         * inventory quantity.
         */
        $displayConversionFactor =
            $this->getInventoryConversionFactor($inventory);

        $displayQuantity =
            $newBaseQuantity / $displayConversionFactor;

        /*
         * Only update the stock balance.
         *
         * Do not overwrite:
         * - product_unit_id
         * - conversion_factor
         *
         * Those describe the inventory display unit.
         */
        $inventory->update([
            'quantity' => $displayQuantity,
            'base_quantity' => $newBaseQuantity,
        ]);

        /*
         * Store the movement's own unit and conversion factor.
         */
        $this->createTransaction(
            inventory: $inventory,
            type: $validated['movement_type'],
            quantity: $enteredQuantity,
            baseQuantity: $movementBaseQuantity,
            productUnitId: $productUnit->id,
            conversionFactor: $movementConversionFactor,
            reference:
                $validated['movement_type'] === 'in'
                    ? 'Inventory stock added'
                    : 'Inventory stock removed'
        );
    });

    return redirect()
        ->route('inventories.show', $inventory)
        ->with(
            'success',
            $validated['movement_type'] === 'in'
                ? 'Stock added successfully.'
                : 'Stock removed successfully.'
        );
}

/**
 * Delete inventory.
 *
 * Remaining stock is first recorded as an OUT transaction.
 * The inventory transaction survives because the FK uses SET NULL.
 */
public function destroy(Inventory $inventory)
{
    DB::transaction(function () use ($inventory) {
        $inventory = Inventory::whereKey(
            $inventory->id
        )
            ->lockForUpdate()
            ->firstOrFail();

        $baseQuantity =
            $this->resolveBaseQuantity($inventory);

        if ($baseQuantity > 0.0000001) {
            $quantity =
                $this->resolveDisplayQuantity(
                    $inventory,
                    $baseQuantity
                );

            $conversionFactor =
                $this->getInventoryConversionFactor(
                    $inventory
                );

            $this->createTransaction(
                inventory: $inventory,
                type: 'out',
                quantity: $quantity,
                baseQuantity: $baseQuantity,
                productUnitId:
                    $inventory->product_unit_id,
                conversionFactor:
                    $conversionFactor,
                reference:
                    'Inventory deleted',
                notes:
                    'Remaining inventory recorded as OUT before inventory deletion.'
            );
        }

        $inventory->delete();
    });

    return redirect()
        ->route('inventories.index')
        ->with(
            'success',
            'Inventory deleted successfully.'
        );
}

/**
 * Validate ProductUnit ownership and conversion factor.
 */
private function getValidProductUnit(
    int $productId,
    int $productUnitId
): ProductUnit {
    $productUnit = ProductUnit::with(
        'unitOfMeasure'
    )
        ->whereKey($productUnitId)
        ->where(
            'product_id',
            $productId
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

    return $productUnit;
}

/**
 * Resolve inventory stock in base units.
 *
 * base_quantity is authoritative when it is populated.
 *
 * The fallback exists for older records where base_quantity may
 * not have been populated yet.
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
        $this->getInventoryConversionFactor($inventory);

    return $quantity * $conversionFactor;
}

/**
 * Get the inventory's configured display-unit conversion factor.
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
 * Convert base quantity into the inventory's display quantity.
 */
private function resolveDisplayQuantity(
    Inventory $inventory,
    float $baseQuantity
): float {
    $conversionFactor =
        $this->getInventoryConversionFactor($inventory);

    return $baseQuantity / $conversionFactor;
}

/**
 * Create normalized inventory transaction.
 *
 * quantity and conversion_factor describe the unit used
 * for the movement.
 *
 * base_quantity is the normalized stock movement.
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
    if (!in_array($type, ['in', 'out'], true)) {
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
