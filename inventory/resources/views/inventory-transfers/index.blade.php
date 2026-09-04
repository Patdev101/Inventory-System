@extends('layouts.app')

@section('title', 'Transfer History')

@section('content')

<style>
    .transfer-pagination-wrapper {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 15px;
        flex-wrap: wrap;
        margin-top: 20px;
        padding-top: 15px;
        border-top: 1px solid #e5e7eb;
    }

    .transfer-pagination-info {
        color: #64748b;
        font-size: 13px;
    }

    .transfer-pagination {
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .transfer-pagination-link,
    .transfer-pagination-current,
    .transfer-pagination-disabled {
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

    .transfer-pagination-link {
        color: #2563eb;
        transition: 0.15s;
    }

    .transfer-pagination-link:hover {
        background: #eff6ff;
        border-color: #93c5fd;
    }

    .transfer-pagination-current {
        background: #2563eb;
        border-color: #2563eb;
        color: white;
        font-weight: 600;
    }

    .transfer-pagination-disabled {
        color: #9ca3af;
        background: #f9fafb;
        cursor: not-allowed;
    }

    .transfer-pagination-arrow {
        font-size: 16px;
        font-weight: bold;
    }

    @media (max-width: 600px) {
        .transfer-pagination-wrapper {
            flex-direction: column;
            align-items: flex-start;
        }

        .transfer-pagination {
            width: 100%;
            overflow-x: auto;
            padding-bottom: 3px;
        }
    }
</style>


<div class="page-header">

    <div>
        <h1>Transfer History</h1>

        <p style="margin: 6px 0 0; color: #64748b;">
            View all inventory transfers between locations.
        </p>
    </div>

    <div style="display: flex; gap: 10px;">

        <a
            href="{{ route('inventory-transfers.pending-audits') }}"
            class="btn btn-secondary"
        >
            Audit Transfers
        </a>

        @if (auth()->user()->hasRole('admin', 'manager'))
        <a
            href="{{ route('inventory-transfers.create') }}"
            class="btn btn-primary"
        >
            Transfer Inventory
        </a>
        @endif

    </div>

</div>


@if ($transfers->count() > 0 || $search !== '')

    <div class="card" style="margin-bottom: 20px;">

        <form
            method="GET"
            action="{{ route('inventory-transfers.index') }}"
        >

            <input type="hidden" name="sort" value="{{ $sort }}">
            <input type="hidden" name="direction" value="{{ $direction }}">

            <div style="
                display: flex;
                gap: 10px;
                align-items: flex-end;
                flex-wrap: wrap;
            ">

                <div style="flex: 1; min-width: 250px;">

                    <label for="search">
                        Search Transfers
                    </label>

                    <input
                        type="text"
                        id="search"
                        name="search"
                        value="{{ $search }}"
                        placeholder="Search product, location, reference, or notes..."
                    >

                </div>

                <div>

                    <button
                        type="submit"
                        class="btn btn-primary"
                    >
                        Search
                    </button>

                </div>

                @if ($search !== '')

                    <div>

                        <a
                            href="{{ route('inventory-transfers.index') }}"
                            class="btn btn-secondary"
                        >
                            Clear
                        </a>

                    </div>

                @endif

            </div>

        </form>

    </div>


    @if ($transfers->count() > 0)

        <div style="
            margin-bottom: 14px;
            color: #64748b;
            font-size: 14px;
        ">

            Showing
            <strong>{{ $transfers->firstItem() }}</strong>
            -
            <strong>{{ $transfers->lastItem() }}</strong>
            of
            <strong>{{ $transfers->total() }}</strong>
            transfer(s)

        </div>


        <div class="table-wrapper">

            <table>

                <thead>

                    @php
                        // Builds the href for a sortable header: clicking a
                        // column that isn't currently sorted starts it at
                        // ascending; clicking the already-active column
                        // flips the direction. Search term and pagination
                        // page reset (new sort = back to page 1) are
                        // preserved automatically since we only touch
                        // 'sort'/'direction' in the query string.
                        $sortLink = function (string $column) use ($sort, $direction) {
                            $nextDirection = ($sort === $column && $direction === 'asc') ? 'desc' : 'asc';

                            return request()->fullUrlWithQuery([
                                'sort' => $column,
                                'direction' => $nextDirection,
                                'page' => null,
                            ]);
                        };

                        $sortIndicator = function (string $column) use ($sort, $direction) {
                            if ($sort !== $column) {
                                return '';
                            }

                            return $direction === 'asc' ? ' ▲' : ' ▼';
                        };
                    @endphp

                    <tr>
                        <th>ID</th>
                        <th>
                            <a href="{{ $sortLink('created_at') }}" style="color: inherit; text-decoration: none;">
                                Date{{ $sortIndicator('created_at') }}
                            </a>
                        </th>
                        <th>Product</th>
                        <th>
                            <a href="{{ $sortLink('from_location') }}" style="color: inherit; text-decoration: none;">
                                From{{ $sortIndicator('from_location') }}
                            </a>
                        </th>
                        <th>
                            <a href="{{ $sortLink('to_location') }}" style="color: inherit; text-decoration: none;">
                                To{{ $sortIndicator('to_location') }}
                            </a>
                        </th>
                        <th>Transfer Quantity</th>
                        <th>Base Quantity</th>
                        <th>Reference</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>

                </thead>

                <tbody>

                    @foreach ($transfers as $transfer)

                        <tr>

                            <td>
                                #{{ $transfer->id }}
                            </td>

                            <td>
                                {{ $transfer->created_at->format('Y-m-d H:i:s') }}
                            </td>

                            <td>
                                <strong>
                                    {{ $transfer->product->name ?? 'N/A' }}
                                </strong>
                            </td>

                            <td>
                                {{ $transfer->sourceInventory->location->name ?? 'N/A' }}
                            </td>

                            <td>
                                {{ $transfer->destinationInventory->location->name ?? 'N/A' }}
                            </td>

                            <td>
                                {{ number_format((float) $transfer->quantity, 4) }}
                                {{ $transfer->productUnit?->unitOfMeasure?->code ?? '' }}
                            </td>

                            <td>
                                {{ number_format((float) $transfer->base_quantity, 4) }}
                                base units
                            </td>

                            <td>
                                {{ $transfer->reference ?? '—' }}
                            </td>

                            <td>
                                @if ($transfer->status === 'pending')
                                    <span style="background: #fef3c7; color: #d97706; padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: bold;">Pending</span>
                                @elseif ($transfer->status === 'rejected')
                                    <span style="background: #fee2e2; color: #991b1b; padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: bold;">Rejected</span>
                                @else
                                    <span style="background: #d1fae5; color: #065f46; padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: bold;">Completed</span>
                                @endif
                            </td>

                            <td>
                                {{-- Audit + Receive both require the assigned receiver and
                                     live on the show page, not here — a single "Receive"
                                     button on the list would skip the audit step and let
                                     anyone attempt it regardless of role. --}}
                                <a
                                    href="{{ route('inventory-transfers.show', $transfer) }}"
                                    class="btn btn-secondary"
                                    style="padding: 4px 8px; font-size: 12px;"
                                >
                                    View
                                </a>
                            </td>

                        </tr>

                    @endforeach

                </tbody>

            </table>

        </div>


        {{-- Compact Pagination --}}
        @if ($transfers->hasPages())

            <div class="transfer-pagination-wrapper">

                <div class="transfer-pagination-info">

                    Page
                    <strong>
                        {{ $transfers->currentPage() }}
                    </strong>

                    of

                    <strong>
                        {{ $transfers->lastPage() }}
                    </strong>

                </div>


                <div class="transfer-pagination">

                    {{-- Previous --}}
                    @if ($transfers->onFirstPage())

                        <span class="transfer-pagination-disabled">
                            <span class="transfer-pagination-arrow">
                                ‹
                            </span>
                            Previous
                        </span>

                    @else

                        <a
                            href="{{ $transfers->appends(request()->except('page'))->previousPageUrl() }}"
                            class="transfer-pagination-link"
                            aria-label="Previous page"
                        >
                            <span class="transfer-pagination-arrow">
                                ‹
                            </span>
                            Previous
                        </a>

                    @endif


                    @php
                        $currentPage = $transfers->currentPage();
                        $lastPage = $transfers->lastPage();

                        $startPage = max(1, $currentPage - 2);
                        $endPage = min($lastPage, $currentPage + 2);
                    @endphp


                    {{-- First page --}}
                    @if ($startPage > 1)

                        <a
                            href="{{ $transfers->appends(request()->except('page'))->url(1) }}"
                            class="transfer-pagination-link"
                        >
                            1
                        </a>

                        @if ($startPage > 2)

                            <span class="transfer-pagination-disabled">
                                ...
                            </span>

                        @endif

                    @endif


                    {{-- Page numbers --}}
                    @for (
                        $page = $startPage;
                        $page <= $endPage;
                        $page++
                    )

                        @if ($page == $currentPage)

                            <span class="transfer-pagination-current">
                                {{ $page }}
                            </span>

                        @else

                            <a
                                href="{{ $transfers->appends(request()->except('page'))->url($page) }}"
                                class="transfer-pagination-link"
                            >
                                {{ $page }}
                            </a>

                        @endif

                    @endfor


                    {{-- Last page --}}
                    @if ($endPage < $lastPage)

                        @if ($endPage < $lastPage - 1)

                            <span class="transfer-pagination-disabled">
                                ...
                            </span>

                        @endif

                        <a
                            href="{{ $transfers->appends(request()->except('page'))->url($lastPage) }}"
                            class="transfer-pagination-link"
                        >
                            {{ $lastPage }}
                        </a>

                    @endif


                    {{-- Next --}}
                    @if ($transfers->hasMorePages())

                        <a
                            href="{{ $transfers->appends(request()->except('page'))->nextPageUrl() }}"
                            class="transfer-pagination-link"
                            aria-label="Next page"
                        >
                            Next

                            <span class="transfer-pagination-arrow">
                                ›
                            </span>
                        </a>

                    @else

                        <span class="transfer-pagination-disabled">
                            Next

                            <span class="transfer-pagination-arrow">
                                ›
                            </span>
                        </span>

                    @endif

                </div>

            </div>

        @endif

    @else

        <div class="empty-state">

            <p>
                No transfers matched your search.
            </p>

            <a
                href="{{ route('inventory-transfers.index') }}"
                class="btn btn-secondary"
            >
                Clear Search
            </a>

        </div>

    @endif

@else

    <div class="empty-state">

        <p>
            No inventory transfers have been recorded yet.
        </p>

        @if (auth()->user()->hasRole('admin', 'manager'))
        <a
            href="{{ route('inventory-transfers.create') }}"
            class="btn btn-primary"
        >
            Create First Transfer
        </a>
        @endif

    </div>

@endif

@endsection