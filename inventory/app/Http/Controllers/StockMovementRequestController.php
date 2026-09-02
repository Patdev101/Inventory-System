<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\StockMovementRequest;
use App\Services\InventoryMovementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StockMovementRequestController extends Controller
{
    public function __construct(
        private readonly InventoryMovementService $movementService
    ) {
    }

    public function index(Request $request): View
    {
        $query = StockMovementRequest::with([
            'product',
            'location',
            'productUnit.unitOfMeasure',
            'requestedBy',
            'reviewedBy',
        ]);

        /*
         * Staff only ever see their own submissions - this page
         * doubles as their "My Requests" transparency view. Admins
         * and managers see everything, since they review all of it.
         */
        if (!$request->user()->hasRole('admin', 'manager')) {
            $query->where('requested_by', $request->user()->id);
        }

        $requests = $query
            ->orderByRaw("CASE WHEN status = 'pending' THEN 0 ELSE 1 END")
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view(
            'stock-movement-requests.index',
            compact('requests')
        );
    }

    public function approve(
        Request $request,
        StockMovementRequest $stockMovementRequest
    ): RedirectResponse {
        abort_unless(
            $request->user()->hasRole('admin', 'manager'),
            403
        );

        if (!$stockMovementRequest->isPending()) {
            return back()->with(
                'error',
                'This request has already been reviewed.'
            );
        }

        if ($stockMovementRequest->inventory_id) {
            $inventory = Inventory::findOrFail(
                $stockMovementRequest->inventory_id
            );

            $this->movementService->moveStock(
                $inventory,
                $stockMovementRequest->product_id,
                $stockMovementRequest->product_unit_id,
                $stockMovementRequest->type,
                (float) $stockMovementRequest->quantity
            );
        } else {
            $this->movementService->addStock(
                $stockMovementRequest->product_id,
                $stockMovementRequest->location_id,
                $stockMovementRequest->product_unit_id,
                (float) $stockMovementRequest->quantity
            );
        }

        $stockMovementRequest->update([
            'status' => StockMovementRequest::STATUS_APPROVED,
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
        ]);

        return back()->with('success', 'Stock movement approved and applied.');
    }

    public function reject(
        Request $request,
        StockMovementRequest $stockMovementRequest
    ): RedirectResponse {
        abort_unless(
            $request->user()->hasRole('admin', 'manager'),
            403
        );

        if (!$stockMovementRequest->isPending()) {
            return back()->with(
                'error',
                'This request has already been reviewed.'
            );
        }

        $validated = $request->validate([
            'rejection_reason' => ['nullable', 'string', 'max:255'],
        ]);

        $stockMovementRequest->update([
            'status' => StockMovementRequest::STATUS_REJECTED,
            'rejection_reason' => $validated['rejection_reason'] ?? null,
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
        ]);

        return back()->with('success', 'Stock movement request rejected.');
    }
}
