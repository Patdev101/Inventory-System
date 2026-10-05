@extends('layouts.app')

@section('title', 'Edit Product')

@section('content')

<div class="product-form">

    <div class="page-header">

        <div>

            <h1>Edit Product</h1>

            <p style="margin: 6px 0 0; color: #64748b;">
                Update product information and manage its measurement units.
            </p>

        </div>

        <div style="display: flex; gap: 8px;">

            <a
                href="{{ route('products.show', $product) }}"
                class="btn btn-secondary"
            >
                View Product
            </a>

            <a
                href="{{ route('products.index') }}"
                class="btn btn-primary"
            >
                Back to Products
            </a>

        </div>

    </div>


    @if ($errors->any())

        <div class="error-box">

            <strong>
                Please correct the following:
            </strong>

            <ul>

                @foreach ($errors->all() as $error)

                    <li>
                        {{ $error }}
                    </li>

                @endforeach

            </ul>

        </div>

    @endif


    @if ($hasInventoryHistory)

        <div class="warning-box">

            <strong>
                🔒 Unit definitions are protected
            </strong>

            <p>
                This product already has inventory or transaction history.
                Existing base-unit definitions and conversion factors
                cannot be changed.
            </p>

            <p style="margin-bottom: 0;">
                New measurement units may still be added.
            </p>

        </div>

    @endif


    @php

        $formUnits = old('units');

        if ($formUnits === null) {

            $formUnits =
                $product->productUnits
                    ->map(function ($productUnit) {

                        return [
                            'unit_of_measure_id' =>
                                $productUnit->unit_of_measure_id,

                            'conversion_factor' =>
                                $productUnit->conversion_factor,

                            'product_unit_id' =>
                                $productUnit->id,

                            'is_default' =>
                                $productUnit->is_default,
                        ];

                    })
                    ->values()
                    ->toArray();
        }

    @endphp


    <form
        action="{{ route('products.update', $product) }}"
        method="POST"
        id="product-form"
        enctype="multipart/form-data"
    >

        @csrf

        @method('PUT')


        <div class="form-group">

            <label for="product_category_id">
                Product Category
            </label>

            <select
                id="product_category_id"
                name="product_category_id"
                class="form-control"
                required
            >

                <option value="">
                    -- Select Category --
                </option>

                @foreach ($categories as $category)

                    <option
                        value="{{ $category->id }}"
                        {{ old(
                            'product_category_id',
                            $product->product_category_id
                        ) == $category->id ? 'selected' : '' }}
                    >
                        {{ $category->name }}

                        @if ($category->code)
                            ({{ $category->code }})
                        @endif
                    </option>

                @endforeach

            </select>

        </div>


        <div class="form-group">

            <label for="company_id">
                Company
            </label>

            <select
                id="company_id"
                name="company_id"
                class="form-control"
                required
            >

                <option value="">
                    -- Select Company --
                </option>

                @foreach ($companies as $company)

                    <option
                        value="{{ $company->id }}"
                        {{ old(
                            'company_id',
                            $product->company_id
                        ) == $company->id ? 'selected' : '' }}
                    >
                        {{ $company->name }}

                        @if ($company->code)
                            ({{ $company->code }})
                        @endif
                    </option>

                @endforeach

            </select>

        </div>


        <div class="form-group">

            <label for="name">
                Product Name
            </label>

            <input
                type="text"
                id="name"
                name="name"
                class="form-control"
                value="{{ old('name', $product->name) }}"
                maxlength="200"
                required
            >

        </div>


        <hr>


        <h2>
            Base Unit

            @if ($hasInventoryHistory)

                <span class="locked-badge">
                    🔒 Locked
                </span>

            @endif

        </h2>


        <div class="form-group">

            <select
                id="base_unit_id"
                name="base_unit_id"
                class="form-control {{ $hasInventoryHistory ? 'locked' : '' }}"
                required

                @if ($hasInventoryHistory)
                    disabled
                @endif
            >

                <option value="">
                    -- Select Base Unit --
                </option>

                @foreach ($units as $unit)

                    <option
                        value="{{ $unit->id }}"
                        {{ old(
                            'base_unit_id',
                            $product->base_unit_id
                        ) == $unit->id ? 'selected' : '' }}
                    >

                        {{ $unit->name }}
                        ({{ $unit->code }})

                    </option>

                @endforeach

            </select>


            @if ($hasInventoryHistory)

                <input
                    type="hidden"
                    name="base_unit_id"
                    value="{{ $product->base_unit_id }}"
                >

                <div class="info-box">
                    🔒 The base unit cannot be changed because
                    historical inventory depends on its definition.
                </div>

            @endif

        </div>


        @include('products._unit-suggestions')



        <h2>
            Available Units
        </h2>


        <div class="unit-table-wrapper">

            <table class="unit-table">

                <thead>

                    <tr>

                        <th>Unit</th>

                        <th>
                            Conversion to Base Unit
                        </th>

                        <th>Status</th>

                        <th style="text-align: center;">
                            Action
                        </th>

                    </tr>

                </thead>

                <tbody id="units-container">

                    @foreach ($formUnits as $index => $formUnit)

                        @php

                            $unitId =
                                $formUnit['unit_of_measure_id']
                                ?? '';

                            $conversion =
                                $formUnit['conversion_factor']
                                ?? '1';

                            $existingProductUnit =
                                $product->productUnits
                                    ->firstWhere(
                                        'unit_of_measure_id',
                                        $unitId
                                    );

                            $isExisting =
                                $existingProductUnit !== null;

                            $isUnitLocked =
                                $hasInventoryHistory &&
                                $isExisting;

                        @endphp

                        <tr
                            class="unit-row {{ $isUnitLocked ? 'is-locked' : '' }}"
                        >

                            <td>

                                <select
                                    name="units[{{ $index }}][unit_of_measure_id]"
                                    class="form-control unit-select {{ $isUnitLocked ? 'locked' : '' }}"
                                    required

                                    @if ($isUnitLocked)
                                        disabled
                                    @endif
                                >

                                    <option value="">
                                        -- Select Unit --
                                    </option>

                                    @foreach ($units as $unit)

                                        <option
                                            value="{{ $unit->id }}"
                                            {{ (string) $unitId === (string) $unit->id ? 'selected' : '' }}
                                        >
                                            {{ $unit->name }}
                                            ({{ $unit->code }})
                                        </option>

                                    @endforeach

                                </select>


                                @if ($isUnitLocked)

                                    <input
                                        type="hidden"
                                        name="units[{{ $index }}][unit_of_measure_id]"
                                        value="{{ $unitId }}"
                                    >

                                @endif

                            </td>


                            <td>

                                <input
                                    type="text" inputmode="decimal" autocomplete="off" data-numeric
                                    name="units[{{ $index }}][conversion_factor]"
                                    class="form-control conversion-input {{ $isUnitLocked ? 'locked' : '' }}"
                                    value="{{ $conversion }}"
                                    min="0.0001"
                                    step="0.0001"
                                    required

                                    @if ($isUnitLocked)
                                        readonly
                                        data-history-locked="1"
                                    @endif
                                >

                                <small class="help-text">

                                    1 selected unit =

                                    <span class="factor-preview">
                                        {{ format_qty((float) $conversion) }}
                                    </span>

                                    base units.

                                </small>

                            </td>


                            <td>

                                @if ($isUnitLocked)

                                    <span class="locked-badge">
                                        🔒 Existing
                                    </span>

                                @else

                                    <span style="color: #166534;">
                                        Editable
                                    </span>

                                @endif

                            </td>


                            <td style="text-align: center;">

                                <button
                                    type="button"
                                    class="remove-unit btn btn-secondary"
                                >
                                    Remove
                                </button>

                            </td>

                        </tr>

                    @endforeach

                </tbody>

            </table>

        </div>


        <button
            type="button"
            id="add-unit"
            class="btn btn-secondary"
            style="margin-top: 15px;"
        >
            + Add Unit
        </button>


        <hr>


        <h2>Pricing</h2>

        <p class="muted">
            Prices are per <strong>base unit</strong> and already include VAT.
        </p>


        <details class="cost-helper">
            <summary>Bought by the box or case? Work out the cost per base unit</summary>

        <div class="cost-helper-box">

            <div class="cost-helper-row">

                <div>
                    <label for="cost-helper-amount">
                        I paid
                    </label>
                    <input
                        type="text" inputmode="decimal" autocomplete="off" data-numeric
                        id="cost-helper-amount"
                        class="form-control"
                        min="0"
                        step="0.01"
                        placeholder="e.g. 200"
                    >
                </div>

                <div>
                    <label for="cost-helper-unit">
                        per
                    </label>
                    <select
                        id="cost-helper-unit"
                        class="form-control"
                    >
                        <option value="">-- Select a unit --</option>
                    </select>
                </div>

                <div>
                    <button
                        type="button"
                        id="cost-helper-apply"
                        class="btn btn-secondary"
                    >
                        Use this cost
                    </button>
                </div>

            </div>

            <small class="help-text" id="cost-helper-result"></small>

        </div>


        </details>


        <div class="pricing-grid">

        <div class="form-group">

            <label for="cost_price">
                Cost Price
                <small>(per base unit)</small>
            </label>

            <input
                type="text" inputmode="decimal" autocomplete="off" data-numeric
                id="cost_price"
                name="cost_price"
                class="form-control"
                value="{{ old('cost_price', $product->cost_price) }}"
                min="0"
                step="0.0001"
                placeholder="0.00"
            >

        </div>


        <div class="form-group">

            <label for="pricing_method">
                Pricing Method
            </label>

            <select
                id="pricing_method"
                name="pricing_method"
                class="form-control"
            >

                <option
                    value="manual"
                    {{ old('pricing_method', $product->pricing_method) === 'manual' ? 'selected' : '' }}
                >
                    Manual
                </option>

                <option
                    value="markup"
                    {{ old('pricing_method', $product->pricing_method) === 'markup' ? 'selected' : '' }}
                >
                    Markup
                </option>

            </select>

        </div>


        <div class="form-group" id="markup-field-group">

            <label for="markup_percentage">
                Markup %
            </label>

            <input
                type="text" inputmode="decimal" autocomplete="off" data-numeric
                id="markup_percentage"
                name="markup_percentage"
                class="form-control"
                value="{{ old('markup_percentage', $product->markup_percentage) }}"
                min="0"
                step="0.01"
                placeholder="0.00"
            >

        </div>


        <div class="form-group">

            <label for="selling_price">
                Selling Price
                <small>(per base unit)</small>
            </label>

            <input
                type="text" inputmode="decimal" autocomplete="off" data-numeric
                id="selling_price"
                name="selling_price"
                class="form-control"
                value="{{ old('selling_price', $product->selling_price) }}"
                min="0"
                step="0.01"
                placeholder="0.00"
            >

            <small class="help-text" id="selling-price-help">
                Enter the selling price directly.
            </small>

        </div>


        </div>


        <div class="price-summary">
            <span class="price-summary-empty">Enter a cost and a selling price to see profit, VAT and the price for each unit.</span>
            <div class="summary-group" id="pricing-preview" hidden></div>
            <div class="summary-group" id="vat-breakdown" hidden></div>
            <div class="summary-group summary-units" id="unit-pricing-preview" hidden></div>
        </div>


        <hr>


        <h2>Stock Settings and Details</h2>


        <div class="form-group">

            <label for="reorder_point">
                Reorder Point
            </label>

            <input
                type="text" inputmode="decimal" autocomplete="off" data-numeric
                id="reorder_point"
                name="reorder_point"
                class="form-control"
                value="{{ old('reorder_point', $product->reorder_point) }}"
                min="0"
                step="0.0001"
                required
            >

        </div>


        <div class="form-group">

            <label>
                Product Status
            </label>

            <label class="checkbox-label">

                <input
                    type="checkbox"
                    name="is_active"
                    value="1"
                    {{ old(
                        'is_active',
                        $product->is_active
                    ) ? 'checked' : '' }}
                >

                Active

            </label>

        </div>


        <div class="form-group">

            <label>
                Product Image
                <small>(Optional)</small>
            </label>

            <div class="image-upload">

                <input
                    type="file"
                    id="image"
                    name="image"
                    class="image-upload-input"
                    accept="image/*"
                >

                <input
                    type="hidden"
                    name="remove_image"
                    id="remove_image"
                    value="0"
                >

                <div class="image-upload-preview" id="image-upload-preview">

                    <div
                        class="image-upload-placeholder"
                        id="image-upload-placeholder"
                        style="{{ $product->image_url ? 'display:none;' : '' }}"
                    >

                        <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 16V4M12 4l-4 4M12 4l4 4"></path>
                            <path d="M4 16v3a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1v-3"></path>
                        </svg>

                        <span>Click or drag an image here</span>

                    </div>

                    <img
                        id="image-upload-img"
                        alt="{{ $product->name }}"
                        src="{{ $product->image_url }}"
                        style="{{ $product->image_url ? 'display:block;' : '' }}"
                    >

                    <button
                        type="button"
                        id="image-upload-remove"
                        class="image-upload-remove"
                        aria-label="Remove image"
                        style="{{ $product->image_url ? 'display:flex;' : '' }}"
                    >&times;</button>

                </div>

            </div>

            <small class="help-text">
                JPG, PNG or similar. Max 4MB. Click the image to replace it, or use × to remove it.
            </small>

        </div>


        <div class="form-group">

            <label for="description">
                Description
            </label>

            <textarea
                id="description"
                name="description"
                class="form-control"
                maxlength="500"
                rows="5"
            >{{ old('description', $product->description) }}</textarea>

        </div>


        <hr>


        <h2>Product Codes (optional)</h2>

        <p class="muted">
            Leave these blank and the system fills them in for you.
        </p>


        <div class="form-group">

            <label for="sku">
                SKU
                <small>(Optional)</small>
            </label>

            <input
                type="text"
                id="sku"
                name="sku"
                class="form-control"
                value="{{ old('sku', $product->sku) }}"
                maxlength="100"
            >

        </div>


        <div class="form-group">

            <label for="barcode">
                Barcode
                <small>(Optional)</small>
            </label>

            <input
                type="text"
                id="barcode"
                name="barcode"
                class="form-control"
                value="{{ old('barcode', $product->barcode) }}"
                maxlength="100"
                placeholder="Scan or type the product barcode"
            >

            <small class="help-text">
                Used by the POS to add this product to a sale by scanning.
            </small>

        </div>


        <div class="form-group">

            <label for="item_code">
                Item Code
                <small>(Optional)</small>
            </label>

            <input
                type="text"
                id="item_code"
                name="item_code"
                class="form-control"
                value="{{ old('item_code', $product->item_code) }}"
                maxlength="100"
            >

        </div>


        <div class="button-row">

            <button
                type="submit"
                class="btn btn-primary"
            >
                Update Product
            </button>

            <a
                href="{{ route('products.index') }}"
                class="btn btn-secondary"
            >
                Cancel
            </a>

        </div>

    </form>

