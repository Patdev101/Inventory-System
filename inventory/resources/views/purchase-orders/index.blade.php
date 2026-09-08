@extends('layouts.app')

@section('title', 'Purchase Orders')

@section('content')

@php

    $statusOptions = [
        '' => 'All Orders',
        'draft' => 'Draft',
        'pending_approval' => 'Pending Approval',
        'approved' => 'Approved',
        'rejected' => 'Rejected',
        'ordered' => 'Ordered',
        'partially_received' => 'Partially Received',
        'received' => 'Received',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
    ];

    $statusClasses = [
        'draft' => 'stock-status-neutral',
        'pending_approval' => 'stock-low',
        'approved' => 'stock-status-info',
        'rejected' => 'stock-critical',
        'ordered' => 'stock-status-info',
        'partially_received' => 'stock-low',
        'received' => 'stock-ok',
        'completed' => 'stock-ok',
        'cancelled' => 'stock-status-neutral',
    ];

@endphp

<style>
    .inventory-toolbar {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        padding: 18px;
        margin-bottom: 20px;
    }

    .inventory-toolbar form {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        align-items: end;
    }

    .filter-group {
        flex: 1;
        min-width: 220px;
    }

    .filter-group label {
        display: block;
        margin-bottom: 6px;
        font-size: 13px;
        font-weight: 600;
        color: #374151;
    }

    .filter-input,
    .filter-select {
        width: 100%;
        padding: 9px 11px;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        font-size: 14px;
        background: white;
    }

    .filter-input:focus,
    .filter-select:focus {
        outline: none;
        border-color: #2563eb;
        box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.12);
    }

    .btn-search {
        background: #2563eb;
        color: white;
        border: none;
        cursor: pointer;
    }

    .btn-search:hover {
        background: #1d4ed8;
    }

    .btn-reset {
        background: #6b7280;
        color: white;
        text-decoration: none;
    }

    .btn-reset:hover {
        background: #4b5563;
    }

    .inventory-summary {
        margin-bottom: 15px;
        color: #6b7280;
        font-size: 14px;
    }

    .stock-status {
        display: inline-block;
        padding: 4px 9px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 700;
        white-space: nowrap;
    }

    .stock-ok {
        background: #dcfce7;
        color: #166534;
    }

    .stock-low {
        background: #fef3c7;
        color: #92400e;
    }

    .stock-critical {
        background: #fee2e2;
        color: #991b1b;
    }

    .stock-status-info {
        background: #dbeafe;
        color: #1d4ed8;
    }

    .stock-status-neutral {
        background: #f1f5f9;
        color: #475569;
    }

    .actions {
        display: flex;
        gap: 6px;
        flex-wrap: wrap;
    }

    .actions form {
        display: inline;
        margin: 0;
    }

    .empty-state {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        padding: 50px 20px;
        text-align: center;
        color: #6b7280;
    }

    .empty-state p {
        margin: 0 0 15px;
    }

    .table-wrapper {
        overflow-x: auto;
    }

    .inventory-table {
        width: 100%;
        border-collapse: collapse;
    }

    .inventory-table th,
    .inventory-table td {
        padding: 12px;
        border-bottom: 1px solid #e5e7eb;
        text-align: left;
        white-space: nowrap;
    }

    .inventory-table th {
        background: #f9fafb;
        color: #4b5563;
        font-size: 13px;
    }

    .inventory-table tbody tr:hover {
        background: #f9fafb;
    }

    .product-name {
        font-weight: 600;
        color: #111827;
    }

    .product-code {
        color: #6b7280;
        font-size: 12px;
    }

    .pagination-wrapper {
        margin-top: 20px;
    }

    @media (max-width: 700px) {
        .inventory-toolbar form {
            flex-direction: column;
            align-items: stretch;
        }

        .filter-group {
            width: 100%;
        }

        .actions {
            flex-direction: column;
            align-items: stretch;
        }

        .actions .btn {
            text-align: center;
        }
    }
