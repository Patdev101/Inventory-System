@extends('layouts.app')

@section('title', 'Add Product')

@section('content')

<div class="product-form">

    <div class="page-header">

        <div>

            <h1>Add Product</h1>

            <p style="margin: 6px 0 0; color: #64748b;">
                Create a product and define its measurement units.
            </p>

        </div>

        <a
            href="{{ route('products.index') }}"
            class="btn btn-secondary"
        >
            Back to Products
        </a>

    </div>


    @if ($errors->any())

        <div class="error-box">

            <strong>
                Please correct the following:
            </strong>

            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>

        </div>

    @endif


    <form
        action="{{ route('products.store') }}"
        method="POST"
        id="product-form"
    >

        @csrf


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
                        {{ old('product_category_id') == $category->id ? 'selected' : '' }}
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
                        {{ old('company_id') == $company->id ? 'selected' : '' }}
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
                value="{{ old('name') }}"
                maxlength="200"
                required
            >

        </div>


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
                value="{{ old('sku') }}"
                maxlength="100"
                placeholder="Optional — can be entered/scanned later"
            >

            <small class="help-text">
                SKU can later be populated from a barcode scanner.
            </small>

        </div>


        <div class="form-group">

            <label for="reorder_point">
                Reorder Point
            </label>

            <input
                type="number"
                id="reorder_point"
                name="reorder_point"
                class="form-control"
                value="{{ old('reorder_point', '0') }}"
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
                    {{ old('is_active', '1') ? 'checked' : '' }}
                >

                Active

            </label>

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
                placeholder="Optional product description"
            >{{ old('description') }}</textarea>

        </div>


        <hr>


        <h2>Base Unit</h2>

        <p class="muted">
            The base unit is the reference measurement used internally
            for inventory calculations.
        </p>


        <div class="form-group">

            <label for="base_unit_id">
                Base Unit
            </label>

            <select
                id="base_unit_id"
                name="base_unit_id"
                class="form-control"
                required
            >

                <option value="">
                    -- Select Base Unit --
                </option>

                @foreach ($units as $unit)

                    <option
                        value="{{ $unit->id }}"
                        {{ old('base_unit_id') == $unit->id ? 'selected' : '' }}
                    >

                        {{ $unit->name }}
                        ({{ $unit->code }})

                    </option>

                @endforeach

            </select>

        </div>


        <h2>
            Available Units
        </h2>

        <p class="muted">
            Add every measurement that can be used for this product.
        </p>


        <div class="unit-table-wrapper">

            <table class="unit-table">

                <thead>

                    <tr>
                        <th>Unit</th>
                        <th>Conversion to Base Unit</th>
                        <th style="text-align: center;">Action</th>
                    </tr>

                </thead>

                <tbody id="units-container">

                    @php
                        $oldUnits = old('units', [
                            [
                                'unit_of_measure_id' => '',
                                'conversion_factor' => '1',
                            ],
                        ]);
                    @endphp

                    @foreach ($oldUnits as $index => $oldUnit)

                        <tr class="unit-row">

                            <td>

                                <select
                                    name="units[{{ $index }}][unit_of_measure_id]"
                                    class="form-control unit-select"
                                    required
                                >

                                    <option value="">
                                        -- Select Unit --
                                    </option>

                                    @foreach ($units as $unit)

                                        <option
                                            value="{{ $unit->id }}"
                                            {{ (string) ($oldUnit['unit_of_measure_id'] ?? '') === (string) $unit->id ? 'selected' : '' }}
                                        >
                                            {{ $unit->name }}
                                            ({{ $unit->code }})
                                        </option>

                                    @endforeach

                                </select>

                            </td>

                            <td>

                                <input
                                    type="number"
                                    name="units[{{ $index }}][conversion_factor]"
                                    class="form-control conversion-input"
                                    value="{{ $oldUnit['conversion_factor'] ?? '1' }}"
                                    min="0.0001"
                                    step="0.0001"
                                    required
                                >

                                <small class="help-text">
                                    1 selected unit =
                                    <span class="factor-preview">
                                        {{ number_format((float) ($oldUnit['conversion_factor'] ?? 1), 4) }}
                                    </span>
                                    base units.
                                </small>

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


        <div id="unit-info" class="info-box">
            Select the base unit and configure the available units.
        </div>


        <hr>


        <h2>Pricing</h2>

        <p class="muted">
            Cost price and selling price are always entered per
            <strong>base unit</strong> (the unit selected above). Set the
            selling price manually, or let it be calculated automatically
            from a cost price and markup percentage.
        </p>


        <div class="form-group">

            <label for="cost_price">
                Cost Price
                <small>(per base unit)</small>
            </label>

            <input
                type="number"
                id="cost_price"
                name="cost_price"
                class="form-control"
                value="{{ old('cost_price') }}"
                min="0"
                step="0.0001"
                placeholder="0.00"
            >

        </div>


        <div class="cost-helper-box">

            <strong>
                Not sure what that is per base unit?
            </strong>

            <p class="muted" style="margin: 4px 0 10px;">
                Tell us what you paid per purchasing unit (e.g. per Box)
                and we'll work out the cost per base unit for you.
            </p>

            <div class="cost-helper-row">

                <div>
                    <label for="cost-helper-amount">
                        I paid
                    </label>
                    <input
                        type="number"
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
                    {{ old('pricing_method', 'manual') === 'manual' ? 'selected' : '' }}
                >
                    Manual
                </option>

                <option
                    value="markup"
                    {{ old('pricing_method') === 'markup' ? 'selected' : '' }}
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
                type="number"
                id="markup_percentage"
                name="markup_percentage"
                class="form-control"
                value="{{ old('markup_percentage') }}"
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
                type="number"
                id="selling_price"
                name="selling_price"
                class="form-control"
                value="{{ old('selling_price') }}"
                min="0"
                step="0.01"
                placeholder="0.00"
            >

            <small class="help-text" id="selling-price-help">
                Enter the selling price directly.
            </small>

        </div>


        <div class="info-box" id="pricing-preview">
            Enter a cost price to see the expected profit and margin.
        </div>


        <div class="info-box" id="vat-breakdown">
            Enter a selling price to see the VAT breakdown.
        </div>


        <div id="unit-pricing-preview" class="info-box"></div>


        <div class="button-row">

            <button
                type="submit"
                class="btn btn-primary"
            >
                Save Product
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

    .form-control:focus {
        outline: none;
        border-color: #2563eb;
        box-shadow: 0 0 0 2px rgba(37, 99, 235, .1);
    }

    .help-text {
        display: block;
        margin-top: 6px;
        color: #6b7280;
        font-size: 12px;
    }

    .muted {
        margin: 0 0 15px;
        color: #64748b;
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

    .error-box {
        margin-bottom: 20px;
        padding: 15px;
        background: #fee2e2;
        border: 1px solid #fecaca;
        border-radius: 8px;
        color: #991b1b;
    }

    .error-box ul {
        margin-bottom: 0;
    }

    .info-box {
        margin-top: 15px;
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

</style>


<script>
document.addEventListener('DOMContentLoaded', function () {

    const container =
        document.getElementById('units-container');

    const addButton =
        document.getElementById('add-unit');

    const baseUnit =
        document.getElementById('base_unit_id');

    const info =
        document.getElementById('unit-info');

    const form =
        document.getElementById('product-form');

    let unitIndex =
        container.querySelectorAll('.unit-row').length;


    function attachRowEvents(row) {

        const removeButton =
            row.querySelector('.remove-unit');

        const select =
            row.querySelector('.unit-select');

        const conversion =
            row.querySelector('.conversion-input');


        removeButton.addEventListener('click', function () {

            const rows =
                container.querySelectorAll('.unit-row');

            if (rows.length <= 1) {

                alert(
                    'A product must have at least one available unit.'
                );

                return;
            }

            row.remove();

            updateBaseUnit();
            updateInfo();
        });


        select.addEventListener('change', function () {

            updateBaseUnit();
            updateInfo();
        });


        conversion.addEventListener('input', function () {

            updatePreview(row);
            updateInfo();
        });
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

        preview.textContent =
            isNaN(value)
                ? '-'
                : value.toFixed(4);
    }


    function updateBaseUnit() {

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
                    row.querySelector('.conversion-input');

                if (
                    select.value === baseId
                ) {

                    conversion.value = '1';

                    conversion.readOnly = true;

                    conversion.style.backgroundColor =
                        '#f3f4f6';

                    updatePreview(row);

                } else {

                    conversion.readOnly = false;

                    conversion.style.backgroundColor =
                        '';
                }
            });
    }


    function updateInfo() {

        if (!baseUnit.value) {

            info.innerHTML =
                'Select the base unit and configure the available units.';

            return;
        }

        const option =
            baseUnit.options[
                baseUnit.selectedIndex
            ];

        let html =
            '<strong>Base Unit:</strong> '
            + escapeHtml(option.text)
            + '<br><br>';

        let hasUnits = false;

        container
            .querySelectorAll('.unit-row')
            .forEach(function (row) {

                const select =
                    row.querySelector('.unit-select');

                const conversion =
                    row.querySelector('.conversion-input');

                if (!select.value) {
                    return;
                }

                hasUnits = true;

                const selectedOption =
                    select.options[
                        select.selectedIndex
                    ];

                const factor =
                    parseFloat(conversion.value);

                html +=
                    escapeHtml(selectedOption.text)
                    + ' = '
                    + (
                        isNaN(factor)
                            ? '-'
                            : factor.toFixed(4)
                    )
                    + ' base units<br>';
            });

        if (!hasUnits) {
            html += 'No units have been selected yet.';
        }

        info.innerHTML = html;

        updateUnitPricingPreview();
        populateCostHelperUnitOptions();
    }


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
            preview.innerHTML =
                'Enter a cost price or selling price to see what each unit costs and sells for.';
            return;
        }

        let html =
            '<strong>Price by Unit</strong><br><br>';

        let hasRows = false;

        container
            .querySelectorAll('.unit-row')
            .forEach(function (row) {

                const select =
                    row.querySelector('.unit-select');

                const conversion =
                    row.querySelector('.conversion-input');

                if (!select || !select.value) {
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
                    escapeHtml(selectedOption.text);

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

                html +=
                    unitLabel + ': ' + parts.join(' — ') + '<br>';
            });

        if (!hasRows) {
            preview.innerHTML =
                'Configure at least one unit to see pricing per unit.';
            return;
        }

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
                    + factor.toFixed(4)
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
            '₱' + amount.toFixed(2) + ' ÷ ' + factor.toFixed(4)
            + ' = ₱' + costPerBaseUnit.toFixed(4) + ' per base unit — applied below.';

        updatePricingUI();
        updateUnitPricingPreview();
    }


    const costHelperApplyButton =
        document.getElementById('cost-helper-apply');

    if (costHelperApplyButton) {
        costHelperApplyButton.addEventListener('click', applyCostHelper);
    }


    function escapeHtml(value) {

        const div =
            document.createElement('div');

        div.textContent = value;

        return div.innerHTML;
    }


    addButton.addEventListener('click', function () {

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
                    type="number"
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
        updateInfo();
    });


    container
        .querySelectorAll('.unit-row')
        .forEach(function (row) {

            attachRowEvents(row);
            updatePreview(row);

        });


    baseUnit.addEventListener(
        'change',
        function () {

            updateBaseUnit();
            updateInfo();

        }
    );


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
                        row.querySelector('.conversion-input');

                    if (!select.value) {
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
                            Math.abs(factor - 1) >
                            0.0000001
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
    updateInfo();


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
            pricingPreview.innerHTML =
                'Enter a cost price to see the expected profit and margin.';
            return;
        }

        const profit = sellingPrice - costPrice;
        const margin = sellingPrice > 0 ? (profit / sellingPrice) * 100 : null;

        pricingPreview.innerHTML =
            '<strong>Expected Profit:</strong> ' + formatMoney(profit) +
            '<br><strong>Profit Margin:</strong> ' +
            (margin === null ? 'N/A' : margin.toFixed(2) + '%');
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
            breakdown.innerHTML =
                'Enter a selling price to see the VAT breakdown.';
            return;
        }

        if (vatRate <= 0) {
            breakdown.innerHTML =
                '<strong>VAT:</strong> not applicable (VAT rate is 0%).' +
                '<br>Selling Price: ' + formatMoney(sellingPrice);
            return;
        }

        const vatableSales = sellingPrice / (1 + (vatRate / 100));
        const vatAmount = sellingPrice - vatableSales;

        breakdown.innerHTML =
            '<strong>Why the selling price is what it is (VAT ' + vatRate + '%):</strong><br>' +
            'Subtotal (VAT-exclusive): ' + formatMoney(vatableSales) +
            ' + VAT: ' + formatMoney(vatAmount) +
            ' = Selling Price: ' + formatMoney(sellingPrice) +
            '<br><small>This is exactly how the POS will show it on the receipt — ' +
            'the customer pays ' + formatMoney(sellingPrice) + ', VAT is just disclosed, never added on top.</small>';
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

@endsection
