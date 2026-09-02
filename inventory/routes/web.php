<?php

use App\Http\Controllers\CompanyController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\InventoryTransactionController;
use App\Http\Controllers\InventoryTransferController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\ProductCategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\UnitOfMeasureController;
use App\Http\Controllers\StockAlertController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;


/*
|--------------------------------------------------------------------------
| Dashboard
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::get('/login', [
    AuthController::class,
    'create',
])->name('login');

Route::post('/login', [
    AuthController::class,
    'store',
])->name('login.store');

Route::post('/logout', [
    AuthController::class,
    'destroy',
])->middleware('auth')->name('logout');


/*
|--------------------------------------------------------------------------
| Password Reset
|--------------------------------------------------------------------------
*/

Route::get('/forgot-password', [
    PasswordResetController::class,
    'create',
])->name('password.request');

Route::post('/forgot-password', [
    PasswordResetController::class,
    'store',
])->name('password.email');

Route::get('/reset-password/{token}', [
    PasswordResetController::class,
    'edit',
])->name('password.reset');

Route::post('/reset-password', [
    PasswordResetController::class,
    'update',
])->name('password.update');

Route::middleware('auth')->group(function () {

Route::get('/dashboard', [
    DashboardController::class,
    'index',
])->name('dashboard');

Route::get('/stock-alerts', [
    StockAlertController::class,
    'index',
])->name('stock-alerts.index');

Route::middleware('role:admin,manager')->group(function () {

    Route::patch('/stock-alerts/{stockAlert}/acknowledge', [
        StockAlertController::class,
        'acknowledge',
    ])->name('stock-alerts.acknowledge');

    Route::patch('/stock-alerts/{stockAlert}/resolve', [
        StockAlertController::class,
        'resolve',
    ])->name('stock-alerts.resolve');

});


/*
|--------------------------------------------------------------------------
| Reports
|--------------------------------------------------------------------------
*/

Route::prefix('reports')->name('reports.')->group(function () {

    Route::get('/', [ReportController::class, 'index'])
        ->name('index');

    Route::get('/stock-movements', [ReportController::class, 'stockMovements'])
        ->name('stock-movements');

    Route::get('/transfers', [ReportController::class, 'transfers'])
        ->name('transfers');

    Route::get('/low-stock', [ReportController::class, 'lowStock'])
        ->name('low-stock');

});


/*
|--------------------------------------------------------------------------
| Companies, Product Categories, Locations, Units of Measure
|--------------------------------------------------------------------------
|
| These are master/reference data. Anyone signed in may view them;
| only admins may create, edit, or delete them.
|
| The admin-only routes (which include the static "create" segment)
| are registered before the view-only routes, so that GET
| "companies/create" is matched before the "companies/{company}"
| wildcard used by show. Registering them in the opposite order
| would let the {company} route-model binding swallow "create" and
| return a 404 instead of reaching the create route.
*/

Route::middleware('role:admin')->group(function () {

    Route::resource('companies', CompanyController::class)
        ->except(['index', 'show']);

    Route::resource('product-categories', ProductCategoryController::class)
        ->except(['index', 'show']);

    Route::resource('locations', LocationController::class)
        ->except(['index', 'show']);

    Route::resource('units-of-measure', UnitOfMeasureController::class)
        ->except(['index', 'show']);

});

Route::resource('companies', CompanyController::class)
    ->only(['index', 'show']);

Route::resource('product-categories', ProductCategoryController::class)
    ->only(['index', 'show']);

Route::resource('locations', LocationController::class)
    ->only(['index', 'show']);

Route::resource('units-of-measure', UnitOfMeasureController::class)
    ->only(['index', 'show']);


/*
|--------------------------------------------------------------------------
| Products
|--------------------------------------------------------------------------
|
| Viewing products is open to any signed-in user. Managing the product
| catalog (create/edit/delete/activate/deactivate) is admin-only.
|
| Registered before the view-only routes for the same reason as above:
| "products/create" must be matched before "products/{product}".
*/

Route::middleware('role:admin')->group(function () {

    Route::resource('products', ProductController::class)
        ->except(['index', 'show']);

    Route::patch(
        '/products/{product}/deactivate',
        [
            ProductController::class,
            'deactivate',
        ]
    )->name('products.deactivate');

    Route::patch(
        '/products/{product}/activate',
        [
            ProductController::class,
            'activate',
        ]
    )->name('products.activate');

});

Route::resource('products', ProductController::class)
    ->only(['index', 'show']);


/*
|--------------------------------------------------------------------------
| Inventories
|--------------------------------------------------------------------------
|
| Viewing inventory is open to any signed-in user. Adding stock and
| stock movements require manager or admin. Deleting an inventory
| record is admin-only.
|
| Registered before the view-only routes for the same reason as above:
| "inventories/create" must be matched before "inventories/{inventory}".
*/

Route::middleware('role:admin,manager')->group(function () {

    Route::resource('inventories', InventoryController::class)
        ->only(['create', 'store', 'edit', 'update']);

});

Route::middleware('role:admin')->group(function () {

    Route::resource('inventories', InventoryController::class)
        ->only(['destroy']);

});

Route::resource('inventories', InventoryController::class)
    ->only(['index', 'show']);


/*
|--------------------------------------------------------------------------
| Inventory Transactions
|--------------------------------------------------------------------------
*/

Route::get(
    '/inventory-transactions',
    [
        InventoryTransactionController::class,
        'index',
    ]
)->name('inventory-transactions.index');

Route::get(
    '/inventory-transactions/{transaction}',
    [
        InventoryTransactionController::class,
        'show',
    ]
)->name('inventory-transactions.show');


/*
|--------------------------------------------------------------------------
| Inventory Transfers
|--------------------------------------------------------------------------
|
| Viewing transfer history is open to any signed-in user. Creating a
| transfer requires manager or admin.
|
| Registered before the show route for the same reason as above:
| "inventory-transfers/create" must be matched before
| "inventory-transfers/{transfer}".
*/

Route::middleware('role:admin,manager')->group(function () {

    Route::get(
        '/inventory-transfers/create',
        [
            InventoryTransferController::class,
            'create',
        ]
    )->name('inventory-transfers.create');

    Route::post(
        '/inventory-transfers',
        [
            InventoryTransferController::class,
            'store',
        ]
    )->name('inventory-transfers.store');

});

Route::get(
    '/inventory-transfers',
    [
        InventoryTransferController::class,
        'index',
    ]
)->name('inventory-transfers.index');

Route::get(
    '/inventory-transfers/{transfer}',
    [
        InventoryTransferController::class,
        'show',
    ]
)->name('inventory-transfers.show');

});
