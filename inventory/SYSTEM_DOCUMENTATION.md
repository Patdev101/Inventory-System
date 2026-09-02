# Inventory ERP System Documentation

**Project:** Inventory Management / ERP System  
**Framework:** Laravel 12  
**Database:** SQL Server  
**Project path:** `C:\projects\inventory\inventory`  
**Documentation date:** 2026-09-02

## 1. System Purpose

This application is an inventory and warehouse management system. It manages products, product-specific units, storage locations, stock quantities, inventory movements, transfers, stock alerts, and audit history.

The application uses Laravel controllers, Eloquent models, Blade views, migrations, and vanilla JavaScript. No frontend framework is required for the existing workflows.

All application pages use a shared dashboard-style shell: a fixed left sidebar with the Inventory System brand and navigation, a signed-in user profile and Sign Out control at the bottom, a light-gray page background, white content cards, blue primary actions, consistent tables/forms/badges, and a responsive mobile navigation button. The reusable shell is defined in `resources/views/layouts/sidebar.blade.php` and `resources/views/layouts/app.blade.php`.

The system is functional for local development and controlled internal operations. The core inventory workflow is now largely complete, and the remaining gap to full enterprise readiness is mostly in permissions, reporting, procurement/sales extensions, and production deployment hardening.

---

## Known Inventory Limitations and Risks

The inventory application is functional for warehouse operations, but it is
not yet a complete enterprise ERP. Known limitations include:

- Procurement, vendor, receiving, and landed-cost workflows are not complete.
- Inventory valuation, costing, cost of goods, and profit reporting are
  limited.
- Manufacturing, production, and bill-of-materials workflows are not
  included.
- Location records and transfers exist, but complete user-to-location
  authorization and approval controls need more work.
- There is no offline warehouse operation or synchronization mode.
- Barcode scanning, mobile warehouse workflows, label printing, and hardware
  integrations are limited.
- Advanced aging, demand forecasting, and valuation reports remain future
  work.
- Scheduler, queue, mail, monitoring, backup, and disaster-recovery setup
  require production configuration.
- API rate limiting, retry behavior, versioning, bulk imports, and duplicate
  data prevention need additional hardening.

The highest-priority risks are costing gaps, deployment controls, location
authorization, reconciliation, backup/recovery, and integration hardening.

## 2. Current Maturity Summary

### Functional completion estimate

The warehouse and stock-management foundation is approximately 90-95% complete for the current scope. The system is stable for day-to-day inventory operations, stock monitoring, transfers, and alert handling.

### Verified status

- Core inventory CRUD and stock movement flows are implemented.
- Base-unit conversion and stock classification rules are active.
- Persistent alert system is implemented and scheduled.
- Authentication is in place for protected routes.
- Automated validation currently passes: 9 tests, 11 assertions.
- The authenticated product API includes each inventory record's related
  location data for POS stock-by-location display.
- The authenticated `GET /api/locations` endpoint returns location IDs, names,
  and codes for POS selling-location selection.
- POS installations can use this endpoint to resolve an administrator-defined
  fixed selling location; cashiers should not switch locations during normal
  sales.

### Remaining priority work

- Role-based permissions and stronger user governance
- Reporting and analytics layer
- Procurement and receiving workflows
- Sales/warehouse integration features
- Production-hardening and operational deployment controls

## 3. Core Inventory Principle

`inventories.base_quantity` is the authoritative inventory quantity.

Users may enter and view stock using different product units, but all stock calculations must use base units.

Example:

- Product: Rice
- Base unit: KG
- Alternate unit: BAG
- Conversion: 1 BAG = 25 KG
- Entered quantity: 2 BAG
- Stored base quantity: 50 KG

The stored values represent:

```text
quantity          = 2
product_unit      = BAG
conversion_factor = 25
base_quantity     = 50
```

Never compare a displayed quantity directly with a reorder point unless both values are confirmed to use the same unit.

## 4. Domain Relationships

```text
Company
  |
  +-- Locations

ProductCategory
  |
  +-- Products
        |
        +-- ProductUnits
              |
              +-- UnitOfMeasure

Product
  |
  +-- Inventories
        |
        +-- InventoryTransactions

InventoryTransfer
  +-- Source Inventory
  +-- Destination Inventory
  +-- Product
  +-- ProductUnit

Inventory
  +-- StockAlerts
```

