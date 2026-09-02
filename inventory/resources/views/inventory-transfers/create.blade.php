@extends('layouts.app')

@section('title', 'Transfer Inventory')

@section('content')

<div class="page-header">
    <div>
        <h1>Transfer Inventory</h1>

        <p style="margin: 6px 0 0; color: #64748b;">
            Move stock from one location to another.
            The source and destination must contain the same product.
        </p>
    </div>

    <a
        href="{{ route('inventory-transfers.index') }}"
        class="btn btn-secondary"
    >
        Transfer History
    </a>
</div>


@if ($errors->any())

    <div class="alert-error">

        <strong>Please fix the following:</strong>

        <ul style="margin: 10px 0 0;">

            @foreach ($errors->all() as $error)

                <li>{{ $error }}</li>

            @endforeach

        </ul>

    </div>

@endif


<div class="card">

    <form
        action="{{ route('inventory-transfers.store') }}"
        method="POST"
        id="transfer-form"
    >

        @csrf


        {{-- =========================
             SOURCE INVENTORY
        ========================== --}}

        <div class="form-group">

            <label for="source_inventory_id">
                Source Inventory
            </label>

            <select
                id="source_inventory_id"
                name="source_inventory_id"
                required
            >

                <option value="">
                    -- Select Source Inventory --
                </option>

                @foreach ($inventories as $inventory)

                    @if ((float) $inventory->base_quantity > 0)

                        <option
                            value="{{ $inventory->id }}"
                            data-product-id="{{ $inventory->product_id }}"
                            data-location-id="{{ $inventory->location_id }}"
                            data-stock="{{ $inventory->base_quantity }}"
                            {{ old('source_inventory_id') == $inventory->id ? 'selected' : '' }}
                        >

                            {{ $inventory->product->name }}
                            -
                            {{ $inventory->location->name }}
                            -
                            {{ number_format((float) $inventory->base_quantity, 4) }}
                            base units

                        </option>

                    @endif

                @endforeach

            </select>

        </div>


        {{-- =========================
             DESTINATION INVENTORY
        ========================== --}}

        <div class="form-group">

            <label for="destination_inventory_id">
                Destination Inventory
            </label>

            <select
                id="destination_inventory_id"
                name="destination_inventory_id"
                required
                disabled
            >

                <option value="">
                    -- Select Source First --
                </option>

            </select>

            <small
                id="destination-help"
                style="
                    display: block;
                    margin-top: 6px;
                    color: #64748b;
                "
            >
                Select a source inventory first.
            </small>

        </div>


        {{-- =========================
             PRODUCT
        ========================== --}}

        <div class="form-group">

            <label for="product_display">
                Product
            </label>

            <input
                type="text"
                id="product_display"
                readonly
                placeholder="Select Source First"
                style="background: #f8fafc;"
            >

        </div>


        {{-- =========================
             TRANSFER UNIT
        ========================== --}}

        <div class="form-group">

            <label for="product_unit_id">
                Transfer Unit
            </label>

            <select
                id="product_unit_id"
                name="product_unit_id"
                required
                disabled
            >

                <option value="">
                    -- Select Source First --
                </option>

            </select>

            <small
                id="unit-help"
                style="
                    display: block;
                    margin-top: 6px;
                    color: #64748b;
                "
            ></small>

        </div>


        {{-- =========================
             CURRENT SOURCE STOCK
        ========================== --}}

        <div
            id="source-stock-container"
            class="form-group"
            style="display: none;"
        >

            <label>
                Current Source Stock
            </label>

            <div
                id="source-stock"
                style="
                    background: #f8fafc;
                    border: 1px solid #e2e8f0;
                    border-radius: 6px;
                    padding: 12px;
                    font-weight: bold;
                "
            ></div>

        </div>


        {{-- =========================
             QUANTITY
        ========================== --}}

        <div class="form-group">

            <label for="quantity">
                Quantity to Transfer
            </label>

            <input
                type="number"
                id="quantity"
                name="quantity"
                value="{{ old('quantity') }}"
                min="0.0001"
                step="0.0001"
                required
                disabled
            >

            <small
                style="
                    display: block;
                    margin-top: 6px;
                    color: #64748b;
                "
            >
                Enter the amount of stock to move.
            </small>

        </div>


        {{-- =========================
             TRANSFER PREVIEW
        ========================== --}}

        <div
            id="preview-container"
            class="form-group"
            style="
                display: none;
                background: #eff6ff;
                border: 1px solid #bfdbfe;
                border-radius: 8px;
                padding: 18px;
            "
        >

            <label
                style="
                    display: block;
                    color: #1e40af;
                    font-weight: bold;
                    margin-bottom: 12px;
                "
            >
                Transfer Preview
            </label>


            <div
                id="preview-movement"
                style="
                    font-size: 16px;
                    font-weight: bold;
                    margin-bottom: 7px;
                "
            ></div>


            <div
                id="preview-conversion"
                style="
                    color: #475569;
                    margin-bottom: 7px;
                "
            ></div>


            <div
                id="preview-base"
                style="
                    color: #1e3a8a;
                    font-weight: bold;
                    margin-bottom: 14px;
                "
            ></div>


            <div
                style="
                    border-top: 1px solid #bfdbfe;
                    padding-top: 12px;
                "
            >

                <div
                    id="preview-source"
                    style="
                        margin-bottom: 7px;
                        font-weight: bold;
                    "
                ></div>


                <div
                    id="preview-destination"
                    style="
                        font-weight: bold;
                    "
                ></div>

            </div>

        </div>


        {{-- =========================
             REFERENCE
        ========================== --}}

        <div class="form-group">

            <label for="reference">
                Reference
            </label>

            <input
                type="text"
                id="reference"
                name="reference"
                value="{{ old('reference') }}"
                maxlength="255"
                placeholder="e.g. Warehouse transfer"
            >

        </div>


        {{-- =========================
             NOTES
        ========================== --}}

        <div class="form-group">

            <label for="notes">
                Notes
            </label>

            <textarea
                id="notes"
                name="notes"
                placeholder="Optional transfer notes..."
            >{{ old('notes') }}</textarea>

        </div>


        {{-- =========================
             BUTTONS
        ========================== --}}

        <div
            style="
                display: flex;
                gap: 10px;
                flex-wrap: wrap;
                margin-top: 25px;
            "
        >

            <button
                type="submit"
                class="btn btn-primary"
                id="submit-button"
                disabled
            >
                Transfer Stock
            </button>

            <a
                href="{{ route('inventories.index') }}"
                class="btn btn-secondary"
            >
                Cancel
            </a>

        </div>

    </form>

