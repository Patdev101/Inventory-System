<?php

namespace App\Http\Controllers;

use App\Models\Location;
use App\Models\Product;
use App\Models\ProductUnit;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Services\PurchaseOrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PurchaseOrderController extends Controller
{
    public function __construct(
        private readonly PurchaseOrderService $purchaseOrderService
    ) {
    }

    /**
     * List purchase orders with search/filtering.
     */
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search'));
        $status = $request->input('status');

        $purchaseOrders = PurchaseOrder::with([
            'supplier',
            'location',
            'createdBy',
            'items',
        ])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where(
                        'po_number',
                        'like',
                        '%' . $search . '%'
                    )
                        ->orWhere(
                            'reference',
                            'like',
                            '%' . $search . '%'
                        )
                        ->orWhereHas('supplier', function ($query) use ($search) {
                            $query->where(
                                'name',
                                'like',
                                '%' . $search . '%'
                            );
                        });
                });
            })
            ->when($status, function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view(
            'purchase-orders.index',
            compact(
                'purchaseOrders',
                'search',
                'status'
            )
        );
    }

    /**
     * Show the purchase order creation form.
     *
     * Products are NOT loaded here.
     *
     * Products are loaded dynamically after a supplier is selected,
     * because products are company-specific.
     */
    public function create()
    {
        /*
        |--------------------------------------------------------------------------
        | Suppliers
        |--------------------------------------------------------------------------
        |
        | Each supplier belongs to exactly one company.
        |
        | Example:
        |
        | Supplier A -> Company 1
        | Supplier B -> Company 2
        |
        */
        $suppliers = Supplier::query()
            ->active()
            ->with('company')
            ->orderBy('name')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Locations
        |--------------------------------------------------------------------------
        |
        | Locations are also company-specific.
        |
        | The frontend will filter these based on the selected
        | supplier's company.
        |
        */
        $locations = Location::query()
            ->with('company')
            ->orderBy('name')
            ->get();

        return view(
            'purchase-orders.create',
            compact(
                'suppliers',
                'locations'
            )
        );
    }

    /**
     * Return products available to a supplier.
     *
     * IMPORTANT:
     *
     * A supplier does not directly have products in the current
     * database structure.
     *
     * Instead:
     *
     * supplier -> company -> products
     *
     * Therefore only products belonging to the supplier's company
     * are returned.
     */
    public function supplierProducts(Supplier $supplier)
    {
        /*
        |--------------------------------------------------------------------------
        | Only active supplier products
        |--------------------------------------------------------------------------
        */
        $products = Product::query()
            ->with([
                'productUnits.unitOfMeasure',
                'category',
            ])
            ->where(
                'company_id',
                $supplier->company_id
            )
            ->where(
                'is_active',
                true
            )
            ->orderBy('name')
            ->get();

        return response()->json([
            'products' => $products,
        ]);
    }

    /**
     * Create a new purchase order as a draft with its line items.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_id' => [
                'required',
                'integer',
                'exists:suppliers,id',
            ],

            'location_id' => [
                'required',
                'integer',
                'exists:locations,id',
            ],

            'expected_delivery_date' => [
                'nullable',
                'date',
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

            'items' => [
                'required',
                'array',
                'min:1',
            ],

            'items.*.product_id' => [
                'required',
                'integer',
                'exists:products,id',
            ],

            'items.*.product_unit_id' => [
                'required',
                'integer',
                'exists:product_units,id',
            ],

            'items.*.quantity_ordered' => [
                'required',
                'numeric',
                'gt:0',
            ],

            'items.*.unit_price' => [
                'required',
                'numeric',
                'gte:0',
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Load selected supplier
        |--------------------------------------------------------------------------
        */

        $supplier = Supplier::query()
            ->findOrFail(
                $validated['supplier_id']
            );

        /*
        |--------------------------------------------------------------------------
        | Load selected receiving location
        |--------------------------------------------------------------------------
        */

        $location = Location::query()
            ->findOrFail(
                $validated['location_id']
            );

        /*
        |--------------------------------------------------------------------------
        | Supplier and location MUST belong to same company
        |--------------------------------------------------------------------------
        |
        | This prevents:
        |
        | Supplier from Company A
        | +
        | Warehouse from Company B
        |
        */
        if (
            (int) $supplier->company_id
            !==
            (int) $location->company_id
        ) {
            return back()
                ->withInput()
                ->withErrors([
                    'location_id' =>
                        'The selected receiving location does not belong to the supplier company.',
                ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Get submitted product IDs
        |--------------------------------------------------------------------------
        */

        $productIds = collect(
            $validated['items']
        )
            ->pluck('product_id')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        /*
        |--------------------------------------------------------------------------
        | Validate products belong to supplier company
        |--------------------------------------------------------------------------
        |
        | This is the important server-side protection.
        |
        | Even if someone modifies the HTML/JavaScript and submits
        | a product belonging to another company, the PO will not
        | be created.
        |
        */
        $validProductIds = Product::query()
            ->whereIn(
                'id',
                $productIds
            )
            ->where(
                'company_id',
                $supplier->company_id
            )
            ->where(
                'is_active',
                true
            )
            ->pluck('id');

        if (
            $validProductIds->count()
            !==
            $productIds->count()
        ) {
            return back()
                ->withInput()
                ->withErrors([
                    'items' =>
                        'One or more selected products are not available from the selected supplier company.',
                ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Validate every product unit
        |--------------------------------------------------------------------------
        |
        | A ProductUnit must belong to the selected Product.
        |
        | Example:
        |
        | COKE -> Piece
        | COKE -> Case
        |
        | A unit belonging to another product cannot be submitted.
        |
        */
        foreach ($validated['items'] as $item) {
            $validUnit = ProductUnit::query()
                ->whereKey(
                    $item['product_unit_id']
                )
                ->where(
                    'product_id',
                    $item['product_id']
                )
                ->exists();

            if (!$validUnit) {
                return back()
                    ->withInput()
                    ->withErrors([
                        'items' =>
                            'One or more selected purchasing units do not belong to the selected product.',
                    ]);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Create purchase order
        |--------------------------------------------------------------------------
        |
        | Company is taken from the supplier.
        |
        | We do NOT trust a company_id coming from the browser.
        |
        */
        $purchaseOrder = $this->purchaseOrderService->createDraft(
            companyId: (int) $supplier->company_id,
            supplierId: (int) $validated['supplier_id'],
            locationId: (int) $validated['location_id'],
            createdByUserId: Auth::id(),
            items: $validated['items'],
            reference: $validated['reference'] ?? null,
            notes: $validated['notes'] ?? null,
            expectedDeliveryDate: $validated['expected_delivery_date'] ?? null
        );

        return redirect()
            ->route(
                'purchase-orders.show',
                $purchaseOrder
            )
            ->with(
                'success',
                'Purchase order '
                . $purchaseOrder->po_number
                . ' created as a draft.'
            );
    }

    /**
     * Show a single purchase order.
     */
    public function show(
        PurchaseOrder $purchaseOrder
    ) {
        $purchaseOrder->load([
            'supplier',
            'location',
            'createdBy',
            'approvedBy',
            'items.product',
            'items.productUnit.unitOfMeasure',
            'receipts.receivedBy',
            'receipts.items.purchaseOrderItem.product',
        ]);

        return view(
            'purchase-orders.show',
            compact('purchaseOrder')
        );
    }

    /**
     * Show the receiving form.
     *
     * Available when the PO is ordered or partially received.
     */
    public function receiveForm(
        PurchaseOrder $purchaseOrder
    ) {
        if (!in_array(
            $purchaseOrder->status,
            [
                PurchaseOrder::STATUS_ORDERED,
                PurchaseOrder::STATUS_PARTIALLY_RECEIVED,
            ],
            true
        )) {
            return redirect()
                ->route(
                    'purchase-orders.show',
                    $purchaseOrder
                )
                ->withErrors([
                    'status' =>
                        'This purchase order is not open for receiving.',
                ]);
        }

        $purchaseOrder->load([
            'supplier',
            'location',
            'items.product',
            'items.productUnit.unitOfMeasure',
        ]);

        return view(
            'purchase-orders.receive',
            compact('purchaseOrder')
        );
    }

    /**
     * Submit a purchase order for approval.
     *
     * draft -> pending_approval
     */
    public function submit(
        PurchaseOrder $purchaseOrder
    ) {
        $this->purchaseOrderService
            ->submitForApproval(
                $purchaseOrder
            );

        return back()
            ->with(
                'success',
                'Purchase order '
                . $purchaseOrder->po_number
                . ' submitted for approval.'
            );
    }

    /**
     * Approve a purchase order.
     *
     * pending_approval -> approved
     */
    public function approve(
        Request $request,
        PurchaseOrder $purchaseOrder
    ) {
        $validated = $request->validate([
            'approval_notes' => [
                'nullable',
                'string',
            ],
        ]);

        $this->purchaseOrderService->approve(
            purchaseOrder: $purchaseOrder,
            approverUserId: Auth::id(),
            notes: $validated['approval_notes'] ?? null
        );

        return back()
            ->with(
                'success',
                'Purchase order '
                . $purchaseOrder->po_number
                . ' approved.'
            );
    }

    /**
     * Reject a purchase order.
     *
     * pending_approval -> rejected
     */
    public function reject(
        Request $request,
        PurchaseOrder $purchaseOrder
    ) {
        $validated = $request->validate([
            'rejection_reason' => [
                'required',
                'string',
                'max:500',
            ],
        ]);

        $this->purchaseOrderService->reject(
            purchaseOrder: $purchaseOrder,
            approverUserId: Auth::id(),
            reason: $validated['rejection_reason']
        );

        return back()
            ->with(
                'success',
                'Purchase order '
                . $purchaseOrder->po_number
                . ' rejected.'
            );
    }

    /**
     * Mark an approved PO as ordered.
     *
     * approved -> ordered
     */
    public function markOrdered(
        PurchaseOrder $purchaseOrder
    ) {
        $this->purchaseOrderService
            ->markOrdered(
                $purchaseOrder
            );

        return back()
            ->with(
                'success',
                'Purchase order '
                . $purchaseOrder->po_number
                . ' marked as ordered.'
            );
    }

    /**
     * Record receiving.
     *
     * Supports full and partial receiving.
     */
    public function receive(
        Request $request,
        PurchaseOrder $purchaseOrder
    ) {
        $validated = $request->validate([
            'lines' => [
                'required',
                'array',
                'min:1',
            ],

            'lines.*.purchase_order_item_id' => [
                'required',
                'integer',
                'exists:purchase_order_items,id',
            ],

            'lines.*.quantity_received' => [
                'nullable',
                'numeric',
                'gte:0',
            ],

            'lines.*.notes' => [
                'nullable',
                'string',
            ],

            'notes' => [
                'nullable',
                'string',
            ],
        ]);

        $this->purchaseOrderService->receiveItems(
            purchaseOrder: $purchaseOrder,
            receivedByUserId: Auth::id(),
            lines: $validated['lines'],
            notes: $validated['notes'] ?? null
        );

        return redirect()
            ->route(
                'purchase-orders.show',
                $purchaseOrder
            )
            ->with(
                'success',
                'Receiving recorded for purchase order '
                . $purchaseOrder->po_number
                . '.'
            );
    }
}
