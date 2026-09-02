@extends('layouts.app')

@section('title', 'Product Details')

@section('content')

<div class="page-header">

    <div>
        <h1>Product Details</h1>

        <p style="margin: 6px 0 0; color: #64748b;">
            View product information and measurement units.
        </p>
    </div>

    <div style="display: flex; gap: 8px;">

        @if (auth()->user()->isAdmin())
            <a
                href="{{ route('products.edit', $product) }}"
                class="btn btn-primary"
            >
                Edit
            </a>
        @endif

        <a
            href="{{ route('products.index') }}"
            class="btn btn-secondary"
        >
            Back
        </a>

    </div>

</div>


<div class="card">

    <div class="details-grid">

        <div>
            <strong>ID</strong>
            <span>{{ $product->id }}</span>
        </div>

        <div>
            <strong>Status</strong>

            <span>
                @if ($product->is_active)
                    <span class="status-badge status-active">
                        Active
                    </span>
                @else
                    <span class="status-badge status-inactive">
                        Inactive
                    </span>
                @endif
            </span>
        </div>

        <div>
            <strong>Category</strong>
            <span>{{ $product->category->name ?? '-' }}</span>
        </div>

        <div>
            <strong>Company</strong>
            <span>{{ $product->company->name ?? '-' }}</span>
        </div>

        <div>
            <strong>Product Name</strong>
            <span>{{ $product->name }}</span>
        </div>

        <div>
            <strong>SKU</strong>
            <span>{{ $product->sku ?? '-' }}</span>
        </div>

        <div>
            <strong>Base Unit</strong>
            <span>
                @if ($product->baseUnit)
                    {{ $product->baseUnit->name }}
                    ({{ $product->baseUnit->code }})
                @else
                    -
                @endif
            </span>
        </div>

        <div>
            <strong>Reorder Point</strong>
            <span>
                {{ number_format((float) $product->reorder_point, 4) }}
            </span>
        </div>

    </div>


    <div style="margin-top: 25px;">

        <strong>Description</strong>

        <p style="color: #475569;">
            {{ $product->description ?: '-' }}
        </p>

    </div>


    <hr style="margin: 30px 0;">


    <h2>Units of Measure</h2>

    @if ($product->productUnits->count())

        <div class="table-wrapper">

            <table>

                <thead>
                    <tr>
                        <th>Unit</th>
                        <th>Code</th>
                        <th>Conversion Factor</th>
                        <th>Default</th>
                    </tr>
                </thead>

                <tbody>

                    @foreach ($product->productUnits as $productUnit)

                        <tr>

                            <td>
                                {{ $productUnit->unitOfMeasure->name ?? '-' }}
                            </td>

                            <td>
                                {{ $productUnit->unitOfMeasure->code ?? '-' }}
                            </td>

                            <td>
                                {{ $productUnit->conversion_factor }}
                            </td>

                            <td>

                                @if ($productUnit->is_default)

                                    <span class="status-badge status-active">
                                        Yes
                                    </span>

                                @else

                                    No

                                @endif

                            </td>

                        </tr>

                    @endforeach

                </tbody>

            </table>

        </div>

    @else

        <p>
            No units of measure assigned.
        </p>

    @endif

</div>


<style>

    .details-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 20px;
    }

    .details-grid > div {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .details-grid strong {
        color: #64748b;
        font-size: 13px;
    }

    .details-grid span {
        color: #0f172a;
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
        color: #166534 !important;
    }

    .status-inactive {
        background: #f1f5f9;
        color: #64748b !important;
    }

    .table-wrapper {
        overflow-x: auto;
    }

    @media (max-width: 700px) {

        .details-grid {
            grid-template-columns: 1fr;
        }

    }

</style>

@endsection