</div>


<script>

    /*
    |--------------------------------------------------------------------------
    | Inventory data supplied by Laravel
    |--------------------------------------------------------------------------
    */

    const inventories = @json($inventories);


    /*
    |--------------------------------------------------------------------------
    | DOM elements
    |--------------------------------------------------------------------------
    */

    const sourceSelect =
        document.getElementById('source_inventory_id');

    const destinationSelect =
        document.getElementById('destination_inventory_id');

    const productDisplay =
        document.getElementById('product_display');

    const unitSelect =
        document.getElementById('product_unit_id');

    const quantityInput =
        document.getElementById('quantity');

    const sourceStockContainer =
        document.getElementById('source-stock-container');

    const sourceStock =
        document.getElementById('source-stock');

    const destinationHelp =
        document.getElementById('destination-help');

    const unitHelp =
        document.getElementById('unit-help');

    const previewContainer =
        document.getElementById('preview-container');

    const previewMovement =
        document.getElementById('preview-movement');

    const previewConversion =
        document.getElementById('preview-conversion');

    const previewBase =
        document.getElementById('preview-base');

    const previewSource =
        document.getElementById('preview-source');

    const previewDestination =
        document.getElementById('preview-destination');

    const submitButton =
        document.getElementById('submit-button');


    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    function getInventoryById(id)
    {
        return inventories.find(function (inventory) {

            return String(inventory.id) === String(id);

        });
    }


    function getUnitOfMeasure(productUnit)
    {
        return productUnit?.unit_of_measure
            ?? productUnit?.unitOfMeasure
            ?? null;
    }


    function getProductUnits(product)
    {
        return product?.product_units
            ?? product?.productUnits
            ?? [];
    }


    function getUnitName(productUnit)
    {
        const unit =
            getUnitOfMeasure(productUnit);

        return unit?.name ?? 'Unit';
    }


    function getUnitCode(productUnit)
    {
        const unit =
            getUnitOfMeasure(productUnit);

        return unit?.code ?? '';
    }


    function formatNumber(value)
    {
        return Number(value || 0)
            .toFixed(4);
    }


    /*
    |--------------------------------------------------------------------------
    | Reset destination
    |--------------------------------------------------------------------------
    */

    function resetDestination()
    {
        destinationSelect.innerHTML = '';

        const option =
            document.createElement('option');

        option.value = '';

        option.textContent =
            '-- Select Source First --';

        destinationSelect.appendChild(option);

        destinationSelect.disabled = true;

        destinationHelp.textContent =
            'Select a source inventory first.';
    }


    /*
    |--------------------------------------------------------------------------
    | Reset units
    |--------------------------------------------------------------------------
    */

    function resetUnits()
    {
        unitSelect.innerHTML = '';

        const option =
            document.createElement('option');

        option.value = '';

        option.textContent =
            '-- Select Source First --';

        unitSelect.appendChild(option);

        unitSelect.disabled = true;

        unitHelp.textContent = '';
    }


    /*
    |--------------------------------------------------------------------------
    | Reset preview
    |--------------------------------------------------------------------------
    */

    function resetPreview()
    {
        previewContainer.style.display = 'none';

        previewMovement.textContent = '';

        previewConversion.textContent = '';

        previewBase.textContent = '';

        previewSource.textContent = '';

        previewDestination.textContent = '';

        previewSource.style.color = '';

        previewDestination.style.color = '';
    }


    /*
    |--------------------------------------------------------------------------
    | Reset fields after changing source
    |--------------------------------------------------------------------------
    */

    function resetFormAfterSourceChange()
    {
        quantityInput.value = '';

        quantityInput.disabled = true;

        submitButton.disabled = true;

        sourceStockContainer.style.display = 'none';

        productDisplay.value = '';

        resetDestination();

        resetUnits();

        resetPreview();
    }


    /*
    |--------------------------------------------------------------------------
    | Update destination options
    |--------------------------------------------------------------------------
    */

    function updateDestinationOptions()
    {
        const sourceId =
            sourceSelect.value;

        if (!sourceId) {

            resetDestination();

            return;
        }


        const source =
            getInventoryById(sourceId);


        if (!source) {

            resetDestination();

            return;
        }


        destinationSelect.innerHTML = '';


        const placeholder =
            document.createElement('option');

        placeholder.value = '';

        placeholder.textContent =
            '-- Select Destination Inventory --';

        destinationSelect.appendChild(
            placeholder
        );


        let destinationCount = 0;


        inventories.forEach(function (inventory) {

            /*
             * Cannot transfer to itself.
             */
            if (
                String(inventory.id) ===
                String(source.id)
            ) {
                return;
            }


            /*
             * Destination must contain
             * the same product.
             */
            if (
                String(inventory.product_id) !==
                String(source.product_id)
            ) {
                return;
            }


            const option =
                document.createElement('option');

            option.value =
                inventory.id;


            option.textContent =
                inventory.product.name
                + ' - '
                + inventory.location.name
                + ' - '
                + formatNumber(
                    inventory.base_quantity
                )
                + ' base units';


            destinationSelect.appendChild(
                option
            );

            destinationCount++;

        });


        if (destinationCount === 0) {

            destinationSelect.innerHTML = '';


            const option =
                document.createElement('option');

            option.value = '';

            option.textContent =
                '-- No Matching Destination --';


            destinationSelect.appendChild(
                option
            );


            destinationSelect.disabled = true;


            destinationHelp.textContent =
                'No other inventory location contains this product.';


            return;
        }


        destinationSelect.disabled = false;


        destinationHelp.textContent =
            'Only locations containing the same product are shown.';
    }


    /*
    |--------------------------------------------------------------------------
    | Update product and units
    |--------------------------------------------------------------------------
    */

    function updateProductAndUnits()
    {
        const sourceId =
            sourceSelect.value;


        resetUnits();


        if (!sourceId) {

            return;
        }


        const source =
            getInventoryById(sourceId);


        if (!source) {

            return;
        }


        /*
         * Product.
         */
        productDisplay.value =
            source.product?.name ?? 'Unknown Product';


        /*
         * Source stock.
         */
        const stock =
            Number(source.base_quantity || 0);


        sourceStockContainer.style.display =
            'block';


        sourceStock.textContent =
            formatNumber(stock)
            + ' base units';


        /*
         * Product units.
         */
        const productUnits =
            getProductUnits(source.product);


        let unitCount = 0;


        productUnits.forEach(function (productUnit) {

            const unit =
                getUnitOfMeasure(productUnit);


            if (!unit) {

                return;
            }


            const conversion =
                Number(
                    productUnit.conversion_factor
                );


            if (
                !Number.isFinite(conversion) ||
                conversion <= 0
            ) {

                return;
            }


            const option =
                document.createElement('option');


            option.value =
                productUnit.id;


            option.dataset.conversion =
                conversion;


            option.dataset.unitName =
                unit.name ?? 'Unit';


            option.dataset.unitCode =
                unit.code ?? '';


            option.textContent =
                (unit.name ?? 'Unit')
                + ' ('
                + (unit.code ?? '')
                + ') - 1 unit = '
                + formatNumber(conversion)
                + ' base units';


            unitSelect.appendChild(option);


            unitCount++;

        });


        if (unitCount === 0) {

            unitSelect.innerHTML = '';


            const option =
                document.createElement('option');


            option.value = '';

            option.textContent =
                '-- No Units Configured --';


            unitSelect.appendChild(option);


            unitSelect.disabled = true;


            unitHelp.textContent =
                'No valid units are configured for this product.';


            return;
        }


        /*
         * Add placeholder.
         */
        const placeholder =
            document.createElement('option');


        placeholder.value = '';


        placeholder.textContent =
            '-- Select Unit --';


        unitSelect.insertBefore(
            placeholder,
            unitSelect.firstChild
        );


        unitSelect.disabled = false;


        unitHelp.textContent =
            'Choose the unit in which you want to transfer stock.';


        quantityInput.disabled = true;


        resetPreview();
    }


    /*
    |--------------------------------------------------------------------------
    | Calculate transfer preview
    |--------------------------------------------------------------------------
    */

    function calculatePreview()
    {
        const sourceId =
            sourceSelect.value;


        const destinationId =
            destinationSelect.value;


        const unitOption =
            unitSelect.options[
                unitSelect.selectedIndex
            ];


        const quantity =
            Number(quantityInput.value);


        /*
         * Required fields.
         */
        if (
            !sourceId ||
            !destinationId ||
            !unitOption ||
            !unitOption.value ||
            !unitOption.dataset.conversion ||
            !Number.isFinite(quantity) ||
            quantity <= 0
        ) {

            resetPreview();

            submitButton.disabled = true;

            return;
        }


        const source =
            getInventoryById(sourceId);


        const destination =
            getInventoryById(destinationId);


        if (!source || !destination) {

            resetPreview();

            submitButton.disabled = true;

            return;
        }


        /*
         * Conversion.
         */
        const conversion =
            Number(
                unitOption.dataset.conversion
            );


        if (
            !Number.isFinite(conversion) ||
            conversion <= 0
        ) {

            resetPreview();

            submitButton.disabled = true;

            return;
        }


        /*
         * Base movement.
         */
        const baseQuantity =
            quantity * conversion;


        /*
         * Current source stock.
         */
        const currentSourceStock =
            Number(
                source.base_quantity || 0
            );


        /*
         * Current destination stock.
         */
        const currentDestinationStock =
            Number(
                destination.base_quantity || 0
            );


        /*
         * New balances.
         */
        const newSourceStock =
            currentSourceStock - baseQuantity;


        const newDestinationStock =
            currentDestinationStock + baseQuantity;


        /*
         * Unit display.
         */
        const unitName =
            unitOption.dataset.unitName
            || 'Unit';


        const unitCode =
            unitOption.dataset.unitCode
            || '';


        const unitDisplay =
            unitCode
                ? unitName + ' (' + unitCode + ')'
                : unitName;


        /*
         * Show preview.
         */
        previewContainer.style.display =
            'block';


        /*
         * Movement.
         */
        previewMovement.textContent =
            quantity.toFixed(4)
            + ' '
            + unitCode
            + ' will be transferred';


        /*
         * Conversion.
         */
        previewConversion.textContent =
            'Conversion: '
            + quantity.toFixed(4)
            + ' '
            + unitCode
            + ' × '
            + conversion.toFixed(4)
            + ' base units';


        /*
         * Base movement.
         */
        previewBase.textContent =
            'Movement in base units: '
            + baseQuantity.toFixed(4)
            + ' base units';


        /*
         * Insufficient stock.
         */
        if (newSourceStock < -0.0000001) {

            previewSource.textContent =
                'Source stock after transfer: '
                + formatNumber(
                    Math.max(0, newSourceStock)
                )
                + ' base units — INSUFFICIENT STOCK';


            previewSource.style.color =
                '#dc2626';


            previewDestination.textContent =
                'Destination stock after transfer: '
                + formatNumber(
                    newDestinationStock
                )
                + ' base units';


            previewDestination.style.color =
                '#64748b';


            submitButton.disabled = true;


            return;
        }


        /*
         * Valid source balance.
         */
        previewSource.textContent =
            'Source stock after transfer: '
            + formatNumber(
                Math.max(0, newSourceStock)
            )
            + ' base units';


        previewSource.style.color =
            '#166534';


        /*
         * Destination balance.
         */
        previewDestination.textContent =
            'Destination stock after transfer: '
            + formatNumber(
                newDestinationStock
            )
            + ' base units';


        previewDestination.style.color =
            '#166534';


        submitButton.disabled = false;
    }


    /*
    |--------------------------------------------------------------------------
    | Source change
    |--------------------------------------------------------------------------
    */

    sourceSelect.addEventListener(
        'change',
        function () {

            resetFormAfterSourceChange();

            updateProductAndUnits();

            updateDestinationOptions();

        }
    );


    /*
    |--------------------------------------------------------------------------
    | Destination change
    |--------------------------------------------------------------------------
    */

    destinationSelect.addEventListener(
        'change',
        function () {

            calculatePreview();

        }
    );


    /*
    |--------------------------------------------------------------------------
    | Unit change
    |--------------------------------------------------------------------------
    */

    unitSelect.addEventListener(
        'change',
        function () {

            if (unitSelect.value) {

                quantityInput.disabled = false;

            } else {

                quantityInput.disabled = true;

                quantityInput.value = '';

            }


            calculatePreview();

        }
    );


    /*
    |--------------------------------------------------------------------------
    | Quantity change
    |--------------------------------------------------------------------------
    */

    quantityInput.addEventListener(
        'input',
        calculatePreview
    );


    /*
    |--------------------------------------------------------------------------
    | Prevent invalid submit
    |--------------------------------------------------------------------------
    */

    document
        .getElementById('transfer-form')
        .addEventListener(
            'submit',
            function (event) {

                if (
                    submitButton.disabled
                ) {

                    event.preventDefault();

                    return;
                }


                /*
                 * Prevent accidental double submission.
                 */
                submitButton.disabled = true;

                submitButton.textContent =
                    'Transferring...';

            }
        );


    /*
    |--------------------------------------------------------------------------
    | Initialize old Laravel values
    |--------------------------------------------------------------------------
    */

    const oldDestination =
        @json(old('destination_inventory_id'));


    const oldUnit =
        @json(old('product_unit_id'));


    if (sourceSelect.value) {

        updateProductAndUnits();

        updateDestinationOptions();


        if (oldDestination) {

            destinationSelect.value =
                oldDestination;

        }


        if (oldUnit) {

            unitSelect.value =
                oldUnit;

            quantityInput.disabled = false;

        }


        calculatePreview();

    }

</script>

@endsection
