<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\InventoryTransaction;
use App\Models\InventoryTransfer;
use App\Models\Product;
use App\Models\Location;
use App\Services\StockAlertService;

class DashboardController extends Controller
{
    /**
     * Display the inventory dashboard.
     */
    public function index(StockAlertService $stockAlertService)
    {
        $stockAlertService->synchronize();

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
            'product',
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
            'product',
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
            'product',
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
            'product',
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
            'product',
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
                'chartOut'
            )
        );
    }
}