## 5. Main Models

### User

Location: `app/Models/User.php`

The user model uses Laravel authentication and notifications. It supports session login, database notifications, and email notifications.

### Company

Location: `app/Models/Company.php`

Represents a company or business entity. A company can have multiple locations.

### Location

Location: `app/Models/Location.php`

Represents a warehouse or storage location. Locations belong to companies and are used by inventory records.

### ProductCategory

Location: `app/Models/ProductCategory.php`

Groups related products.

### Product

Location: `app/Models/Product.php`

Products contain product information, company/category relationships, base unit, reorder point, active state, product units, inventories, and transactions.

Important fields include:

- `name`
- `sku`
- `product_category_id`
- `company_id`
- `base_unit_id`
- `reorder_point`
- `is_active`

`reorder_point` is stored in base units.

### UnitOfMeasure

Location: `app/Models/UnitOfMeasure.php`

Stores global units such as PCS, BOX, KG, BAG, L, and CASE.

### ProductUnit

Location: `app/Models/ProductUnit.php`

Connects a product to a unit of measure and stores the conversion factor for that product.

Important fields:

- `product_id`
- `unit_of_measure_id`
- `conversion_factor`
- `is_default`

A product may have multiple product units. Conversion factors are product-specific.

### Inventory

Location: `app/Models/Inventory.php`

Represents the stock of one product at one location.

Important fields:

- `product_id`
- `location_id`
- `product_unit_id`
- `quantity`
- `conversion_factor`
- `base_quantity`

The model contains the centralized stock rules and query scopes:

- `getBaseQuantityValue()`
- `getQuantityValue()`
- `getReorderPointValue()`
- `getStockStatus()`
- `isOutOfStock()`
- `isCriticalStock()`
- `isLowStock()`
- `isInStock()`
- `outOfStock()` query scope
- `criticalStock()` query scope
- `lowStock()` query scope

### InventoryTransaction

Location: `app/Models/InventoryTransaction.php`

Stores every stock movement for audit purposes.

Important fields:

- `inventory_id`
- `product_id`
- `location_id`
- `type`
- `quantity`
- `base_quantity`
- `product_unit_id`
- `conversion_factor`
- `reference`
- `notes`

Transaction helpers include signed quantity and direction methods. IN movements are positive and OUT movements are negative.

Transaction records preserve product and location references directly so historical records remain useful even if an inventory record is removed.

### InventoryTransfer

Location: `app/Models/InventoryTransfer.php`

Represents stock moved between two inventory locations.

Important fields:

- `source_inventory_id`
- `destination_inventory_id`
- `product_id`
- `product_unit_id`
- `conversion_factor`
- `quantity`
- `base_quantity`
- `reference`
- `notes`

### StockAlert

Location: `app/Models/StockAlert.php`

Stores persistent stock alert history.

Important fields:

- `inventory_id`
- `severity`
- `status`
- `base_quantity`
- `reorder_point`
- `acknowledged_by`
- `acknowledged_at`
- `resolved_at`

Supported statuses:

- `open`
- `acknowledged`
- `resolved`

## 6. Stock Status Rules

All status decisions use `base_quantity` and `product.reorder_point`.

```text
base_quantity <= 0
    Out of Stock

base_quantity > 0 and reorder_point > 0
and base_quantity <= reorder_point / 2
    Critical

base_quantity > reorder_point / 2
and base_quantity <= reorder_point
    Low Stock

base_quantity > reorder_point
    In Stock

reorder_point <= 0 and base_quantity > 0
    In Stock
```

Out of Stock has the highest alert priority, followed by Critical and Low Stock.

## 7. Inventory Creation Workflow

View: `resources/views/inventories/create.blade.php`  
Controller: `app/Http/Controllers/InventoryController.php`

1. Select an active product.
2. Select a location.
3. Select a product-specific unit.
4. Enter a positive quantity.
5. The interface displays the conversion factor and calculated base quantity.
6. The backend validates that the product unit belongs to the selected product.
7. The backend calculates `quantity * conversion_factor`.
8. If the product/location inventory exists, its base quantity is increased.
9. Otherwise, a new inventory record is created.
10. An IN transaction is created.

