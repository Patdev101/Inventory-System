@extends('layouts.app')

@section('title', 'Products')

@section('content')

<div class="page-header">
    <div>
        <h1>Products</h1>

        <p style="margin: 6px 0 0; color: #64748b;">
            Manage your products and their availability.
        </p>
    </div>

    @if (auth()->user()->isAdmin())
        <a
            href="{{ route('products.create') }}"
            class="btn btn-primary"
        >
            + Add Product
        </a>
    @endif
</div>


@if (session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif


@if (session('error'))
    <div class="alert alert-error">
        {{ session('error') }}
    </div>
@endif


@if ($products->count())

    <div class="table-wrapper">

        <table>

            <thead>
                <tr>
                    <th>ID</th>
                    <th>Category</th>
                    <th>Company</th>
                    <th>Name</th>
                    <th>SKU</th>
                    <th>Unit</th>
                    <th>Conversion</th>
                    <th>Reorder Point</th>
                    <th>Status</th>
                    <th>Description</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>

                @foreach ($products as $product)

                    @php
                        $defaultUnit = $product->productUnits
                            ->firstWhere('is_default', true);
                    @endphp

                    <tr>

                        <td>
                            {{ $product->id }}
                        </td>

                        <td>
                            {{ $product->category->name ?? '-' }}
                        </td>

                        <td>
                            {{ $product->company->name ?? '-' }}
                        </td>

                        <td>
                            <strong style="color: #0f172a;">
                                {{ $product->name }}
                            </strong>
                        </td>

                        <td>
                            {{ $product->sku ?? '-' }}
                        </td>

                        <td>
                            @if ($defaultUnit && $defaultUnit->unitOfMeasure)

                                {{ $defaultUnit->unitOfMeasure->name }}

                                <span style="color: #64748b;">
                                    ({{ $defaultUnit->unitOfMeasure->code }})
                                </span>

                            @else
                                -
                            @endif
                        </td>

                        <td>
                            {{ $defaultUnit ? $defaultUnit->conversion_factor : '-' }}
                        </td>

                        <td>
                            {{ number_format((float) $product->reorder_point, 4) }}
                        </td>

                        <td>
                            @if ($product->is_active)
                                <span class="status-badge status-active">
                                    Active
                                </span>
                            @else
                                <span class="status-badge status-inactive">
                                    Inactive
                                </span>
                            @endif
                        </td>

                        <td>
                            @if ($product->description)
                                {{ $product->description }}
                            @else
                                <span style="color: #94a3b8;">
                                    -
                                </span>
                            @endif
                        </td>

                        <td>
                            <div class="actions">

                                <a
                                    href="{{ route('products.show', $product) }}"
                                    class="btn btn-primary btn-sm"
                                >
                                    View
                                </a>

                                @if (auth()->user()->isAdmin())

                                    <a
                                        href="{{ route('products.edit', $product) }}"
                                        class="btn btn-secondary btn-sm"
                                    >
                                        Edit
                                    </a>

                                    @if ($product->is_active)

                                        <form
                                            action="{{ route('products.deactivate', $product) }}"
                                            method="POST"
                                        >
                                            @csrf
                                            @method('PATCH')

                                            <button
                                                type="submit"
                                                class="btn btn-warning btn-sm"
                                                onclick="return confirm('Deactivate this product?')"
                                            >
                                                Deactivate
                                            </button>
                                        </form>

                                    @else

                                        <form
                                            action="{{ route('products.activate', $product) }}"
                                            method="POST"
                                        >
                                            @csrf
                                            @method('PATCH')

                                            <button
                                                type="submit"
                                                class="btn btn-success btn-sm"
                                                onclick="return confirm('Activate this product again?')"
                                            >
                                                Activate
                                            </button>
                                        </form>

                                    @endif

                                @endif

                            </div>
                        </td>

                    </tr>

                @endforeach

            </tbody>

        </table>

    </div>


    {{-- Pagination --}}

    <div class="pagination-wrapper">

        <div class="pagination-info">
            Showing
            <strong>{{ $products->firstItem() }}</strong>
            to
            <strong>{{ $products->lastItem() }}</strong>
            of
            <strong>{{ $products->total() }}</strong>
            products
        </div>

        <div class="pagination">
            {{ $products->onEachSide(1)->links() }}
        </div>

    </div>


@else

    <div class="empty-state">

        <p>No products found.</p>

        @if (auth()->user()->isAdmin())
            <a
                href="{{ route('products.create') }}"
                class="btn btn-primary"
            >
                + Add Product
            </a>
        @endif

    </div>

@endif


<style>

    .table-wrapper {
        overflow-x: auto;
    }

    .table-wrapper table {
        min-width: 1500px;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        padding: 5px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 700;
        white-space: nowrap;
    }

    .status-active {
        background: #dcfce7;
        color: #166534;
    }

    .status-inactive {
        background: #f1f5f9;
        color: #64748b;
    }

    .btn-sm {
        padding: 7px 11px;
        font-size: 13px;
        white-space: nowrap;
    }

    .btn-warning {
        background: #f59e0b;
        color: #fff;
    }

    .btn-warning:hover {
        background: #d97706;
        color: #fff;
    }

    .actions {
        display: flex;
        align-items: center;
        gap: 6px;
        flex-wrap: nowrap;
        white-space: nowrap;
    }

    .actions form {
        margin: 0;
        padding: 0;
    }

    .pagination-wrapper {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
        margin-top: 20px;
        min-height: 40px;
    }

    .pagination-info {
        color: #64748b;
        font-size: 13px;
    }

    .pagination {
        margin-left: auto;
    }

    .pagination nav {
        margin: 0;
    }

    /*
     * Prevent pagination from causing the browser
     * to jump to the top when navigating pages.
     */
    .pagination a {
        scroll-behavior: auto;
    }

    @media (max-width: 700px) {

        .pagination-wrapper {
            flex-direction: column;
            align-items: flex-start;
        }

        .pagination {
            margin-left: 0;
        }

    }

</style>

@endsection
