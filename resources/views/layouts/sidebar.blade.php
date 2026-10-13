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
        background: #2563eb;
        color: white;
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
        font-size: 11px;
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
        margin-top: 14px;
    }

    .system-nav-label {
        padding: 4px 10px 6px;
        color: #7c8799;
        font-size: 11px;
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
        font-size: 13.5px;
        font-weight: 400;
        line-height: 1.3;
    }

    .system-link:hover {
        background: #1f2937;
        color: white;
    }

    .system-link.active {
        background: #2563eb;
        color: white;
        font-weight: 600;
    }

    .system-link-icon {
        width: 18px;
        height: 18px;
        display: grid;
        place-items: center;
        color: #8391a7;
        flex: 0 0 18px;
    }

    .system-link-icon svg,
    .system-brand-icon svg,
    .system-logout svg {
        width: 17px;
        height: 17px;
        fill: none;
        stroke: currentColor;
        stroke-width: 1.7;
        stroke-linecap: round;
        stroke-linejoin: round;
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
        font-size: 11.5px;
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
        font-family: inherit;
        font-size: 13px;
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
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M21 8l-9-5-9 5v8l9 5 9-5zM3 8l9 5 9-5M12 13v8"/></svg>
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
            /*
             * The three badge counts below run on every page, so each is
             * remembered for 20 seconds instead of hitting the database on
             * every click.
             */
            $badgeTtl = 20;

            $pendingAuditsCount = $user
                ? \Illuminate\Support\Facades\Cache::remember(
                    'nav:pending-audits:' . $user->id,
                    $badgeTtl,
                    fn () => \App\Models\InventoryTransfer::where('receiver_id', $user->id)
                        ->where('status', 'pending')
                        ->count()
                )
                : 0;


            /*
             * Active stock alerts (open or acknowledged, not yet
             * resolved). Counted directly against stock_alerts rather
             * than re-running the synchronize scan here, since the
             * sidebar renders on every page load.
             */
            $stockAlertsCount = $user
                ? \Illuminate\Support\Facades\Cache::remember(
                    'nav:stock-alerts:' . $user->id,
                    $badgeTtl,
                    fn () => \App\Models\StockAlert::whereIn('status', ['open', 'acknowledged'])->count()
                )
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
                $purchaseOrdersCount = \Illuminate\Support\Facades\Cache::remember(
                    'nav:open-purchase-orders:' . $user->id,
                    $badgeTtl,
                    fn () => \App\Models\PurchaseOrder::whereIn(
                        'status',
                        [
                            'draft',
                            'pending',
                            'approved',
                            'ordered',
                            'partially_received',
                        ]
                    )->count()
                );
            }


            /*
             * Menu icons: one consistent line-drawn set, kept here so the
             * sidebar needs no icon font or extra download.
             */
            $systemNavigationIcons = [
                'home' => '<path d="M3 11l9-8 9 8M5 10v10h5v-6h4v6h5V10"/>',
                'box' => '<path d="M21 8l-9-5-9 5v8l9 5 9-5zM3 8l9 5 9-5M12 13v8"/>',
                'tag' => '<path d="M3 12V4h8l10 10-8 8zM7.5 8.5h.01"/>',
                'ruler' => '<path d="M3 17L17 3l4 4L7 21zM8 12l2 2M11 9l2 2M14 6l2 2"/>',
                'stock' => '<path d="M4 7h16M4 12h16M4 17h16"/>',
                'move' => '<path d="M7 7h13l-3-3M17 17H4l3 3"/>',
                'check' => '<path d="M9 11l3 3 8-8M20 12v7H4V5h11"/>',
                'history' => '<path d="M3 12a9 9 0 109-9 9 9 0 00-7 3.4M3 4v4h4M12 7v5l3 2"/>',
                'list' => '<path d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"/>',
                'alert' => '<path d="M12 3l10 18H2zM12 10v5M12 18h.01"/>',
                'cart' => '<path d="M3 4h2l2.5 11h10L20 7H6M9 20h.01M17 20h.01"/>',
                'mail' => '<path d="M3 6h18v12H3zM3 7l9 6 9-6"/>',
                'approve' => '<path d="M12 21a9 9 0 100-18 9 9 0 000 18zM8 12l3 3 5-6"/>',
                'pin' => '<path d="M12 21s7-6.5 7-12a7 7 0 10-14 0c0 5.500 7 12 7 12zM12 11.5a2.500 2.500 0 100-5 2.500 2.500 0 000 5z"/>',
                'building' => '<path d="M4 21V4h10v17M14 9h6v12M8 8h2M8 12h2M8 16h2M17 13h.01M17 17h.01M3 21h18"/>',
                'users' => '<path d="M16 20v-1a4 4 0 00-4-4H7a4 4 0 00-4 4v1M9.500 11a3.500 3.500 0 100-7 3.500 3.500 0 000 7zM21 20v-1a4 4 0 00-3-3.900M16 4.100a3.500 3.500 0 010 6.800"/>',
                'chart' => '<path d="M4 20V10M10 20V4M16 20v-7M22 20H2"/>',
                'gear' => '<path d="M12 15a3 3 0 100-6 3 3 0 000 6zM19 12l2-1-2-4-2 1a7 7 0 00-2-1l-.500-2h-4L10 7a7 7 0 00-2 1L6 7 4 11l2 1a7 7 0 000 2l-2 1 2 4 2-1a7 7 0 002 1l.500 2h4l.500-2a7 7 0 002-1l2 1 2-4-2-1a7 7 0 000-2z"/>',
            ];


            $systemNavigationGroups = [

                'Overview' => [
                    [
                        'route' => 'dashboard',
                        'label' => 'Dashboard',
                        'icon' => 'home',
                        'match' => 'dashboard',
                    ],
                ],

                'Catalog' => [
                    [
                        'route' => 'products.index',
                        'label' => 'Products',
                        'icon' => 'box',
                        'match' => 'products.*',
                    ],

                    [
                        'route' => 'product-categories.index',
                        'label' => 'Product Categories',
                        'icon' => 'tag',
                        'match' => 'product-categories.*',
                        'roles' => ['admin'],
                    ],

                    [
                        'route' => 'units-of-measure.index',
                        'label' => 'Units of Measure',
                        'icon' => 'ruler',
                        'match' => 'units-of-measure.*',
                        'roles' => ['admin'],
                    ],
                ],

                'Inventory' => [
                    [
                        'route' => 'inventories.index',
                        'label' => 'Inventory',
                        'icon' => 'stock',
                        'match' => 'inventories.*',
                    ],

                    [
                        'route' => 'inventory-transfers.create',
                        'label' => 'Transfer Inventory',
                        'icon' => 'move',
                        'match' => 'inventory-transfers.create',
                        'roles' => ['admin', 'manager'],
                    ],

                    [
                        'route' => 'inventory-transfers.pending-audits',
                        'label' => 'Audit Transfers',
                        'icon' => 'check',
                        'match' => 'inventory-transfers.pending-audits',
                        'badge' => $pendingAuditsCount,
                    ],

                    [
                        'route' => 'inventory-transfers.index',
                        'label' => 'Transfer History',
                        'icon' => 'history',
                        'match' => 'inventory-transfers.index|inventory-transfers.show',
                    ],

                    [
                        'route' => 'inventory-transactions.index',
                        'label' => 'Transactions',
                        'icon' => 'list',
                        'match' => 'inventory-transactions.*',
                    ],

                    [
                        'route' => 'stock-alerts.index',
                        'label' => 'Stock Alerts',
                        'icon' => 'alert',
                        'match' => 'stock-alerts.*',
                        'badge' => $stockAlertsCount,
                    ],
                ],

                'Purchasing' => [
                    [
                        'route' => 'purchase-orders.index',
                        'label' => 'Purchase Orders',
                        'icon' => 'cart',
                        'match' => 'purchase-orders.*',
                        'roles' => ['admin', 'manager'],
                        'badge' => $purchaseOrdersCount,
                        'class' => 'system-link-purchase',
                    ],

                    [
                        'route' => 'purchase-order-emails.index',
                        'label' => 'Sent Emails',
                        'icon' => 'mail',
                        'match' => 'purchase-order-emails.*',
                    ],

                    [
                        'route' => 'stock-movement-requests.index',
                        'label' => 'Stock Approvals',
                        'icon' => 'approve',
                        'match' => 'stock-movement-requests.*',
                        'roles' => ['admin', 'manager'],
                    ],

                    [
                        'route' => 'stock-movement-requests.index',
                        'label' => 'My Requests',
                        'icon' => 'approve',
                        'match' => 'stock-movement-requests.*',
                        'roles' => ['staff'],
                    ],
                ],

                'Organization' => [
                    [
                        'route' => 'locations.index',
                        'label' => 'Locations',
                        'icon' => 'pin',
                        'match' => 'locations.*',
                    ],

                    [
                        'route' => 'companies.index',
                        'label' => 'Companies',
                        'icon' => 'building',
                        'match' => 'companies.*',
                        'roles' => ['admin'],
                    ],

                    [
                        'route' => 'users.index',
                        'label' => 'Users',
                        'icon' => 'users',
                        'match' => 'users.*',
                        'roles' => ['admin', 'manager'],
                    ],
                ],

                'Reports' => [
                    [
                        'route' => 'reports.index',
                        'label' => 'Reports',
                        'icon' => 'chart',
                        'match' => 'reports.*',
                    ],
                ],

                'Account' => [
                    [
                        'route' => 'account.edit',
                        'label' => 'My Account',
                        'icon' => 'gear',
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
                                    <svg viewBox="0 0 24 24" aria-hidden="true">{!! $systemNavigationIcons[$item['icon']] ?? '' !!}</svg>
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
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M9 21H5V3h4M16 17l5-5-5-5M21 12H9"/></svg>
                <span>Sign Out</span>
            </button>

        </form>

    </div>

</aside>
