@extends('layouts.app')

@section('title', 'Adjust Inventory')

@section('content')

<style>
    .inventory-current {
        background: #f9fafb;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 25px;
    }

    .current-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 20px;
    }

    .current-label {
        color: #6b7280;
        font-size: 13px;
        margin-bottom: 5px;
    }

    .current-value {
        font-size: 17px;
        font-weight: bold;
        color: #111827;
    }

    .form-table {
        width: 100%;
        border-collapse: collapse;
    }

    .form-table th,
    .form-table td {
        padding: 15px;
        border-bottom: 1px solid #e5e7eb;
        text-align: left;
        vertical-align: top;
    }

    .form-table th {
        width: 220px;
        color: #374151;
    }

    .form-control {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        background: white;
        font-size: 14px;
        box-sizing: border-box;
    }

    .form-control:focus {
        outline: none;
        border-color: #2563eb;
        box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.1);
    }

    .help-text {
        margin-top: 6px;
        color: #6b7280;
        font-size: 12px;
    }

    .preview-box {
        padding: 15px;
        background: #f9fafb;
        border-radius: 6px;
        border: 1px solid #e5e7eb;
    }

    .preview-row {
        margin-bottom: 8px;
    }

    .preview-row:last-child {
        margin-bottom: 0;
    }

    .adjustment-in {
        color: #166534;
        font-weight: bold;
    }

    .adjustment-out {
        color: #991b1b;
        font-weight: bold;
    }

    .error-box {
        background: #fee2e2;
        color: #991b1b;
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 20px;
    }

    .error-box ul {
        margin-bottom: 0;
    }

    .movement-buttons {
        display: flex;
        gap: 10px;
    }

    .movement-button {
        flex: 1;
        padding: 12px 15px;
        border: 2px solid #d1d5db;
        border-radius: 7px;
        background: white;
        cursor: pointer;
        font-weight: bold;
        font-size: 14px;
        transition: all 0.15s ease;
    }

    .movement-button:hover {
        border-color: #9ca3af;
    }

    .movement-button.in.active {
        background: #dcfce7;
        border-color: #16a34a;
        color: #166534;
    }

    .movement-button.out.active {
        background: #fee2e2;
        border-color: #dc2626;
        color: #991b1b;
    }

    .movement-label {
        display: block;
        font-size: 16px;
        margin-bottom: 3px;
    }

    .movement-description {
        display: block;
        font-size: 12px;
        font-weight: normal;
        color: #6b7280;
    }

    .movement-button.in.active .movement-description {
        color: #166534;
    }

    .movement-button.out.active .movement-description {
        color: #991b1b;
    }

    @media (max-width: 800px) {
        .current-grid {
            grid-template-columns: 1fr;
        }

        .form-table th {
            width: auto;
        }

        .movement-buttons {
            flex-direction: column;
        }
    }
</style>


<div class="page-header">

    <div>

        <h1>Adjust Inventory</h1>

        <p style="margin: 5px 0 0; color: #6b7280;">
            Add stock or remove stock from this inventory.
        </p>

    </div>

    <div class="actions">

        <a
            href="{{ route('inventories.show', $inventory) }}"
            class="btn btn-secondary"
        >
            View Inventory
        </a>

        <a
            href="{{ route('inventories.index') }}"
            class="btn btn-primary"
        >
            Back to Inventory
        </a>

    </div>

</div>


@if ($errors->any())

    <div class="error-box">

        <strong>Please correct the following:</strong>

        <ul>

            @foreach ($errors->all() as $error)

                <li>{{ $error }}</li>

            @endforeach

        </ul>

    </div>

@endif


{{-- CURRENT STOCK --}}

<div class="inventory-current">

    <h2 style="margin-top: 0;">
        Current Stock
    </h2>

    <div class="current-grid">

        <div>

            <div class="current-label">
                Product
            </div>

            <div class="current-value">

                {{ $inventory->product ? $inventory->product->name : '-' }}

                @if ($inventory->product && $inventory->product->code)

                    ({{ $inventory->product->code }})

                @endif

            </div>

        </div>


        <div>

            <div class="current-label">
                Location
            </div>

            <div class="current-value">

                {{ $inventory->location ? $inventory->location->name : '-' }}

                @if ($inventory->location && $inventory->location->code)

                    ({{ $inventory->location->code }})

                @endif

            </div>

        </div>


        <div>

            <div class="current-label">
                Current Stock
            </div>

            <div class="current-value">

                {{ number_format((float) $inventory->quantity, 4) }}

                @if (
                    $inventory->productUnit &&
                    $inventory->productUnit->unitOfMeasure
                )

                    {{ $inventory->productUnit->unitOfMeasure->code }}

                @endif

            </div>

            <div class="help-text">

                {{ number_format((float) $inventory->base_quantity, 4) }}

                base units

            </div>

        </div>

    </div>

