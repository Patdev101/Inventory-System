<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\InventoryTransaction;
use App\Models\InventoryTransfer;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(): View
    {
        return view('reports.index', [
            'outOfStockCount' => Inventory::outOfStock()->count(),
            'criticalStockCount' => Inventory::criticalStock()->count(),
            'lowStockCount' => Inventory::lowStock()->count(),
        ]);
    }

    public function stockMovements(Request $request): View
    {
        $filters = $request->only(['type', 'from', 'to', 'search']);

        $transactions = InventoryTransaction::with([
            'product',
            'location',
            'productUnit.unitOfMeasure',
        ])
            ->when($filters['type'] ?? null, function ($query, $type) {
                $query->where('type', $type);
            })
            ->when($filters['from'] ?? null, function ($query, $from) {
                $query->whereDate('created_at', '>=', $from);
            })
            ->when($filters['to'] ?? null, function ($query, $to) {
                $query->whereDate('created_at', '<=', $to);
            })
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->whereHas('product', function ($query) use ($search) {
                        $query->where('name', 'like', '%' . $search . '%')
                            ->orWhere('sku', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('location', function ($query) use ($search) {
                        $query->where('name', 'like', '%' . $search . '%');
                    });
                });
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('reports.stock-movements', compact('transactions', 'filters'));
    }

    public function transfers(Request $request): View
    {
        $filters = $request->only(['from', 'to', 'search']);

        $transfers = InventoryTransfer::with([
            'product',
            'sourceInventory.location',
            'destinationInventory.location',
            'productUnit.unitOfMeasure',
        ])
            ->when($filters['from'] ?? null, function ($query, $from) {
                $query->whereDate('created_at', '>=', $from);
            })
            ->when($filters['to'] ?? null, function ($query, $to) {
                $query->whereDate('created_at', '<=', $to);
            })
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->whereHas('product', function ($query) use ($search) {
                    $query->where('name', 'like', '%' . $search . '%');
                });
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('reports.transfers', compact('transfers', 'filters'));
    }

    public function lowStock(Request $request): View
    {
        $status = $request->query('status', 'all');

        $query = Inventory::with(['product', 'location']);

        $query = match ($status) {
            'out_of_stock' => $query->outOfStock(),
            'critical' => $query->criticalStock(),
            'low' => $query->lowStock(),
            default => $query->where(function ($query) {
                $query->where('base_quantity', '<=', 0)
                    ->orWhere(function ($query) {
                        $query->where('base_quantity', '>', 0)
                            ->whereHas('product', function ($query) {
                                $query->whereNotNull('reorder_point')
                                    ->where('reorder_point', '>', 0)
                                    ->whereColumn(
                                        'inventories.base_quantity',
                                        '<=',
                                        'products.reorder_point'
                                    );
                            });
                    });
            }),
        };

        $inventories = $query
            ->orderBy('base_quantity')
            ->paginate(20)
            ->withQueryString();

        return view('reports.low-stock', compact('inventories', 'status'));
    }
}
