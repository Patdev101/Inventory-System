@extends('layouts.app')

@section('title', 'Transfer Report')

@section('content')

<div class="page-header">
    <div>
        <h1>Transfer Report</h1>
        <p style="margin: 6px 0 0; color: #64748b;">Stock moved between locations.</p>
    </div>
    <a href="{{ route('reports.index') }}" class="btn btn-secondary">Back to Reports</a>
</div>

<div class="card" style="margin-bottom: 20px;">
    <form method="GET" action="{{ route('reports.transfers') }}" style="display: flex; gap: 12px; flex-wrap: wrap; align-items: end;">
        <div class="form-group" style="flex: 1; min-width: 180px; margin-bottom: 0;">
            <label for="search">Search</label>
            <input type="text" id="search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Product name">
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
            <a href="{{ route('reports.transfers') }}" class="btn btn-secondary">Reset</a>
        @endif
    </form>
</div>

@if ($transfers->count())
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Product</th>
                    <th>From</th>
                    <th>To</th>
                    <th>Quantity</th>
                    <th>Reference</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($transfers as $transfer)
                    <tr>
                        <td>{{ $transfer->created_at?->format('Y-m-d H:i') }}</td>
                        <td>{{ $transfer->product?->name ?? 'Deleted product' }}</td>
                        <td>{{ $transfer->sourceInventory?->location?->name ?? '-' }}</td>
                        <td>{{ $transfer->destinationInventory?->location?->name ?? '-' }}</td>
                        <td>{{ number_format((float) $transfer->quantity, 4) }} {{ $transfer->productUnit?->unitOfMeasure?->code }}</td>
                        <td>{{ $transfer->reference ?: '-' }}</td>
                        <td><a href="{{ route('inventory-transfers.show', $transfer) }}" class="btn btn-secondary">View</a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div style="margin-top: 20px;">
        {{ $transfers->links() }}
    </div>
@else
    <div class="empty-state">
        <p>No transfers match these filters.</p>
    </div>
@endif

@endsection
