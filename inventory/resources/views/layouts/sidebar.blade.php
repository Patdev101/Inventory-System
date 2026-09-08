<style>
    body:has(.system-shell) {
        padding: 0 !important;
        background: #f5f7fb !important;
    }

    .system-shell {
        min-height: 100vh;
        display: flex;
        background: #f5f7fb;
    }

    /* FIXED SIDEBAR */
    .system-sidebar {
        position: fixed;
        top: 0;
        left: 0;
        bottom: 0;
        width: 250px;
        display: flex;
        flex-direction: column;
        background: #111827;
        color: #d1d5db;
        z-index: 1000;
        border-right: 1px solid #1f2937;
        transition: transform .25s ease;
        overflow: hidden;
    }

    .system-sidebar-toggle {
        display: none;
        position: fixed;
        top: 15px;
        left: 15px;
        width: 40px;
        height: 40px;
        border: 0;
        border-radius: 8px;
        background: #111827;
        color: white;
        cursor: pointer;
        z-index: 1100;
        font-size: 18px;
    }

    .system-brand {
        height: 60px;
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 0 18px;
        color: white;
        text-decoration: none;
        border-bottom: 1px solid #1f2937;
        flex: 0 0 auto;
    }

    .system-brand-icon {
        width: 32px;
        height: 32px;
        display: grid;
        place-items: center;
        flex: 0 0 auto;
        border-radius: 8px;
        background: linear-gradient(135deg, #2563eb, #3b82f6);
        font-size: 16px;
    }

    .system-brand-title {
        display: block;
        color: white;
        font-size: 14px;
        font-weight: 700;
    }

    .system-brand-subtitle {
        display: block;
        margin-top: 1px;
        color: #9ca3af;
        font-size: 10px;
    }

    /*
     * SIDEBAR NAVIGATION
     * No overflow-y here.
     * The sidebar stays fixed.
     */
    .system-nav {
        flex: 1 1 auto;
        min-height: 0;
        padding: 10px 10px 4px;
        overflow-y: auto;
        overflow-x: hidden;
        scrollbar-width: thin;
        scrollbar-color: #374151 transparent;
    }

    .system-nav::-webkit-scrollbar {
        width: 6px;
    }

    .system-nav::-webkit-scrollbar-thumb {
        background: #374151;
        border-radius: 999px;
    }

    .system-nav-group {
        margin-bottom: 4px;
    }

    .system-nav-group + .system-nav-group {
        margin-top: 10px;
        padding-top: 10px;
        border-top: 1px solid #1f2937;
    }

    .system-nav-label {
        padding: 4px 10px 6px;
        color: #6b7280;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: .8px;
        text-transform: uppercase;
    }

    .system-nav-list {
        display: grid;
        gap: 2px;
        margin: 0;
        padding: 0;
        list-style: none;
    }

    .system-link {
        display: flex;
        align-items: center;
        gap: 10px;
        min-height: 34px;
        padding: 7px 10px;
        border-radius: 7px;
        color: #cbd5e1;
        text-decoration: none;
        font-size: 12.5px;
        font-weight: 500;
    }

    .system-link:hover {
        background: #1f2937;
        color: white;
    }

    .system-link.active {
        background: linear-gradient(90deg, #1d4ed8, #2563eb);
        color: white;
        box-shadow: 0 4px 12px rgba(37, 99, 235, .2);
    }

    .system-link-icon {
        width: 18px;
        color: #94a3b8;
        text-align: center;
        font-size: 13px;
        flex: 0 0 18px;
    }

    .system-link.active .system-link-icon,
    .system-link:hover .system-link-icon {
        color: white;
    }

    .system-link-badge {
        margin-left: auto;
        min-width: 16px;
        height: 16px;
        padding: 0 5px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 999px;
        background: #dc2626;
        color: white;
        font-size: 10px;
        font-weight: 700;
        line-height: 1;
    }

    .system-link.active .system-link-badge {
        background: white;
        color: #1d4ed8;
    }

    /* PURCHASE ORDER SPECIAL LINK */
    .system-link-purchase {
        border: 1px solid rgba(59, 130, 246, .18);
        background: rgba(37, 99, 235, .06);
    }

    .system-link-purchase:hover {
        background: #1f2937;
    }

    /* USER AREA */
    .system-user {
        flex: 0 0 auto;
        padding: 10px;
        border-top: 1px solid #1f2937;
        background: #0f172a;
    }

    .system-user-info {
        display: flex;
        align-items: center;
        gap: 9px;
        padding: 5px 5px 9px;
    }

    .system-avatar {
        width: 32px;
        height: 32px;
        display: grid;
        place-items: center;
        flex: 0 0 auto;
        border-radius: 50%;
        background: linear-gradient(135deg, #3b82f6, #60a5fa);
        color: white;
        font-size: 13px;
        font-weight: 700;
    }

    .system-user-details {
        min-width: 0;
        display: grid;
    }

    .system-user-details strong {
        overflow: hidden;
        color: white;
        font-size: 12.5px;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .system-user-details span {
        overflow: hidden;
        margin-top: 2px;
        color: #94a3b8;
        font-size: 9.5px;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .system-logout {
        width: 100%;
        display: flex;
        gap: 9px;
        align-items: center;
        padding: 8px 9px;
        border: 0;
        border-radius: 7px;
        background: transparent;
        color: #cbd5e1;
        cursor: pointer;
        font-size: 11.5px;
        text-align: left;
    }

    .system-logout:hover {
        background: #1f2937;
        color: #fca5a5;
    }

    /* MAIN CONTENT */
    .system-main {
        width: calc(100% - 250px);
        min-height: 100vh;
        margin-left: 250px;
        padding: 30px;
    }

    .system-main > .container {
        max-width: 1450px;
        padding: 0;
    }

    /* MOBILE */
    @media (max-width: 950px) {

        .system-sidebar {
            transform: translateX(-100%);
        }

        .system-sidebar.open {
            transform: translateX(0);
        }

        .system-sidebar-toggle {
            display: block;
        }

        .system-main {
            width: 100%;
            margin-left: 0;
            padding: 24px;
        }
    }

    @media (max-width: 600px) {

        .system-main {
            padding: 15px;
        }
    }
</style>


<button
    type="button"
    class="system-sidebar-toggle"
    onclick="document.querySelector('.system-sidebar').classList.toggle('open')"
    aria-label="Open navigation"
>
    ☰
</button>


<aside class="system-sidebar">

    {{-- BRAND --}}
    <a
        href="{{ route('dashboard') }}"
        class="system-brand"
    >
        <span class="system-brand-icon">
            📦
        </span>

        <span>
            <span class="system-brand-title">
                Inventory System
            </span>

            <span class="system-brand-subtitle">
                Management Dashboard
            </span>
        </span>
    </a>


    {{-- NAVIGATION --}}
    <nav class="system-nav">

        @php

            $user = auth()->user();

            /*
             * Count transfers assigned to the current user
             * that are still pending.
             */
            $pendingAuditsCount = $user
                ? \App\Models\InventoryTransfer::where(
                    'receiver_id',
                    $user->id
                )
                ->where(
                    'status',
                    'pending'
                )
                ->count()
                : 0;


            /*
             * Active stock alerts (open or acknowledged, not yet
             * resolved). Counted directly against stock_alerts rather
             * than re-running the synchronize scan here, since the
             * sidebar renders on every page load.
             */
            $stockAlertsCount = $user
                ? \App\Models\StockAlert::whereIn(
                    'status',
                    ['open', 'acknowledged']
                )->count()
                : 0;


            /*
             * Purchase order count.
             *
             * This is intentionally kept lightweight.
             * If the PurchaseOrder model exists, count draft/open
             * purchase orders. Otherwise return zero.
             */
            $purchaseOrdersCount = 0;

            if (
                $user &&
                class_exists(\App\Models\PurchaseOrder::class)
            ) {
                $purchaseOrdersCount = \App\Models\PurchaseOrder::whereIn(
                    'status',
                    [
                        'draft',
                        'pending',
                        'approved',
                        'ordered',
                        'partially_received',
                    ]
                )->count();
            }


            $systemNavigationGroups = [

                'Overview' => [
                    [
                        'route' => 'dashboard',
                        'label' => 'Dashboard',
                        'icon' => '▦',
                        'match' => 'dashboard',
                    ],
                ],

                'Catalog' => [
                    [
                        'route' => 'products.index',
                        'label' => 'Products',
                        'icon' => '◫',
                        'match' => 'products.*',
                    ],

                    [
                        'route' => 'product-categories.index',
                        'label' => 'Product Categories',
                        'icon' => '◇',
                        'match' => 'product-categories.*',
                        'roles' => ['admin'],
                    ],

                    [
                        'route' => 'units-of-measure.index',
                        'label' => 'Units of Measure',
                        'icon' => '#',
                        'match' => 'units-of-measure.*',
                        'roles' => ['admin'],
                    ],
                ],

                'Inventory' => [
                    [
                        'route' => 'inventories.index',
                        'label' => 'Inventory',
                        'icon' => '▤',
                        'match' => 'inventories.*',
                    ],

                    [
                        'route' => 'inventory-transfers.create',
                        'label' => 'Transfer Inventory',
                        'icon' => '⇄',
                        'match' => 'inventory-transfers.create',
                        'roles' => ['admin', 'manager'],
                    ],

                    [
                        'route' => 'inventory-transfers.pending-audits',
                        'label' => 'Audit Transfers',
                        'icon' => '✔',
                        'match' => 'inventory-transfers.pending-audits',
                        'badge' => $pendingAuditsCount,
                    ],

                    [
                        'route' => 'inventory-transfers.index',
                        'label' => 'Transfer History',
                        'icon' => '↔',
                        'match' => 'inventory-transfers.index|inventory-transfers.show',
                    ],

                    [
                        'route' => 'inventory-transactions.index',
                        'label' => 'Transactions',
                        'icon' => '≡',
                        'match' => 'inventory-transactions.*',
                    ],

                    [
                        'route' => 'stock-alerts.index',
                        'label' => 'Stock Alerts',
                        'icon' => '!',
                        'match' => 'stock-alerts.*',
                        'badge' => $stockAlertsCount,
                    ],
                ],

                'Purchasing' => [
                    [
                        'route' => 'purchase-orders.index',
                        'label' => 'Purchase Orders',
                        'icon' => '🛒',
                        'match' => 'purchase-orders.*',
                        'roles' => ['admin', 'manager'],
                        'badge' => $purchaseOrdersCount,
                        'class' => 'system-link-purchase',
                    ],

                    [
                        'route' => 'purchase-order-emails.index',
                        'label' => 'Sent Emails',
                        'icon' => '✉',
                        'match' => 'purchase-order-emails.*',
                    ],

                    [
                        'route' => 'stock-movement-requests.index',
                        'label' => 'Stock Approvals',
                        'icon' => '✓',
                        'match' => 'stock-movement-requests.*',
                        'roles' => ['admin', 'manager'],
                    ],

                    [
                        'route' => 'stock-movement-requests.index',
                        'label' => 'My Requests',
                        'icon' => '✓',
                        'match' => 'stock-movement-requests.*',
                        'roles' => ['staff'],
                    ],
                ],

                'Organization' => [
                    [
                        'route' => 'locations.index',
                        'label' => 'Locations',
                        'icon' => '◉',
                        'match' => 'locations.*',
                    ],

                    [
                        'route' => 'companies.index',
                        'label' => 'Companies',
                        'icon' => '◎',
                        'match' => 'companies.*',
                        'roles' => ['admin'],
                    ],

                    [
                        'route' => 'users.index',
                        'label' => 'Users',
                        'icon' => '◈',
                        'match' => 'users.*',
                        'roles' => ['admin', 'manager'],
                    ],
                ],

                'Reports' => [
                    [
                        'route' => 'reports.index',
                        'label' => 'Reports',
                        'icon' => '▥',
                        'match' => 'reports.*',
                    ],
                ],

                'Account' => [
                    [
                        'route' => 'account.edit',
                        'label' => 'My Account',
                        'icon' => '⚙',
                        'match' => 'account.*',
                    ],
                ],

            ];


            /*
             * Filter each group's items by role, then drop any
             * group left with no visible items.
             */
            $systemNavigationGroups = array_filter(
                array_map(
                    function ($items) use ($user) {

                        return array_filter(
                            $items,
                            function ($item) use ($user) {

                                if (!isset($item['roles'])) {
                                    return true;
                                }

                                if (!$user) {
                                    return false;
                                }

                                return $user->hasRole(...$item['roles']);
                            }
                        );
                    },
                    $systemNavigationGroups
                ),
                function ($items) {
                    return count($items) > 0;
                }
            );

        @endphp


        @foreach ($systemNavigationGroups as $groupLabel => $items)

            <div class="system-nav-group">

                <div class="system-nav-label">
                    {{ $groupLabel }}
                </div>


                <ul class="system-nav-list">

                    @foreach ($items as $item)

                        <li>

                            <a
                                href="{{ route($item['route']) }}"
                                class="system-link {{ $item['class'] ?? '' }} {{ request()->routeIs(explode('|', $item['match'])) ? 'active' : '' }}"
                                onclick="document.querySelector('.system-sidebar').classList.remove('open')"
                            >

                                <span class="system-link-icon">
                                    {{ $item['icon'] }}
                                </span>

                                <span>
                                    {{ $item['label'] }}
                                </span>

                                @if (!empty($item['badge']) && $item['badge'] > 0)

                                    <span class="system-link-badge">
                                        {{ $item['badge'] }}
                                    </span>

                                @endif

                            </a>

                        </li>

                    @endforeach

                </ul>

            </div>

        @endforeach

    </nav>


    {{-- USER AREA --}}
    <div class="system-user">

        <div class="system-user-info">

            <div class="system-avatar">
                {{ strtoupper(substr($user->name ?? 'U', 0, 1)) }}
            </div>

            <div class="system-user-details">

                <strong>
                    {{ $user->name ?? 'User' }}
                </strong>

                <span>
                    {{ $user->email ?? '' }}
                </span>

            </div>

        </div>


        <form
            method="POST"
            action="{{ route('logout') }}"
        >

            @csrf

            <button
                type="submit"
                class="system-logout"
            >
                <span>↪</span>
                <span>Sign Out</span>
            </button>

        </form>

    </div>

</aside>
