@extends('layouts.app')

@section('title', 'Low Stock Report')

@section('content')

<div class="page-header">
    <div>
        <h1>Low Stock Report</h1>
        <p style="margin: 6px 0 0; color: #64748b;">Inventory at or below its reorder point.</p>
    </div>
    <a href="{{ route('reports.index') }}" class="btn btn-secondary">Back to Reports</a>
</div>

<div class="card" style="margin-bottom: 20px;">
    <div style="display: flex; gap: 8px; flex-wrap: wrap;">
        <a href="{{ route('reports.low-stock', ['status' => 'all']) }}" class="btn {{ $status === 'all' ? 'btn-primary' : 'btn-secondary' }}">All</a>
        <a href="{{ route('reports.low-stock', ['status' => 'out_of_stock']) }}" class="btn {{ $status === 'out_of_stock' ? 'btn-primary' : 'btn-secondary' }}">Out of Stock</a>
        <a href="{{ route('reports.low-stock', ['status' => 'critical']) }}" class="btn {{ $status === 'critical' ? 'btn-primary' : 'btn-secondary' }}">Critical</a>
        <a href="{{ route('reports.low-stock', ['status' => 'low']) }}" class="btn {{ $status === 'low' ? 'btn-primary' : 'btn-secondary' }}">Low Stock</a>
    </div>
</div>

@if ($inventories->count())
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Location</th>
                    <th>Base Quantity</th>
                    <th>Reorder Point</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($inventories as $inventory)
                    @php
                        $stockStatus = $inventory->getStockStatus();
                        $stockStatusClass = match ($stockStatus) {
                            'Out of Stock', 'Critical' => 'stock-critical',
                            'Low Stock' => 'stock-low',
                            default => 'stock-ok',
                        };
                    @endphp
                    <tr>
                        <td>{{ $inventory->product?->name ?? 'Deleted product' }}</td>
                        <td>{{ $inventory->location?->name ?? 'Deleted location' }}</td>
                        <td>{{ number_format($inventory->getBaseQuantityValue(), 4) }}</td>
                        <td>{{ number_format($inventory->getReorderPointValue(), 4) }}</td>
                        <td><span class="stock-status {{ $stockStatusClass }}">{{ $stockStatus }}</span></td>
                        <td><a href="{{ route('inventories.show', $inventory) }}" class="btn btn-secondary">View</a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div style="margin-top: 20px;">
        {{ $inventories->appends(['status' => $status])->links() }}
    </div>
@else
    <div class="empty-state">
        <p>No inventory records match this filter.</p>
    </div>
@endif

<style>
    .stock-status { display: inline-block; padding: 4px 9px; border-radius: 999px; font-size: 12px; font-weight: 700; }
    .stock-ok { background: #dcfce7; color: #166534; }
    .stock-low { background: #fef3c7; color: #92400e; }
    .stock-critical { background: #fee2e2; color: #991b1b; }
</style>

@endsection
