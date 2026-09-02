<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Transaction History</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            background: #f5f5f5;
            margin: 0;
            padding: 30px;
            color: #222;
        }

        .container {
            max-width: 1500px;
            margin: 0 auto;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            gap: 20px;
        }

        h1 {
            margin: 0 0 8px 0;
        }

        .header p {
            margin: 0;
            color: #666;
        }

        .button {
            display: inline-block;
            padding: 10px 16px;
            background: #2563eb;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            font-size: 14px;
        }

        .button:hover {
            background: #1d4ed8;
        }

        .button-secondary {
            background: #6b7280;
        }

        .button-secondary:hover {
            background: #4b5563;
        }

        .card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            overflow-x: auto;
        }

        /*
         * Filters
         */
        .filters {
            display: grid;
            grid-template-columns:
                minmax(200px, 2fr)
                minmax(160px, 1fr)
                minmax(160px, 1fr)
                minmax(130px, 1fr)
                minmax(150px, 1fr)
                minmax(150px, 1fr);
            gap: 15px;
            align-items: end;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .filter-group label {
            font-size: 13px;
            font-weight: 600;
            color: #374151;
        }

        .filter-group input,
        .filter-group select {
            width: 100%;
            box-sizing: border-box;
            padding: 10px 12px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            background: white;
            font-size: 14px;
        }

        .filter-group input:focus,
        .filter-group select:focus {
            outline: none;
            border-color: #2563eb;
            box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.1);
        }

        .filter-actions {
            display: flex;
            gap: 10px;
            align-items: end;
        }

        .filter-actions .button {
            white-space: nowrap;
        }

        /*
         * Results information
         */
        .results-info {
            color: #6b7280;
            font-size: 14px;
            margin-bottom: 15px;
        }

        /*
         * Table
         */
        .table-wrapper {
            width: 100%;
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 1100px;
        }

        th,
        td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
            text-align: left;
            white-space: nowrap;
            vertical-align: middle;
        }

        th {
            background: #f3f4f6;
            font-weight: 600;
        }

        tr:hover {
            background: #f9fafb;
        }

        /*
         * Badges
         */
        .badge {
            display: inline-block;
            padding: 4px 9px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: bold;
        }

        .badge-in {
            background: #dcfce7;
            color: #166534;
        }

        .badge-out {
            background: #fee2e2;
            color: #991b1b;
        }

        .deleted {
            color: #6b7280;
            font-style: italic;
        }

        .deleted-badge {
            display: inline-block;
            margin-top: 5px;
            padding: 3px 7px;
            border-radius: 4px;
            background: #e5e7eb;
            color: #4b5563;
            font-size: 11px;
            font-weight: bold;
        }

        .view-link {
            color: #2563eb;
            text-decoration: none;
        }

        .view-link:hover {
            text-decoration: underline;
        }

        .transaction-link {
            display: inline-block;
            margin-top: 5px;
        }

        .reference {
            max-width: 200px;
            white-space: normal;
        }

        .base-positive {
            color: #15803d;
            font-weight: 600;
        }

        .base-negative {
            color: #dc2626;
            font-weight: 600;
        }

        small {
            color: #666;
        }

        /*
         * Empty state
         */
        .empty {
            text-align: center;
            padding: 40px;
            color: #666;
        }

        /*
         * Pagination
         *
         * Do NOT use Laravel's default links() here.
         * The default pagination view can render SVG arrows
         * without the expected Tailwind CSS, causing the
         * giant < and > arrows shown in the screenshot.
         */
        .pagination-wrapper {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
            margin-top: 20px;
            padding-top: 15px;
            border-top: 1px solid #e5e7eb;
        }

        .pagination-info {
            color: #6b7280;
            font-size: 13px;
        }

        .pagination {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .pagination-link,
        .pagination-current,
        .pagination-disabled {
            min-width: 34px;
            height: 34px;
            padding: 0 10px;

            display: inline-flex;
            align-items: center;
            justify-content: center;

            border: 1px solid #d1d5db;
            border-radius: 6px;

            background: white;

            font-size: 13px;
            line-height: 1;

            text-decoration: none;

            white-space: nowrap;
        }

        .pagination-link {
            color: #2563eb;
            transition: 0.15s;
        }

        .pagination-link:hover {
            background: #eff6ff;
            border-color: #93c5fd;
        }

        .pagination-current {
            background: #2563eb;
            border-color: #2563eb;
            color: white;
            font-weight: 600;
        }

        .pagination-disabled {
            color: #9ca3af;
            background: #f9fafb;
            cursor: not-allowed;
        }

        .pagination-arrow {
            font-size: 16px;
            font-weight: bold;
        }

        /*
         * Mobile
         */
        @media (max-width: 1000px) {
            .filters {
                grid-template-columns: 1fr 1fr;
            }
        }

        @media (max-width: 600px) {
            body {
                padding: 15px;
            }

            .header {
                flex-direction: column;
                align-items: flex-start;
            }

            .filters {
                grid-template-columns: 1fr;
            }

            .filter-actions {
                align-items: stretch;
            }

            .filter-actions .button {
                flex: 1;
                text-align: center;
            }

            .pagination-wrapper {
                flex-direction: column;
                align-items: flex-start;
            }

            .pagination {
                width: 100%;
                overflow-x: auto;
                padding-bottom: 3px;
            }
        }
    </style>
</head>

<body>

<div class="system-shell">

    @include('layouts.sidebar')

    <main class="system-main">
        <div class="container">

    {{-- Header --}}
    <div class="header">

        <div>
            <h1>
                Transaction History
            </h1>

            <p>
                Complete inventory movement audit history.
            </p>
        </div>

    </div>


    {{-- Filters --}}
    <div class="card">

        <form
            method="GET"
            action="{{ route('inventory-transactions.index') }}"
        >

            <div class="filters">

                {{-- Search --}}
                <div class="filter-group">

                    <label for="search">
                        Search
                    </label>

                    <input
                        type="text"
                        id="search"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Product, location, reference..."
                    >

                </div>


                {{-- Product --}}
                <div class="filter-group">

                    <label for="product_id">
                        Product
                    </label>

                    <select
                        id="product_id"
                        name="product_id"
                    >

                        <option value="">
                            All Products
                        </option>

                        @foreach ($products as $product)

                            <option
                                value="{{ $product->id }}"
                                @selected(
                                    (string) request('product_id')
                                    ===
                                    (string) $product->id
                                )
                            >
                                {{ $product->name }}

                                @if ($product->code)
                                    ({{ $product->code }})
                                @endif

                            </option>

                        @endforeach

                    </select>

                </div>


                {{-- Location --}}
                <div class="filter-group">

                    <label for="location_id">
                        Location
                    </label>

                    <select
                        id="location_id"
                        name="location_id"
                    >

                        <option value="">
                            All Locations
                        </option>

                        @foreach ($locations as $location)

                            <option
                                value="{{ $location->id }}"
                                @selected(
                                    (string) request('location_id')
                                    ===
                                    (string) $location->id
                                )
                            >
                                {{ $location->name }}

                                @if ($location->code)
                                    ({{ $location->code }})
                                @endif

                            </option>

                        @endforeach

                    </select>

                </div>


                {{-- Type --}}
                <div class="filter-group">

                    <label for="type">
                        Type
                    </label>

                    <select
                        id="type"
                        name="type"
                    >

                        <option value="">
                            All Types
                        </option>

                        <option
                            value="in"
                            @selected(request('type') === 'in')
                        >
                            IN
                        </option>

                        <option
                            value="out"
                            @selected(request('type') === 'out')
                        >
                            OUT
                        </option>

                    </select>

                </div>


                {{-- From date --}}
                <div class="filter-group">

                    <label for="date_from">
                        From Date
                    </label>

                    <input
                        type="date"
                        id="date_from"
                        name="date_from"
                        value="{{ request('date_from') }}"
                    >

                </div>


                {{-- To date --}}
                <div class="filter-group">

                    <label for="date_to">
                        To Date
                    </label>

                    <input
                        type="date"
                        id="date_to"
                        name="date_to"
                        value="{{ request('date_to') }}"
                    >

                </div>

            </div>


            {{-- Buttons --}}
            <div
                class="filter-actions"
                style="margin-top: 15px;"
            >

                <button
                    type="submit"
                    class="button"
                >
                    Search
                </button>

                <a
                    href="{{ route('inventory-transactions.index') }}"
                    class="button button-secondary"
                >
                    Reset
                </a>

            </div>

        </form>

    </div>


    {{-- Results --}}
    <div class="card">

        @if ($transactions->count())

            <div class="results-info">

                Showing
                <strong>
                    {{ $transactions->firstItem() }}
                </strong>

                to

                <strong>
                    {{ $transactions->lastItem() }}
                </strong>

                of

                <strong>
                    {{ $transactions->total() }}
                </strong>

                transaction(s).

            </div>


            <div class="table-wrapper">

                <table>

                    <thead>

                        <tr>
                            <th>ID</th>
                            <th>Date</th>
                            <th>Product</th>
                            <th>Location</th>
                            <th>Type</th>
                            <th>Quantity</th>
                            <th>Base Quantity</th>
                            <th>Unit</th>
                            <th>Reference</th>
                            <th>Inventory</th>
                        </tr>

                    </thead>

                    <tbody>

                    @foreach ($transactions as $transaction)

                        <tr>

                            {{-- ID --}}
                            <td>
                                <strong>
                                    {{ $transaction->id }}
                                </strong>
                            </td>


                            {{-- Date --}}
                            <td>
                                {{ $transaction->created_at?->format('Y-m-d H:i:s') }}
                            </td>


                            {{-- Product --}}
                            <td>

                                @if ($transaction->product)

                                    {{ $transaction->product->name }}

                                    @if ($transaction->product->code)
                                        ({{ $transaction->product->code }})
                                    @endif

                                @else

                                    <span class="deleted">
                                        Product deleted
                                    </span>

                                @endif

                            </td>


                            {{-- Location --}}
                            <td>

                                @if ($transaction->location)

                                    {{ $transaction->location->name }}

                                    @if ($transaction->location->code)
                                        ({{ $transaction->location->code }})
                                    @endif

                                @else

                                    <span class="deleted">
                                        Location deleted
                                    </span>

                                @endif

                            </td>


                            {{-- Type --}}
                            <td>

                                @if ($transaction->type === 'in')

                                    <span class="badge badge-in">
                                        IN
                                    </span>

                                @else

                                    <span class="badge badge-out">
                                        OUT
                                    </span>

                                @endif

                            </td>


                            {{-- Quantity --}}
                            <td>

                                {{ number_format(
                                    (float) $transaction->quantity,
                                    4
                                ) }}

                                @if ($transaction->productUnit?->unitOfMeasure)

                                    {{ $transaction->productUnit->unitOfMeasure->code }}

                                @endif

                            </td>


                            {{-- Base Quantity --}}
                            <td>

                                @if ($transaction->type === 'in')

                                    <span class="base-positive">
                                        +
                                        {{ number_format(
                                            (float) $transaction->base_quantity,
                                            4
                                        ) }}
                                    </span>

                                @else

                                    <span class="base-negative">
                                        -
                                        {{ number_format(
                                            (float) $transaction->base_quantity,
                                            4
                                        ) }}
                                    </span>

                                @endif

                                <br>

                                <small>
                                    Base unit
                                </small>

                            </td>


                            {{-- Unit --}}
                            <td>

                                @if ($transaction->productUnit)

                                    {{ $transaction->productUnit->name ?? 'Unit' }}

                                    @if ($transaction->productUnit->unitOfMeasure)

                                        ({{ $transaction->productUnit->unitOfMeasure->code }})

                                    @endif

                                @else

                                    <span class="deleted">
                                        Unit deleted
                                    </span>

                                @endif

                            </td>


                            {{-- Reference --}}
                            <td class="reference">

                                {{ $transaction->reference ?? '-' }}

                            </td>


                            {{-- Inventory --}}
                            <td>

                                @if ($transaction->inventory)

                                    <a
                                        href="{{ route(
                                            'inventories.show',
                                            $transaction->inventory
                                        ) }}"
                                        class="view-link"
                                    >
                                        View Inventory
                                    </a>

                                @else

                                    <span class="deleted">
                                        Inventory deleted
                                    </span>

                                    <br>

                                    <span class="deleted-badge">
                                        AUDIT RECORD PRESERVED
                                    </span>

                                @endif

                                <br>

                                <a
                                    href="{{ route(
                                        'inventory-transactions.show',
                                        $transaction
                                    ) }}"
                                    class="view-link transaction-link"
                                >
                                    View Transaction
                                </a>

                            </td>

                        </tr>

                    @endforeach

                    </tbody>

                </table>

            </div>

        @else

            <div class="empty">

                <h3>
                    No transactions found
                </h3>

                <p>
                    Try changing your filters or search terms.
                </p>

                <a
                    href="{{ route('inventory-transactions.index') }}"
                    class="button"
                >
                    Clear Filters
                </a>

            </div>

        @endif


        {{-- Compact Pagination --}}
        @if ($transactions->hasPages())

            <div class="pagination-wrapper">

                <div class="pagination-info">

                    Page
                    <strong>{{ $transactions->currentPage() }}</strong>
                    of
                    <strong>{{ $transactions->lastPage() }}</strong>

                </div>


                <div class="pagination">

                    {{-- Previous --}}
                    @if ($transactions->onFirstPage())

                        <span class="pagination-disabled">
                            <span class="pagination-arrow">‹</span>
                            Previous
                        </span>

                    @else

                        <a
                            href="{{ $transactions->appends(request()->except('page'))->previousPageUrl() }}"
                            class="pagination-link"
                            aria-label="Previous page"
                        >
                            <span class="pagination-arrow">‹</span>
                            Previous
                        </a>

                    @endif


                    {{-- Page numbers --}}
                    @php
                        $currentPage = $transactions->currentPage();
                        $lastPage = $transactions->lastPage();

                        $startPage = max(1, $currentPage - 2);
                        $endPage = min($lastPage, $currentPage + 2);
                    @endphp


                    @if ($startPage > 1)

                        <a
                            href="{{ $transactions->appends(request()->except('page'))->url(1) }}"
                            class="pagination-link"
                        >
                            1
                        </a>

                        @if ($startPage > 2)
                            <span class="pagination-disabled">
                                ...
                            </span>
                        @endif

                    @endif


                    @for ($page = $startPage; $page <= $endPage; $page++)

                        @if ($page == $currentPage)

                            <span class="pagination-current">
                                {{ $page }}
                            </span>

                        @else

                            <a
                                href="{{ $transactions->appends(request()->except('page'))->url($page) }}"
                                class="pagination-link"
                            >
                                {{ $page }}
                            </a>

                        @endif

                    @endfor


                    @if ($endPage < $lastPage)

                        @if ($endPage < $lastPage - 1)
                            <span class="pagination-disabled">
                                ...
                            </span>
                        @endif

                        <a
                            href="{{ $transactions->appends(request()->except('page'))->url($lastPage) }}"
                            class="pagination-link"
                        >
                            {{ $lastPage }}
                        </a>

                    @endif


                    {{-- Next --}}
                    @if ($transactions->hasMorePages())

                        <a
                            href="{{ $transactions->appends(request()->except('page'))->nextPageUrl() }}"
                            class="pagination-link"
                            aria-label="Next page"
                        >
                            Next
                            <span class="pagination-arrow">›</span>
                        </a>

                    @else

                        <span class="pagination-disabled">
                            Next
                            <span class="pagination-arrow">›</span>
                        </span>

                    @endif

                </div>

            </div>

        @endif

    </div>

        </div>
    </main>
</div>

</body>
</html>
