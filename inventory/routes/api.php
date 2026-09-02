<?php

use App\Models\Product;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\InventoryApiController;


Route::middleware('inventory.api-token')->group(function () {

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
            ->map(function (Product $product) {
                $product->stock_quantity = $product->inventories
                    ->sum('base_quantity');

                return $product;
            });
    });

    Route::get('/locations', function () {
        return Location::query()
            ->orderBy('name')
            ->get(['id', 'name', 'code']);
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
