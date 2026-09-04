<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\InventoryTransaction;
use App\Models\Location;
use App\Models\Product;
use App\Models\StockMovementRequest;
use App\Models\User;
use App\Services\InventoryMovementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InventoryController extends Controller
{
    public function __construct(
        private readonly InventoryMovementService $movementService
    ) {
    }

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
 * Staff submissions are queued as a StockMovementRequest pending
 * manager/admin approval instead of being applied immediately.
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

    if ($request->user()?->hasRole(User::ROLE_STAFF)) {
        StockMovementRequest::create([
            'inventory_id' => null,
            'product_id' => $validated['product_id'],
            'location_id' => $validated['location_id'],
            'product_unit_id' => $validated['product_unit_id'],
            'type' => 'in',
            'quantity' => $validated['quantity'],
            'status' => StockMovementRequest::STATUS_PENDING,
            'requested_by' => $request->user()->id,
        ]);

        return redirect()
            ->route('inventories.index')
            ->with(
                'success',
                'Stock addition submitted for manager approval.'
            );
    }

    $this->movementService->addStock(
        (int) $validated['product_id'],
        (int) $validated['location_id'],
        (int) $validated['product_unit_id'],
        (float) $validated['quantity']
    );

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

    $transferCandidates = collect();

    if ($inventory->isOutOfStock() && $inventory->location && $inventory->product) {
        $transferCandidates = Inventory::with('location')
            ->where('product_id', $inventory->product_id)
            ->where('base_quantity', '>', 0)
            ->whereHas('location', function ($query) use ($inventory) {
                $query->where('company_id', $inventory->location->company_id)
                    ->where('id', '!=', $inventory->location_id);
            })
            ->get();
    }

    $productUnits = $inventory->product
        ? $inventory->product->productUnits()->with('unitOfMeasure')->get()
        : collect();

    return view(
        'inventories.show',
        compact('inventory', 'transactions', 'transferCandidates', 'productUnits')
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
 * Staff submissions are queued as a StockMovementRequest pending
 * manager/admin approval instead of being applied immediately.
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

    if ($request->user()?->hasRole(User::ROLE_STAFF)) {
        StockMovementRequest::create([
            'inventory_id' => $inventory->id,
            'product_id' => $validated['product_id'],
            'location_id' => $validated['location_id'],
            'product_unit_id' => $validated['product_unit_id'],
            'type' => $validated['movement_type'],
            'quantity' => $validated['quantity'],
            'status' => StockMovementRequest::STATUS_PENDING,
            'requested_by' => $request->user()->id,
        ]);

        return redirect()
            ->route('inventories.show', $inventory)
            ->with(
                'success',
                'Stock movement submitted for manager approval.'
            );
    }

    /*
     * Product + location identifies an inventory record. Staff
     * requests skip this check since it is re-validated at approval
     * time against whatever the inventory looks like then.
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
        ->exists();

    if ($duplicate) {
        throw ValidationException::withMessages([
            'product_id' =>
                'Inventory already exists for this product and location. '
                . 'Edit the existing inventory instead.',
        ]);
    }

    $inventory = $this->movementService->moveStock(
        $inventory,
        (int) $validated['product_id'],
        (int) $validated['product_unit_id'],
        $validated['movement_type'],
        (float) $validated['quantity']
    );

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
 * Request stock be moved in from another location of the same company
 * that currently has it, to cover an out-of-stock (or low-stock)
 * inventory record.
 *
 * Staff submissions are queued as a StockMovementRequest pending
 * manager/admin approval, same as any other stock movement. Admins and
 * managers already have full access to Transfer Inventory, so their
 * request executes immediately.
 */
public function requestTransfer(Request $request, Inventory $inventory)
{
    $inventory->load('location');

    $validated = $request->validate([
        'source_location_id' => [
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

    if ((int) $validated['source_location_id'] === (int) $inventory->location_id) {
        throw ValidationException::withMessages([
            'source_location_id' =>
                'The source location must be different from this one.',
        ]);
    }

    $sourceInventory = Inventory::where('product_id', $inventory->product_id)
        ->where('location_id', $validated['source_location_id'])
        ->first();

    if (!$sourceInventory || $this->movementService->resolveBaseQuantity($sourceInventory) <= 0) {
        throw ValidationException::withMessages([
            'source_location_id' =>
                'The selected location does not currently have stock of this product.',
        ]);
    }

    $sourceLocation = Location::with('company')->findOrFail($validated['source_location_id']);

    if ((int) $sourceLocation->company_id !== (int) $inventory->location->company_id) {
        throw ValidationException::withMessages([
            'source_location_id' =>
                'The source location must belong to the same company.',
        ]);
    }

    if ($request->user()?->hasRole(User::ROLE_STAFF)) {
        StockMovementRequest::create([
            'inventory_id' => $sourceInventory->id,
            'product_id' => $inventory->product_id,
            'location_id' => $sourceInventory->location_id,
            'destination_location_id' => $inventory->location_id,
            'product_unit_id' => $validated['product_unit_id'],
            'type' => StockMovementRequest::TYPE_TRANSFER,
            'quantity' => $validated['quantity'],
            'status' => StockMovementRequest::STATUS_PENDING,
            'requested_by' => $request->user()->id,
        ]);

        return redirect()
            ->route('inventories.show', $inventory)
            ->with(
                'success',
                'Transfer request submitted for manager approval.'
            );
    }

    $this->movementService->transferStock(
        sourceInventoryId: $sourceInventory->id,
        destinationInventoryId: $inventory->id,
        productUnitId: (int) $validated['product_unit_id'],
        quantity: (float) $validated['quantity'],
        reference: 'Requested transfer to cover low/out-of-stock inventory'
    );

    return redirect()
        ->route('inventories.show', $inventory)
        ->with(
            'success',
            'Stock transferred successfully.'
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
            $this->movementService->resolveBaseQuantity($inventory);

        if ($baseQuantity > 0.0000001) {
            $conversionFactor =
                $this->movementService->getInventoryConversionFactor(
                    $inventory
                );

            $quantity = $baseQuantity / $conversionFactor;

            InventoryTransaction::create([
                'inventory_id' => $inventory->id,
                'product_id' => $inventory->product_id,
                'location_id' => $inventory->location_id,
                'type' => 'out',
                'quantity' => $quantity,
                'base_quantity' => $baseQuantity,
                'product_unit_id' => $inventory->product_unit_id,
                'conversion_factor' => $conversionFactor,
                'reference' => 'Inventory deleted',
                'notes' => 'Remaining inventory recorded as OUT before inventory deletion.',
            ]);
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

}