</div>


<style>

    .product-form {
        max-width: 1100px;
        margin: 0 auto;
    }

    .product-form h2 {
        margin: 0 0 8px;
        font-size: 20px;
        color: #111827;
    }

    .product-form hr {
        margin: 28px 0;
        border: none;
        border-top: 2px solid #cbd5e1;
    }

    .muted {
        margin: 0 0 15px;
        color: #64748b;
    }

    .form-group {
        margin-bottom: 18px;
    }

    .form-group > label {
        display: block;
        margin-bottom: 7px;
        font-weight: 600;
        color: #374151;
    }

    .form-control {
        width: 100%;
        box-sizing: border-box;
        padding: 10px 12px;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        font-size: 14px;
        background: #fff;
    }

    .locked {
        background: #f3f4f6 !important;
        color: #6b7280;
        cursor: not-allowed;
    }

    .help-text {
        display: block;
        margin-top: 6px;
        color: #6b7280;
        font-size: 12px;
    }

    .error-box {
        margin-bottom: 20px;
        padding: 15px;
        background: #fee2e2;
        border: 1px solid #fecaca;
        border-radius: 8px;
        color: #991b1b;
    }

    .warning-box {
        margin-bottom: 20px;
        padding: 15px;
        background: #fff7ed;
        border: 1px solid #fed7aa;
        border-radius: 8px;
        color: #9a3412;
    }

    .info-box {
        margin: 15px 0;
        padding: 12px;
        background: #eff6ff;
        border: 1px solid #bfdbfe;
        border-radius: 6px;
        color: #1e40af;
    }

    .cost-helper-box {
        margin-bottom: 18px;
        padding: 14px;
        background: #fffbeb;
        border: 1px solid #fde68a;
        border-radius: 8px;
        color: #78350f;
    }

    .cost-helper-row {
        display: flex;
        align-items: flex-end;
        gap: 10px;
        flex-wrap: wrap;
    }

    .cost-helper-row > div {
        flex: 1;
        min-width: 140px;
    }

    .cost-helper-row label {
        display: block;
        margin-bottom: 5px;
        font-size: 12px;
        font-weight: 600;
    }

    .unit-table-wrapper {
        overflow-x: auto;
        border: 1px solid #ddd;
        border-radius: 6px;
    }

    .unit-table {
        width: 100%;
        border-collapse: collapse;
    }

    .unit-table th,
    .unit-table td {
        padding: 10px;
        border-bottom: 1px solid #e5e7eb;
        text-align: left;
        vertical-align: middle;
    }

    .unit-table th {
        background: #f5f5f5;
    }

    .unit-row.is-locked {
        background: #fafafa;
    }

    .locked-badge {
        display: inline-block;
        padding: 3px 7px;
        background: #e5e7eb;
        color: #4b5563;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 600;
    }

    /* Units table: fields line up along the top of each row */
    .unit-table td { vertical-align: top; }
    .unit-table .form-control,
    .unit-table .btn { height: 44px; box-sizing: border-box; }
    .unit-table .help-text { margin-top: 4px; }

    /* Pricing: compact layout */
    .cost-helper {
        margin-bottom: 16px;
        border: 1px solid #fde68a;
        background: #fffbeb;
        border-radius: 8px;
        color: #78350f;
    }
    .cost-helper summary { cursor: pointer; padding: 10px 14px; font-size: 14px; font-weight: 600; }
    .cost-helper .cost-helper-box { margin: 0; border: 0; border-top: 1px solid #fde68a; border-radius: 0 0 8px 8px; }
    .pricing-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
        gap: 0 16px;
        align-items: start;
    }
    .price-summary {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 10px 22px;
        margin-top: 6px;
        padding: 12px 14px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
    }
    .price-summary-empty { color: #64748b; font-size: 13px; }
    .price-summary:has(.summary-group:not([hidden])) .price-summary-empty { display: none; }
    .summary-group { display: flex; flex-wrap: wrap; gap: 8px 22px; }
    .summary-group[hidden] { display: none; }
    .summary-units { flex-basis: 100%; gap: 8px; padding-top: 10px; border-top: 1px solid #e2e8f0; }
    .summary-stat small {
        display: block; font-size: 11px; font-weight: 700; letter-spacing: .04em;
        text-transform: uppercase; color: #64748b;
    }
    .summary-stat b { font-size: 17px; color: #0f172a; font-variant-numeric: tabular-nums; }
    .summary-stat.is-loss b { color: #b91c1c; }
    .summary-chip {
        font-size: 13px; padding: 5px 10px; border-radius: 999px;
        background: #fff; border: 1px solid #cbd5e1; color: #334155;
    }
    .summary-chip b { color: #0f172a; }
    .checkbox-label {
        display: inline-flex !important;
        align-items: center;
        gap: 7px;
        cursor: pointer;
        font-weight: normal !important;
    }

    .button-row {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        margin-top: 25px;
    }

    .image-upload {
        position: relative;
        width: 180px;
    }

    .image-upload-preview {
        position: relative;
        width: 180px;
        height: 180px;
        border: 2px dashed #cbd5e1;
        border-radius: 14px;
        background: #f8fafc;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        transition: border-color .15s ease, background-color .15s ease;
    }

    .image-upload-preview.dragover {
        border-color: #2563eb;
        background: #eff6ff;
    }

    .image-upload-input {
        position: absolute;
        inset: 0;
        width: 180px;
        height: 180px;
        opacity: 0;
        cursor: pointer;
        z-index: 2;
    }

    .image-upload-placeholder {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 8px;
        color: #94a3b8;
        padding: 14px;
        text-align: center;
        font-size: 12px;
        font-weight: 600;
    }

    .image-upload-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: none;
    }

    .image-upload-remove {
        position: absolute;
        top: 6px;
        right: 6px;
        width: 26px;
        height: 26px;
        border-radius: 50%;
        background: rgba(15, 23, 42, .68);
        color: #fff;
        border: none;
        cursor: pointer;
        font-size: 16px;
        line-height: 1;
        z-index: 3;
        display: none;
        align-items: center;
        justify-content: center;
    }

    .image-upload-remove:hover {
        background: rgba(15, 23, 42, .85);
    }

</style>


<script>
function formatQty(value) {
    const num = Number(value);
    if (!isFinite(num)) {
        return String(value);
    }
    return parseFloat(num.toFixed(2)).toString();
}

document.addEventListener('DOMContentLoaded', function () {

    const container =
        document.getElementById('units-container');

    const addButton =
        document.getElementById('add-unit');

    const form =
        document.getElementById('product-form');

    const baseUnit =
        document.getElementById('base_unit_id');

    let unitIndex =
        container.querySelectorAll('.unit-row').length;


    /*
    |--------------------------------------------------------------------------
    | Price by unit
    |--------------------------------------------------------------------------
    |
    | Cost price and selling price are always entered per BASE unit. This
    | shows what each configured unit (Box, Case, ...) actually costs and
    | sells for, so it's clear a ₱23.75 base-unit price means a Box of 12
    | is ₱285.00 — not ₱23.75 a box.
    */

    function escapeUnitLabel(value) {

        const div =
            document.createElement('div');

        div.textContent = value;

        return div.innerHTML;
    }


    function updateUnitPricingPreview() {

        const preview =
            document.getElementById('unit-pricing-preview');

        if (!preview) {
            return;
        }

        const sellingPriceField =
            document.getElementById('selling_price');

        const costPriceField =
            document.getElementById('cost_price');

        const sellingPrice =
            sellingPriceField
                ? parseFloat(sellingPriceField.value)
                : NaN;

        const costPrice =
            costPriceField
                ? parseFloat(costPriceField.value)
                : NaN;

        if (isNaN(sellingPrice) && isNaN(costPrice)) {
            preview.hidden = true;
            return;
        }

        let html = '';

        let hasRows = false;

        container
            .querySelectorAll('.unit-row')
            .forEach(function (row) {

                const select =
                    row.querySelector('.unit-select');

                const conversion =
                    row.querySelector('.conversion-input');

                if (!select || !select.value || !conversion) {
                    return;
                }

                const factor =
                    parseFloat(conversion.value);

                if (isNaN(factor)) {
                    return;
                }

                hasRows = true;

                const selectedOption =
                    select.options[select.selectedIndex];

                const unitLabel =
                    escapeUnitLabel(
                        selectedOption
                            ? selectedOption.text
                            : 'Unit'
                    );

                const parts = [];

                if (!isNaN(costPrice)) {
                    parts.push(
                        'Cost ₱' + (costPrice * factor).toFixed(2)
                    );
                }

                if (!isNaN(sellingPrice)) {
                    parts.push(
                        'Sells ₱' + (sellingPrice * factor).toFixed(2)
                    );
                }

                html += '<span class="summary-chip"><b>' + unitLabel + '</b> ' + parts.join(' · ') + '</span>';
            });

        if (!hasRows) {
            preview.hidden = true;
            return;
        }

        preview.hidden = false;
        preview.innerHTML = html;
    }


    /*
    |--------------------------------------------------------------------------
    | Cost price helper
    |--------------------------------------------------------------------------
    |
    | Cost Price must be entered per base unit, but purchases usually come
    | in a bigger unit (e.g. "₱200 per Box of 12"). This lets the user type
    | what they actually paid and picks the right unit, and we do the
    | division into a per-base-unit cost for them.
    */

    function populateCostHelperUnitOptions() {

        const select =
            document.getElementById('cost-helper-unit');

        if (!select) {
            return;
        }

        const previousValue =
            select.value;

        select.innerHTML =
            '<option value="">-- Select a unit --</option>';

        container
            .querySelectorAll('.unit-row')
            .forEach(function (row) {

                const unitSelect =
                    row.querySelector('.unit-select');

                const conversion =
                    row.querySelector('.conversion-input');

                if (!unitSelect || !unitSelect.value || !conversion) {
                    return;
                }

                const factor =
                    parseFloat(conversion.value);

                if (isNaN(factor) || factor <= 0) {
                    return;
                }

                const selectedOption =
                    unitSelect.options[unitSelect.selectedIndex];

                const option =
                    document.createElement('option');

                option.value = factor;

                option.textContent =
                    (selectedOption ? selectedOption.text : 'Unit')
                    + ' (1 = '
                    + formatQty(factor)
                    + ' base units)';

                select.appendChild(option);
            });

        if (previousValue) {
            select.value = previousValue;
        }
    }


    function applyCostHelper() {

        const amountField =
            document.getElementById('cost-helper-amount');

        const unitSelect =
            document.getElementById('cost-helper-unit');

        const resultText =
            document.getElementById('cost-helper-result');

        const amount =
            parseFloat(amountField.value);

        const factor =
            parseFloat(unitSelect.value);

        if (isNaN(amount) || amount < 0) {
            resultText.textContent =
                'Enter how much you paid first.';
            return;
        }

        if (!unitSelect.value || isNaN(factor) || factor <= 0) {
            resultText.textContent =
                'Select which unit that price is for.';
            return;
        }

        const costPerBaseUnit =
            amount / factor;

        document.getElementById('cost_price').value =
            costPerBaseUnit.toFixed(4);

        resultText.textContent =
            '₱' + amount.toFixed(2) + ' ÷ ' + formatQty(factor)
            + ' = ₱' + costPerBaseUnit.toFixed(4) + ' per base unit — applied below.';

        updatePricingUI();
        updateUnitPricingPreview();
    }


    const costHelperApplyButton =
        document.getElementById('cost-helper-apply');

    if (costHelperApplyButton) {
        costHelperApplyButton.addEventListener('click', applyCostHelper);
    }


    function updatePreview(row) {

        const input =
            row.querySelector('.conversion-input');

        const preview =
            row.querySelector('.factor-preview');

        if (!input || !preview) {
            return;
        }

        const value =
            parseFloat(input.value);

        updateUnitPricingPreview();
        populateCostHelperUnitOptions();

        preview.textContent =
            isNaN(value)
                ? '-'
                : formatQty(value);
    }


    function attachRowEvents(row) {

        const removeButton =
            row.querySelector('.remove-unit');

        const select =
            row.querySelector('.unit-select');

        const conversion =
            row.querySelector('.conversion-input');


        if (removeButton) {

            removeButton.addEventListener(
                'click',
                function () {

                    const rows =
                        container.querySelectorAll('.unit-row');

                    if (rows.length <= 1) {

                        alert(
                            'A product must have at least one available unit.'
                        );

                        return;
                    }

                    if (
                        row.classList.contains(
                            'is-locked'
                        )
                    ) {

                        alert(
                            'This unit cannot be removed because it is already used by inventory or transaction history.'
                        );

                        return;
                    }

                    row.remove();

                    updateBaseUnit();

                }
            );

        }


        if (select) {

            select.addEventListener(
                'change',
                function () {

                    updateBaseUnit();

                }
            );

        }


        if (conversion) {

            conversion.addEventListener(
                'input',
                function () {

                    updatePreview(row);

                }
            );

        }

    }


    function updateBaseUnit() {

        updateUnitPricingPreview();
        populateCostHelperUnitOptions();

        if (!baseUnit) {
            return;
        }

        const baseId =
            baseUnit.value;

        if (!baseId) {
            return;
        }

        container
            .querySelectorAll('.unit-row')
            .forEach(function (row) {

                const select =
                    row.querySelector('.unit-select');

                const conversion =
                    row.querySelector(
                        '.conversion-input'
                    );

                if (!select || !conversion) {
                    return;
                }

                const historyLocked =
                    conversion.hasAttribute(
                        'data-history-locked'
                    );

                if (select.value === baseId) {

                    if (!historyLocked) {

                        conversion.value = '1';

                        conversion.readOnly = true;

                        conversion.style.backgroundColor =
                            '#f3f4f6';

                        updatePreview(row);

                    }

                } else {

                    if (!historyLocked) {

                        conversion.readOnly = false;

                        conversion.style.backgroundColor =
                            '';

                    }

                }

            });

    }


    addButton.addEventListener(
        'click',
        function () {

            const row =
                document.createElement('tr');

            row.className =
                'unit-row';

            row.innerHTML = `
                <td>

                    <select
                        name="units[${unitIndex}][unit_of_measure_id]"
                        class="form-control unit-select"
                        required
                    >

                        <option value="">
                            -- Select Unit --
                        </option>

                        @foreach ($units as $unit)

                            <option value="{{ $unit->id }}">
                                {{ $unit->name }}
                                ({{ $unit->code }})
                            </option>

                        @endforeach

                    </select>

                </td>

                <td>

                    <input
                        type="text" inputmode="decimal" autocomplete="off" data-numeric
                        name="units[${unitIndex}][conversion_factor]"
                        class="form-control conversion-input"
                        value="1"
                        min="0.0001"
                        step="0.0001"
                        required
                    >

                    <small class="help-text">

                        1 selected unit =

                        <span class="factor-preview">
                            1.0000
                        </span>

                        base units.

                    </small>

                </td>

                <td>

                    <span style="color: #166534;">
                        New unit
                    </span>

                </td>

                <td style="text-align: center;">

                    <button
                        type="button"
                        class="remove-unit btn btn-secondary"
                    >
                        Remove
                    </button>

                </td>
            `;

            container.appendChild(row);

            unitIndex++;

            attachRowEvents(row);

            updateBaseUnit();

        }
    );


    container
        .querySelectorAll('.unit-row')
        .forEach(function (row) {

            attachRowEvents(row);

            updatePreview(row);

        });


    if (baseUnit) {

        baseUnit.addEventListener(
            'change',
            function () {

                updateBaseUnit();

            }
        );

    }


    form.addEventListener(
        'submit',
        function (event) {

            const selectedUnits = [];

            let duplicate = false;

            let hasBaseUnit = false;

            let invalidBaseFactor = false;


            container
                .querySelectorAll('.unit-row')
                .forEach(function (row) {

                    const select =
                        row.querySelector('.unit-select');

                    const conversion =
                        row.querySelector(
                            '.conversion-input'
                        );

                    if (
                        !select ||
                        !select.value
                    ) {
                        return;
                    }

                    if (
                        selectedUnits.includes(
                            select.value
                        )
                    ) {
                        duplicate = true;
                    }

                    selectedUnits.push(
                        select.value
                    );


                    if (
                        baseUnit &&
                        select.value ===
                        baseUnit.value
                    ) {

                        hasBaseUnit = true;

                        const factor =
                            parseFloat(
                                conversion.value
                            );

                        if (
                            isNaN(factor) ||
                            Math.abs(
                                factor - 1
                            ) > 0.0000001
                        ) {
                            invalidBaseFactor = true;
                        }

                    }

                });


            if (duplicate) {

                event.preventDefault();

                alert(
                    'The same unit cannot be added more than once.'
                );

                return;

            }


            if (
                baseUnit &&
                baseUnit.value &&
                !hasBaseUnit
            ) {

                event.preventDefault();

                alert(
                    'The selected base unit must also be included in the available units.'
                );

                return;

            }


            if (invalidBaseFactor) {

                event.preventDefault();

                alert(
                    'The base unit must have a conversion factor of 1.'
                );

            }

        }
    );


    updateBaseUnit();


    /*
    |--------------------------------------------------------------------------
    | Live pricing preview
    |--------------------------------------------------------------------------
    |
    | This is a convenience preview only. The server always recalculates
    | the markup-based selling price independently and never trusts
    | whatever the browser computed here.
    */

    const costPriceInput = document.getElementById('cost_price');
    const pricingMethodSelect = document.getElementById('pricing_method');
    const markupInput = document.getElementById('markup_percentage');
    const sellingPriceInput = document.getElementById('selling_price');
    const markupFieldGroup = document.getElementById('markup-field-group');
    const sellingPriceHelp = document.getElementById('selling-price-help');
    const pricingPreview = document.getElementById('pricing-preview');

    function formatMoney(value) {
        return '₱' + Number(value || 0).toFixed(2);
    }

    function updatePricingUI() {
        const isMarkup = pricingMethodSelect.value === 'markup';

        markupFieldGroup.style.display = isMarkup ? '' : 'none';
        sellingPriceInput.readOnly = isMarkup;
        sellingPriceInput.style.backgroundColor = isMarkup ? '#f3f4f6' : '';

        sellingPriceHelp.textContent = isMarkup
            ? 'Calculated automatically from cost price and markup %.'
            : 'Enter the selling price directly.';

        const costPrice = parseFloat(costPriceInput.value);

        if (isMarkup) {
            const markupPercentage = parseFloat(markupInput.value);

            if (!isNaN(costPrice) && !isNaN(markupPercentage)) {
                const computedSellingPrice =
                    costPrice + (costPrice * markupPercentage / 100);

                sellingPriceInput.value = computedSellingPrice.toFixed(2);
            } else {
                sellingPriceInput.value = '';
            }
        }

        const sellingPrice = parseFloat(sellingPriceInput.value);

        updateVatBreakdown(sellingPrice);

        if (isNaN(costPrice) || isNaN(sellingPrice)) {
            pricingPreview.hidden = true;
            return;
        }

        const profit = sellingPrice - costPrice;
        const margin = sellingPrice > 0 ? (profit / sellingPrice) * 100 : null;

        pricingPreview.hidden = false;
        pricingPreview.innerHTML =
            '<span class="summary-stat' + (profit < 0 ? ' is-loss' : '') + '"><small>Profit</small><b>' + formatMoney(profit) + '</b></span>' +
            '<span class="summary-stat"><small>Margin</small><b>' + (margin === null ? 'N/A' : margin.toFixed(2) + '%') + '</b></span>';
    }


    /*
    |--------------------------------------------------------------------------
    | VAT breakdown
    |--------------------------------------------------------------------------
    |
    | selling_price is VAT-inclusive — it's the exact shelf price the POS
    | charges, VAT is never added on top of it (see the POS checkout logic).
    | This shows the two numbers that already make it up, so it's clear
    | why Subtotal + VAT = Selling Price rather than Selling Price + VAT.
    */

    const vatRate = {{ (float) $vatRate }};

    function updateVatBreakdown(sellingPrice) {
        const breakdown = document.getElementById('vat-breakdown');

        if (!breakdown) {
            return;
        }

        if (isNaN(sellingPrice) || sellingPrice <= 0) {
            breakdown.hidden = true;
            return;
        }

        if (vatRate <= 0) {
            breakdown.hidden = true;
            return;
        }

        const vatableSales = sellingPrice / (1 + (vatRate / 100));
        const vatAmount = sellingPrice - vatableSales;

        breakdown.hidden = false;
        breakdown.title = 'The selling price already includes VAT. The POS charges exactly this price and only shows the VAT on the receipt.';
        breakdown.innerHTML =
            '<span class="summary-stat"><small>Before VAT</small><b>' + formatMoney(vatableSales) + '</b></span>' +
            '<span class="summary-stat"><small>VAT ' + vatRate + '%</small><b>' + formatMoney(vatAmount) + '</b></span>';
    }

    costPriceInput.addEventListener('input', function () {
        updatePricingUI();
        updateUnitPricingPreview();
    });

    pricingMethodSelect.addEventListener('change', function () {
        updatePricingUI();
        updateUnitPricingPreview();
    });

    markupInput.addEventListener('input', function () {
        updatePricingUI();
        updateUnitPricingPreview();
    });

    sellingPriceInput.addEventListener('input', function () {
        updatePricingUI();
        updateUnitPricingPreview();
    });

    updatePricingUI();
    updateUnitPricingPreview();

});
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const imageInput = document.getElementById('image');
    const imagePreview = document.getElementById('image-upload-preview');
    const imagePlaceholder = document.getElementById('image-upload-placeholder');
    const imagePreviewImg = document.getElementById('image-upload-img');
    const imageRemoveBtn = document.getElementById('image-upload-remove');
    const removeImageField = document.getElementById('remove_image');

    if (!imageInput) {
        return;
    }

    function showImageFile(file) {
        const reader = new FileReader();

        reader.onload = function (event) {
            imagePreviewImg.src = event.target.result;
            imagePreviewImg.style.display = 'block';
            imagePlaceholder.style.display = 'none';
            imageRemoveBtn.style.display = 'flex';
            removeImageField.value = '0';
        };

        reader.readAsDataURL(file);
    }

    imageInput.addEventListener('change', function () {
        if (imageInput.files && imageInput.files[0]) {
            showImageFile(imageInput.files[0]);
        }
    });

    imagePreview.addEventListener('dragover', function (event) {
        event.preventDefault();
        imagePreview.classList.add('dragover');
    });

    ['dragleave', 'drop'].forEach(function (eventName) {
        imagePreview.addEventListener(eventName, function (event) {
            event.preventDefault();
            imagePreview.classList.remove('dragover');
        });
    });

    imagePreview.addEventListener('drop', function (event) {
        const file = event.dataTransfer.files[0];

        if (file) {
            imageInput.files = event.dataTransfer.files;
            showImageFile(file);
        }
    });

    imageRemoveBtn.addEventListener('click', function (event) {
        event.stopPropagation();
        imageInput.value = '';
        imagePreviewImg.style.display = 'none';
        imagePreviewImg.src = '';
        imagePlaceholder.style.display = 'flex';
        imageRemoveBtn.style.display = 'none';
        removeImageField.value = '1';
    });

});
</script>

@endsection
