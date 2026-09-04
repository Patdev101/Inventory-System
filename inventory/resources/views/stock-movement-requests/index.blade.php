@extends('layouts.app')

@section('title', auth()->user()->hasRole('admin', 'manager') ? 'Stock Approvals' : 'My Requests')

@section('content')

<div class="page-header">
    <div>
        @if (auth()->user()->hasRole('admin', 'manager'))
            <h1>Stock Approvals</h1>
            <p>Stock movements submitted by staff, awaiting manager or admin approval.</p>
        @else
            <h1>My Requests</h1>
            <p>Stock movements you've submitted, and whether they were approved or rejected.</p>
        @endif
    </div>
</div>

@if (session('success'))
    <div class="alert-success">{{ session('success') }}</div>
@endif

@if (session('error'))
    <div class="error">{{ session('error') }}</div>
@endif

@forelse ($requests as $stockMovementRequest)
    <div class="card stock-request-card {{ $stockMovementRequest->status !== 'pending' ? 'stock-request-reviewed' : '' }}">
        <div class="stock-request-header">
            <div>
                <h2 class="stock-request-type stock-request-type-{{ $stockMovementRequest->type }}">
                    @if ($stockMovementRequest->isTransfer())
                        Transfer Request
                    @else
                        {{ $stockMovementRequest->type === 'in' ? 'Stock In' : 'Stock Out' }}
                    @endif
                </h2>
                <div class="stock-request-meta">
                    {{ $stockMovementRequest->product?->name ?? 'Deleted product' }}

                    @if ($stockMovementRequest->isTransfer())
                        from {{ $stockMovementRequest->location?->name ?? 'Deleted location' }}
                        to {{ $stockMovementRequest->destinationLocation?->name ?? 'Deleted location' }}
                    @else
                        at {{ $stockMovementRequest->location?->name ?? 'Deleted location' }}
                    @endif

                    &middot; Requested by {{ $stockMovementRequest->requestedBy?->name ?? 'Unknown' }}
                </div>
            </div>
            <strong class="stock-request-status">{{ ucfirst($stockMovementRequest->status) }}</strong>
        </div>

        <p>
            Quantity: {{ number_format((float) $stockMovementRequest->quantity, 4) }}
            {{ $stockMovementRequest->productUnit?->unitOfMeasure?->name ?? '' }}
        </p>

        @if ($stockMovementRequest->status === 'rejected' && $stockMovementRequest->rejection_reason)
            <p class="stock-request-reason">Reason: {{ $stockMovementRequest->rejection_reason }}</p>
        @endif

        @if ($stockMovementRequest->status === 'pending' && auth()->user()->hasRole('admin', 'manager'))
            <div class="actions">
                <form method="POST" action="{{ route('stock-movement-requests.approve', $stockMovementRequest) }}">
                    @csrf
                    @method('PATCH')
                    <button class="btn btn-success" type="submit">Approve</button>
                </form>

                <form method="POST" action="{{ route('stock-movement-requests.reject', $stockMovementRequest) }}"
                      onsubmit="return confirm('Reject this stock movement request?');">
                    @csrf
                    @method('PATCH')
                    <button class="btn btn-danger" type="submit">Reject</button>
                </form>
            </div>
        @endif
    </div>
@empty
    <div class="empty-state">
        <p>No stock movement requests yet.</p>
    </div>
@endforelse

<div class="pagination-wrap">
    {{ $requests->links() }}
</div>

<style>
    .stock-request-card { margin-bottom: 14px; }
    .stock-request-header { display: flex; align-items: center; justify-content: space-between; gap: 16px; margin-bottom: 12px; }
    .stock-request-header h2 { margin: 0 0 5px; font-size: 18px; }
    .stock-request-meta { color: #64748b; font-size: 13px; }
    .stock-request-status { color: #475569; font-size: 13px; }
    .stock-request-type-in { color: #15803d; }
    .stock-request-type-out { color: #b45309; }
    .stock-request-type-transfer { color: #1d4ed8; }
    .stock-request-reason { color: #b91c1c; font-size: 13px; }
    .stock-request-reviewed { opacity: .65; }
    .pagination-wrap { margin-top: 20px; }
    @media (max-width: 600px) { .stock-request-header { align-items: flex-start; flex-direction: column; } }
</style>

@endsection
