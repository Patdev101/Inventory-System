<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\InventoryTransfer;
use App\Models\Location;
use App\Models\User;
use App\Services\InventoryMovementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InventoryTransferController extends Controller
{
    public function __construct(
        private readonly InventoryMovementService $movementService
    ) {
    }

    /**
     * Show transfer history.
     */
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search'));

        // Sorting: default is newest first, same as before. "From
        // location" / "To location" are also sortable — these live on
        // related tables (inventory_transfers -> inventories ->
        // locations), so Eloquent's normal orderBy() can't reach them
        // without a join. Two left joins (aliased per side, since both
        // source and destination point at the same inventories/locations
        // tables) let us sort by the joined location name directly.
        $sort = $request->input('sort', 'created_at');
        $direction = strtolower((string) $request->input('direction', 'desc')) === 'asc' ? 'asc' : 'desc';

        $sortable = [
            'created_at'    => 'inventory_transfers.created_at',
            'from_location' => 'source_locations.name',
            'to_location'   => 'destination_locations.name',
        ];

        $sortColumn = $sortable[$sort] ?? $sortable['created_at'];

        $transfers = InventoryTransfer::query()
            ->select('inventory_transfers.*')
            ->with([
                'product',
                'sourceInventory.location',
                'destinationInventory.location',
                'productUnit.unitOfMeasure',
                'receiver',
            ])
            ->leftJoin('inventories as source_inventories', 'source_inventories.id', '=', 'inventory_transfers.source_inventory_id')
            ->leftJoin('locations as source_locations', 'source_locations.id', '=', 'source_inventories.location_id')
            ->leftJoin('inventories as destination_inventories', 'destination_inventories.id', '=', 'inventory_transfers.destination_inventory_id')
            ->leftJoin('locations as destination_locations', 'destination_locations.id', '=', 'destination_inventories.location_id')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {

                    $query->where(
                        'inventory_transfers.reference',
                        'like',
                        '%' . $search . '%'
                    )

                    ->orWhere(
                        'inventory_transfers.notes',
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

                    ->orWhere(
                        'source_locations.name',
                        'like',
                        '%' . $search . '%'
                    )
                    ->orWhere(
                        'source_locations.code',
                        'like',
                        '%' . $search . '%'
                    )

                    ->orWhere(
                        'destination_locations.name',
                        'like',
                        '%' . $search . '%'
                    )
                    ->orWhere(
                        'destination_locations.code',
                        'like',
                        '%' . $search . '%'
                    );
                });
            })
            ->orderBy($sortColumn, $direction)
            ->paginate(10)
            ->withQueryString();

        return view(
            'inventory-transfers.index',
            compact(
                'transfers',
                'search',
                'sort',
                'direction'
            )
        );
    }

    /**
     * Transfers assigned to the current user that are still awaiting
     * audit or receipt. This is the actionable queue — distinct from
     * index(), which is a full read-only history log of every transfer
     * regardless of who's involved or what state it's in.
     */
    public function pendingAudits(Request $request)
    {
        $transfers = InventoryTransfer::with([
            'product',
            'sourceInventory.location',
            'destinationInventory.location',
            'productUnit.unitOfMeasure',
        ])
            ->where('receiver_id', Auth::id())
            ->where('status', 'pending')
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view(
            'inventory-transfers.pending',
            compact('transfers')
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

        /*
         * Destination is a location, not an existing inventory record — a
         * location with zero (or no) stock of the product yet is exactly
         * where you'd want to transfer stock to. The inventory record is
         * created automatically on transfer if it doesn't exist yet.
         */
        $locations = Location::with('company')
            ->orderBy('name')
            ->get();

        /*
         * Users who can be assigned as the receiver for a transfer.
         * NOTE: not filtered by role yet — your users table doesn't have
         * a confirmed `role` column, so every user is listed and the
         * manager/staff designation is picked separately per transfer via
         * receiver_role. Tighten this later if/when you add real roles.
         */
        $receivers = User::orderBy('name')->get();

        return view(
            'inventory-transfers.create',
            compact('inventories', 'locations', 'receivers')
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
            'receiver',
            'auditedBy',
            'receivedBy',
        ]);

        return view(
            'inventory-transfers.show',
            compact('transfer')
        );
    }

       /**
     * Create pending transfers (one per checklist item) awaiting
     * receiver audit.
     *
     * Stock leaves the source immediately for each item, but does not
     * reach the destination until the assigned receiver passes the audit
     * and marks it received (see audit() and receive() below). All items
     * in the batch share the same destination, receiver, reference, and
     * notes; each item is still its own InventoryTransfer row so it can
     * be audited/received independently if needed.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'destination_location_id' => [
                'required',
                'integer',
                'exists:locations,id',
            ],

            'receiver_id' => [
                'required',
                'integer',
                'exists:users,id',
            ],

            'receiver_role' => [
                'required',
                'string',
                'in:admin,manager,staff',
            ],

            'items' => [
                'required',
                'array',
                'min:1',
            ],

            'items.*.source_inventory_id' => [
                'required',
                'integer',
                'exists:inventories,id',
            ],

            'items.*.product_unit_id' => [
                'required',
                'integer',
                'exists:product_units,id',
            ],

            'items.*.quantity' => [
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

        foreach ($validated['items'] as $item) {
            $this->movementService->initiateTransfer(
                sourceInventoryId: (int) $item['source_inventory_id'],
                destinationLocationId: (int) $validated['destination_location_id'],
                productUnitId: (int) $item['product_unit_id'],
                quantity: (float) $item['quantity'],
                receiverId: (int) $validated['receiver_id'],
                receiverRole: $validated['receiver_role'],
                reference: $validated['reference'] ?? null,
                notes: $validated['notes'] ?? null
            );
        }

        return redirect()
            ->route('inventory-transfers.index')
            ->with(
                'success',
                count($validated['items']) . ' transfer(s) created and stock deducted from source. Awaiting receiver audit.'
            );
    }

    /**
     * Receiver inspects the transferred items and marks pass/fail.
     */
    public function audit(Request $request, InventoryTransfer $transfer)
    {
        if ($transfer->status !== 'pending') {
            return back()->with(
                'error',
                'This transfer is not awaiting audit.'
            );
        }

        if ((int) $transfer->receiver_id !== (int) Auth::id()) {
            abort(403, 'Only the assigned receiver can audit this transfer.');
        }

        $validated = $request->validate([
            'result' => [
                'required',
                'string',
                'in:pass,fail',
            ],

            'audit_notes' => [
                'nullable',
                'string',
            ],
        ]);

        if ($validated['result'] === 'fail') {
            $this->movementService->reverseTransfer(
                transfer: $transfer,
                auditedByUserId: (int) Auth::id(),
                auditNotes: $validated['audit_notes'] ?? null
            );

            return back()->with(
                'success',
                'Transfer marked as failed audit. Stock has been returned to the source location.'
            );
        }

        $transfer->update([
            'audit_status' => 'passed',
            'audited_by' => Auth::id(),
            'audited_at' => now(),
            'audit_notes' => $validated['audit_notes'] ?? null,
        ]);

        return back()->with(
            'success',
            'Item passed audit. You can now mark it received.'
        );
    }

    /**
     * Credit destination stock once the transfer has passed audit.
     */
    public function receive(InventoryTransfer $transfer)
    {
        if ((int) $transfer->receiver_id !== (int) Auth::id()) {
            abort(403, 'Only the assigned receiver can receive this transfer.');
        }

        $this->movementService->completeTransferReceipt(
            transfer: $transfer,
            receivedByUserId: (int) Auth::id()
        );

        return back()->with(
            'success',
            'Transfer received. Stock has been added to the destination location.'
        );
    }
}