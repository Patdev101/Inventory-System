@extends('layouts.app')

@section('title', 'Inventory')

@section('content')

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

    .filter-input {
        width: 100%;
        padding: 9px 11px;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        font-size: 14px;
        background: white;
    }

    .filter-input:focus {
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

    .quantity-main {
        font-weight: 600;
        color: #111827;
    }

    .base-quantity {
        color: #374151;
        font-weight: 600;
    }

    .base-unit {
        color: #6b7280;
        font-size: 12px;
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

    .success-message {
        color: #166534;
        background: #dcfce7;
        border: 1px solid #bbf7d0;
        border-radius: 6px;
        padding: 10px 14px;
        margin-bottom: 15px;
    }

    .error-message {
        color: #991b1b;
        background: #fee2e2;
        border: 1px solid #fecaca;
        border-radius: 6px;
        padding: 10px 14px;
        margin-bottom: 15px;
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

    .product-code,
    .location-code {
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
        <h1>Inventory</h1>

        <p style="color: #6b7280; margin-top: 5px;">
            View and manage current inventory stock.
        </p>
    </div>

    @if (auth()->user()->hasRole('admin', 'manager', 'staff'))
    <a
        href="{{ route('inventories.create') }}"
        class="btn btn-primary"
    >
        Add Inventory
    </a>
    @endif

</div>


{{-- =========================
     FLASH MESSAGES
========================= --}}

@if (session('success'))

    <div class="success-message">
        {{ session('success') }}
    </div>

@endif


@if (session('error'))

    <div class="error-message">
        {{ session('error') }}
    </div>

@endif


@if ($errors->any())

    <div class="error-message">

        <strong>Please fix the following:</strong>

        <ul style="margin: 8px 0 0 20px;">

            @foreach ($errors->all() as $error)

                <li>{{ $error }}</li>

            @endforeach

        </ul>

    </div>

@endif


{{-- =========================
     SEARCH / FILTER
========================= --}}

<div class="inventory-toolbar">

    <form
        action="{{ route('inventories.index') }}"
        method="GET"
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
                value="{{ request('search') }}"
                placeholder="Search product or location..."
            >

        </div>


        <button
            type="submit"
            class="btn btn-search"
        >
            Search
        </button>


        @if (request('search'))

            <a
                href="{{ route('inventories.index') }}"
                class="btn btn-reset"
            >
                Reset
            </a>

        @endif

    </form>

</div>


{{-- =========================
     INVENTORY TABLE
========================= --}}

@if ($inventories->count())

    <div class="inventory-summary">

        Showing
        <strong>{{ $inventories->count() }}</strong>
        inventory record(s)

        @if (request('search'))
            matching
            <strong>"{{ request('search') }}"</strong>
        @endif

    </div>


    <div class="table-wrapper">

        <table class="inventory-table">

            <thead>

                <tr>

                    <th>
                        ID
                    </th>

                    <th>
                        Product
                    </th>

                    <th>
                        Location
                    </th>

                    <th>
                        Unit
                    </th>

                    <th>
                        Quantity
                    </th>

                    <th>
                        Base Quantity
                    </th>

                    <th>
                        Status
                    </th>

                    <th>
                        Actions
                    </th>

                </tr>

            </thead>


            <tbody>

            @foreach ($inventories as $inventory)

                @php
                    $baseQuantity = $inventory->getBaseQuantityValue();
                    $stockStatus = $inventory->getStockStatus();
                    $stockStatusClass = match ($stockStatus) {
                        'Out of Stock', 'Critical' => 'stock-critical',
                        'Low Stock' => 'stock-low',
                        default => 'stock-ok',
                    };
                @endphp

                <tr>

                    {{-- ID --}}

                    <td>
                        <strong>
                            {{ $inventory->id }}
                        </strong>
                    </td>


                    {{-- PRODUCT --}}

                    <td>

                        @if ($inventory->product)

                            <div class="product-name">
                                {{ $inventory->product->name }}
                            </div>

                            @if ($inventory->product->code)

                                <div class="product-code">
                                    {{ $inventory->product->code }}
                                </div>

                            @endif

                        @else

                            <span class="deleted">
                                Product deleted
                            </span>

                        @endif

                    </td>


                    {{-- LOCATION --}}

                    <td>

                        @if ($inventory->location)

                            <div>
                                {{ $inventory->location->name }}
                            </div>

                            @if ($inventory->location->code)

                                <div class="location-code">
                                    {{ $inventory->location->code }}
                                </div>

                            @endif

                        @else

                            <span class="deleted">
                                Location deleted
                            </span>

                        @endif

                    </td>


                    {{-- UNIT --}}

                    <td>

                        @if ($inventory->productUnit?->unitOfMeasure)

                            {{ $inventory->productUnit->unitOfMeasure->name }}

                            <br>

                            <small style="color: #6b7280;">
                                {{ $inventory->productUnit->unitOfMeasure->code }}
                            </small>

                        @else

                            -

                        @endif

                    </td>


                    {{-- QUANTITY --}}

                    <td>

                        <span class="quantity-main">

                            {{ number_format(
                                (float) $inventory->quantity,
                                4
                            ) }}

                        </span>

                        @if ($inventory->productUnit?->unitOfMeasure)

                            {{ $inventory->productUnit->unitOfMeasure->code }}

                        @endif

                    </td>


                    {{-- BASE QUANTITY --}}

                    <td>

                        <span class="base-quantity">

                            {{ number_format(
                                $baseQuantity,
                                4
                            ) }}

                        </span>

                        <div class="base-unit">
                            Base units
                        </div>

                    </td>


<td>

    <span class="stock-status {{ $stockStatusClass }}">
        {{ $stockStatus }}
    </span>

</td>




                    {{-- ACTIONS --}}

                    <td>

                        <div class="actions">

                            <a
                                href="{{ route(
                                    'inventories.show',
                                    $inventory
                                ) }}"
                                class="btn btn-primary"
                            >
                                View
                            </a>


                            @if (auth()->user()->hasRole('admin', 'manager', 'staff'))
                            <a
                                href="{{ route(
                                    'inventories.edit',
                                    $inventory
                                ) }}"
                                class="btn btn-secondary"
                            >
                                Edit
                            </a>
                            @endif


                            @if (auth()->user()->isAdmin())
                            <form
                                action="{{ route(
                                    'inventories.destroy',
                                    $inventory
                                ) }}"
                                method="POST"
                            >

                                @csrf

                                @method('DELETE')

                                <button
                                    type="submit"
                                    class="btn btn-danger"
                                    onclick="return confirm(
                                        'Delete this inventory record? The transaction audit record will be preserved.'
                                    )"
                                >
                                    Delete
                                </button>

                            </form>
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

        {{ $inventories->appends(
            request()->query()
        )->links() }}

    </div>


@else

    {{-- =========================
         EMPTY STATE
    ========================== --}}

    <div class="empty-state">

        @if (request('search'))

            <p>
                No inventory records found for
                <strong>"{{ request('search') }}"</strong>.
            </p>

            <a
                href="{{ route('inventories.index') }}"
                class="btn btn-secondary"
            >
                Clear Search
            </a>

        @else

            <p>
                No inventory records found.
            </p>

            @if (auth()->user()->hasRole('admin', 'manager', 'staff'))
            <a
                href="{{ route('inventories.create') }}"
                class="btn btn-primary"
            >
                Add Inventory
            </a>
            @endif

        @endif

    </div>

@endif

@endsection