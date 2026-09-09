# Inventory ERP System Documentation

**Project:** Inventory Management / ERP System
**Framework:** Laravel 12
**Database:** SQL Server (production/dev), SQLite in-memory (automated tests)
**Project path:** `c:\projects\shogun\inventory`
**Companion project:** `c:\projects\shogun\possystem` (POS) — separate Laravel app, separate database, talks to this app only over the token-authenticated `/api/*` routes below.
**Documentation date:** 2026-09-09 (see Section 18 changelog for everything shipped since the 2026-09-08 version below — UX pass, soft-delete/restore, deploy prep, repo cleanup)

---

## 0. What changed in this rewrite (read this first)

This document replaces an earlier version that:
- Recorded the project path as `C:\projects\inventory\inventory` (wrong — corrected above).
- Said the test suite was "9 tests, 11 assertions" — it is now **93 tests, 289 assertions, all passing** (Inventory) plus **62 tests, 274 assertions, all passing** (POS).
- Did not mention **Purchase Orders / Suppliers / Receiving** at all, even though that module already exists in the codebase (Section 7).
- Did not mention the **Pricing module** (cost price, markup, VAT breakdown — Section 6.1) or **Account Management / forced password change** (Section 14) that were built in earlier sessions.

While auditing for this rewrite, one **critical, currently-broken bug** was found and fixed (not "found and left for later" — it's already fixed, migrated, and tests are green). See Section 1 for details. Everything else in this document is descriptive of the real, current code — verified by reading the actual files and running the actual test suite, not assumed.

**Second verification pass (same day):** re-read `app/Http/Controllers/`, `app/Models/`, `app/Services/`, and `php artisan route:list` directly against this document.
- Found an entire undocumented module: **Reports** (`ReportController`, 4 routes, Section 6.2 below) — dashboard-style stock/movement/transfer reporting that already exists and works, just wasn't written up.
- Found a **dead model**: `app/Models/InventoryTransferitem.php` (table `inventory_transfer_items`). Grepped the whole `app/` tree — nothing references this class anywhere. The live transfer workflow (Section 8) still moves one product/unit per `InventoryTransfer` row, not a multi-line checklist. This looks like a leftover from an abandoned "multi-item transfer" idea; don't build on it without confirming it's actually wired up first.
- Confirmed test suite still **93 passed / 289 assertions**, confirmed 41 migration files, confirmed the full controller/model/service file lists below match what's on disk right now.

---

## 1. Critical Finding (Fixed This Session)

### 🔴 `inventory_transfers` was missing `status`, `received_by`, `received_at` columns entirely

**Evidence:** `App\Models\InventoryTransfer::$fillable` contains a comment reading *"pre-existing columns (added by an earlier migration, now wired up here for the first time)"* for `status`, `received_at`, `received_by`. `App\Services\InventoryMovementService` reads and writes these columns in **every** transfer code path (`transferStock()`, `initiateTransfer()`, `completeTransferReceipt()`, `reverseTransfer()`). But grepping `database/migrations/` for every migration touching `inventory_transfers` turned up only two files — the original `create_inventory_transfers_table` (which never defines these columns) and `add_audit_fields_to_inventory_transfers_table` (which adds `receiver_id`/`receiver_role`/`audit_status`/`audited_by`/`audited_at`/`audit_notes`, positioned `->after('status')`, assuming `status` already exists).

**Impact confirmed by running the test suite before the fix:** every inventory-transfer action (the legacy immediate transfer *and* the newer audited receive workflow) failed with `PDOException: table inventory_transfers has no column named status` on a freshly migrated database. This is not a test-only problem — running `php artisan migrate --force` against the real SQL Server dev database in this session **also** applied the missing columns for the first time, meaning the real database was in the same broken state.

**Fix applied:** `database/migrations/2026_09_04_080800_add_status_and_receiving_fields_to_inventory_transfers_table.php` — adds `status` (string, default `completed`), `received_by` (nullable FK to `users`, `NO ACTION` delete/update per this codebase's SQL Server cascade-path convention), `received_at` (nullable timestamp). Guarded with `Schema::hasColumn()` checks so it's safe to run even on an environment where these were added by hand at some point.

**Verified:** `php artisan migrate --force` ran clean against the real SQL Server database. Full test suite went from **6 failing** to **0 failing**.

**Lesson for future work — read before touching migrations:** this codebase has at least one instance of a migration being written/run against a real database without the corresponding migration *file* being committed (or it was deleted after the fact). Before assuming "the database probably already has column X because the code uses it," **grep the migrations folder to confirm**, the way this fix did. Don't trust code comments or model `$fillable` lists as proof a column exists.

### Also found: 4 stale tests (not app bugs)

`tests/Feature/InventoryTransferTest.php` had 4 tests written against an **older** `InventoryTransferController::store()` contract (single `source_inventory_id` / `destination_location_id` / `product_unit_id` / `quantity` fields). The controller was legitimately redesigned since those tests were written into the two-phase checklist/audited-receiver workflow (`items[]` array + `receiver_id` + `receiver_role`, documented in Section 8.2 below). Rewrote the 4 tests to match the current, real, working contract — this was **not** an app bug, just tests that hadn't kept up with a real feature evolution. All rewritten tests pass against the unmodified controller/service code.

---

## 2. System Purpose

This application is an inventory, warehouse, and now **purchasing/receiving** management system. It manages products, product-specific units, storage locations, stock quantities, inventory movements, audited transfers between locations, purchase orders and supplier receiving, stock alerts, pricing (with VAT-inclusive breakdown), user account management, and audit history.

Laravel controllers, Eloquent models, Blade views, migrations, and vanilla JavaScript. No frontend framework. All pages share one dashboard shell (`resources/views/layouts/sidebar.blade.php` + `layouts/app.blade.php`): fixed left sidebar, signed-in user + Sign Out, light-gray background, white cards, blue primary actions.

**The core warehouse workflow (products, inventory, transfers, alerts) and the primary purchasing workflow (PO → Receiving → Inventory → POS) are both functionally complete and covered by passing automated tests.** The remaining gaps are in governance/reporting polish, not the core data flow — see Section 12 for the honest breakdown.

---

## 3. Core Inventory Principle (unchanged, still authoritative)

`inventories.base_quantity` is the single source of truth for stock. Every other quantity (`quantity` on `inventories`, `quantity_ordered`/`quantity_received` on PO items, cart quantities in POS) is expressed in whatever unit the user picked and must be multiplied by that unit's `conversion_factor` to get `base_quantity` before it's ever compared, summed, or persisted as the authoritative figure.

```text
Product: Rice, base unit KG, alternate unit BAG, 1 BAG = 25 KG
Entered: 2 BAG  ->  quantity=2, product_unit=BAG, conversion_factor=25, base_quantity=50
```

Never compare a displayed `quantity` directly against `reorder_point` — `reorder_point` is stored in base units.

**Every stock-changing code path in this app funnels through one of two places, and only two:**
1. `App\Services\InventoryMovementService` — used in-process by `InventoryController` (manual stock in/out), `InventoryTransferController` (transfers), and `PurchaseOrderService` (PO receiving). This is the single source of truth for base-unit math, locking, and `InventoryTransaction` audit rows.
2. `App\Http\Controllers\Api\InventoryApiController` (`/api/inventory/in`, `/api/inventory/out`) — the only path used by the **separate POS application**, since POS is a different app/database and can't call the PHP service class directly. It duplicates the same base-unit math (see Section 9.3's known-risk note) but writes to the exact same `inventories` and `inventory_transactions` tables.

There is **one** `inventories` table. There is **no** second, cached, or POS-local copy of stock. This is why PO receiving automatically becomes visible to POS (Section 9) without any extra sync code — they already share the same source of truth.

---

## 4. Domain Relationships

```text
Company
  +-- Locations
  +-- Suppliers                                (NEW)

ProductCategory
  +-- Products
        +-- ProductUnits -- UnitOfMeasure

Product
  +-- Inventories -- InventoryTransactions
  +-- PurchaseOrderItems                        (NEW, via PurchaseOrder)

Supplier                                        (NEW)
  +-- PurchaseOrders
        +-- PurchaseOrderItems
        +-- PurchaseOrderReceipts
              +-- PurchaseOrderReceiptItems -> PurchaseOrderItem

InventoryTransfer
  +-- Source Inventory / Destination Inventory / Product / ProductUnit
  +-- Receiver / Audited By / Received By (User)

Inventory
  +-- StockAlerts

User
  +-- must_change_password (forced password-change flow)
```

---

## 5. Main Models (Existing)

### User — `app/Models/User.php`
Roles: `admin`, `manager`, `staff` (`ROLE_*` constants, `hasRole()`). Also has `is_active` (enable/disable) and `must_change_password` (forced-change-on-next-login flow — see Section 14).

### Company / Location / ProductCategory / UnitOfMeasure / ProductUnit
Unchanged from prior documentation. `ProductUnit` connects a `Product` to a `UnitOfMeasure` with a product-specific `conversion_factor` and `is_default` flag.

### Product — `app/Models/Product.php`
Fields: `name`, `sku`, `product_category_id`, `company_id`, `base_unit_id`, `reorder_point` (base units), `is_active`, plus the pricing fields below.

### Product Pricing (added since the earlier doc) — same model
- `selling_price` — **VAT-inclusive**, per base unit. This is the exact shelf price the POS charges; VAT is disclosed on the receipt, never added on top.
- `cost_price` — per base unit. A "cost helper" widget on the create/edit form lets an admin type what they paid per purchasing unit (e.g. "₱200 per Box of 12") and it divides down to the per-base-unit figure for them.
- `markup_percentage`, `pricing_method` (`manual` or `markup`) — when `markup`, `selling_price` is **always recalculated server-side** in `ProductController` from `cost_price + cost_price * markup_percentage / 100`; the browser's live preview is never trusted.
- `profit` / `profit_margin` — computed accessors (`selling_price - cost_price`, `(profit / selling_price) * 100`), `null`-safe against missing cost price or zero selling price.
- ⚠️ **Known gap, not yet built:** Purchase-order receiving does **not** feed the actual price paid (`purchase_order_items.unit_price`) back into `Product.cost_price`. These are two independent numbers today — an admin sets `cost_price` by hand in the Pricing UI, and a PO's `unit_price` is entered separately when creating the PO. If the two drift, profit/margin figures on the Product page won't reflect what was actually paid on the most recent PO. See Section 12, P2.

### Inventory — `app/Models/Inventory.php`
One row per (`product_id`, `location_id`). `base_quantity` is authoritative. Centralizes `getStockStatus()`, `isOutOfStock()`/`isCriticalStock()`/`isLowStock()`/`isInStock()`, and query scopes `outOfStock()`/`criticalStock()`/`lowStock()`. **Every stock display and every stock alert in the app uses these same methods** — there is no duplicated threshold logic anywhere else.

### InventoryTransaction — `app/Models/InventoryTransaction.php`
Append-only audit log for every stock movement (`type`: `in`/`out`, `quantity`, `base_quantity`, `product_unit_id`, `conversion_factor`, `reference`, `notes`). Preserves `product_id`/`location_id` directly so history survives even if the `Inventory` row is deleted.

### InventoryTransfer — `app/Models/InventoryTransfer.php`
See Section 8 for the full two-phase audited workflow this model now supports (`status`, `receiver_id`, `receiver_role`, `audit_status`, `audited_by`, `audited_at`, `audit_notes`, `received_by`, `received_at` — all now backed by real migrations, see Section 1).

### StockAlert — `app/Models/StockAlert.php`
Unchanged — persistent alert history (`open`/`acknowledged`/`resolved`), synchronized from live `Inventory` status by `StockAlertService`.

---

## 6. Stock Status Rules (unchanged)

```text
base_quantity <= 0                                        -> Out of Stock
base_quantity > 0 and 0 < base_quantity <= reorder/2       -> Critical
base_quantity > reorder/2 and base_quantity <= reorder     -> Low Stock
base_quantity > reorder (or reorder_point <= 0)            -> In Stock
```

## 6.1 Pricing & VAT (new section)

Selling price is **VAT-inclusive** by deliberate design decision (matches Philippine retail norms — the price on the shelf/menu is what's paid; VAT is disclosed, not stacked on top). The Product create/edit form shows, live as you type:

```text
Subtotal (VAT-exclusive) = Selling Price / (1 + VAT_RATE/100)
VAT                      = Selling Price - Subtotal
```

`VAT_RATE` comes from `config/pricing.php` (`.env` → `VAT_RATE`, default 12). **This value is duplicated in the separate POS app as `POS_TAX_RATE`** — the two apps have no shared config, so if the VAT rate ever changes, both `.env` files must be updated or the two apps' receipts will disagree about the VAT breakdown of the same price.

The Pricing form also shows a **cost calculator** (type what you paid per purchasing unit, e.g. "₱200 per Box", pick the unit, it fills in the per-base-unit cost) and a **Price by Unit** table (shows what every configured unit — Piece, Box, Case — actually costs/sells for, computed from the base-unit price × that unit's conversion factor).

## 6.2 Reports (undocumented until this pass — exists and works)

**Controller:** `app/Http/Controllers/ReportController.php` · **Views:** `resources/views/reports/*.blade.php` · **No dedicated model/service** — queries the existing `Inventory`/`InventoryTransaction`/`InventoryTransfer` models directly.

| Route | Method | Purpose |
|---|---|---|
| `GET /reports` | `index()` | Landing dashboard — out-of-stock / critical / low-stock counts |
| `GET /reports/stock-movements` | `stockMovements()` | Paginated `InventoryTransaction` log, filterable by `type`, `from`/`to` date range, and product/location text search |
| `GET /reports/transfers` | `transfers()` | Paginated `InventoryTransfer` log, filterable by date range and product search |
| `GET /reports/low-stock` | `lowStock()` | Paginated `Inventory` list, filterable by `status=out_of_stock\|critical\|low\|all`, reusing the same `outOfStock()`/`criticalStock()`/`lowStock()` scopes as everywhere else (Section 5) |

No authorization gate beyond standard `auth` middleware — any logged-in user (staff included) can view all reports. Not covered by any automated test (see Section 13).

---

## 7. Purchase Orders & Receiving (NEW — the primary workflow this document was rewritten to cover)

**Files:**
- Models: `app/Models/PurchaseOrder.php`, `PurchaseOrderItem.php`, `PurchaseOrderReceipt.php`, `PurchaseOrderReceiptItem.php`, `Supplier.php`
- Service: `app/Services/PurchaseOrderService.php` (all business logic — the controller is a thin HTTP wrapper)
- Controller: `app/Http/Controllers/PurchaseOrderController.php`
- Views: `resources/views/purchase-orders/{index,create,show,receive}.blade.php`
- Migrations: `2026_09_07_032449_create_purchase_orders_table.php`, `..._032545_create_purchase_order_items_table.php`, `..._032622_create_purchase_order_receipts_table.php`, `..._032648_create_purchase_order_receipt_items_table.php`
- Tests: `tests/Feature/PurchaseOrderReceivingTest.php` (new this session — 3 tests, 17 assertions, all passing)

### 7.1 Status lifecycle

```text
draft -> pending_approval -> approved -> ordered -> partially_received -> completed
                          \-> rejected                                 (or straight to completed if fully received in one receipt)
```

`PurchaseOrder::STATUS_*` constants. Creating a PO (`createDraft()`) does **not** touch inventory. Only `receiveItems()` does.

### 7.2 The receiving flow (verified working end-to-end by a real test)

`PurchaseOrderService::receiveItems()`:

1. Rejects if the PO isn't `ordered` or `partially_received`.
2. Filters out any submitted line with a zero/blank received quantity; rejects if *every* line is zero.
3. Opens a DB transaction, **locks the PO row** (`lockForUpdate()`) — a second concurrent receiving request on the same PO blocks until the first commits.
4. Creates one `PurchaseOrderReceipt` header (one receiving *event*) with `received_by`/`received_at`/`notes`.
5. For each non-zero line:
   - **Locks the specific `PurchaseOrderItem` row** — protects against two concurrent receipts double-crediting the same line.
   - Rejects if that item is already fully received.
   - Rejects if the submitted quantity would exceed what's remaining (`remaining = ordered - already_received`) — **over-receiving is impossible**, not just discouraged.
   - Creates a `PurchaseOrderReceiptItem` (the per-line record for *this* delivery event).
   - Updates `PurchaseOrderItem.quantity_received` (a denormalized running total).
   - Calls `InventoryMovementService::addStock()` — the exact same method used by manual stock-in — with `reference: 'PO #' . $purchaseOrder->po_number`, so every unit added shows up in `InventoryTransaction` traceable back to the PO.
6. Recalculates PO status: `completed` if every item's `remaining_quantity <= 0`, else `partially_received`.
7. Commits. If anything above throws, the entire receipt — receipt header, line items, PO item quantities, inventory increases, PO status — rolls back together. **There is no scenario where inventory increases but the PO record doesn't reflect it, or vice versa.**

Verified by `test_full_receiving_workflow_increases_inventory_and_updates_po_status`: creates a PO for 100 units, receives 40 (status → `partially_received`, inventory → 40), receives the remaining 60 (status → `completed`, inventory → 100), then confirms a further over-receive attempt is rejected and inventory stays at 100.

### 7.3 Duplicate/concurrent receiving protection — what exists vs. what doesn't

| Protection | Status | How |
|---|---|---|
| Receiving more than ordered | ✅ Enforced | `remaining` check per line, server-side, inside the locked transaction |
| Receiving a cancelled/draft/pending/approved PO | ✅ Enforced | Status must be `ordered` or `partially_received`, checked twice (once before the lock, once after acquiring it) |
| Two concurrent receiving requests on the same PO | ✅ Enforced | `lockForUpdate()` on the PO row and on each `PurchaseOrderItem` row inside one DB transaction |
| Partial receiving, multiple times | ✅ Supported | Each call creates a new `PurchaseOrderReceipt`; `quantity_received` accumulates correctly (verified by the test above) |
| Duplicate *HTTP* form submission (e.g. double-click / browser back-button resubmit) | ⚠️ Partially covered | Row locking prevents a true race, but there's no idempotency key on the receive request the way POS checkout has one. A genuine double-submit of the *same* line quantities would either succeed twice (if there's remaining quantity for both) or the second would correctly fail with "already fully received" / "exceeds remaining" — it will **not** silently double-credit inventory, but it also doesn't proactively disable the submit button client-side. Low risk, not a data-integrity bug, just missing UX polish. |
| Negative/invalid quantities, invalid product IDs | ✅ Enforced | Laravel validation (`gt:0`, `exists:`) on `PurchaseOrderController::receive()`, plus the service re-validates the line belongs to this PO |

### 7.4 Authorization

- Create/submit/mark-ordered/receive: `role:admin,manager,staff` (same pattern as Stock Movement Requests — staff can do the operational work).
- Approve/reject: `role:admin,manager` only.
- View list/detail: any authenticated user.
- **Not yet verified by a test:** the approve/reject role restriction, and the store() cross-company validation (supplier and receiving location must belong to the same company — this exists in `PurchaseOrderController::store()` but has no dedicated test). See Section 13.

### 7.5 Database (all EXISTING, verified against real migrations)

| Table | Key fields | Notes |
|---|---|---|
| `suppliers` | `company_id`, `name`, `is_active` | One supplier belongs to exactly one company |
| `purchase_orders` | `po_number` (unique, `PO-000001` style), `company_id`, `supplier_id`, `location_id` (receiving location), `status`, `created_by`, `approved_by`/`approved_at`/`approval_notes`/`rejection_reason` | `location_id` is the warehouse this PO will be received into |
| `purchase_order_items` | `purchase_order_id`, `product_id`, `product_unit_id`, `quantity_ordered`, `quantity_received` (denormalized running total), `unit_price` | |
| `purchase_order_receipts` | `purchase_order_id`, `received_by`, `received_at`, `notes` | One row per receiving *event* (a delivery), may cover multiple line items |
| `purchase_order_receipt_items` | `purchase_order_receipt_id`, `purchase_order_item_id`, `quantity_received`, `notes` | Per-line record for one receipt; sum of these per `purchase_order_item_id` equals that item's `quantity_received` |

All FK cascade-path issues (SQL Server rejects a second cascade path to the same table) are already handled the established way in this codebase: cascade the "primary" relationship, add secondary FKs in a separate `Schema::table()` call with `onDelete('no action')`.

---

## 8. Inventory Transfer Workflow (updated — audited two-phase receive)

Controller: `InventoryTransferController` · Service: `InventoryMovementService`

### 8.1 Two code paths, intentionally kept separate

- **`transferStock()`** — immediate, single-step (source and destination both updated atomically, no audit step). Not called by the controller's `store()` anymore, but still used by `InventoryController::requestTransfer()` for the admin/manager "fix an out-of-stock item" quick-action (Section 8.3). Retained for any caller that wants an immediate, non-audited transfer.
- **`initiateTransfer()` / `completeTransferReceipt()` / `reverseTransfer()`** — the two-phase workflow `store()`, `audit()`, and `receive()` actually use.

### 8.2 The audited workflow

**Phase 1 — Initiate** (`initiateTransfer()`, on `store()`): locks + validates source, confirms a different destination location, deducts source stock **immediately** and creates an OUT transaction, creates (or `firstOrCreate`s, at zero) the destination inventory row, creates the `InventoryTransfer` with `status=pending`, `audit_status=pending`, and the assigned `receiver_id`/`receiver_role`. Stock has left the source but is **not yet** credited anywhere — it's "in transit."

**Phase 2a — Audit pass → Receive**: only `transfer.receiver_id === auth()->id()` may act (enforced in the controller, both `audit()` and `receive()`). Pass sets `audit_status=passed`. Receive (`completeTransferReceipt()`) — locks the transfer row itself (so two concurrent "Mark Received" clicks can't both pass the check and both credit stock) and the destination inventory row, credits `base_quantity`, creates an IN transaction, sets `status=completed`.

**Phase 2b — Audit fail → automatic reversal**: `reverseTransfer()` locks the transfer, adds the deducted quantity back to the source, creates an IN transaction documenting the return, sets `status=rejected`, `audit_status=failed`. No stock is ever left in limbo.

### 8.3 "Request Transfer" quick-action (for out-of-stock items)

On an `Inventory` detail page with `base_quantity <= 0`, a panel offers to pull stock from another location in the **same company** that has it. Staff requests go through the `StockMovementRequest` approval queue (`type=transfer`); admin/manager requests execute **immediately** via the legacy `transferStock()` — this is the one place that method is still live. Verified by `tests/Feature/InventoryTransferRequestTest.php` (5 tests, all passing after the Section 1 migration fix).

### 8.4 Known limitation (documented, not yet fixed)
`receiver_role` is a snapshot of the receiver's role at transfer-creation time — not re-validated against their current role at audit/receive time. If a user's role changes after being assigned, the transfer still honors the original `receiver_id` assignment. Low risk (role changes are rare and the receiver-identity check via `receiver_id` still holds), but worth knowing.

### 8.5 Testing gap
The `audit()`/`receive()` HTTP actions themselves (as opposed to `initiateTransfer()`/`completeTransferReceipt()`/`reverseTransfer()` at the service level, which are exercised indirectly) have **no dedicated feature test** for the full pass→receive or fail→reverse flow through the controller. See Section 13, P1.

---

## 9. Inventory → POS Synchronization

**This already works, end-to-end, with zero additional code needed for PO receiving to reach POS.** Here's exactly why.

### 9.1 There is one source of truth, read live

POS has no product/inventory database of its own. Its `App\Services\InventoryService` (in `possystem`) calls this app's `GET /api/products` (bearer-token-protected, `routes/api.php`) on every catalog load. That endpoint does a plain, uncached Eloquent query against `products`/`inventories` **at request time** — no caching layer sits between it and the real `inventories` table. Whatever `base_quantity` a PO receipt just wrote is what the very next `/api/products` call returns.

### 9.2 POS polls, it doesn't push

POS's `app.js` re-fetches the product catalog **every 15 seconds** (paused while the tab isn't visible, refreshed instantly on tab focus) — this was built specifically so a price or stock change made in Inventory shows up without a manual page reload. So: receive a PO → within at most 15 seconds, any open POS terminal shows the new stock. There is no WebSocket/Redis/queue infrastructure involved, and none is needed at the current scale — polling an uncached endpoint every 15s against a single small business's product catalog is not a performance concern.

### 9.3 Two independent code paths write to the same table (known duplication risk)

- **In-process** (`InventoryMovementService`, inside this app): used by PO receiving, manual stock in/out, and transfers.
- **Cross-app HTTP** (`InventoryApiController@add`/`@remove`, `/api/inventory/in` and `/out`): the **only** path POS can use, since it's a separate application/database. Used for POS checkout deduction and void/refund restocking.

Both paths implement the *same* base-unit conversion math independently (they have to — POS can't call the PHP service class over HTTP). **⚠️ DECISION REQUIRED / risk to track:** if `InventoryMovementService`'s stock math is ever changed (e.g. a new validation rule, a new field), `InventoryApiController` must be updated in lockstep, or the two apps' stock calculations can silently diverge. There is currently no shared code between them enforcing this — it's discipline, not a compiler-enforced constraint. Recommended: extract the shared math (conversion, negative-stock guard) into a Trait or a pure helper class the two controllers/services both use, rather than two independent implementations of the same formula. Not done in this pass — flagged as a real but non-blocking risk (Section 13, P2).

### 9.4 What POS does NOT know about

POS has zero awareness of Purchase Orders, Suppliers, or the approval/receiving workflow — and doesn't need to. It only ever sees the *result* (`inventories.base_quantity`), never the process that produced it. This is the correct boundary and should be preserved — do not add PO-specific logic to POS.

---

## 10. Persistent Stock Alerts, Notifications, Scheduling (unchanged)

Service: `StockAlertService` · Command: `stock-alerts:sync [--notify]`, scheduled every 5 minutes. See prior sections — no changes found in this audit.

## 11. Authentication & Account Management (updated)

Session-guard auth (`/login`, `/logout`), rate-limited login (5 attempts/60s per email+IP). **New since the prior doc:**

- **My Account** (`/account`) — every user can change their own email (requires current password) and password (requires current password, confirmed). Route: `AccountController`.
- **Admin User Management** (`/users/{user}/edit`, `/users/{user}/reset-password`) — admin can edit anyone's name/email/role/active-status and reset anyone's password, **including other admins** (fixed this session — see the git history around `UserController::canManage()`; admin used to be blocked from managing other admins due to reusing the "creatable roles" list for "manageable users", which is a different question). An admin can never act on their **own** account through these routes (redirected to My Account instead) — this is how "can't disable own account" / "can't remove own admin role" is enforced.
- **Forced password change** — `users.must_change_password`. When set (by an admin's "require password change on next login" checkbox during reset), `EnsureNoForcedPasswordChange` middleware blocks every route except `/account` and logout until the user sets a new password.
- **No email-based password reset.** `/forgot-password` shows a static "contact an administrator" page. There is no mail service wired up for this — by design (local/internal system).

---

## 12. Current Progress Tracker

### Purchase Orders
- [x] Supplier management (CRUD via `Supplier` model; UI not audited in this pass — assume basic CRUD exists, verify before building on it)
- [x] PO creation (draft, with items, server-validated supplier/location/company consistency)
- [x] PO approval workflow (submit → approve/reject)
- [x] Partial receiving
- [x] Full receiving → auto-completion
- [x] Receiving validation (over-receive blocked, wrong-status blocked, concurrent-request-safe)
- [x] Inventory increases on receipt, via the shared `InventoryMovementService`
- [x] Traceable: every received unit creates an `InventoryTransaction` referencing the PO number
- [ ] PO received cost does not feed back into `Product.cost_price` (Section 5, known gap)
- [ ] No dedicated test for the approve/reject role gate or cross-company validation

### Inventory
- [x] Inventory model, base-unit source of truth
- [x] Stock IN/OUT with audit trail
- [x] Transfers — audited two-phase workflow (initiate/audit/receive/reverse)
- [x] Transfers — legacy immediate path (still used by the out-of-stock quick-action)
- [x] Stock alerts, persistent history, notifications
- [x] **Migration bug fixed this session** — `inventory_transfers.status`/`received_by`/`received_at` now actually exist

### Reports
- [x] Stock status dashboard (out-of-stock/critical/low counts)
- [x] Stock movement log with filters (type, date range, search)
- [x] Transfer log with filters (date range, search)
- [x] Low-stock drill-down list by status
- [ ] No automated test coverage for any report route
- [ ] No role restriction — any authenticated user (including staff) sees all reports; confirm this is intentional

### POS Sync
- [x] POS reads live, uncached inventory via `/api/products`
- [x] POS polls every 15s + refreshes on tab focus — no manual reload needed
- [x] PO receiving is automatically visible to POS with zero PO-specific POS code
- [ ] Shared stock-math risk between `InventoryMovementService` and `InventoryApiController` not yet unified (Section 9.3)

### Testing
- [x] PO creation test
- [x] PO number generation/uniqueness test (also serves as a SQL-Server-raw-SQL-on-SQLite portability check — verified passing)
- [x] Full partial→complete receiving workflow test, including over-receive rejection
- [x] Inventory transfer tests rewritten to match the current audited-workflow contract (were stale, now current)
- [x] Inventory transfer request (out-of-stock quick-action) tests
- [ ] No test for `audit()`/`receive()` HTTP actions specifically (pass/fail paths)
- [ ] No test for PO approve/reject authorization
- [ ] No test for PO cross-company validation rejection

---

## 13. Priority Roadmap

| Priority | Task | Why | Files | Status |
|---|---|---|---|---|
| **P0** | ~~Fix missing `inventory_transfers` migration~~ | Blocked *all* transfer functionality on any fresh install | New migration file (Section 1) | ✅ **Done this session** |
| **P0** | ~~Rewrite 4 stale `InventoryTransferTest` cases~~ | Test suite must be trustworthy before further PO/transfer work | `tests/Feature/InventoryTransferTest.php` | ✅ **Done this session** |
| P1 | Add `audit()`/`receive()` HTTP-level tests (pass path, fail/reverse path) | The two-phase receiver workflow's controller layer is currently unverified by any test | `InventoryTransferController`, new test file | Not started |
| P1 | Add PO approve/reject authorization test + cross-company validation test | `PurchaseOrderController::store()`/`approve()`/`reject()` have real logic with zero direct test coverage | `PurchaseOrderService`, `PurchaseOrderController` | Not started |
| P2 | Feed PO `unit_price` back into `Product.cost_price` on receipt (or make the relationship an explicit choice, e.g. "update cost price from latest PO" toggle) | Pricing/margin figures can silently drift from what was actually paid | `PurchaseOrderService::receiveItems()`, `ProductController` | Not started — needs a product decision first (always overwrite? average cost? manual-only with a suggestion?) |
| P2 | Unify stock math between `InventoryMovementService` and `InventoryApiController` | Two independent implementations of the same base-unit conversion/negative-stock-guard logic can drift | Both files | Not started |
| P3 | Client-side double-submit guard on the PO receive form (disable button on submit) | Minor UX polish; server-side locking already prevents real data corruption | `resources/views/purchase-orders/receive.blade.php` | Not started |
| P3 | Re-sync `VAT_RATE` (Inventory) / `POS_TAX_RATE` (POS) into one place, or add a startup check that warns if they differ | Silent mismatch would make receipts disagree between systems | Both apps' `.env`/config | Not started |
| P2 | Add feature tests for `ReportController` (stock-movements/transfers/low-stock filters) | Zero automated coverage on a module already live in production | New test file, Section 6.2 | Not started |
| P3 | Decide the fate of `App\Models\InventoryTransferitem.php` (table `inventory_transfer_items`) | Dead code — referenced nowhere in `app/`; either wire it into a real multi-line-transfer feature or delete the model + migration | `app/Models/InventoryTransferitem.php` | Not started — needs a product decision |
| P3 | Decide whether Reports should be role-restricted | Currently any authenticated user, including staff, can view all stock/movement/transfer reports | `routes/web.php`, `ReportController` | Not started — needs a product decision |

---

## 14. File/Module Map (Purchase Orders — the module this rewrite focused on)

```text
Purchase Orders
├── app/Models/PurchaseOrder.php              status lifecycle, total attribute, relationships
├── app/Models/PurchaseOrderItem.php          remaining_quantity/subtotal accessors, isFullyReceived()
├── app/Models/PurchaseOrderReceipt.php       one row per delivery event
├── app/Models/PurchaseOrderReceiptItem.php   one row per line item within a delivery event
├── app/Models/Supplier.php                   belongs to one Company
├── app/Services/PurchaseOrderService.php     ALL business logic — read this first for any PO change
├── app/Http/Controllers/PurchaseOrderController.php   thin HTTP wrapper + cross-company validation
├── resources/views/purchase-orders/
│   ├── index.blade.php                       list + search/filter (not deeply audited this pass)
│   ├── create.blade.php                      draft creation form (not deeply audited this pass)
│   ├── show.blade.php                        PO detail + status actions (not deeply audited this pass)
│   └── receive.blade.php                     receiving form — audited in full, solid implementation
├── database/migrations/2026_09_07_03*.php    the 4 PO-related tables
└── tests/Feature/PurchaseOrderReceivingTest.php   NEW this session
```

For everything else (Inventory core, Transfers, Stock Alerts, Products, Auth, Accounts), the file locations are unchanged from prior documentation — see Sections 5, 8, 10, 11 for the relevant classes.

---

## 15. Definition of Done — Primary Workflow (PO → Receiving → Inventory → POS)

| # | Step | Status | Evidence |
|---|---|---|---|
| 1 | User creates Purchase Order | ✅ | `PurchaseOrderService::createDraft()`, tested |
| 2 | PO contains products and quantities | ✅ | `PurchaseOrderItem` rows, validated server-side |
| 3 | User receives the PO | ✅ | `receiveForm()` / `receive()`, tested |
| 4 | Received quantities are validated | ✅ | Over-receive rejected, tested |
| 5 | Inventory increases correctly | ✅ | Via `InventoryMovementService::addStock()`, tested (40 then +60 = 100) |
| 6 | Stock movement/history is recorded | ✅ | `InventoryTransaction` created per line, referencing PO number |
| 7 | PO received quantity updates | ✅ | `PurchaseOrderItem.quantity_received`, tested |
| 8 | PO remaining quantity is correct | ✅ | `getRemainingQuantityAttribute()`, tested |
| 9 | PO status becomes correct | ✅ | `partially_received` → `completed`, tested |
| 10 | POS obtains the updated stock | ✅ | Same `inventories` table, `/api/products` reads live |
| 11 | POS displays the new quantity | ✅ | 15s auto-poll + focus refresh (Section 9.2) — not covered by an automated cross-app test, but the mechanism is verified by code reading and the existing POS auto-refresh tests from an earlier session |
| 12 | No duplicate inventory increase occurs | ✅ | Row locking on PO + PO item, tested against a real over-receive attempt |
| 13 | Database remains consistent if an error occurs | ✅ | Everything inside one `DB::transaction()`, all-or-nothing |

**The primary workflow's Definition of Done is met.** Remaining work (Section 13) is about test coverage breadth and secondary risks (cost-price sync, POS/Inventory math duplication), not about the primary workflow being incomplete or broken.

---

## 16. AI Handoff — Start Here

**What this system is:** A Laravel 12 inventory/ERP app (`c:\projects\shogun\inventory`) with a companion, separately-deployed POS app (`c:\projects\shogun\possystem`) that reads this app's `/api/*` routes over a bearer token. Two different databases, two different `User` tables — never assume a user or session in one app applies to the other.

**Current architecture:** Traditional server-rendered Laravel (Blade + vanilla JS, no SPA framework). One service class per concern (`InventoryMovementService`, `PurchaseOrderService`, `StockAlertService`) — controllers are thin. `inventories.base_quantity` is the one and only source of truth for stock; everything else is derived or historical.

**What's already working (verified by tests, not assumed):** Products, units/conversion, inventory CRUD, stock in/out, the full two-phase audited transfer workflow, the out-of-stock quick-transfer request, persistent stock alerts, authentication, account self-service + admin management + forced password change, VAT-inclusive pricing with live breakdown, and — as of this session — the **complete PO → Receiving → Inventory → POS pipeline**.

**What was broken and got fixed this session:** `inventory_transfers` was missing 3 columns that the model/service code assumed existed (Section 1). Fixed with an additive migration; verified against both SQLite (tests) and the real SQL Server dev database.

**Current priority:** Section 13's P1 items — test coverage for `audit()`/`receive()` HTTP actions and PO approve/reject authorization. Neither is broken; both are just unverified by automated tests.

**Exact next task, if asked to continue this work:** Write `tests/Feature/InventoryTransferAuditTest.php` covering: assigned receiver can pass audit then receive (destination credited, status transitions); assigned receiver can fail audit (source stock returned, status=rejected); a user who is *not* the assigned receiver gets 403 on both `audit()` and `receive()`.

**Files to inspect first for any PO/receiving change:** `app/Services/PurchaseOrderService.php` (all logic lives here), `app/Http/Controllers/PurchaseOrderController.php` (thin wrapper), `tests/Feature/PurchaseOrderReceivingTest.php` (current coverage — extend, don't duplicate).

**Files to inspect first for any transfer change:** `app/Services/InventoryMovementService.php`, `app/Http/Controllers/InventoryTransferController.php`, `app/Models/InventoryTransfer.php` (check `$fillable`/`$casts` against the actual migrations before trusting either — this is exactly how the Section 1 bug was found).

**Important business rules (do not violate):**
1. `base_quantity` is authoritative; `reorder_point` is base-unit.
2. Every stock change goes through `InventoryMovementService` (in-process) or `InventoryApiController` (cross-app) — never write to `inventories` directly from a controller.
3. Every stock change creates an `InventoryTransaction`.
4. Transfers created via the checklist form do not credit the destination until `audit_status=passed` AND the receiver marks it received.
5. A failed audit must always fully reverse the source deduction in the same request.
6. PO receiving can never exceed `quantity_ordered - quantity_received` for a line.
7. `selling_price` is VAT-inclusive; VAT is disclosed, never added on top, in both Inventory's pricing preview and POS's receipt.
8. `VAT_RATE` (Inventory) and `POS_TAX_RATE` (POS) are two separate config values with no automatic sync — keep them equal by hand.

**Database constraints to respect:** SQL Server rejects a second cascade-delete path to the same table ("multiple cascade paths" error) — this codebase's convention is to cascade the primary FK and add any secondary FK to the same target table in a separate `Schema::table()` call with `->onDelete('no action')`. Follow this pattern for any new FK to `users` or `locations` from a table that already cascades there another way.

**Testing requirements:** `php artisan test` must run clean against the in-memory SQLite config in `phpunit.xml` — this is different from the production SQL Server driver, so avoid driver-specific raw SQL where possible (the one exception, `PurchaseOrderService::generatePoNumber()`'s `SUBSTRING()` call, was verified empirically in this session to work correctly on SQLite too — see the passing `test_po_numbers_increment_correctly_across_multiple_orders` test — but be aware this is exactly the kind of thing that silently breaks on a stricter driver, so re-verify if you ever change it).

**What NOT to change without a strong reason:**
- Don't rename or restructure `inventories`/`inventory_transactions` — everything reads through `InventoryMovementService`'s existing method signatures.
- Don't merge `transferStock()` and `initiateTransfer()` into one method — they're deliberately kept separate (immediate vs. audited), and `requestTransfer()`'s admin/manager path depends on the immediate one.
- Don't add PO-specific logic to the POS codebase — POS should only ever see `inventories.base_quantity`, never the purchasing process behind it.
- Don't reintroduce email-based password reset — this is a deliberate local/internal-system decision, not an oversight.

**Known risks carried forward (not fixed, not blocking):** cost-price/PO-price drift (Section 5), stock-math duplication between the two update paths (Section 9.3), `receiver_role` snapshot staleness (Section 8.4), VAT rate config duplication across apps (Section 6.1).

**Definition of Done for the primary workflow:** met — see Section 15's full checklist with evidence for every step.

---

## 17. Database Setup, Local Dev Commands, Security Checklist (unchanged from prior doc)

```env
DB_CONNECTION=sqlsrv
DB_HOST=127.0.0.1
DB_PORT=1433
DB_DATABASE=inventory
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

```powershell
php artisan migrate
php artisan db:seed
php artisan serve --host=127.0.0.1 --port=8001
php artisan test
```

Production hardening checklist (permissions, mail, backups, monitoring, secrets) is unchanged from the prior version of this document — none of that was in scope for this audit and no new information was found either way.

---

## 18. Changelog

### 2026-09-09 — UX pass, soft-delete/restore, deploy prep, repo cleanup

**Full suite: 136 tests passing** (up from 93).

- **UX pass across the whole app:** loading states + `.is-loading` spinner class on double-submit-guarded forms; toast notifications replacing full-page flash banners; a shared confirm-modal system (`data-confirm`/`data-confirm-title` attributes intercepted globally in `layouts/app.blade.php`) replacing every native `confirm()`/`prompt()` call across `companies`, `locations`, `product_categories`, `units_of_measure`, `suppliers`, `users`, `products`, `stock-movement-requests`, and inventory/transfer show pages; a global mobile table-scroll safety net (`table { display:block; overflow-x:auto; }` under 600px); a dev-mode banner (`@unless (app()->environment('production'))`) that disappears automatically once `APP_ENV=production` — no manual toggle needed at deploy time.
- **Soft-delete + Recently-Deleted/Restore added to `Location`, `Company`, `ProductCategory`, `UnitOfMeasure`** — each gained `deleted_at` (2 new migrations), a dependency-guarded `destroy()`, and a `trashed()`/`restore()` view+route pair. **`Product` was deliberately excluded** from the Restore UI (though it keeps the `SoftDeletes` trait on the model as a harmless backend safety net) — Products only ever had a Deactivate action, never a real Delete button, so a "Recently Deleted Products" screen would have had nothing to show.
- **Genuine pre-existing bug found and fixed while building this:** neither `ProductCategoryController` nor `UnitOfMeasureController` had a dependency guard before this change — a category or unit still referenced by products could previously be silently soft-deleted, orphaning those products. Both now block deletion with a clear message if anything still references them.
- **`PurchaseOrderService`** (`submitForApproval()`/`approve()`/`reject()`/`markOrdered()`) rewritten to `lockForUpdate()` + re-check status inside a `DB::transaction()` before mutating, closing a double-click race window where the same PO could be approved/rejected twice.
- **Account page redesigned** (hero header + Profile/Security/System sections) with a "Change Name" form added (`AccountController::updateName()`, logged via `AccountAuditLogger::nameChangedBySelf()`), mirroring the same feature added to POS.
- **Deploy prep:** `.env.production` template (not live, `APP_DEBUG=false`, fresh `APP_KEY`, `TODO:` markers for real secrets), `public/web.config` (IIS rewrite rules), `GET /api/config` route added, `/api/*` group throttled (`throttle:120,1`). `.env.example` fixed (`STOCK_APPROVAL_EMAILS_ENABLED`, `VAT_RATE=12`).
- **New tests:** `SoftDeleteTest.php` (12), extra double-transition-rejection cases in `PurchaseOrderApprovalTest.php`, a PDF-render test in `PurchaseOrderReceivingTest.php`, 10 new cases in `FormattingHelpersTest.php`.
- **Repo cleanup (2026-09-09):** deleted the `resources/views/purchase-orders/_backup_2026-09-08/` directory (7 files — a mid-session backup made before the PO index rewrite, superseded and no longer needed) and the stale `DEVELOPMENT_PROGRESS.md` (last touched 2026-09-02, weeks behind this file). This file is now the single documentation source alongside `README.md`.
- **Database confirmed unchanged in a way that would affect a `.bak` taken before this session's edits**: the only schema changes this session were additive migrations already covered above; no data was deleted or altered outside of normal app usage.

### 2026-09-08 — Documentation rewrite + critical migration fix + PO audit

- Rewrote this document from scratch after a full read of the actual codebase (not incremental notes) — corrected the recorded project path, corrected the test count, added full Purchase Order documentation (previously entirely missing), added Pricing/VAT and Account Management sections (previously entirely missing).
- **Found and fixed a critical bug**: `inventory_transfers` table was missing `status`, `received_by`, `received_at` columns that the model and service layer had assumed existed since an undocumented prior change. New migration: `2026_09_04_080800_add_status_and_receiving_fields_to_inventory_transfers_table.php`. Verified against both SQLite (tests) and the real SQL Server dev database.
- Rewrote 4 stale tests in `InventoryTransferTest.php` to match the current (correct, working) checklist/audited-receiver `store()` contract.
- Added `tests/Feature/PurchaseOrderReceivingTest.php` (3 tests) covering PO creation, PO-number generation/uniqueness (which incidentally serves as a SQLite-portability check for a SQL-Server-flavored raw query), and the full partial→complete receiving workflow including over-receive rejection.
- Test suite: Inventory **93 passed / 289 assertions** (was 85 passed / 6 failing before this session's fixes). POS **62 passed / 274 assertions** (unaffected, confirmed still green).
- Documented, but did not fix (Section 13, P2/P3): PO-price-to-cost-price sync gap, stock-math duplication between `InventoryMovementService` and `InventoryApiController`, `receiver_role` staleness, VAT rate config duplication.

### 2026-09-04 — Audited Receiver Workflow for Inventory Transfers
(Prior entry, preserved) Added the two-phase audited receipt process — see Section 8 for current-state documentation (this changelog entry originally described the design; Section 8 above reflects what was verified against the actual running code in this session, including the migration fix).
