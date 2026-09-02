<style>
    body:has(.system-shell) { padding: 0 !important; background: #f5f7fb !important; }
    .system-shell { min-height: 100vh; display: flex; background: #f5f7fb; }
    .system-sidebar { position: fixed; inset: 0 auto 0 0; width: 250px; display: flex; flex-direction: column; background: #111827; color: #d1d5db; z-index: 1000; border-right: 1px solid #1f2937; transition: transform .25s ease; }
    .system-sidebar-toggle { display: none; position: fixed; top: 15px; left: 15px; width: 40px; height: 40px; border: 0; border-radius: 8px; background: #111827; color: white; cursor: pointer; z-index: 1100; font-size: 18px; }
    .system-brand { height: 60px; display: flex; align-items: center; gap: 10px; padding: 0 18px; color: white; text-decoration: none; border-bottom: 1px solid #1f2937; flex: 0 0 auto; }
    .system-brand-icon { width: 32px; height: 32px; display: grid; place-items: center; flex: 0 0 auto; border-radius: 8px; background: linear-gradient(135deg, #2563eb, #3b82f6); font-size: 16px; }
    .system-brand-title { color: white; font-size: 14px; font-weight: 700; }
    .system-brand-subtitle { margin-top: 1px; color: #9ca3af; font-size: 10px; }
    .system-nav { flex: 1 1 auto; min-height: 0; overflow-y: auto; padding: 10px 10px; scrollbar-width: thin; scrollbar-color: #374151 transparent; }
    .system-nav::-webkit-scrollbar { width: 5px; }
    .system-nav::-webkit-scrollbar-thumb { background: #374151; border-radius: 3px; }
    .system-nav::-webkit-scrollbar-track { background: transparent; }
    .system-nav-label { padding: 6px 10px 5px; color: #6b7280; font-size: 10px; font-weight: 700; letter-spacing: .8px; text-transform: uppercase; }
    .system-nav-list { display: grid; gap: 2px; margin: 0; padding: 0; list-style: none; }
    .system-link { display: flex; align-items: center; gap: 10px; min-height: 34px; padding: 7px 10px; border-radius: 7px; color: #cbd5e1; text-decoration: none; font-size: 12.5px; font-weight: 500; }
    .system-link:hover { background: #1f2937; color: white; }
    .system-link.active { background: linear-gradient(90deg, #1d4ed8, #2563eb); color: white; box-shadow: 0 4px 12px rgba(37,99,235,.2); }
    .system-link-icon { width: 18px; color: #94a3b8; text-align: center; font-size: 13px; }
    .system-link.active .system-link-icon, .system-link:hover .system-link-icon { color: white; }
    .system-user { flex: 0 0 auto; padding: 10px; border-top: 1px solid #1f2937; background: #0f172a; }
    .system-user-info { display: flex; align-items: center; gap: 9px; padding: 5px 5px 9px; }
    .system-avatar { width: 32px; height: 32px; display: grid; place-items: center; flex: 0 0 auto; border-radius: 50%; background: linear-gradient(135deg,#3b82f6,#60a5fa); color: white; font-size: 13px; font-weight: 700; }
    .system-user-details { min-width: 0; display: grid; }
    .system-user-details strong { overflow: hidden; color: white; font-size: 12.5px; text-overflow: ellipsis; white-space: nowrap; }
    .system-user-details span { overflow: hidden; margin-top: 2px; color: #94a3b8; font-size: 9.5px; text-overflow: ellipsis; white-space: nowrap; }
    .system-logout { width: 100%; display: flex; gap: 9px; align-items: center; padding: 8px 9px; border: 0; border-radius: 7px; background: transparent; color: #cbd5e1; cursor: pointer; font-size: 11.5px; text-align: left; }
    .system-logout:hover { background: #1f2937; color: #fca5a5; }
    .system-main { width: calc(100% - 250px); min-height: 100vh; margin-left: 250px; padding: 30px; }
    .system-main > .container { max-width: 1450px; padding: 0; }
    @media (max-width: 950px) { .system-sidebar { transform: translateX(-100%); } .system-sidebar.open { transform: translateX(0); } .system-sidebar-toggle { display: block; } .system-main { width: 100%; margin-left: 0; padding: 24px; } }
    @media (max-width: 600px) { .system-main { padding: 15px; } }
</style>

<button type="button" class="system-sidebar-toggle" onclick="document.querySelector('.system-sidebar').classList.toggle('open')" aria-label="Open navigation">☰</button>

<aside class="system-sidebar">
    <a href="{{ route('dashboard') }}" class="system-brand">
        <span class="system-brand-icon">📦</span>
        <span>
            <span class="system-brand-title">Inventory System</span>
            <span class="system-brand-subtitle">Management Dashboard</span>
        </span>
    </a>

    <nav class="system-nav">
        @php
            $systemNavigation = [
                ['route' => 'dashboard', 'label' => 'Dashboard', 'icon' => '▦', 'match' => 'dashboard'],
                ['route' => 'products.index', 'label' => 'Products', 'icon' => '◫', 'match' => 'products.*'],
                ['route' => 'inventories.index', 'label' => 'Inventory', 'icon' => '▤', 'match' => 'inventories.*'],
                ['route' => 'inventory-transfers.create', 'label' => 'Transfer Inventory', 'icon' => '⇄', 'match' => 'inventory-transfers.create', 'roles' => ['admin', 'manager']],
                ['route' => 'inventory-transfers.index', 'label' => 'Transfer History', 'icon' => '↔', 'match' => 'inventory-transfers.*'],
                ['route' => 'inventory-transactions.index', 'label' => 'Transactions', 'icon' => '≡', 'match' => 'inventory-transactions.*'],
                ['route' => 'stock-alerts.index', 'label' => 'Stock Alerts', 'icon' => '!', 'match' => 'stock-alerts.*'],
                ['route' => 'stock-movement-requests.index', 'label' => 'Stock Approvals', 'icon' => '✓', 'match' => 'stock-movement-requests.*', 'roles' => ['admin', 'manager']],
                ['route' => 'stock-movement-requests.index', 'label' => 'My Requests', 'icon' => '✓', 'match' => 'stock-movement-requests.*', 'roles' => ['staff']],
                ['route' => 'reports.index', 'label' => 'Reports', 'icon' => '▥', 'match' => 'reports.*'],
                ['route' => 'locations.index', 'label' => 'Locations', 'icon' => '◉', 'match' => 'locations.*'],
                ['route' => 'companies.index', 'label' => 'Companies', 'icon' => '◎', 'match' => 'companies.*', 'roles' => ['admin']],
                ['route' => 'product-categories.index', 'label' => 'Product Categories', 'icon' => '◇', 'match' => 'product-categories.*', 'roles' => ['admin']],
                ['route' => 'units-of-measure.index', 'label' => 'Units of Measure', 'icon' => '#', 'match' => 'units-of-measure.*', 'roles' => ['admin']],
                ['route' => 'users.index', 'label' => 'Users', 'icon' => '◈', 'match' => 'users.*', 'roles' => ['admin', 'manager']],
            ];

            $systemNavigation = array_filter(
                $systemNavigation,
                fn ($item) => !isset($item['roles']) || (auth()->user() && auth()->user()->hasRole(...$item['roles']))
            );
        @endphp

        <div class="system-nav-label">Navigation</div>
        <ul class="system-nav-list">
            @foreach ($systemNavigation as $item)
                <li>
                    <a href="{{ route($item['route']) }}" class="system-link {{ request()->routeIs($item['match']) ? 'active' : '' }}" onclick="document.querySelector('.system-sidebar').classList.remove('open')">
                        <span class="system-link-icon">{{ $item['icon'] }}</span>
                        <span>{{ $item['label'] }}</span>
                    </a>
                </li>
            @endforeach
        </ul>
    </nav>

    <div class="system-user">
        <div class="system-user-info">
            <div class="system-avatar">{{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}</div>
            <div class="system-user-details">
                <strong>{{ auth()->user()->name ?? 'User' }}</strong>
                <span>{{ auth()->user()->email ?? '' }}</span>
            </div>
        </div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="system-logout"><span>↪</span><span>Sign Out</span></button>
        </form>
    </div>
</aside>