Inventory changes occur inside a database transaction and use row locking when updating an existing inventory record.

## 8. Stock Adjustment Workflow

View: `resources/views/inventories/edit.blade.php`  
Controller: `app/Http/Controllers/InventoryController.php`

The edit screen is a stock movement screen rather than a direct quantity replacement form.

The user selects:

- IN to add stock
- OUT to remove stock
- Product unit
- Quantity

The backend calculates:

```text
movement_base_quantity = entered_quantity * conversion_factor
```

For IN:

```text
new_base_quantity = current_base_quantity + movement_base_quantity
```

For OUT:

```text
new_base_quantity = current_base_quantity - movement_base_quantity
```

OUT movements are rejected when they would create negative stock. The backend is authoritative even when the frontend preview shows an insufficient-stock warning.

Every successful movement updates inventory and creates an audit transaction in the same database transaction.

## 9. Inventory Transfer Workflow

Controller: `app/Http/Controllers/InventoryTransferController.php`

Routes:

```text
GET  /inventory-transfers
GET  /inventory-transfers/create
POST /inventory-transfers
GET  /inventory-transfers/{transfer}
```

Transfer processing:

1. Validate source inventory, destination inventory, product unit, and quantity.
2. Confirm source and destination are different records.
3. Lock both inventory rows in consistent ID order.
4. Confirm both inventories contain the same product.
5. Confirm the selected product unit belongs to that product.
6. Calculate base quantity from the selected unit.
7. Confirm the source has enough base quantity.
8. Decrease source base quantity.
9. Increase destination base quantity.
10. Create the transfer record.
11. Create an OUT transaction for the source.
12. Create an IN transaction for the destination.
13. Commit the whole operation atomically.

If any step fails, the transaction rolls back.

## 10. Persistent Stock Alert Workflow

Service: `app/Services/StockAlertService.php`  
Controller: `app/Http/Controllers/StockAlertController.php`  
View: `resources/views/stock-alerts/index.blade.php`

The service synchronizes current inventory status with persistent alert records.

When an inventory becomes alert-worthy:

- A new alert record is opened if no active alert exists.
- If the current severity is unchanged, the existing alert is updated with current quantities.
- If the severity changes, the previous alert is resolved and a new alert is opened.

When inventory returns to normal:

- The active alert is marked resolved.
- `resolved_at` is recorded.
- The historical alert remains in the database.

The alert history page supports:

- Viewing active and resolved alerts
- Viewing related inventory
- Acknowledging open alerts
- Resolving active alerts
- Viewing severity and quantity snapshots

## 11. Notifications

Notification class: `app/Notifications/StockAlertNotification.php`

New alert notifications support Laravel's database channel. The notification stores alert information in the `notifications` table.

Email delivery is optional and controlled by:

```env
STOCK_ALERT_EMAILS_ENABLED=false
```

Set it to `true` after configuring the mail settings in `.env`.

The current notification is sent to users with email addresses when synchronization is run with notifications enabled.

## 12. Alert Synchronization and Scheduling

Manual synchronization:

```powershell
php artisan stock-alerts:sync
```

Synchronization with notifications:

```powershell
php artisan stock-alerts:sync --notify
```

The Laravel scheduler runs synchronization every five minutes:

```text
*/5 * * * * php artisan stock-alerts:sync --notify
```

For local development:

```powershell
php artisan schedule:work
```

For production, configure a scheduler to run `php artisan schedule:run` every minute. If queued mail is enabled, also run a queue worker.

## 13. Authentication

Authentication uses Laravel's session guard.

Routes:

```text
GET  /login
POST /login
POST /logout
```

Application routes are protected by the `auth` middleware, including:

- Dashboard
- Companies
- Product categories
- Products
- Locations
- Units of measure
- Inventory
- Transactions
- Transfers
- Stock alerts

Login regenerates the session. Logout invalidates the session and regenerates the CSRF token.

Development seed credentials:

```text
Email: test@example.com
Password: password
```

Change or remove development credentials before production use.

## 14. Important Routes

