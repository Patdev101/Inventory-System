@extends('layouts.app')

@section('title', 'Stock Alerts')

@section('content')

<div class="page-header">
    <div>
        <h1>Stock Alerts</h1>
        <p>Live low-stock, critical, and out-of-stock alerts across all locations.</p>
    </div>
</div>



{{-- SUMMARY --}}
<div class="alert-stats-row">

    <div class="alert-stat alert-stat-danger">
        <div class="alert-stat-value">{{ $counts['out_of_stock'] }}</div>
        <div class="alert-stat-label">Out of Stock</div>
    </div>

    <div class="alert-stat alert-stat-orange">
        <div class="alert-stat-value">{{ $counts['critical'] }}</div>
        <div class="alert-stat-label">Critical</div>
    </div>

    <div class="alert-stat alert-stat-warning">
        <div class="alert-stat-value">{{ $counts['low'] }}</div>
        <div class="alert-stat-label">Low Stock</div>
    </div>

    <div class="alert-stat alert-stat-muted">
        <div class="alert-stat-value">{{ $counts['acknowledged'] }}</div>
        <div class="alert-stat-label">Acknowledged</div>
    </div>

    <div class="alert-stat alert-stat-success">
        <div class="alert-stat-value">{{ $counts['resolved'] }}</div>
        <div class="alert-stat-label">Resolved</div>
    </div>

</div>


{{-- FILTERS --}}
<div class="alert-filters">

    <div class="filter-group-tabs">

        @foreach ([
            'active' => 'Active',
            'open' => 'Open',
            'acknowledged' => 'Acknowledged',
            'resolved' => 'Resolved',
        ] as $key => $label)

            <a
                href="{{ route('stock-alerts.index', array_filter(['status' => $key, 'severity' => $severity])) }}"
                class="filter-tab {{ $status === $key ? 'active' : '' }}"
            >
                {{ $label }}
            </a>

        @endforeach

    </div>

    <div class="filter-group-select">

        <select onchange="window.location.href = this.value">

            <option value="{{ route('stock-alerts.index', array_filter(['status' => $status])) }}" {{ !$severity ? 'selected' : '' }}>
                All severities
            </option>

            @foreach (['out_of_stock' => 'Out of Stock', 'critical' => 'Critical', 'low' => 'Low Stock'] as $key => $label)

                <option
                    value="{{ route('stock-alerts.index', array_filter(['status' => $status, 'severity' => $key])) }}"
                    {{ $severity === $key ? 'selected' : '' }}
                >
                    {{ $label }}
                </option>

            @endforeach

        </select>

    </div>

</div>


@forelse ($alerts as $alert)

    <div class="card stock-alert-card {{ $alert->status === 'resolved' ? 'stock-alert-resolved' : '' }}">

        <div class="stock-alert-thumb">

            @if ($alert->inventory?->product?->image_url)

                <img
                    src="{{ $alert->inventory->product->image_url }}"
                    alt="{{ $alert->inventory->product->name }}"
                >

            @else

                <div class="stock-alert-thumb-empty">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="4" width="18" height="16" rx="2"></rect>
                        <circle cx="9" cy="10" r="2"></circle>
                        <path d="M21 16l-5.2-5.2a2 2 0 0 0-2.8 0L4 20"></path>
                    </svg>
                </div>

            @endif

        </div>

        <div class="stock-alert-body">

            <div class="stock-alert-history-header">

                <div>

                    <div class="stock-alert-badges">

                        <span class="severity-badge severity-{{ $alert->severity }}">
                            {{ str_replace('_', ' ', $alert->severity) }}
                        </span>

                        <span class="status-pill status-pill-{{ $alert->status }}">
                            {{ ucfirst($alert->status) }}
                        </span>

                    </div>

                    <div class="stock-alert-product">
                        {{ $alert->inventory?->product?->name ?? 'Deleted product' }}
                    </div>

                    <div class="stock-alert-meta">
                        at {{ $alert->inventory?->location?->name ?? 'Deleted location' }}
                    </div>

                </div>

            </div>

            <p class="stock-alert-quantities">
                Current: <strong>{{ format_qty((float) $alert->base_quantity) }}</strong> base units
                &middot;
                Reorder point: <strong>{{ format_qty((float) $alert->reorder_point) }}</strong>
            </p>

            @if ($alert->status === 'acknowledged' && $alert->acknowledgedBy)

                <p class="stock-alert-ack-note">
                    Acknowledged by {{ $alert->acknowledgedBy->name }}
                    @if ($alert->acknowledged_at)
                        on {{ format_datetime($alert->acknowledged_at) }}
                    @endif
                </p>

            @endif

            <div class="actions">

                @if ($alert->inventory)
                    <a class="btn btn-primary" href="{{ route('inventories.show', $alert->inventory) }}">View Inventory</a>
                @endif

                @if (auth()->user()->hasRole('admin', 'manager'))

                    @if ($alert->status === 'open')
                        <form method="POST" action="{{ route('stock-alerts.acknowledge', $alert) }}">
                            @csrf
                            @method('PATCH')
                            <button class="btn btn-secondary" type="submit">Acknowledge</button>
                        </form>
                    @endif

                    @if (in_array($alert->status, ['open', 'acknowledged'], true))
                        <form method="POST" action="{{ route('stock-alerts.resolve', $alert) }}">
                            @csrf
                            @method('PATCH')
                            <button class="btn btn-success" type="submit">Resolve</button>
                        </form>
                    @endif

                @endif

            </div>

        </div>

    </div>

