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