```text
GET    /                         Redirects to dashboard
GET    /dashboard                Dashboard
GET    /login                   Login form
POST   /login                   Authenticate user
POST   /logout                  End session
GET    /stock-alerts             Persistent alert history
PATCH  /stock-alerts/{id}/acknowledge
PATCH  /stock-alerts/{id}/resolve
```

Resource routes exist for:

- `/companies`
- `/product-categories`
- `/products`
- `/locations`
- `/units-of-measure`
- `/inventories`

## 15. Database Setup

The application uses SQL Server through the `sqlsrv` connection.

Configure `.env` with the correct values:

```env
DB_CONNECTION=sqlsrv
DB_HOST=127.0.0.1
DB_PORT=1433
DB_DATABASE=inventory
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

Run migrations:

```powershell
php artisan migrate
```

Seed development data:

```powershell
php artisan db:seed
```

Clear cached configuration and views when configuration or Blade behavior appears stale:

```powershell
php artisan optimize:clear
php artisan view:clear
```

## 16. Local Development Commands

Start Laravel:

```powershell
php artisan serve --host=127.0.0.1 --port=8001
```

Open:

```text
http://127.0.0.1:8001/login
```

Run tests:

```powershell
php artisan test
```

Compile Blade views:

```powershell
php artisan view:cache
```

Inspect routes:

```powershell
php artisan route:list
```

Inspect scheduler:

```powershell
php artisan schedule:list
```

## 17. Testing Status

The current test suite includes:

- Centralized stock status tests
- Out-of-stock behavior
- Critical threshold behavior
- Low-stock behavior
- Normal stock behavior
- Zero reorder point behavior
- Guest dashboard protection
- Login page availability
- Root redirect behavior

Latest recorded result:

```text
9 tests passed
11 assertions passed
```

Additional tests should cover persistent alert creation, acknowledgement, resolution, severity changes, notification records, login credentials, and complete transfer behavior.

## 18. Security and Production Requirements

Before production deployment:

- Add role-based permissions.
- Restrict stock adjustments and transfers by user role.
- Restrict alert acknowledgement and resolution by permission.
- Add password reset and account management.
- Add login throttling and stronger account security.
- Change all development credentials.
- Configure `APP_KEY`, `APP_ENV`, `APP_DEBUG`, and `APP_URL` correctly.
- Configure real mail delivery if email alerts are required.
- Run scheduler and queue workers as managed services.
- Configure database backups.
- Add error monitoring and operational logging.
- Review CSRF, session, cookie, and HTTPS settings.
- Verify SQL Server permissions and least-privilege database access.

## 19. Remaining ERP Features

The inventory core is implemented, but these larger ERP capabilities remain:

- Role and permission administration
- Purchase requests and purchase orders
- Goods receiving workflow
- Sales orders and shipments
- Picking and warehouse workflows
- Inventory valuation and cost tracking
- Stock movement reports
- Transfer reports
- Low-stock reports
- Supplier management
- Customer management
- Barcode support
- Batch or lot tracking
- Expiry-date tracking
- Multi-company reporting
- Dashboard filtering by company or location

## 20. Rules for Future Development

Future changes must preserve these rules:

1. Base quantity is authoritative.
2. Reorder points are base-unit values.
3. Product units and conversion factors are product-specific.
4. Stock IN increases base quantity.
5. Stock OUT decreases base quantity.
6. Stock OUT cannot create negative inventory.
7. Every movement creates an audit transaction.
8. Transfers update source and destination atomically.
9. Transfers create source OUT and destination IN transactions.
10. Stock alerts use the same centralized status rules as inventory views.
11. Historical transactions and resolved alerts should not be destroyed unnecessarily.
12. New changes should extend existing models, services, controllers, and routes instead of duplicating business logic.

## 21. Handoff Summary

This is an existing Laravel inventory application, not a blank project. The correct development approach is:

1. Read this document.
2. Identify the requested feature.
3. Inspect only the related files.
4. Reuse existing models, services, scopes, validation, and routes.
5. Preserve base-unit calculations and audit behavior.
6. Add focused tests.
7. Run validation before declaring the feature complete.

The most important implementation boundary is the inventory model's base-unit logic. Any feature involving quantities, stock status, alerts, transfers, reports, procurement, or notifications must use `base_quantity` rather than raw displayed quantities.
