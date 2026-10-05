<?php

use App\Models\Product;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\InventoryApiController;


Route::middleware(['inventory.api-token', 'throttle:120,1'])->group(function () {

    Route::get('/config', function () {
        return [
            'vat_rate' => (float) config('pricing.vat_rate'),
        ];
    });

    Route::get('/products', function (Request $request) {
        $search = trim((string) $request->query('search', ''));

        return Product::query()
            ->where('is_active', true)
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', '%' . $search . '%')
                        ->orWhere('sku', 'like', '%' . $search . '%');
                });
            })
            ->with([
                'category',
                'baseUnit',
                'productUnits.unitOfMeasure',
                'inventories.location',
            ])
            ->get()
            // Only what the POS needs to sell — cost, markup and profit
            // stay inside the Inventory system.
            ->map(fn (Product $product) => [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'barcode' => $product->barcode,
                'image_url' => $product->image_url,
                'selling_price' => $product->selling_price,
                'is_active' => $product->is_active,
                'stock_quantity' => $product->inventories->sum('base_quantity'),
                'category' => $product->category?->only(['id', 'name']),
                'base_unit' => $product->baseUnit?->only(['id', 'name', 'code']),
                'product_units' => $product->productUnits->map(fn ($unit) => [
                    'id' => $unit->id,
                    'conversion_factor' => $unit->conversion_factor,
                    'is_default' => (bool) $unit->is_default,
                    'unit_of_measure' => $unit->unitOfMeasure?->only(['id', 'name', 'code']),
                ])->values(),
                'inventories' => $product->inventories->map(fn ($inventory) => [
                    'location_id' => $inventory->location_id,
                    'base_quantity' => $inventory->base_quantity,
                    'location' => $inventory->location?->only(['id', 'name', 'code']),
                ])->values(),
            ]);
    });

    Route::get('/locations', function () {
        return Location::query()
            ->with('company:id,name')
            ->orderBy('name')
            ->get(['id', 'name', 'code', 'company_id'])
            ->map(fn (Location $location) => [
                'id' => $location->id,
                'name' => $location->name,
                'code' => $location->code,
                'company' => $location->company?->only(['id', 'name']),
            ]);
    });

    Route::post(
        '/inventory/out',
        [InventoryApiController::class, 'remove']
    );

    Route::post(
        '/inventory/in',
        [InventoryApiController::class, 'add']
    );

});