</div>


<form
    action="{{ route('inventories.update', $inventory) }}"
    method="POST"
>

    @csrf

    @method('PUT')


    <div class="table-wrapper">

        <table class="form-table">

            <tbody>


                {{-- PRODUCT --}}

                <tr>

                    <th>
                        Product
                    </th>

                    <td>

                        <select
                            name="product_id"
                            id="product_id"
                            class="form-control"
                            required
                        >

                            <option value="">
                                Select product
                            </option>

                            @foreach ($products as $product)

                                <option
                                    value="{{ $product->id }}"
                                    @if (
                                        old(
                                            'product_id',
                                            $inventory->product_id
                                        ) == $product->id
                                    )
                                        selected
                                    @endif
                                >

                                    {{ $product->name }}

                                    @if ($product->code)

                                        ({{ $product->code }})

                                    @endif

                                </option>

                            @endforeach

                        </select>

                    </td>

                </tr>


                {{-- LOCATION --}}

                <tr>

                    <th>
                        Location
                    </th>

                    <td>

                        <select
                            name="location_id"
                            id="location_id"
                            class="form-control"
                            required
                        >

                            <option value="">
                                Select location
                            </option>

                            @foreach ($locations as $location)

                                <option
                                    value="{{ $location->id }}"
                                    @if (
                                        old(
                                            'location_id',
                                            $inventory->location_id
                                        ) == $location->id
                                    )
                                        selected
                                    @endif
                                >

                                    {{ $location->name }}

                                    @if ($location->code)

                                        ({{ $location->code }})

                                    @endif

                                    @if ($location->company)

                                        - {{ $location->company->name }}

                                    @endif

                                </option>

                            @endforeach

                        </select>

                    </td>

                </tr>


                {{-- MOVEMENT TYPE --}}

                <tr>

                    <th>
                        Stock Movement
                    </th>

                    <td>

                        <div class="movement-buttons">

                            <button
                                type="button"
                                id="movement-in"
                                class="movement-button in"
                            >

                                <span class="movement-label">
                                    + IN
                                </span>

                                <span class="movement-description">
                                    Add stock
                                </span>

                            </button>


                            <button
                                type="button"
                                id="movement-out"
                                class="movement-button out"
                            >

                                <span class="movement-label">
                                    − OUT
                                </span>

                                <span class="movement-description">
                                    Remove stock
                                </span>

                            </button>

                        </div>


                        <input
                            type="hidden"
                            name="movement_type"
                            id="movement_type"
                            value="{{ old('movement_type', 'in') }}"
                        >


                        <div
                            id="movement-help"
                            class="help-text"
                        >
                            Stock will be added to the current inventory.
                        </div>

                    </td>

                </tr>


                {{-- UNIT --}}

                <tr>

                    <th>
                        Unit
                    </th>

                    <td>

                        <select
                            name="product_unit_id"
                            id="product_unit_id"
                            class="form-control"
                            required
                        >

                            <option value="">
                                Select unit
                            </option>

                            @foreach ($products as $product)

                                @foreach ($product->productUnits as $productUnit)

                                    <option
                                        value="{{ $productUnit->id }}"
                                        data-product-id="{{ $product->id }}"
                                        data-conversion-factor="{{ $productUnit->conversion_factor }}"

                                        @if (
                                            old(
                                                'product_unit_id',
                                                $inventory->product_unit_id
                                            ) == $productUnit->id
                                        )
                                            selected
                                        @endif
                                    >

                                        @if ($productUnit->unitOfMeasure)

                                            {{ $productUnit->unitOfMeasure->name }}

                                            ({{ $productUnit->unitOfMeasure->code }})

                                        @else

                                            Unit #{{ $productUnit->id }}

                                        @endif

                                    </option>

                                @endforeach

                            @endforeach

                        </select>


                        <div
                            id="unit-help"
                            class="help-text"
                        >
                            Select a unit for this product.
                        </div>

                    </td>

                </tr>


                {{-- MOVEMENT QUANTITY --}}

                <tr>

                    <th>
                        Quantity
                    </th>

                    <td>

                        <input
                            type="number"
                            name="quantity"
                            id="quantity"
                            class="form-control"
                            value="{{ old('quantity') }}"
                            min="0.0001"
                            step="0.0001"
                            required
                        >

                        <div class="help-text">

                            Enter the amount of stock you want to
                            <strong id="quantity-action">add</strong>.

                        </div>

                    </td>

                </tr>


                {{-- PREVIEW --}}

                <tr>

                    <th>
                        Stock Preview
                    </th>

                    <td>

                        <div class="preview-box">

                            <div class="preview-row">

                                <strong>
                                    Movement:
                                </strong>

                                <span id="movement-preview">
                                    -
                                </span>

                            </div>


                            <div class="preview-row">

                                <strong>
                                    Movement in base units:
                                </strong>

                                <span id="base-movement-preview">
                                    -
                                </span>

                            </div>


                            <div class="preview-row">

                                <strong>
                                    New Stock:
                                </strong>

                                <span id="new-stock-preview">
                                    -
                                </span>

                            </div>


                            <div
                                id="preview-message"
                                style="margin-top: 10px;"
                            ></div>

                        </div>

                    </td>

                </tr>

            </tbody>

        </table>

    </div>


    <div
        style="
            margin-top: 25px;
            padding: 15px;
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 8px;
            color: #1e40af;
        "
    >

        <strong>How this works:</strong>

        <div style="margin-top: 8px;">

            Choose <strong>IN</strong> when receiving or adding stock.

            Choose <strong>OUT</strong> when selling, consuming, transferring,
            or removing stock.

            The quantity you enter is the amount being moved,
            <strong>not the new total stock.</strong>

        </div>

    </div>


    <div
        style="
            margin-top: 25px;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        "
    >

        <button
            type="submit"
            class="btn btn-primary"
        >
            Save Stock Movement
        </button>

        <a
            href="{{ route('inventories.show', $inventory) }}"
            class="btn btn-secondary"
        >
            Cancel
        </a>

    </div>