</style>


{{-- =========================
     PAGE HEADER
========================= --}}

<div class="page-header">

    <div>
        <h1>Purchase Orders</h1>

        <p style="color: #6b7280; margin-top: 5px;">
            Manage supplier orders, approvals, deliveries, and receiving.
        </p>
    </div>

    @if (auth()->user()->hasRole('admin', 'manager', 'staff'))
    <a
        href="{{ route('purchase-orders.create') }}"
        class="btn btn-primary"
    >
        + New Purchase Order
    </a>
    @endif

</div>


{{-- =========================
     SEARCH / FILTER
========================= --}}

<div class="inventory-toolbar">

    <form
        action="{{ route('purchase-orders.index') }}"
        method="GET"
        id="po-filter-form"
    >

        <div class="filter-group">

            <label for="search">
                Search
            </label>

            <input
                type="text"
                name="search"
                id="search"
                class="filter-input"
                value="{{ $search }}"
                placeholder="Search PO number, reference, or supplier..."
            >

        </div>


        <div class="filter-group" style="flex: 0 0 220px;">

            <label for="status">
                Status
            </label>

            <select
                name="status"
                id="status"
                class="filter-select"
                onchange="this.form.submit()"
            >

                @foreach ($statusOptions as $value => $label)

                    <option
                        value="{{ $value }}"
                        {{ (string) $status === (string) $value ? 'selected' : '' }}
                    >
                        {{ $label }}
                    </option>

                @endforeach

            </select>

        </div>


        @if ($search !== '' || $status)

            <a
                href="{{ route('purchase-orders.index') }}"
                class="btn btn-reset"
            >
                Reset
            </a>

        @endif

    </form>

</div>


<script>
    (function () {
        var searchInput = document.getElementById('search');
        var form = document.getElementById('po-filter-form');
        var timer = null;

        searchInput.addEventListener('input', function () {
            clearTimeout(timer);
            timer = setTimeout(function () {
                form.submit();
            }, 500);
        });
    })();
</script>


{{-- =========================
     PURCHASE ORDERS TABLE
========================= --}}