@empty

    <div class="empty-state">
        <p>No stock alerts match this filter.</p>
        <a href="{{ route('stock-alerts.index') }}" class="btn btn-secondary">Clear Filters</a>
    </div>

@endforelse

<div class="pagination-wrap">
    {{ $alerts->links() }}
</div>


<style>

    .alert-stats-row {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: 14px;
        margin-bottom: 20px;
    }

    .alert-stat {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 16px 14px;
        border-left: 4px solid #cbd5e1;
    }

    .alert-stat-value {
        font-size: 24px;
        font-weight: 800;
        color: #0f172a;
    }

    .alert-stat-label {
        margin-top: 4px;
        color: #64748b;
        font-size: 12px;
        font-weight: 600;
    }

    .alert-stat-danger { border-left-color: #dc2626; }
    .alert-stat-orange { border-left-color: #ea580c; }
    .alert-stat-warning { border-left-color: #d97706; }
    .alert-stat-muted { border-left-color: #64748b; }
    .alert-stat-success { border-left-color: #16a34a; }

    .alert-filters {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
        margin-bottom: 18px;
    }

    .filter-group-tabs {
        display: flex;
        gap: 6px;
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 4px;
    }

    .filter-tab {
        padding: 7px 14px;
        border-radius: 7px;
        font-size: 13px;
        font-weight: 600;
        color: #475569;
        text-decoration: none;
    }

    .filter-tab:hover {
        background: #f1f5f9;
        color: #1e293b;
    }

    .filter-tab.active {
        background: #2563eb;
        color: #fff;
    }

    .filter-group-select select {
        padding: 8px 12px;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        background: #fff;
        font-size: 13px;
        font-weight: 600;
        color: #334155;
    }

    .stock-alert-card {
        display: flex;
        gap: 16px;
        margin-bottom: 14px;
    }

    .stock-alert-thumb {
        flex-shrink: 0;
        width: 56px;
        height: 56px;
        border-radius: 10px;
        overflow: hidden;
        border: 1px solid #e2e8f0;
    }

    .stock-alert-thumb img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .stock-alert-thumb-empty {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f1f5f9;
        color: #94a3b8;
    }

    .stock-alert-body {
        flex: 1;
        min-width: 0;
    }

    .stock-alert-history-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 16px;
        margin-bottom: 8px;
    }

    .stock-alert-badges {
        display: flex;
        gap: 6px;
        margin-bottom: 6px;
    }

    .severity-badge {
        display: inline-flex;
        padding: 3px 9px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 700;
        text-transform: capitalize;
    }

    .severity-out_of_stock {
        background: #fee2e2;
        color: #991b1b;
    }

    .severity-critical {
        background: #ffedd5;
        color: #9a3412;
    }

    .severity-low {
        background: #fef3c7;
        color: #92400e;
    }

    .status-pill {
        display: inline-flex;
        padding: 3px 9px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 700;
    }

    .status-pill-open {
        background: #dbeafe;
        color: #1e40af;
    }

    .status-pill-acknowledged {
        background: #f1f5f9;
        color: #475569;
    }

    .status-pill-resolved {
        background: #dcfce7;
        color: #166534;
    }

    .stock-alert-product {
        font-weight: 700;
        color: #0f172a;
        font-size: 15px;
    }

    .stock-alert-meta {
        color: #64748b;
        font-size: 13px;
        margin-top: 2px;
    }

    .stock-alert-quantities {
        margin: 10px 0;
        color: #475569;
        font-size: 13px;
    }

    .stock-alert-ack-note {
        margin: 0 0 10px;
        color: #64748b;
        font-size: 12px;
        font-style: italic;
    }

    .stock-alert-resolved {
        opacity: .65;
    }

    .pagination-wrap {
        margin-top: 20px;
    }

    @media (max-width: 900px) {

        .alert-stats-row {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

    }

    @media (max-width: 600px) {

        .alert-filters {
            flex-direction: column;
            align-items: stretch;
        }

        .filter-group-tabs {
            overflow-x: auto;
        }

        .stock-alert-card {
            flex-direction: column;
        }

    }

</style>

@endsection
