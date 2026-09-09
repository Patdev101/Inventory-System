<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PurchaseOrderEmailController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\InventoryTransactionController;
use App\Http\Controllers\InventoryTransferController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\ProductCategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\StockAlertController;
use App\Http\Controllers\StockMovementRequestController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UnitOfMeasureController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;


/*
|--------------------------------------------------------------------------
| Public Routes
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
*/

Route::get('/forgot-password', [
    PasswordResetController::class,
    'create',
])->name('password.request');


/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Dashboard
    |--------------------------------------------------------------------------
    */

    Route::get('/dashboard', [
        DashboardController::class,
        'index',
    ])->name('dashboard');


    /*
    |--------------------------------------------------------------------------
    | Notifications
    |--------------------------------------------------------------------------
    */

    Route::patch('/notifications/read-all', [
        NotificationController::class,
        'readAll',
    ])->name('notifications.read-all');

    Route::patch('/notifications/{notification}/read', [
        NotificationController::class,
        'read',
    ])->name('notifications.read');


    /*
    |--------------------------------------------------------------------------
    | My Account
    |--------------------------------------------------------------------------
    */

    Route::get('/account', [
        AccountController::class,
        'edit',
    ])->name('account.edit');

    Route::put('/account/name', [
        AccountController::class,
        'updateName',
    ])->name('account.name.update');

    Route::put('/account/email', [
        AccountController::class,
        'updateEmail',
    ])->name('account.email.update');

    Route::put('/account/password', [
        AccountController::class,
        'updatePassword',
    ])->name('account.password.update');

    Route::post('/account/test-email', [
        AccountController::class,
        'sendTestEmail',
    ])->name('account.test-email');


    /*
    |--------------------------------------------------------------------------
    | Stock Alerts
    |--------------------------------------------------------------------------
    */

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
    */

    Route::middleware('role:admin,manager')->group(function () {

        Route::resource('users', UserController::class)
            ->only([
                'index',
                'create',
                'store',
                'edit',
                'update',
            ]);

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
    | Stock Movement Requests
    |--------------------------------------------------------------------------
    */

    Route::get('/stock-movement-requests', [
        StockMovementRequestController::class,
        'index',
    ])->name('stock-movement-requests.index');

    Route::middleware('role:admin,manager')->group(function () {

        Route::patch(
            '/stock-movement-requests/{stockMovementRequest}/approve',
            [
                StockMovementRequestController::class,
                'approve',
            ]
        )->name('stock-movement-requests.approve');

        Route::patch(
            '/stock-movement-requests/{stockMovementRequest}/reject',
            [
                StockMovementRequestController::class,
                'reject',
            ]
        )->name('stock-movement-requests.reject');

    });


    /*
    |--------------------------------------------------------------------------
    | Reports
    |--------------------------------------------------------------------------
    */

    Route::prefix('reports')
        ->name('reports.')
        ->group(function () {

            Route::get('/', [
                ReportController::class,
                'index',
            ])->name('index');

            Route::get('/stock-movements', [
                ReportController::class,
                'stockMovements',
            ])->name('stock-movements');

            Route::get('/transfers', [
                ReportController::class,
                'transfers',
            ])->name('transfers');

            Route::get('/low-stock', [
                ReportController::class,
                'lowStock',
            ])->name('low-stock');

        });


    /*
    |--------------------------------------------------------------------------
    | Companies
    |--------------------------------------------------------------------------
    */

    Route::middleware('role:admin')->group(function () {

        Route::resource('companies', CompanyController::class)
            ->except([
                'index',
                'show',
            ]);

        Route::get(
            '/companies-trashed',
            [CompanyController::class, 'trashed']
        )->name('companies.trashed');

        Route::patch(
            '/companies/{company}/restore',
            [CompanyController::class, 'restore']
        )->name('companies.restore');

    });

    Route::resource('companies', CompanyController::class)
        ->only([
            'index',
            'show',
        ]);


    /*
    |--------------------------------------------------------------------------
    | Product Categories
    |--------------------------------------------------------------------------
    */

    Route::middleware('role:admin')->group(function () {

        Route::resource(
            'product-categories',
            ProductCategoryController::class
        )->except([
            'index',
            'show',
        ]);

        Route::get(
            '/product-categories-trashed',
            [ProductCategoryController::class, 'trashed']
        )->name('product-categories.trashed');

        Route::patch(
            '/product-categories/{productCategory}/restore',
            [ProductCategoryController::class, 'restore']
        )->name('product-categories.restore');

    });

    Route::resource(
        'product-categories',
        ProductCategoryController::class
    )->only([
        'index',
        'show',
    ]);


    /*
    |--------------------------------------------------------------------------
    | Locations
    |--------------------------------------------------------------------------
    */

    Route::middleware('role:admin')->group(function () {

        Route::resource('locations', LocationController::class)
            ->except([
                'index',
                'show',
            ]);

        Route::get(
            '/locations-trashed',
            [LocationController::class, 'trashed']
        )->name('locations.trashed');

        Route::patch(
            '/locations/{location}/restore',
            [LocationController::class, 'restore']
        )->name('locations.restore');

    });

    Route::resource('locations', LocationController::class)
        ->only([
            'index',
            'show',
        ]);


    /*
    |--------------------------------------------------------------------------
    | Units of Measure
    |--------------------------------------------------------------------------
    */

    Route::middleware('role:admin')->group(function () {

        Route::resource(
            'units-of-measure',
            UnitOfMeasureController::class
        )->except([
            'index',
            'show',
        ]);

        Route::get(
            '/units-of-measure-trashed',
            [UnitOfMeasureController::class, 'trashed']
        )->name('units-of-measure.trashed');

        Route::patch(
            '/units-of-measure/{units_of_measure}/restore',
            [UnitOfMeasureController::class, 'restore']
        )->name('units-of-measure.restore');

    });

    Route::resource(
        'units-of-measure',
        UnitOfMeasureController::class
    )->only([
        'index',
        'show',
    ]);


    /*
    |--------------------------------------------------------------------------
    | Suppliers
    |--------------------------------------------------------------------------
    */

    Route::middleware('role:admin')->group(function () {

        Route::resource('suppliers', SupplierController::class)
            ->except([
                'index',
                'show',
                'destroy',
            ]);

        Route::patch(
            '/suppliers/{supplier}/deactivate',
            [
                SupplierController::class,
                'deactivate',
            ]
        )->name('suppliers.deactivate');

        Route::patch(
            '/suppliers/{supplier}/activate',
            [
                SupplierController::class,
                'activate',
            ]
        )->name('suppliers.activate');

    });

    Route::resource('suppliers', SupplierController::class)
        ->only([
            'index',
            'show',
        ]);


    /*
    |--------------------------------------------------------------------------
    | Products
    |--------------------------------------------------------------------------
    */

    Route::middleware('role:admin')->group(function () {

        Route::resource('products', ProductController::class)
            ->except([
                'index',
                'show',
            ]);

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
        ->only([
            'index',
            'show',
        ]);


    /*
    |--------------------------------------------------------------------------
    | Inventories
    |--------------------------------------------------------------------------
    */

    Route::middleware('role:admin,manager,staff')->group(function () {

        Route::resource('inventories', InventoryController::class)
            ->only([
                'create',
                'store',
                'edit',
                'update',
            ]);

        Route::post(
            '/inventories/{inventory}/request-transfer',
            [
                InventoryController::class,
                'requestTransfer',
            ]
        )->name('inventories.request-transfer');

    });

    Route::middleware('role:admin')->group(function () {

        Route::resource('inventories', InventoryController::class)
            ->only([
                'destroy',
            ]);

    });

    Route::resource('inventories', InventoryController::class)
        ->only([
            'index',
            'show',
        ]);


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
        [
            InventoryTransferController::class,
            'pendingAudits',
        ]
    )->name('inventory-transfers.pending-audits');

    Route::get(
        '/inventory-transfers/{transfer}',
        [
            InventoryTransferController::class,
            'show',
        ]
    )->name('inventory-transfers.show');


    /*
    |--------------------------------------------------------------------------
    | Audit / Receive Transfers
    |--------------------------------------------------------------------------
    */

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


    /*
    |--------------------------------------------------------------------------
    | Purchase Orders
    |--------------------------------------------------------------------------
    |
    | Admin, manager and staff can create/submit/order/receive.
    | Admin and manager can approve/reject.
    */

    Route::middleware('role:admin,manager,staff')->group(function () {

        /*
        |--------------------------------------------------------------------------
        | Create Purchase Order
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/purchase-orders/create',
            [
                PurchaseOrderController::class,
                'create',
            ]
        )->name('purchase-orders.create');


        /*
        |--------------------------------------------------------------------------
        | Supplier Products
        |--------------------------------------------------------------------------
        |
        | Returns ONLY active products belonging to the selected
        | supplier's company.
        |
        | IMPORTANT:
        | This route must appear before:
        |
        | /purchase-orders/{purchaseOrder}
        |
        | Otherwise Laravel may interpret "supplier-products" as
        | a purchase order ID.
        */

        Route::get(
            '/purchase-orders/supplier-products/{supplier}',
            [
                PurchaseOrderController::class,
                'supplierProducts',
            ]
        )->name('purchase-orders.supplier-products');


        /*
        |--------------------------------------------------------------------------
        | Store Purchase Order
        |--------------------------------------------------------------------------
        */

        Route::post(
            '/purchase-orders',
            [
                PurchaseOrderController::class,
                'store',
            ]
        )->name('purchase-orders.store');


        /*
        |--------------------------------------------------------------------------
        | Submit Purchase Order
        |--------------------------------------------------------------------------
        */

        Route::patch(
            '/purchase-orders/{purchaseOrder}/submit',
            [
                PurchaseOrderController::class,
                'submit',
            ]
        )->name('purchase-orders.submit');


        /*
        |--------------------------------------------------------------------------
        | Mark Ordered
        |--------------------------------------------------------------------------
        */

        Route::patch(
            '/purchase-orders/{purchaseOrder}/mark-ordered',
            [
                PurchaseOrderController::class,
                'markOrdered',
            ]
        )->name('purchase-orders.mark-ordered');


        /*
        |--------------------------------------------------------------------------
        | Receive Purchase Order
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/purchase-orders/{purchaseOrder}/receive',
            [
                PurchaseOrderController::class,
                'receiveForm',
            ]
        )->name('purchase-orders.receive.form');

        Route::patch(
            '/purchase-orders/{purchaseOrder}/receive',
            [
                PurchaseOrderController::class,
                'receive',
            ]
        )->name('purchase-orders.receive');

    });


    /*
    |--------------------------------------------------------------------------
    | Approve / Reject Purchase Orders
    |--------------------------------------------------------------------------
    */

    Route::middleware('role:admin,manager')->group(function () {

        Route::patch(
            '/purchase-orders/{purchaseOrder}/approve',
            [
                PurchaseOrderController::class,
                'approve',
            ]
        )->name('purchase-orders.approve');

        Route::patch(
            '/purchase-orders/{purchaseOrder}/reject',
            [
                PurchaseOrderController::class,
                'reject',
            ]
        )->name('purchase-orders.reject');

    });


    /*
    |--------------------------------------------------------------------------
    | Purchase Order View Routes
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/purchase-orders',
        [
            PurchaseOrderController::class,
            'index',
        ]
    )->name('purchase-orders.index');

    Route::get(
        '/purchase-orders/{purchaseOrder}',
        [
            PurchaseOrderController::class,
            'show',
        ]
    )->name('purchase-orders.show');

    Route::get(
        '/purchase-orders/{purchaseOrder}/pdf',
        [
            PurchaseOrderController::class,
            'downloadPdf',
        ]
    )->name('purchase-orders.pdf');

    Route::get(
        '/purchase-orders/{purchaseOrder}/email',
        [
            PurchaseOrderController::class,
            'composeEmail',
        ]
    )->name('purchase-orders.email.compose');

    Route::post(
        '/purchase-orders/{purchaseOrder}/email',
        [
            PurchaseOrderController::class,
            'sendEmail',
        ]
    )->name('purchase-orders.email.send');


    /*
    |--------------------------------------------------------------------------
    | Purchase Order Emails (global sent log)
    |--------------------------------------------------------------------------
    */

    Route::get('/purchase-order-emails', [
        PurchaseOrderEmailController::class,
        'index',
    ])->name('purchase-order-emails.index');

});
