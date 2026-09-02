@extends('layouts.app')

@section('title', 'Stock Movement Report')

@section('content')

<div class="page-header">
    <div>
        <h1>Stock Movement Report</h1>
        <p style="margin: 6px 0 0; color: #64748b;">Every recorded IN and OUT transaction.</p>
    </div>
    <a href="{{ route('reports.index') }}" class="btn btn-secondary">Back to Reports</a>
</div>

<div class="card" style="margin-bottom: 20px;">
    <form method="GET" action="{{ route('reports.stock-movements') }}" style="display: flex; gap: 12px; flex-wrap: wrap; align-items: end;">
        <div class="form-group" style="flex: 1; min-width: 180px; margin-bottom: 0;">
            <label for="search">Search</label>
            <input type="text" id="search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Product, SKU, or location">
        </div>
        <div class="form-group" style="min-width: 140px; margin-bottom: 0;">
            <label for="type">Type</label>
            <select id="type" name="type">
                <option value="">All</option>
                <option value="in" @selected(($filters['type'] ?? '') === 'in')>Stock In</option>
                <option value="out" @selected(($filters['type'] ?? '') === 'out')>Stock Out</option>
            </select>
        </div>
        <div class="form-group" style="min-width: 160px; margin-bottom: 0;">
            <label for="from">From</label>
            <input type="date" id="from" name="from" value="{{ $filters['from'] ?? '' }}">
        </div>
        <div class="form-group" style="min-width: 160px; margin-bottom: 0;">
            <label for="to">To</label>
            <input type="date" id="to" name="to" value="{{ $filters['to'] ?? '' }}">
        </div>
        <button type="submit" class="btn btn-primary">Filter</button>
        @if (array_filter($filters))
            <a href="{{ route('reports.stock-movements') }}" class="btn btn-secondary">Reset</a>
        @endif
    </form>
</div>

@if ($transactions->count())
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Type</th>
                    <th>Product</th>
                    <th>Location</th>
                    <th>Quantity</th>
                    <th>Base Quantity</th>
                    <th>Reference</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($transactions as $transaction)
                    <tr>
                        <td>{{ $transaction->created_at?->format('Y-m-d H:i') }}</td>
                        <td>
                            <span class="stock-status {{ $transaction->isIn() ? 'stock-ok' : 'stock-critical' }}">
                                {{ $transaction->getDirectionLabelAttribute() }}
                            </span>
                        </td>
                        <td>{{ $transaction->product?->name ?? 'Deleted product' }}</td>
                        <td>{{ $transaction->location?->name ?? 'Deleted location' }}</td>
                        <td>{{ number_format((float) $transaction->quantity, 4) }} {{ $transaction->productUnit?->unitOfMeasure?->code }}</td>
                        <td>{{ number_format((float) $transaction->base_quantity, 4) }}</td>
                        <td>{{ $transaction->reference ?: '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div style="margin-top: 20px;">
        {{ $transactions->links() }}
    </div>
@else
    <div class="empty-state">
        <p>No stock movements match these filters.</p>
    </div>
@endif

<style>
    .stock-status { display: inline-block; padding: 4px 9px; border-radius: 999px; font-size: 12px; font-weight: 700; }
    .stock-ok { background: #dcfce7; color: #166534; }
    .stock-critical { background: #fee2e2; color: #991b1b; }
</style>

@endsection
