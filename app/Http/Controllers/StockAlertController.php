<?php

namespace App\Http\Controllers;

use App\Models\StockAlert;
use App\Services\StockAlertService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StockAlertController extends Controller
{
    public function index(Request $request, StockAlertService $service): View
    {
        $service->synchronize();

        $status = $request->query('status', 'active');
        $severity = $request->query('severity');

        $alerts = StockAlert::with([
            'inventory.product',
            'inventory.location',
            'acknowledgedBy',
        ])
            ->when($status === 'active', function ($query) {
                $query->whereIn('status', ['open', 'acknowledged']);
            })
            ->when(
                in_array($status, ['open', 'acknowledged', 'resolved'], true),
                function ($query) use ($status) {
                    $query->where('status', $status);
                }
            )
            ->when($severity, function ($query) use ($severity) {
                $query->where('severity', $severity);
            })
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        $counts = [
            'open' => StockAlert::where('status', 'open')->count(),
            'acknowledged' => StockAlert::where('status', 'acknowledged')->count(),
            'resolved' => StockAlert::where('status', 'resolved')->count(),
            'out_of_stock' => StockAlert::active()->where('severity', 'out_of_stock')->count(),
            'critical' => StockAlert::active()->where('severity', 'critical')->count(),
            'low' => StockAlert::active()->where('severity', 'low')->count(),
        ];

        return view(
            'stock-alerts.index',
            compact('alerts', 'counts', 'status', 'severity')
        );
    }

    public function acknowledge(
        Request $request,
        StockAlert $stockAlert,
        StockAlertService $service
    ): RedirectResponse {
        $service->acknowledge($stockAlert, $request->user()?->id);

        return back()->with('success', 'Stock alert acknowledged.');
    }

    public function resolve(
        StockAlert $stockAlert,
        StockAlertService $service
    ): RedirectResponse {
        $service->resolve($stockAlert);

        return back()->with('success', 'Stock alert resolved.');
    }
}