@if ($purchaseOrders->count())

    <div class="inventory-summary">

        Showing
        <strong>{{ $purchaseOrders->count() }}</strong>
        purchase order(s)

        @if ($search !== '')
            matching
            <strong>"{{ $search }}"</strong>
        @endif

    </div>


    <div class="table-wrapper">

        <table class="inventory-table">

            <thead>

                <tr>
                    <th>PO Number</th>
                    <th>Products</th>
                    <th>Supplier</th>
                    <th>Location</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Expected Delivery</th>
                    <th>Created By</th>
                    <th>Actions</th>
                </tr>

            </thead>


            <tbody>

            @foreach ($purchaseOrders as $purchaseOrder)

                <tr>

                    <td>
                        <strong>
                            {{ $purchaseOrder->po_number }}
                        </strong>

                        @if ($purchaseOrder->reference)
                            <div class="product-code">
                                Ref: {{ $purchaseOrder->reference }}
                            </div>
                        @endif
                    </td>

                    <td>

                        @php
                            $poProducts = $purchaseOrder->items->pluck('product')->filter();
                            $poProductsFirst = $poProducts->take(3);
                        @endphp

                        @if ($poProducts->isEmpty())

                            <span style="color:#94a3b8;">—</span>

                        @else

                            <div style="display:flex; align-items:center; gap:6px;">

                                <div style="display:flex;">

                                    @foreach ($poProductsFirst as $index => $product)

                                        <div style="margin-left: {{ $index === 0 ? '0' : '-10px' }}; z-index: {{ 10 - $index }};">

                                            @if ($product->image_url)

                                                <img
                                                    src="{{ $product->image_url }}"
                                                    alt="{{ $product->name }}"
                                                    style="width: 32px; height: 32px; object-fit: cover; border-radius: 6px; border: 2px solid #ffffff; box-shadow: 0 0 0 1px #e5e7eb; display: block;"
                                                >

                                            @else

                                                <div style="width: 32px; height: 32px; border-radius: 6px; border: 2px solid #ffffff; box-shadow: 0 0 0 1px #e5e7eb; background: #f1f5f9; display: flex; align-items: center; justify-content: center; color: #94a3b8;">
                                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                                        <rect x="3" y="4" width="18" height="16" rx="2"></rect>
                                                        <circle cx="9" cy="10" r="2"></circle>
                                                        <path d="M21 16l-5.2-5.2a2 2 0 0 0-2.8 0L4 20"></path>
                                                    </svg>
                                                </div>

                                            @endif

                                        </div>

                                    @endforeach

                                </div>

                                <div>

                                    <div class="product-name" style="white-space: normal;">
                                        {{ $poProducts->first()->name }}
                                        @if ($poProducts->count() > 1)
                                            <span style="color:#94a3b8; font-weight: 400;">
                                                + {{ $poProducts->count() - 1 }} more
                                            </span>
                                        @endif
                                    </div>

                                </div>

                            </div>

                        @endif

                    </td>

                    <td>
                        <div class="product-name">
                            {{ $purchaseOrder->supplier->name ?? 'Unknown Supplier' }}
                        </div>
                    </td>

                    <td>
                        {{ $purchaseOrder->location->name ?? 'Unknown Location' }}
                    </td>

                    <td>
                        {{ number_format((float) $purchaseOrder->total, 2) }}
                    </td>

                    <td>
                        <span class="stock-status {{ $statusClasses[$purchaseOrder->status] ?? 'stock-status-neutral' }}">
                            {{ $statusOptions[$purchaseOrder->status] ?? ucfirst($purchaseOrder->status) }}
                        </span>
                    </td>

                    <td>
                        @if ($purchaseOrder->expected_delivery_date)
                            {{ format_date($purchaseOrder->expected_delivery_date) }}
                        @else
                            —
                        @endif
                    </td>

                    <td>
                        {{ $purchaseOrder->createdBy->name ?? '—' }}
                    </td>

                    <td>

                        <div class="actions">

                            <a
                                href="{{ route('purchase-orders.show', $purchaseOrder) }}"
                                class="btn btn-primary"
                            >
                                View
                            </a>

                            @if (in_array($purchaseOrder->status, ['ordered', 'partially_received'], true))

                                <a
                                    href="{{ route('purchase-orders.receive.form', $purchaseOrder) }}"
                                    class="btn btn-secondary"
                                >
                                    Receive
                                </a>

                            @endif

                        </div>

                    </td>

                </tr>

            @endforeach

            </tbody>

        </table>

    </div>


    {{-- =========================
         PAGINATION
    ========================== --}}

    <div class="pagination-wrapper">

        {{ $purchaseOrders->appends(request()->query())->links() }}

    </div>


@else

    {{-- =========================
         EMPTY STATE
    ========================== --}}

    <div class="empty-state">

        @if ($search !== '' || $status)

            <p>
                No purchase orders found
                @if ($search !== '')
                    for <strong>"{{ $search }}"</strong>
                @endif
                . Try clearing the filters.
            </p>

            <a
                href="{{ route('purchase-orders.index') }}"
                class="btn btn-secondary"
            >
                Clear Filters
            </a>

        @else

            <p>
                No purchase orders yet. Create your first purchase order to begin
                managing supplier orders and inventory receiving.
            </p>

            @if (auth()->user()->hasRole('admin', 'manager', 'staff'))
                <a
                    href="{{ route('purchase-orders.create') }}"
                    class="btn btn-primary"
                >
                    + Create Purchase Order
                </a>
            @endif

        @endif

    </div>

@endif

@endsection
