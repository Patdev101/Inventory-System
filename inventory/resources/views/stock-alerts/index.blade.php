@extends('layouts.app')

@section('title', 'Stock Alerts')

@section('content')

<div class="page-header">
    <div>
        <h1>Stock Alerts</h1>
        <p>Persistent stock alerts, acknowledgement, and resolution history.</p>
    </div>
</div>

@if (session('success'))
    <div class="alert-success">{{ session('success') }}</div>
@endif

@forelse ($alerts as $alert)
    <div class="card stock-alert-history-card {{ $alert->status === 'resolved' ? 'stock-alert-resolved' : '' }}">
        <div class="stock-alert-history-header">
            <div>
                <h2 class="stock-alert-severity stock-alert-severity-{{ $alert->severity }}">
                    {{ str_replace('_', ' ', $alert->severity) }}
                </h2>
                <div class="stock-alert-meta">
                    {{ $alert->inventory?->product?->name ?? 'Deleted product' }} at
                    {{ $alert->inventory?->location?->name ?? 'Deleted location' }}
                </div>
            </div>
            <strong class="stock-alert-status">{{ ucfirst($alert->status) }}</strong>
        </div>

        <p>
            Current: {{ number_format((float) $alert->base_quantity, 4) }} base units
            | Reorder point: {{ number_format((float) $alert->reorder_point, 4) }}
        </p>

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
@empty
    <div class="empty-state">
        <p>No stock alert history yet.</p>
    </div>
@endforelse

<div class="pagination-wrap">
    {{ $alerts->links() }}
</div>

<style>
    .stock-alert-history-card { margin-bottom: 14px; }
    .stock-alert-history-header { display: flex; align-items: center; justify-content: space-between; gap: 16px; margin-bottom: 12px; }
    .stock-alert-history-header h2 { margin: 0 0 5px; font-size: 18px; text-transform: capitalize; }
    .stock-alert-meta { color: #64748b; font-size: 13px; }
    .stock-alert-status { color: #475569; font-size: 13px; }
    .stock-alert-severity-out_of_stock, .stock-alert-severity-critical { color: #b91c1c; }
    .stock-alert-severity-low { color: #b45309; }
    .stock-alert-resolved { opacity: .65; }
    .pagination-wrap { margin-top: 20px; }
    @media (max-width: 600px) { .stock-alert-history-header { align-items: flex-start; flex-direction: column; } }
</style>

@endsection
