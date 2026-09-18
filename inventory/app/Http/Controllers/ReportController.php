<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\InventoryTransaction;
use App\Models\InventoryTransfer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

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

        $transactions = $this->stockMovementsQuery($filters)
            ->paginate(20)
            ->withQueryString();

        return view('reports.stock-movements', compact('transactions', 'filters'));
    }

    public function transfers(Request $request): View
    {
        $filters = $request->only(['from', 'to', 'search']);

        $transfers = $this->transfersQuery($filters)
            ->paginate(20)
            ->withQueryString();

        return view('reports.transfers', compact('transfers', 'filters'));
    }

    public function lowStock(Request $request): View
    {
        $status = $request->query('status', 'all');

        $inventories = $this->lowStockQuery($status)
            ->orderBy('base_quantity')
            ->paginate(20)
            ->withQueryString();

        return view('reports.low-stock', compact('inventories', 'status'));
    }

    public function exportStockMovements(Request $request): StreamedResponse
    {
        $filters = $request->only(['type', 'from', 'to', 'search']);
        $transactions = $this->stockMovementsQuery($filters)->get();

        return $this->streamCsv('stock-movements', ['Date', 'Type', 'Product', 'Location', 'Quantity', 'Unit', 'Base Quantity', 'Reference'], $transactions, function ($transaction) {
            return [
                format_datetime($transaction->created_at),
                $transaction->getDirectionLabelAttribute(),
                $transaction->product?->name ?? 'Deleted product',
                $transaction->location?->name ?? 'Deleted location',
                format_qty((float) $transaction->quantity),
                $transaction->productUnit?->unitOfMeasure?->code ?? '',
                format_qty((float) $transaction->base_quantity),
                $transaction->reference ?: '',
            ];
        });
    }

    public function exportTransfers(Request $request): StreamedResponse
    {
        $filters = $request->only(['from', 'to', 'search']);
        $transfers = $this->transfersQuery($filters)->get();

        return $this->streamCsv('transfers', ['Date', 'Product', 'From', 'To', 'Quantity', 'Unit', 'Reference'], $transfers, function ($transfer) {
            return [
                format_datetime($transfer->created_at),
                $transfer->product?->name ?? 'Deleted product',
                $transfer->sourceInventory?->location?->name ?? '',
                $transfer->destinationInventory?->location?->name ?? '',
                format_qty((float) $transfer->quantity),
                $transfer->productUnit?->unitOfMeasure?->code ?? '',
                $transfer->reference ?: '',
            ];
        });
    }

    public function exportLowStock(Request $request): StreamedResponse
    {
        $status = $request->query('status', 'all');
        $inventories = $this->lowStockQuery($status)->orderBy('base_quantity')->get();

        return $this->streamCsv('low-stock', ['Product', 'Location', 'Base Quantity', 'Reorder Point', 'Status'], $inventories, function ($inventory) {
            return [
                $inventory->product?->name ?? 'Deleted product',
                $inventory->location?->name ?? 'Deleted location',
                format_qty($inventory->getBaseQuantityValue()),
                format_qty($inventory->getReorderPointValue()),
                $inventory->getStockStatus(),
            ];
        });
    }

    private function stockMovementsQuery(array $filters)
    {
        return InventoryTransaction::with([
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
            ->latest();
    }

    private function transfersQuery(array $filters)
    {
        return InventoryTransfer::with([
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
            ->latest();
    }

    private function lowStockQuery(string $status)
    {
        $query = Inventory::with(['product', 'location']);

        return match ($status) {
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
    }

    /**
     * Streams a CSV so a large report doesn't have to be built in memory
     * first — rows are written to the output buffer as they're read.
     * Exports the full filtered result set, not just the current page.
     */
    private function streamCsv(string $filename, array $headers, iterable $rows, callable $mapRow): StreamedResponse
    {
        $callback = function () use ($headers, $rows, $mapRow) {
            $handle = fopen('php://output', 'w');
            // Leading BOM so Excel opens UTF-8 CSVs without mangling special characters.
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, $headers);

            foreach ($rows as $row) {
                fputcsv($handle, $mapRow($row));
            }

            fclose($handle);
        };

        return Response::stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '-' . now()->format('Y-m-d') . '.csv"',
        ]);
    }
}
