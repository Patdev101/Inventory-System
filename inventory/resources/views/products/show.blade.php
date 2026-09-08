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


{{-- HERO --}}
<div class="product-hero">

    <div class="product-hero-image">

        @if ($product->image_url)

            <img
                src="{{ $product->image_url }}"
                alt="{{ $product->name }}"
            >

        @else

            <div class="product-hero-placeholder">

                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="4" width="18" height="16" rx="2"></rect>
                    <circle cx="9" cy="10" r="2"></circle>
                    <path d="M21 16l-5.2-5.2a2 2 0 0 0-2.8 0L4 20"></path>
                </svg>

                <span>No image</span>

            </div>

        @endif

    </div>

    <div class="product-hero-body">

        <div class="product-hero-top">

            @if ($product->is_active)
                <span class="status-badge status-active">Active</span>
            @else
                <span class="status-badge status-inactive">Inactive</span>
            @endif

            @if ($product->category)
                <span class="pill">{{ $product->category->name }}</span>
            @endif

            @if ($product->company)
                <span class="pill pill-muted">{{ $product->company->name }}</span>
            @endif

        </div>

        <h2 class="product-hero-name">
            {{ $product->name }}
        </h2>

        <div class="product-hero-sku">
            SKU: {{ $product->sku ?? '—' }} &nbsp;•&nbsp; Item Code: {{ $product->item_code ?? '—' }}
        </div>

        <div class="product-hero-meta">

            <div class="meta-item">
                <span class="meta-label">Base Unit</span>
                <span class="meta-value">
                    @if ($product->baseUnit)
                        {{ $product->baseUnit->name }} ({{ $product->baseUnit->code }})
                    @else
                        —
                    @endif
                </span>
            </div>

            <div class="meta-item">
                <span class="meta-label">Reorder Point</span>
                <span class="meta-value">
                    {{ format_qty((float) $product->reorder_point) }}
                </span>
            </div>

            <div class="meta-item">
                <span class="meta-label">Selling Price</span>
                <span class="meta-value">
                    ₱{{ number_format((float) $product->selling_price, 2) }}
                </span>
            </div>

            <div class="meta-item">
                <span class="meta-label">Product ID</span>
                <span class="meta-value">#{{ $product->id }}</span>
            </div>

        </div>

    </div>

</div>


{{-- DESCRIPTION --}}
<div class="card section-card">

    <h2 class="section-title">Description</h2>

    <p class="description-text">
        {{ $product->description ?: 'No description provided.' }}
    </p>

</div>


{{-- UNITS OF MEASURE --}}
<div class="card section-card">

    <h2 class="section-title">Units of Measure</h2>

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
                                {{ format_qty($productUnit->conversion_factor) }}
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

    .product-hero {
        display: flex;
        gap: 28px;
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        box-shadow: 0 4px 16px rgba(15, 23, 42, .05);
        padding: 26px;
        margin-bottom: 20px;
    }

    .product-hero-image {
        flex-shrink: 0;
        width: 180px;
        height: 180px;
        border-radius: 12px;
        overflow: hidden;
        background: #f1f5f9;
        border: 1px solid #e2e8f0;
    }

    .product-hero-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .product-hero-placeholder {
        width: 100%;
        height: 100%;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 8px;
        color: #94a3b8;
    }

    .product-hero-placeholder span {
        font-size: 12px;
        font-weight: 600;
    }

    .product-hero-body {
        flex: 1;
        min-width: 0;
    }

    .product-hero-top {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
        margin-bottom: 10px;
    }

    .pill {
        display: inline-flex;
        align-items: center;
        padding: 4px 11px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 700;
        background: #eff6ff;
        color: #1d4ed8;
    }

    .pill-muted {
        background: #f1f5f9;
        color: #475569;
    }

    .product-hero-name {
        margin: 0 0 4px;
        font-size: 26px;
        color: #0f172a;
        font-weight: 800;
        line-height: 1.2;
    }

    .product-hero-sku {
        color: #64748b;
        font-size: 14px;
        margin-bottom: 20px;
    }

    .product-hero-meta {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 16px;
    }

    .meta-item {
        display: flex;
        flex-direction: column;
        gap: 5px;
        padding: 12px 14px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
    }

    .meta-label {
        color: #94a3b8;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .04em;
    }

    .meta-value {
        color: #0f172a;
        font-size: 15px;
        font-weight: 700;
    }

    .section-card {
        margin-bottom: 20px;
    }

    .section-title {
        margin: 0 0 14px;
        font-size: 18px;
        color: #0f172a;
    }

    .description-text {
        margin: 0;
        color: #475569;
        line-height: 1.6;
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

        .product-hero {
            flex-direction: column;
        }

        .product-hero-image {
            width: 100%;
            height: 200px;
        }

        .product-hero-meta {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

    }

</style>

@endsection
