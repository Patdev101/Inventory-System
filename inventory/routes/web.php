<?php

use App\Http\Controllers\AccountController;
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
use App\Http\Controllers\StockMovementRequestController;
use App\Http\Controllers\UnitOfMeasureController;
use App\Http\Controllers\UserController;
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
| Forgot Password
|--------------------------------------------------------------------------
|
| This is a local/internal system — there is no mail service wired up to
| deliver reset links. Password resets are always performed by an admin
| from User Management (see UserController::resetPassword). This route
| just shows guests where to go instead of a reset form.
*/

Route::get('/forgot-password', [
    PasswordResetController::class,
    'create',
])->name('password.request');

Route::middleware('auth')->group(function () {

Route::get('/dashboard', [
    DashboardController::class,
    'index',
])->name('dashboard');


/*
|--------------------------------------------------------------------------
| My Account
|--------------------------------------------------------------------------
|
| Every authenticated user manages their own email/password here,
| regardless of role. Changing another user's account never goes through
| these routes — only through Admin User Management below.
*/

Route::get('/account', [
    AccountController::class,
    'edit',
])->name('account.edit');

Route::put('/account/email', [
    AccountController::class,
    'updateEmail',
])->name('account.email.update');

Route::put('/account/password', [
    AccountController::class,
    'updatePassword',
])->name('account.password.update');

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
| Users
|--------------------------------------------------------------------------
|
| Admins may create manager and staff accounts. Managers may create
| staff accounts only. The role dropdown and the server-side
| validation are both driven by UserController::assignableRoles().
*/

Route::middleware('role:admin,manager')->group(function () {

    Route::resource('users', UserController::class)
        ->only(['index', 'create', 'store', 'edit', 'update']);

    Route::patch('/users/{user}/deactivate', [
        UserController::class,
        'deactivate',
    ])->name('users.deactivate');

    Route::patch('/users/{user}/activate', [
        UserController::class,
        'activate',
    ])->name('users.activate');

    Route::get('/users/{user}/reset-password', [
        UserController::class,
        'showResetPassword',
    ])->name('users.reset-password');

    Route::post('/users/{user}/reset-password', [
        UserController::class,
        'resetPassword',
    ])->name('users.reset-password.store');

});


/*
|--------------------------------------------------------------------------
| Stock Movement Requests (staff approval queue)
|--------------------------------------------------------------------------
|
| Staff cannot move stock directly - their stock-in/out submissions
| (see InventoryController@store / @update) are queued here instead.
| Staff may view this page too (scoped to their own submissions in
| StockMovementRequestController@index, for transparency into what
| they requested and whether it was approved/rejected). Only
| admin/manager may approve or reject.
*/

Route::get('/stock-movement-requests', [
    StockMovementRequestController::class,
    'index',
])->name('stock-movement-requests.index');

Route::middleware('role:admin,manager')->group(function () {

    Route::patch('/stock-movement-requests/{stockMovementRequest}/approve', [
        StockMovementRequestController::class,
        'approve',
    ])->name('stock-movement-requests.approve');

    Route::patch('/stock-movement-requests/{stockMovementRequest}/reject', [
        StockMovementRequestController::class,
        'reject',
    ])->name('stock-movement-requests.reject');

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
| stock movements are open to staff too, but a staff-submitted
| movement is queued as a StockMovementRequest pending manager/admin
| approval rather than applied immediately (see InventoryController).
| Deleting an inventory record is admin-only.
|
| Registered before the view-only routes for the same reason as above:
| "inventories/create" must be matched before "inventories/{inventory}".
*/

Route::middleware('role:admin,manager,staff')->group(function () {

    Route::resource('inventories', InventoryController::class)
        ->only(['create', 'store', 'edit', 'update']);

    Route::post(
        '/inventories/{inventory}/request-transfer',
        [InventoryController::class, 'requestTransfer']
    )->name('inventories.request-transfer');

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
|
| Auditing and receiving a transfer (the two-phase QA workflow) is
| restricted to admin/manager/staff — the same set of roles that can
| be assigned as a receiver — and must stay inside the auth group.
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
    '/inventory-transfers/pending-audits',
    [InventoryTransferController::class, 'pendingAudits']
)->name('inventory-transfers.pending-audits');

Route::get(
    '/inventory-transfers/{transfer}',
    [
        InventoryTransferController::class,
        'show',
    ]
)->name('inventory-transfers.show');

Route::middleware('role:admin,manager,staff')->group(function () {

    Route::patch(
        '/inventory-transfers/{transfer}/audit',
        [
            InventoryTransferController::class,
            'audit',
        ]
    )->name('inventory-transfers.audit');

    Route::patch(
        '/inventory-transfers/{transfer}/receive',
        [
            InventoryTransferController::class,
            'receive',
        ]
    )->name('inventory-transfers.receive');

});

});