<?php

namespace App\Http\Controllers;

use App\Models\StockAlert;
use App\Services\StockAlertService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StockAlertController extends Controller
{
    public function index(StockAlertService $service): View
    {
        $service->synchronize();

        $alerts = StockAlert::with([
            'inventory.product',
            'inventory.location',
            'acknowledgedBy',
        ])
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('stock-alerts.index', compact('alerts'));
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
