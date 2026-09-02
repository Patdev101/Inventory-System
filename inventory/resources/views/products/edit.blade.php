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

            <label for="reorder_point">
                Reorder Point
            </label>

            <input
                type="number"
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
                                    type="number"
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
                                        {{ number_format((float) $conversion, 4) }}
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


        <div class="info-box">

            <strong>
                How conversion works
            </strong>

            <br><br>

            If the base unit is Piece:

            <br><br>

            1 Piece = 1 base unit

            <br>

            1 Pack = 6 base units

            <br>

            1 Box = 12 base units

            <br><br>

            Therefore 2 Boxes = 24 Pieces internally.

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
        max-width: 1000px;
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

    const form =
        document.getElementById('product-form');

    const baseUnit =
        document.getElementById('base_unit_id');

    let unitIndex =
        container.querySelectorAll('.unit-row').length;


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

});
</script>

@endsection
