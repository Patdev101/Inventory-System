# Inventory System

Laravel 12 app that manages the product catalog, pricing, stock levels, and
inter-location transfers for the business. It exposes a token-authenticated
API that the separate **POS System** (`../possystem`) reads from at checkout
time — the Inventory app is the single source of truth for products,
pricing, and stock; the POS never stores its own copy of product data.

## Setup

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
```

Default seeded accounts (see `database/seeders/DatabaseSeeder.php`), all
with password `password`:

| Role    | Email               |
|---------|---------------------|
| Admin   | test@example.com    |
| Manager | manager@example.com |
| Staff   | staff@example.com   |

### Key `.env` values

| Variable               | Purpose                                                                 |
|-------------------------|-------------------------------------------------------------------------|
| `DB_CONNECTION=sqlsrv`  | Production/dev DB is Microsoft SQL Server. Tests use in-memory SQLite (`phpunit.xml`), so migrations must stay SQL Server-compatible (no `restrictOnDelete()` — use `noActionOnDelete()`). |
| `INVENTORY_API_TOKEN`   | Bearer token the POS app must send on every request to `/api/*`. Must match the POS's `INVENTORY_API_TOKEN`. |
| `VAT_RATE`              | VAT percentage shown in the product Pricing form's breakdown (`config/pricing.php`). **Must be kept in sync with the POS's `POS_TAX_RATE`** — they're separate apps with separate config, nothing enforces they match. |

## Roles

Three roles (`App\Models\User::ROLE_ADMIN|ROLE_MANAGER|ROLE_STAFF`), enforced
via the `role:` route middleware:

- **Admin** — full access, including deleting products/inventory and
  managing companies/units of measure.
- **Manager** — everything except admin-only destructive actions; approves
  or rejects staff-submitted stock movement/transfer requests.
- **Staff** — can view everything, add/adjust stock, and request transfers,
  but every stock-changing action they submit is queued as a
  `StockMovementRequest` pending manager/admin approval rather than applied
  immediately.

## Products & Pricing

Each product has a `base_unit` (the smallest unit stock is tracked in, e.g.
Piece) and any number of `product_units` (e.g. Box = 12 base units, Case = 6
base units), each with a `conversion_factor`.

**`cost_price` and `selling_price` are always entered per base unit.** If
you paid ₱200 for a Box of 12, the cost price to enter is `200 / 12 = 16.67`
— the Add/Edit Product form has a **cost price helper** (yellow box) that
does this division for you: enter what you paid and which unit it was for,
and it fills in the per-base-unit cost.

Two pricing methods (`pricing_method`):

- **Manual** — you type the selling price directly.
- **Markup** — you enter cost price + markup %, and the selling price is
  computed as `cost_price + (cost_price × markup% / 100)`. This is always
  **recalculated server-side** in `ProductController` — the browser's live
  preview is a convenience only, never trusted.

The Pricing section also shows, live as you type:

- **Expected Profit / Profit Margin** — `selling_price - cost_price` and
  `(profit / selling_price) × 100` (both `null`-safe: no cost price or a
  zero selling price never divides by zero).
- **VAT breakdown** — `selling_price` is **VAT-inclusive** (it's the exact
  shelf price the POS charges — VAT is disclosed, never added on top,
  matching how Jollibee/supermarket receipts work in the Philippines). This
  box shows `Subtotal (VAT-exclusive) + VAT = Selling Price` using the
  `VAT_RATE` config value, so it's clear why the numbers work the way they
  do before the price ever reaches the POS.
- **Price by Unit** — the same cost/selling price multiplied out for every
  configured unit (e.g. "Box: Cost ₱200.00 — Sells ₱300.00"), so a
  per-piece price doesn't get mistaken for a per-box price.

## Inventory & Transfers

- **Inventory** records are per product **and** location, tracked in base
  units (`base_quantity`) with a display `quantity`/`conversion_factor` for
  whatever unit was last used.
- **Transfer Inventory** (admin/manager) moves stock between two locations.
  The destination is picked by **location**, not by an existing inventory
  row — if that location has never stocked the product before, the
  inventory record is created automatically on transfer.
- **Request Transfer** — when an inventory record is at 0 stock, its detail
  page shows a panel to request stock from another location in the *same
  company* that currently has it. Staff requests queue for approval
  (`StockMovementRequest` with `type = 'transfer'`); admin/manager requests
  execute immediately. Both paths share the exact same transfer math via
  `InventoryMovementService::transferStock()`, so there's no risk of the
  two flows computing different results.
- **Stock Approvals** page lists pending stock-in/out/transfer requests for
  admin/manager to approve or reject.
- **Add Inventory is company-scoped.** The form starts with a **Company**
  select; Product and Location stay disabled until a company is picked, then
  repopulate client-side to only that company's own products/locations
  (`inventories/create.blade.php`, filtered against each option's
  `company_id` — no extra request, the full product/location lists are
  already in the page as JSON). This exists because not every company
  carries the same products, and each company has its own distinct set of
  locations — picking company first prevents adding stock for a
  product/location combination that doesn't actually belong together.

## Products: SKU & Item Code

Every product has two independent identifier fields, `sku` and `item_code`.
Both behave identically:

- **Optional on the form.** Leave blank and one is auto-generated
  (`SKU-XXXXXX` / `ITM-XXXXXX`, random hex, checked for uniqueness in a
  retry loop), or type your own (e.g. scanned from a barcode).
- **Unique**, enforced by a SQL Server **filtered** unique index
  (`WHERE sku IS NOT NULL`) — a plain unique index would reject a second
  `NULL` on SQL Server (unlike MySQL/SQLite), so this had to be a raw
  `CREATE UNIQUE INDEX ... WHERE` migration statement, not the Schema
  builder's `->unique()`.
- **Preserved on edit.** Leaving the field blank while editing keeps the
  existing value — it does not null it out or regenerate it.
- Every product picker across the app (Add Inventory, Purchase Order line
  items, etc.) labels each option `Name (item_code or sku)` — falls back to
  `sku` only when `item_code` is blank, never shows a raw empty
  `Name ()` (that was a real bug from an old `$product->code` reference to a
  column that was removed; fixed).

## Purchase Orders

Full supplier ordering and receiving workflow (`PurchaseOrderController`),
role-gated to admin/manager for creation and approval:

- **Lifecycle:** `draft` → `pending_approval` → `approved` → `ordered` →
  `partially_received` / `received` → `completed`, plus `rejected` /
  `cancelled`. Every transition is logged to `PurchaseOrderActivityLog`
  (visible as a timeline on the PO detail page).
- **Receiving** is partial-aware — `purchase-orders.receive` records
  per-line-item `quantity_received`, updates inventory via the same
  `InventoryMovementService` transfers use, and the PO status reflects
  whether every line is fully received yet.
- **PDF generation** (`barryvdh/laravel-dompdf`) — `purchase-orders.pdf`
  renders a printable PO from `purchase-orders/pdf.blade.php`, downloadable
  from the PO detail page.
- **Email to supplier** — `purchase-orders.email.compose` /
  `.email.send` lets a user email the PO (with the same PDF attached) to
  any address, not just the supplier's saved one, with editable
  subject/body and optional CC/BCC. Every send is logged twice: once in
  `PurchaseOrderEmail` (queryable, searchable at `/purchase-order-emails` —
  the global "Sent Emails" page) and once as a PO activity-log entry.
- **Receiving Location is filtered to the selected Supplier's company**,
  client-side (`filterLocationsForSupplier()` in `purchase-orders/create.blade.php`,
  keyed off each `<option data-company-id>`). Picking a supplier hides
  every location belonging to a different company instead of just clearing
  the field if it no longer matched — the same company-scoping principle as
  Add Inventory above, since a PO's receiving location has to belong to the
  same company as the supplier it's ordering from.
- **Line item unit price displays trimmed, not padded** — the cost-price
  autofill and the added-line-item price field both use the shared
  `formatQty()` JS helper (see Number & date formatting below), so a ₱25
  item shows `25`, not `25.0000`. This is a deliberate exception to "never
  for money" below: it's a *default/editable input value*, not a rendered
  total, so trimming trailing zeros here is about not cluttering the input
  while the user is typing — final totals elsewhere still use full 2-decimal
  currency formatting.

## Stock Approval Notifications

When staff submit a stock-in/out/transfer request, `InventoryController`
calls a `notifyApprovers()` helper that dispatches
`StockMovementRequestSubmitted` (Laravel Notification) to every
admin/manager. It always writes to the `notifications` table (bell icon +
dropdown on the dashboard header); it also sends mail if
`config('stockapprovals.email_enabled')` is true (`STOCK_APPROVAL_EMAILS_ENABLED`
in `.env`, defaults to `true`).

**Mail is not configured by default** — `.env` ships with `MAIL_MAILER=log`,
so notification emails land in `storage/logs/laravel.log`, not an inbox.
For real delivery in dev, point `.env` at a local SMTP catcher (e.g.
[Mailpit](https://github.com/axllent/mailpit), default `MAIL_HOST=127.0.0.1`
`MAIL_PORT=1025`) or a real SMTP provider. An admin-only "Send Test Email"
button exists on the Account page (`AccountController::sendTestEmail()`) to
verify delivery without triggering a real stock request.

## Number & date formatting conventions

Two global helpers in `app/Support/helpers.php` (autoloaded via
`composer.json`'s `autoload.files`, not a service provider) standardize
display formatting across every Blade view:

- **`format_qty($value)`** — quantities and conversion factors show at most
  2 decimal places with trailing zeros trimmed (`12.0000` → `12`, `5.5000`
  → `5.5`). Used for stock quantities, base quantities, and unit conversion
  factors everywhere — **never** for a rendered money *total* (those still
  use `number_format($x, 2)` directly, unchanged). The one deliberate
  exception is editable price *input fields* on the PO line-item form — see
  Purchase Orders above.
- **`format_date($value)`** / **`format_datetime($value)`** — Month/Day/Year
  (`09/08/2026`), with the datetime variant adding 12-hour time + AM/PM
  (`02:05 PM`) so morning/evening is unambiguous. Accepts a Carbon instance
  or a date string.
- A parallel `formatQty(value)` **JavaScript** function is duplicated inline
  in every Blade file that computes a live client-side preview (product
  create/edit unit conversion, inventory create/edit quantity previews,
  purchase order line totals) — there's no shared JS asset pipeline in this
  app, so each file defines its own copy rather than importing a module.

## Sidebar navigation

`resources/views/layouts/sidebar.blade.php` renders nav items from a
**grouped** array (`$systemNavigationGroups`): Overview, Catalog, Inventory,
Purchasing, Organization, Reports, Account. Each item can carry a `roles`
key (`hasRole(...)` filter) — a whole group disappears if every item in it
gets filtered out for the current user. The nav area scrolls internally
(`overflow-y: auto`) rather than clipping if it overflows the viewport
height.

## Auto-refresh

The Dashboard and Inventory list pages reload themselves every 20 seconds
(paused while the tab isn't visible, scroll position preserved) so stock
changes made from the POS side show up without a manual refresh. Opt in on
any Blade view with `@section('autoRefreshSeconds', 20)`. The Inventory
*detail* page is deliberately excluded since it hosts the active Request
Transfer form — an auto-reload there could wipe in-progress input.

## API (consumed by the POS)

Protected by the `inventory.api-token` middleware (bearer token = `.env`
`INVENTORY_API_TOKEN`):

- `GET /api/products` — active products with pricing, units, and
  per-location stock. `selling_price` is unchanged for POS compatibility;
  `cost_price`, `markup_percentage`, `pricing_method`, and the computed
  `profit`/`profit_margin` are also included but the POS never reads them.
- `GET /api/locations`
- `POST /api/inventory/out` / `POST /api/inventory/in` — used by the POS at
  checkout and on void/refund respectively.

## Testing

```bash
php artisan test
```

Runs against an in-memory SQLite database (see `phpunit.xml`) — migrations
must work on both SQLite (tests) and SQL Server (real deployment).

## Known limitations

Read this before assuming a feature exists or extending one — these are
current, deliberate gaps, not oversights to silently "fix":

- **No automated test coverage for most of this session's work.**
  Purchase Orders, receiving, PDF/email, notifications, and the formatting
  helpers were verified via live HTTP round-trips against a real dev
  database during development, not via `php artisan test`. There is no
  PHPUnit/Pest suite exercising `PurchaseOrderController`,
  `InventoryMovementService` receiving paths, or the mail classes.
- **VAT rate and tax logic are duplicated, unsynced config.** This app's
  `VAT_RATE` and the POS's `POS_TAX_RATE` are two separate `.env` values in
  two separate apps. Nothing enforces they match — a mismatch silently
  produces incorrect VAT breakdowns on one side.
- **The Inventory ⇄ POS integration trusts the network.** The bearer token
  (`INVENTORY_API_TOKEN`) is the only auth on `/api/*`; there's no request
  signing, no mutual TLS, and no rate limiting. Anyone with the token can
  call the API from anywhere `INVENTORY_API_URL` is reachable.
- **No queue workers assumed running.** `QUEUE_CONNECTION=sync` — mail and
  notifications send inline on the request that triggers them. This means
  submitting a stock request (or emailing a PO) is only as fast as SMTP
  delivery; there is no retry-on-failure or background dispatch.
- **Mail is unconfigured out of the box** (see Stock Approval Notifications
  above) — `MAIL_MAILER=log` until you point it at real SMTP or a local
  catcher like Mailpit.
- **SKU/Item Code uniqueness relies on a SQL Server-specific filtered
  index**, created via raw `DB::statement()` in the migration rather than
  the Schema builder. If this app is ever ported to another database
  (MySQL/Postgres both allow multiple `NULL`s in a unique index natively),
  that migration's `up()`/`down()` need auditing — it may no longer need
  the raw SQL, or may need a different raw statement for that engine.
- **No soft-deletes on core inventory-affecting models.** Deleting a
  Product, Location, or Company is a hard delete (admin-only) constrained
  only by `noActionOnDelete()` foreign keys — the DB will reject deletion
  if dependent rows exist, but there's no "trash and restore" workflow.
- **Purchase Order permissions are coarse.** Creation/approval is
  role-gated (`admin`, `manager`) but there's no per-location or
  per-supplier scoping — any manager can create/approve a PO for any
  location in the system.
- **The product image pipeline is local disk only** (`storage/app/public`
  via the `public` filesystem disk) — no S3/cloud storage config is wired
  up, so this won't survive a stateless/multi-server deployment without
  additional work.
