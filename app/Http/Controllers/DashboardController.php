<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\InventoryTransaction;
use App\Models\InventoryTransfer;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\StockMovementRequest;
use App\Models\Location;
use App\Services\StockAlertService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Display the inventory dashboard.
     */
    public function index(StockAlertService $stockAlertService, Request $request)
    {
        $stockAlertService->synchronizeIfStale();

        /*
        |--------------------------------------------------------------------------
        | Notifications
        |--------------------------------------------------------------------------
        */

        $notifications = $request->user()
            ->notifications()
            ->take(10)
            ->get();

        $unreadNotificationsCount = $request->user()
            ->unreadNotifications()
            ->count();

        /*
        |--------------------------------------------------------------------------
        | Basic statistics
        |--------------------------------------------------------------------------
        */

        $totalProducts = Product::count();

        $totalInventory = Inventory::count();

        $totalLocations = Location::count();

        $totalTransactions = InventoryTransaction::count();

        $totalTransfers = InventoryTransfer::count();


        /*
        |--------------------------------------------------------------------------
        | Current stock
        |--------------------------------------------------------------------------
        */

        $totalBaseStock = Inventory::sum('base_quantity');


        /*
        |--------------------------------------------------------------------------
        | Stock Alerts
        |--------------------------------------------------------------------------
        |
        | Stock status is determined using base_quantity and the product's
        | reorder_point.
        |
        | Rules:
        |
        | base_quantity <= 0
        |     = Out of Stock
        |
        | base_quantity <= reorder_point / 2
        |     = Critical
        |
        | base_quantity <= reorder_point
        |     = Low Stock
        |
        | base_quantity > reorder_point
        |     = In Stock
        |
        | Reorder points are stored in base units, so all comparisons are
        | performed against inventory.base_quantity.
        |
        */


        /*
        |--------------------------------------------------------------------------
        | Out of Stock Alerts
        |--------------------------------------------------------------------------
        */

        $outOfStockInventories = Inventory::outOfStock()->with([
            'product.baseUnit',
            'location',
            'productUnit.unitOfMeasure',
        ])
            ->orderBy('base_quantity')
            ->take(10)
            ->get();


        $outOfStockCount = Inventory::outOfStock()->count();


        /*
        |--------------------------------------------------------------------------
        | Critical Stock Alerts
        |--------------------------------------------------------------------------
        |
        | Critical stock is stock that is above zero but at or below half
        | of the product reorder point.
        |
        */

        $criticalStockInventories = Inventory::criticalStock()->with([
            'product.baseUnit',
            'location',
            'productUnit.unitOfMeasure',
        ])
            ->orderBy('base_quantity')
            ->take(10)
            ->get();


        $criticalStockCount = Inventory::criticalStock()->count();


        /*
        |--------------------------------------------------------------------------
        | Low Stock Alerts
        |--------------------------------------------------------------------------
        |
        | Low stock includes inventory that is above the critical threshold
        | but still at or below the reorder point.
        |
        | This intentionally excludes critical inventory so that an inventory
        | record appears in only one alert severity.
        |
        */

        $lowStockInventories = Inventory::lowStock()->with([
            'product.baseUnit',
            'location',
            'productUnit.unitOfMeasure',
        ])
            ->orderBy('base_quantity')
            ->take(10)
            ->get();


        $lowStockCount = Inventory::lowStock()->count();


        /*
        |--------------------------------------------------------------------------
        | Total Stock Alert Count
        |--------------------------------------------------------------------------
        |
        | This represents every inventory record that currently requires
        | attention.
        |
        */

        $stockAlertCount =
            $outOfStockCount +
            $criticalStockCount +
            $lowStockCount;


        /*
        |--------------------------------------------------------------------------
        | Needs attention
        |--------------------------------------------------------------------------
        |
        | The short list at the top of the dashboard: only things this person
        | can act on, each with the one page that deals with it. Every count
        | goes through the models' location scope, so nobody is told about
        | another location's work.
        |
        */

        $user = $request->user();
        $canApprove = $user->hasRole('admin', 'manager');
        $attentionItems = [];

        if ($outOfStockCount > 0) {
            $attentionItems[] = [
                'tone' => 'danger',
                'title' => $outOfStockCount . ' ' . ($outOfStockCount === 1 ? 'item is' : 'items are') . ' out of stock',
                'detail' => $canApprove ? 'Order more from a supplier to restock.' : 'Let your manager know these need restocking.',
                'action' => $canApprove ? 'Create purchase order' : 'View items',
                'url' => $canApprove ? route('purchase-orders.create') : route('stock-alerts.index', ['severity' => 'out_of_stock']),
            ];
        }

        $runningLowCount = $criticalStockCount + $lowStockCount;

        if ($runningLowCount > 0) {
            $attentionItems[] = [
                'tone' => 'warning',
                'title' => $runningLowCount . ' ' . ($runningLowCount === 1 ? 'item is' : 'items are') . ' running low',
                'detail' => 'At or below the reorder point.',
                'action' => 'View items',
                'url' => route('stock-alerts.index'),
            ];
        }

        // Stock of products that expire: already expired, or expiring within the warning window.
        $expiryBatches = app(\App\Services\ExpiryService::class)->expiringBatches();
        $expiredCount = $expiryBatches->where('expired', true)->count();
        $expiringCount = $expiryBatches->count() - $expiredCount;

        if ($expiredCount > 0) {
            $attentionItems[] = [
                'tone' => 'danger',
                'title' => $expiredCount . ' ' . ($expiredCount === 1 ? 'batch has' : 'batches have') . ' expired',
                'detail' => 'Remove it from stock so it is not sold.',
                'action' => 'View expired stock',
                'url' => route('reports.expiring'),
            ];
        }

        if ($expiringCount > 0) {
            $attentionItems[] = [
                'tone' => 'warning',
                'title' => $expiringCount . ' ' . ($expiringCount === 1 ? 'batch expires' : 'batches expire') . ' within ' . config('stockalerts.expiry_warning_days', 30) . ' days',
                'detail' => 'Sell or move it first.',
                'action' => 'View expiring stock',
                'url' => route('reports.expiring'),
            ];
        }

        $transfersToReceive = InventoryTransfer::where('receiver_id', $user->id)
            ->where('status', 'pending')
            ->count();

        if ($transfersToReceive > 0) {
            $attentionItems[] = [
                'tone' => 'info',
                'title' => $transfersToReceive . ' ' . ($transfersToReceive === 1 ? 'transfer is' : 'transfers are') . ' waiting for you to receive',
                'detail' => 'Check the delivery and confirm it.',
                'action' => 'Receive',
                'url' => route('inventory-transfers.pending-audits'),
            ];
        }

        // Staff are only told about their own requests; approvers about every one they can review.
        $pendingRequests = StockMovementRequest::pending()
            ->when(! $canApprove, fn ($query) => $query->where('requested_by', $user->id))
            ->count();

        if ($pendingRequests > 0) {
            $attentionItems[] = [
                'tone' => 'info',
                'title' => $canApprove
                    ? $pendingRequests . ' stock ' . ($pendingRequests === 1 ? 'request needs' : 'requests need') . ' your approval'
                    : $pendingRequests . ' of your stock ' . ($pendingRequests === 1 ? 'request is' : 'requests are') . ' waiting for approval',
                'detail' => $canApprove ? 'Submitted by staff.' : 'A manager or admin will review it.',
                'action' => $canApprove ? 'Review' : 'View',
                'url' => route('stock-movement-requests.index'),
            ];
        }

        if ($canApprove) {
            $ordersToApprove = PurchaseOrder::where('status', PurchaseOrder::STATUS_PENDING_APPROVAL)->count();

            if ($ordersToApprove > 0) {
                $attentionItems[] = [
                    'tone' => 'info',
                    'title' => $ordersToApprove . ' purchase ' . ($ordersToApprove === 1 ? 'order is' : 'orders are') . ' waiting for approval',
                    'detail' => 'Approve or reject before it is sent to the supplier.',
                    'action' => 'Review',
                    'url' => route('purchase-orders.index', ['status' => PurchaseOrder::STATUS_PENDING_APPROVAL]),
                ];
            }

            $ordersToReceive = PurchaseOrder::whereIn('status', [
                PurchaseOrder::STATUS_ORDERED,
                PurchaseOrder::STATUS_PARTIALLY_RECEIVED,
            ])->count();

            if ($ordersToReceive > 0) {
                $attentionItems[] = [
                    'tone' => 'neutral',
                    'title' => $ordersToReceive . ' purchase ' . ($ordersToReceive === 1 ? 'order is' : 'orders are') . ' on the way',
                    'detail' => 'Receive the stock when the delivery arrives.',
                    'action' => 'View orders',
                    'url' => route('purchase-orders.index', ['status' => PurchaseOrder::STATUS_ORDERED]),
                ];
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Transaction movement
        |--------------------------------------------------------------------------
        */

        $totalIn = InventoryTransaction::where(
            'type',
            'in'
        )->sum('base_quantity');

        $totalOut = InventoryTransaction::where(
            'type',
            'out'
        )->sum('base_quantity');


        /*
        |--------------------------------------------------------------------------
        | Net movement
        |--------------------------------------------------------------------------
        */

        $netMovement =
            (float) $totalIn -
            (float) $totalOut;


        /*
        |--------------------------------------------------------------------------
        | Transfer statistics
        |--------------------------------------------------------------------------
        */

        $totalTransferBaseQuantity =
            InventoryTransfer::sum('base_quantity');


        /*
        |--------------------------------------------------------------------------
        | Recent transactions
        |--------------------------------------------------------------------------
        |
        | Show only 10 records at a time.
        |
        | transactions_page is intentionally separate from
        | transfers_page so both tables can be paginated independently.
        |
        */

        $recentTransactions = InventoryTransaction::with([
            'product.baseUnit',
            'location',
            'productUnit.unitOfMeasure',
        ])
            ->latest('id')
            ->paginate(
                10,
                ['*'],
                'transactions_page'
            );


        /*
        |--------------------------------------------------------------------------
        | Recent transfers
        |--------------------------------------------------------------------------
        |
        | Show only 10 records at a time.
        |
        | transfers_page is intentionally separate from
        | transactions_page so both tables can be paginated independently.
        |
        */

        $recentTransfers = InventoryTransfer::with([
            'product.baseUnit',
            'sourceInventory.location',
            'destinationInventory.location',
            'productUnit.unitOfMeasure',
        ])
            ->latest('id')
            ->paginate(
                10,
                ['*'],
                'transfers_page'
            );


        /*
        |--------------------------------------------------------------------------
        | Chart data
        |--------------------------------------------------------------------------
        |
        | Display only the last 7 calendar days.
        |
        | SQL Server does not support DATE(created_at).
        | CAST(created_at AS DATE) is used instead.
        |
        */

        $chartStartDate = now()
            ->startOfDay()
            ->subDays(6);

        $chartEndDate = now()
            ->endOfDay();


        $chartTransactions = InventoryTransaction::query()
            ->whereBetween(
                'created_at',
                [
                    $chartStartDate,
                    $chartEndDate,
                ]
            )
            ->selectRaw(
                'CAST(created_at AS DATE) as transaction_date'
            )
            ->selectRaw(
                "SUM(CASE WHEN type = 'in' THEN base_quantity ELSE 0 END) as total_in"
            )
            ->selectRaw(
                "SUM(CASE WHEN type = 'out' THEN base_quantity ELSE 0 END) as total_out"
            )
            ->groupByRaw(
                'CAST(created_at AS DATE)'
            )
            ->orderByRaw(
                'CAST(created_at AS DATE)'
            )
            ->get();


        $chartDates = $chartTransactions
            ->pluck('transaction_date')
            ->map(function ($date) {
                return (string) $date;
            })
            ->values()
            ->toArray();


        $chartIn = $chartTransactions
            ->pluck('total_in')
            ->map(function ($value) {
                return (float) $value;
            })
            ->values()
            ->toArray();


        $chartOut = $chartTransactions
            ->pluck('total_out')
            ->map(function ($value) {
                return (float) $value;
            })
            ->values()
            ->toArray();


        /*
        |--------------------------------------------------------------------------
        | Dashboard
        |--------------------------------------------------------------------------
        */

        return view(
            'dashboard',
            compact(
                'totalProducts',
                'totalInventory',
                'totalLocations',
                'totalTransactions',
                'totalTransfers',
                'totalBaseStock',
                'outOfStockInventories',
                'outOfStockCount',
                'criticalStockInventories',
                'criticalStockCount',
                'lowStockInventories',
                'lowStockCount',
                'stockAlertCount',
                'totalIn',
                'totalOut',
                'netMovement',
                'totalTransferBaseQuantity',
                'recentTransactions',
                'recentTransfers',
                'chartTransactions',
                'chartDates',
                'chartIn',
                'chartOut',
                'notifications',
                'unreadNotificationsCount',
                'attentionItems'
            )
        );
    }
}