</form>


<script>

document.addEventListener('DOMContentLoaded', function () {

    const productSelect =
        document.getElementById('product_id');

    const unitSelect =
        document.getElementById('product_unit_id');

    const quantityInput =
        document.getElementById('quantity');

    const movementType =
        document.getElementById('movement_type');

    const movementIn =
        document.getElementById('movement-in');

    const movementOut =
        document.getElementById('movement-out');

    const movementHelp =
        document.getElementById('movement-help');

    const quantityAction =
        document.getElementById('quantity-action');

    const movementPreview =
        document.getElementById('movement-preview');

    const baseMovementPreview =
        document.getElementById('base-movement-preview');

    const newStockPreview =
        document.getElementById('new-stock-preview');

    const previewMessage =
        document.getElementById('preview-message');

    const unitHelp =
        document.getElementById('unit-help');


    /*
     * Current inventory base quantity.
     */
    const currentBaseQuantity =
        {{ (float) $inventory->base_quantity }};


    /*
     * Save all available unit options.
     */
    const allUnitOptions = [];


    for (
        let i = 0;
        i < unitSelect.options.length;
        i++
    ) {

        const original =
            unitSelect.options[i];

        if (!original.dataset.productId) {
            continue;
        }

        allUnitOptions.push({

            value:
                original.value,

            productId:
                original.dataset.productId,

            conversionFactor:
                original.dataset.conversionFactor,

            text:
                original.textContent.trim()

        });

    }


    /*
     * Get selected conversion factor.
     */
    function getConversionFactor() {

        const selectedOption =
            unitSelect.options[
                unitSelect.selectedIndex
            ];


        if (
            !selectedOption ||
            !selectedOption.value
        ) {

            return 0;

        }


        return Number(
            selectedOption.dataset.conversionFactor
        ) || 0;

    }


    /*
     * Update movement buttons.
     */
    function updateMovementButtons() {

        const type =
            movementType.value;


        movementIn.classList.toggle(
            'active',
            type === 'in'
        );

        movementOut.classList.toggle(
            'active',
            type === 'out'
        );


        if (type === 'in') {

            movementHelp.textContent =
                'Stock will be added to the current inventory.';

            quantityAction.textContent =
                'add';

            return;

        }


        movementHelp.textContent =
            'Stock will be removed from the current inventory.';

        quantityAction.textContent =
            'remove';

    }


    /*
     * Select IN.
     */
    movementIn.addEventListener(
        'click',
        function () {

            movementType.value =
                'in';

            updateMovementButtons();

            updatePreview();

        }
    );


    /*
     * Select OUT.
     */
    movementOut.addEventListener(
        'click',
        function () {

            movementType.value =
                'out';

            updateMovementButtons();

            updatePreview();

        }
    );


    /*
     * Unit help text.
     */
    function updateUnitHelp() {

        const factor =
            getConversionFactor();


        if (factor <= 0) {

            unitHelp.textContent =
                'Select a valid unit.';

            return;

        }


        unitHelp.textContent =
            '1 selected unit = ' +
            factor +
            ' base units.';

    }


    /*
     * Calculate stock preview.
     */
    function updatePreview() {

        const quantity =
            Number(quantityInput.value) || 0;


        const factor =
            getConversionFactor();


        const type =
            movementType.value;


        if (
            quantity <= 0 ||
            factor <= 0
        ) {

            movementPreview.textContent =
                '-';

            baseMovementPreview.textContent =
                '-';

            newStockPreview.textContent =
                '-';

            previewMessage.innerHTML =
                '';

            return;

        }


        const movementBaseQuantity =
            quantity * factor;


        let newBaseQuantity;


        if (type === 'in') {

            newBaseQuantity =
                currentBaseQuantity
                + movementBaseQuantity;

            movementPreview.innerHTML =
                '<span class="adjustment-in">' +
                '+' +
                quantity.toFixed(4) +
                ' units (IN)' +
                '</span>';

        } else {

            newBaseQuantity =
                currentBaseQuantity
                - movementBaseQuantity;

            movementPreview.innerHTML =
                '<span class="adjustment-out">' +
                '-' +
                quantity.toFixed(4) +
                ' units (OUT)' +
                '</span>';

        }


        baseMovementPreview.textContent =
            movementBaseQuantity.toFixed(4) +
            ' base units';


        if (newBaseQuantity < 0) {

            newStockPreview.innerHTML =
                '<span class="adjustment-out">' +
                'Insufficient stock' +
                '</span>';

            previewMessage.innerHTML =
                '<span style="color: #991b1b; font-weight: bold;">' +
                'You cannot remove more stock than is currently available.' +
                '</span>';

            return;

        }


        newStockPreview.textContent =
            newBaseQuantity.toFixed(4) +
            ' base units';


        if (type === 'in') {

            previewMessage.innerHTML =
                '<span class="adjustment-in">' +
                'Stock will increase by ' +
                movementBaseQuantity.toFixed(4) +
                ' base units.' +
                '</span>';

        } else {

            previewMessage.innerHTML =
                '<span class="adjustment-out">' +
                'Stock will decrease by ' +
                movementBaseQuantity.toFixed(4) +
                ' base units.' +
                '</span>';

        }

    }


    /*
     * Populate units according to product.
     */
    function populateUnits() {

        const productId =
            productSelect.value;


        const currentUnitId =
            unitSelect.value;


        unitSelect.innerHTML =
            '';


        const defaultOption =
            document.createElement('option');

        defaultOption.value =
            '';

        defaultOption.textContent =
            'Select unit';

        unitSelect.appendChild(
            defaultOption
        );


        let unitFound = false;


        allUnitOptions.forEach(function (unit) {

            if (
                String(unit.productId) !==
                String(productId)
            ) {

                return;

            }


            const option =
                document.createElement('option');


            option.value =
                unit.value;


            option.dataset.productId =
                unit.productId;


            option.dataset.conversionFactor =
                unit.conversionFactor;


            option.textContent =
                unit.text;


            if (
                String(unit.value) ===
                String(currentUnitId)
            ) {

                option.selected =
                    true;

                unitFound = true;

            }


            unitSelect.appendChild(
                option
            );

        });


        /*
         * If the current unit does not
         * belong to the selected product,
         * select the first available unit.
         */
        if (
            !unitFound &&
            unitSelect.options.length > 1
        ) {

            unitSelect.selectedIndex =
                1;

        }


        updateUnitHelp();

        updatePreview();

    }


    /*
     * Product changed.
     */
    productSelect.addEventListener(
        'change',
        function () {

            unitSelect.innerHTML =
                '';


            const defaultOption =
                document.createElement('option');

            defaultOption.value =
                '';

            defaultOption.textContent =
                'Select unit';

            unitSelect.appendChild(
                defaultOption
            );


            populateUnits();

        }
    );


    /*
     * Unit changed.
     */
    unitSelect.addEventListener(
        'change',
        function () {

            updateUnitHelp();

            updatePreview();

        }
    );


    /*
     * Quantity changed.
     */
    quantityInput.addEventListener(
        'input',
        function () {

            updatePreview();

        }
    );


    /*
     * Initial setup.
     */
    updateMovementButtons();

    populateUnits();

    updatePreview();

});

</script>

@endsection
