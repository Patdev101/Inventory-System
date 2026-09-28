@extends('layouts.app')

@section('title', 'Create Purchase Order')

@section('content')

<style>
    .po-page {
        max-width: 1180px;
        margin: 0 auto;
    }

    .po-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 20px;
        margin-bottom: 24px;
    }

    .po-header h1 {
        margin: 0;
        font-size: 28px;
        color: #0f172a;
    }

    .po-header p {
        margin: 6px 0 0;
        color: #64748b;
    }

    .po-section {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(15, 23, 42, .06);
        margin-bottom: 20px;
        overflow: hidden;
    }

    .po-section-header {
        padding: 18px 20px;
        border-bottom: 1px solid #e2e8f0;
        background: #f8fafc;
    }

    .po-section-header h2 {
        margin: 0;
        font-size: 18px;
        color: #0f172a;
    }

    .po-section-header p {
        margin: 5px 0 0;
        color: #64748b;
        font-size: 13px;
    }

    .po-section-body {
        padding: 20px;
    }

    .po-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 18px;
    }

    .po-field {
        margin-bottom: 0;
    }

    .po-field label {
        margin-bottom: 7px;
        font-size: 13px;
    }

    .po-field small {
        display: block;
        margin-top: 5px;
        color: #94a3b8;
        font-size: 12px;
    }

    /*
    |--------------------------------------------------------------------------
    | Product Entry
    |--------------------------------------------------------------------------
    */

    .product-entry {
        display: grid;
        grid-template-columns: minmax(260px, 2fr) 180px 120px 150px auto;
        gap: 10px;
        align-items: end;
    }

    .product-entry-field {
        min-width: 0;
    }

    .product-entry select,
    .product-entry input {
        width: 100%;
    }

    .product-info {
        margin-top: 7px;
        padding: 8px 10px;
        background: #eff6ff;
        border: 1px solid #bfdbfe;
        border-radius: 7px;
        color: #1e40af;
        font-size: 12px;
        display: none;
    }

    .product-info strong {
        color: #1e3a8a;
    }

    .product-help {
        margin-top: 5px;
        color: #94a3b8;
        font-size: 12px;
    }

    .product-loading {
        margin-top: 7px;
        padding: 8px 10px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 7px;
        color: #64748b;
        font-size: 12px;
        display: none;
    }

    /*
    |--------------------------------------------------------------------------
    | Table
    |--------------------------------------------------------------------------
    */

    .order-table-wrapper {
        overflow-x: auto;
    }

    .order-table {
        min-width: 820px;
    }

    .order-table th {
        white-space: nowrap;
    }

    .order-table td {
        vertical-align: middle;
    }

    .product-cell-name {
        font-weight: 600;
        color: #1e293b;
    }

    .product-cell-sku {
        margin-top: 3px;
        color: #94a3b8;
        font-size: 12px;
    }

    .unit-detail {
        color: #475569;
    }

    .unit-conversion {
        margin-top: 3px;
        color: #94a3b8;
        font-size: 11px;
    }

    .qty-control {
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .qty-control input {
        width: 90px;
        text-align: center;
    }

    .line-total {
        font-weight: 700;
        color: #0f172a;
        white-space: nowrap;
    }

    .remove-line {
        color: #dc2626;
        background: #fee2e2;
        border: 1px solid #fecaca;
        padding: 7px 10px;
        border-radius: 6px;
        cursor: pointer;
        font-size: 13px;
    }

    .remove-line:hover {
        background: #fecaca;
    }

    .empty-orders {
        padding: 45px 20px;
        text-align: center;
        color: #64748b;
    }

    .empty-orders strong {
        display: block;
        color: #334155;
        margin-bottom: 5px;
    }

    /*
    |--------------------------------------------------------------------------
    | Summary
    |--------------------------------------------------------------------------
    */

    .po-summary {
        display: flex;
        justify-content: flex-end;
    }

    .summary-box {
        width: 340px;
        max-width: 100%;
    }

    .summary-row {
        display: flex;
        justify-content: space-between;
        gap: 20px;
        padding: 8px 0;
        color: #475569;
    }

    .summary-row.total {
        border-top: 2px solid #e2e8f0;
        margin-top: 8px;
        padding-top: 14px;
        font-size: 18px;
        font-weight: 700;
        color: #0f172a;
    }

    .po-footer {
        display: flex;
        justify-content: flex-end;
        align-items: center;
        gap: 10px;
        margin-top: 20px;
    }

    .item-count {
        color: #64748b;
        font-size: 13px;
    }

    .company-warning {
        margin-top: 10px;
        padding: 10px 12px;
        background: #fff7ed;
        border: 1px solid #fed7aa;
        color: #9a3412;
        border-radius: 7px;
        font-size: 13px;
        display: none;
    }

    .product-error {
        margin-top: 10px;
        padding: 10px 12px;
        background: #fef2f2;
        border: 1px solid #fecaca;
        color: #b91c1c;
        border-radius: 7px;
        font-size: 13px;
        display: none;
    }

    .product-select-disabled {
        background: #f8fafc;
        color: #94a3b8;
    }

    @media (max-width: 900px) {
        .product-entry {
            grid-template-columns: 1fr 1fr;
        }

        .product-entry-field.product-field {
            grid-column: 1 / -1;
        }
    }

    @media (max-width: 700px) {
        .po-grid {
            grid-template-columns: 1fr;
        }

        .po-header {
            flex-direction: column;
        }

        .product-entry {
            grid-template-columns: 1fr;
        }

        .product-entry-field.product-field {
            grid-column: auto;
        }

        .po-section-body {
            padding: 15px;
        }
    }
</style>

<div class="po-page">

    {{-- HEADER --}}
    <div class="po-header">
        <div>
            <h1>Create Purchase Order</h1>

            <p>
                Add products, quantities and supplier pricing to create a draft order.
            </p>
        </div>

        <a
            href="{{ route('purchase-orders.index') }}"
            class="btn btn-secondary"
        >
            Back to Purchase Orders
        </a>
    </div>


    {{-- ERRORS --}}
    @if ($errors->any())
        <div class="alert-error">
            <strong>Please fix the following:</strong>

            <ul style="margin: 10px 0 0; padding-left: 20px;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif


    <form
        action="{{ route('purchase-orders.store') }}"
        method="POST"
        id="purchase-order-form"
    >

        @csrf


        {{-- =========================================================
             PURCHASE ORDER DETAILS
        ========================================================== --}}

        <div class="po-section">

            <div class="po-section-header">
                <h2>Purchase Order Details</h2>

                <p>
                    Select the supplier and warehouse where this order will be received.
                </p>
            </div>

            <div class="po-section-body">

                <div class="po-grid">

                    {{-- SUPPLIER --}}
                    <div class="po-field">

                        <label for="supplier_id">
                            Supplier
                        </label>

                        <select
                            id="supplier_id"
                            name="supplier_id"
                            required
                        >
                            <option value="">
                                Select supplier
                            </option>

                            @foreach ($suppliers as $supplier)

                                <option
                                    value="{{ $supplier->id }}"
                                    data-company-id="{{ $supplier->company_id }}"
                                    @selected(old('supplier_id') == $supplier->id)
                                >
                                    {{ $supplier->name }}
                                </option>

                            @endforeach

                        </select>

                        <small>
                            Products will be limited to this supplier's company.
                        </small>

                    </div>


                    {{-- LOCATION --}}
                    <div class="po-field">

                        <label for="location_id">
                            Receiving Location
                        </label>

                        <select
                            id="location_id"
                            name="location_id"
                            required
                        >

                            <option value="">
                                Select location
                            </option>

                            @foreach ($locations as $location)

                                <option
                                    value="{{ $location->id }}"
                                    data-company-id="{{ $location->company_id }}"
                                    @selected(old('location_id') == $location->id)
                                >
                                    {{ $location->name }}

                                    @if ($location->company)
                                        — {{ $location->company->name }}
                                    @endif

                                </option>

                            @endforeach

                        </select>

                    </div>


                    {{-- EXPECTED DELIVERY --}}
                    <div class="po-field">

                        <label for="expected_delivery_date">
                            Expected Delivery Date
                        </label>

                        <input
                            type="date"
                            id="expected_delivery_date"
                            name="expected_delivery_date"
                            value="{{ old('expected_delivery_date') }}"
                        >

                    </div>


                    {{-- REFERENCE --}}
                    <div class="po-field">

                        <label for="reference">
                            Supplier Reference
                        </label>

                        <input
                            type="text"
                            id="reference"
                            name="reference"
                            maxlength="255"
                            value="{{ old('reference') }}"
                            placeholder="Optional reference"
                        >

                    </div>

                </div>

            </div>

        </div>


        {{-- =========================================================
             ADD PRODUCTS
        ========================================================== --}}

        <div class="po-section">

            <div class="po-section-header">

                <h2>
                    Add Products
                </h2>

                <p>
                    Select a product available to the selected supplier's company,
                    then choose its purchasing unit, quantity and price.
                </p>

            </div>

            <div class="po-section-body">

                <div class="product-entry">

                    {{-- PRODUCT --}}
                    <div class="product-entry-field product-field">

                        <label for="entry-product">
                            Product
                        </label>

                        <select
                            id="entry-product"
                            class="product-select-disabled"
                            disabled
                        >

                            <option value="">
                                Select supplier first
                            </option>

                        </select>

                        <div
                            id="product-loading"
                            class="product-loading"
                        >
                            Loading products...
                        </div>

                        <div
                            id="product-info"
                            class="product-info"
                        ></div>

                        <div
                            id="company-warning"
                            class="company-warning"
                        >
                            No active products are available for this supplier's company.
                        </div>

                        <div
                            id="product-error"
                            class="product-error"
                        ></div>

                    </div>


                    {{-- UNIT --}}
                    <div class="product-entry-field">

                        <label for="entry-unit">
                            Unit
                        </label>

                        <select
                            id="entry-unit"
                            disabled
                        >

                            <option value="">
                                Select product first
                            </option>

                        </select>

                    </div>


                    {{-- QUANTITY --}}
                    <div class="product-entry-field">

                        <label for="entry-quantity">
                            Quantity
                        </label>

                        <input
                            type="number"
                            id="entry-quantity"
                            min="0.0001"
                            step="0.0001"
                            value="1"
                        >

                    </div>


                    {{-- PRICE --}}
                    <div class="product-entry-field">

                        <label for="entry-price">
                            Unit Price
                        </label>

                        <input
                            type="number"
                            id="entry-price"
                            min="0"
                            step="0.0001"
                            placeholder="0.00"
                        >

                    </div>


                    {{-- ADD --}}
                    <div class="product-entry-field">

                        <button
                            type="button"
                            class="btn btn-primary"
                            id="add-product"
                            style="width: 100%;"
                            disabled
                        >
                            Add
                        </button>

                    </div>

                </div>

            </div>

        </div>


        {{-- =========================================================
             ORDER ITEMS
        ========================================================== --}}

        <div class="po-section">

            <div class="po-section-header">

                <div
                    style="
                        display: flex;
                        justify-content: space-between;
                        align-items: center;
                        gap: 15px;
                    "
                >

                    <div>

                        <h2>
                            Order Items
                        </h2>

                        <p>
                            Products currently included in this purchase order.
                        </p>

                    </div>

                    <span
                        id="item-count"
                        class="item-count"
                    >
                        0 items
                    </span>

                </div>

            </div>

            <div
                class="po-section-body"
                style="padding: 0;"
            >

                <div class="order-table-wrapper">

                    <table class="order-table">

                        <thead>

                            <tr>

                                <th>
                                    Product
                                </th>

                                <th>
                                    Unit
                                </th>

                                <th>
                                    Quantity
                                </th>

                                <th>
                                    Unit Price
                                </th>

                                <th>
                                    Total
                                </th>

                                <th>
                                    Action
                                </th>

                            </tr>

                        </thead>

                        <tbody id="items-body"></tbody>

                    </table>

                </div>


                <div
                    id="empty-orders"
                    class="empty-orders"
                >

                    <strong>
                        No products added yet
                    </strong>

                    Select a product above to start building this order.

                </div>

            </div>

        </div>


        {{-- =========================================================
             NOTES + SUMMARY
        ========================================================== --}}

        <div class="po-section">

            <div class="po-section-body">

                <div class="po-grid">

                    <div>

                        <label for="notes">
                            Notes
                        </label>

                        <textarea
                            id="notes"
                            name="notes"
                            placeholder="Add delivery instructions or other notes..."
                        >{{ old('notes') }}</textarea>

                    </div>


                    <div class="po-summary">

                        <div class="summary-box">

                            <div class="summary-row">

                                <span>
                                    Items
                                </span>

                                <strong id="summary-items">
                                    0
                                </strong>

                            </div>

                            <div class="summary-row">

                                <span>
                                    Total Quantity
                                </span>

                                <strong id="summary-quantity">
                                    0
                                </strong>

                            </div>

                            <div class="summary-row total">

                                <span>
                                    Order Total
                                </span>

                                <span>
                                    <span id="order-total">
                                        0.00
                                    </span>
                                </span>

                            </div>

                        </div>

                    </div>

                </div>


                <div class="po-footer">

                    <a
                        href="{{ route('purchase-orders.index') }}"
                        class="btn btn-secondary"
                    >
                        Cancel
                    </a>

                    <button
                        type="submit"
                        class="btn btn-primary"
                        id="save-draft"
                    >
                        Save Draft
                    </button>

                </div>

            </div>

        </div>

    </form>

</div>


<script>

function formatQty(value) {
    const num = Number(value);
    if (!isFinite(num)) {
        return String(value);
    }
    return parseFloat(num.toFixed(2)).toString();
}

document.addEventListener('DOMContentLoaded', function () {

    /*
    |--------------------------------------------------------------------------
    | Laravel Data
    |--------------------------------------------------------------------------
    */

    const suppliers = @json($suppliers);

    const oldItems = @json(old('items', []));


    /*
    |--------------------------------------------------------------------------
    | Routes
    |--------------------------------------------------------------------------
    */

    const supplierProductsUrlTemplate =
        @json(route('purchase-orders.supplier-products', ['supplier' => '__SUPPLIER__']));


    /*
    |--------------------------------------------------------------------------
    | Elements
    |--------------------------------------------------------------------------
    */

    const supplierSelect =
        document.getElementById('supplier_id');

    const locationSelect =
        document.getElementById('location_id');

    const productSelect =
        document.getElementById('entry-product');

    const unitSelect =
        document.getElementById('entry-unit');

    const quantityInput =
        document.getElementById('entry-quantity');

    const priceInput =
        document.getElementById('entry-price');

    const addButton =
        document.getElementById('add-product');

    const productInfo =
        document.getElementById('product-info');

    const productLoading =
        document.getElementById('product-loading');

    const companyWarning =
        document.getElementById('company-warning');

    const productError =
        document.getElementById('product-error');

    const itemsBody =
        document.getElementById('items-body');

    const emptyOrders =
        document.getElementById('empty-orders');

    const totalElement =
        document.getElementById('order-total');

    const itemCountElement =
        document.getElementById('item-count');

    const summaryItems =
        document.getElementById('summary-items');

    const summaryQuantity =
        document.getElementById('summary-quantity');


    /*
    |--------------------------------------------------------------------------
    | State
    |--------------------------------------------------------------------------
    */

    let products = [];

    let selectedProduct = null;

    let itemIndex = 0;

    let productsRequestId = 0;


    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    function findProduct(id) {

        return products.find(function (product) {

            return String(product.id) === String(id);

        }) || null;

    }


    function findSupplier(id) {

        return suppliers.find(function (supplier) {

            return String(supplier.id) === String(id);

        }) || null;

    }


    function getUnits(product) {

        if (
            !product ||
            !Array.isArray(product.product_units)
        ) {
            return [];
        }

        return product.product_units;
    }


    function getUnit(product, unitId) {

        return getUnits(product).find(function (unit) {

            return String(unit.id) === String(unitId);

        }) || null;

    }


    function escapeHtml(value) {

        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');

    }


    function showProductError(message) {

        productError.textContent = message;

        productError.style.display = 'block';

    }


    function hideProductError() {

        productError.textContent = '';

        productError.style.display = 'none';

    }


    /*
    |--------------------------------------------------------------------------
    | Supplier Company
    |--------------------------------------------------------------------------
    */

    function getSupplierCompanyId() {

        const supplier =
            findSupplier(supplierSelect.value);

        return supplier
            ? String(supplier.company_id)
            : null;

    }


    /*
    |--------------------------------------------------------------------------
    | Filter Receiving Location by Supplier's Company
    |--------------------------------------------------------------------------
    */

    function filterLocationsForSupplier(companyId) {

        let visibleCount = 0;

        Array.from(locationSelect.options).forEach(function (option) {

            if (!option.value) {
                return;
            }

            const matches =
                !companyId ||
                option.dataset.companyId === companyId;

            option.hidden = !matches;
            option.disabled = !matches;

            if (matches) {
                visibleCount++;
            }

        });

        const placeholder =
            locationSelect.options[0];

        if (placeholder) {

            placeholder.textContent = companyId && visibleCount === 0
                ? 'No locations for this supplier\'s company'
                : 'Select location';

        }

    }


    /*
    |--------------------------------------------------------------------------
    | Reset Product Entry
    |--------------------------------------------------------------------------
    */

    function resetProductEntry() {

        selectedProduct = null;

        productSelect.innerHTML =
            '<option value="">Select supplier first</option>';

        productSelect.disabled = true;

        productSelect.classList.add(
            'product-select-disabled'
        );

        unitSelect.innerHTML =
            '<option value="">Select product first</option>';

        unitSelect.disabled = true;

        quantityInput.value = '1';

        priceInput.value = '';

        productInfo.innerHTML = '';

        productInfo.style.display = 'none';

        productLoading.style.display = 'none';

        companyWarning.style.display = 'none';

        hideProductError();

        addButton.disabled = true;

    }


    /*
    |--------------------------------------------------------------------------
    | Populate Product Dropdown
    |--------------------------------------------------------------------------
    */

    function populateProductDropdown() {

        selectedProduct = null;

        productSelect.innerHTML =
            '<option value="">Select product...</option>';

        productSelect.disabled = true;

        productSelect.classList.add(
            'product-select-disabled'
        );

        unitSelect.innerHTML =
            '<option value="">Select product first</option>';

        unitSelect.disabled = true;

        priceInput.value = '';

        productInfo.innerHTML = '';

        productInfo.style.display = 'none';

        companyWarning.style.display = 'none';

        hideProductError();

        addButton.disabled = true;


        const companyId =
            getSupplierCompanyId();


        if (!companyId) {

            productSelect.innerHTML =
                '<option value="">Select supplier first</option>';

            return;

        }


        if (products.length === 0) {

            productSelect.innerHTML =
                '<option value="">No products available</option>';

            companyWarning.style.display =
                'block';

            return;

        }


        products.forEach(function (product) {

            const option =
                document.createElement('option');

            option.value =
                product.id;

            option.textContent =
                product.name +
                (
                    product.sku
                        ? ' — SKU: ' + product.sku
                        : ''
                );

            productSelect.appendChild(option);

        });


        productSelect.disabled = false;

        productSelect.classList.remove(
            'product-select-disabled'
        );

    }


    /*
    |--------------------------------------------------------------------------
    | Load Products For Supplier
    |--------------------------------------------------------------------------
    */

    async function loadSupplierProducts(
        supplierId,
        restoreItems = false
    ) {

        const requestId =
            ++productsRequestId;


        products = [];

        resetProductEntry();


        if (!supplierId) {

            return;

        }


        productSelect.innerHTML =
            '<option value="">Loading products...</option>';

        productSelect.disabled = true;

        productSelect.classList.add(
            'product-select-disabled'
        );

        productLoading.style.display =
            'block';


        const url =
            supplierProductsUrlTemplate.replace(
                '__SUPPLIER__',
                encodeURIComponent(supplierId)
            );


        try {

            const response =
                await fetch(
                    url,
                    {
                        method: 'GET',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        credentials: 'same-origin'
                    }
                );


            if (requestId !== productsRequestId) {
                return;
            }


            if (!response.ok) {

                throw new Error(
                    'Unable to load supplier products.'
                );

            }


            const data =
                await response.json();


            if (
                !data ||
                !Array.isArray(data.products)
            ) {

                throw new Error(
                    'The server returned an invalid product response.'
                );

            }


            products =
                data.products;


            /*
            |--------------------------------------------------------------------------
            | Defensive company filtering
            |--------------------------------------------------------------------------
            |
            | The server endpoint already filters products by supplier company.
            | This frontend filter is only an additional UI safeguard.
            |
            */

            const supplier =
                findSupplier(supplierId);


            if (supplier) {

                products =
                    products.filter(function (product) {

                        return (
                            product.is_active !== false &&
                            String(product.company_id) ===
                                String(supplier.company_id)
                        );

                    });

            }


            productLoading.style.display =
                'none';


            populateProductDropdown();


            /*
            |--------------------------------------------------------------------------
            | Restore old items after validation error
            |--------------------------------------------------------------------------
            */

            if (restoreItems) {

                restoreOldItems();

            }

        } catch (error) {

            if (requestId !== productsRequestId) {
                return;
            }


            console.error(
                'Purchase order product loading error:',
                error
            );


            productLoading.style.display =
                'none';


            productSelect.innerHTML =
                '<option value="">Unable to load products</option>';

            productSelect.disabled = true;

            productSelect.classList.add(
                'product-select-disabled'
            );


            showProductError(
                'Unable to load products for this supplier. Please refresh the page and try again.'
            );

        }

    }


    /*
    |--------------------------------------------------------------------------
    | Select Product
    |--------------------------------------------------------------------------
    */

    function selectProduct(product) {

        selectedProduct = product;

        unitSelect.innerHTML =
            '<option value="">Select unit</option>';


        const units =
            getUnits(product);


        units.forEach(function (productUnit) {

            const option =
                document.createElement('option');

            option.value =
                productUnit.id;


            const unitName =
                productUnit.unit_of_measure
                    ? productUnit.unit_of_measure.name
                    : 'Unit';


            option.textContent =
                unitName +
                (
                    productUnit.conversion_factor
                        ? ' (' +
                          formatQty(productUnit.conversion_factor) +
                          'x)'
                        : ''
                );


            if (productUnit.is_default) {
                option.selected = true;
            }


            unitSelect.appendChild(option);

        });


        unitSelect.disabled =
            units.length === 0;


        /*
        |--------------------------------------------------------------------------
        | Default Cost Price
        |--------------------------------------------------------------------------
        */

        if (
            product.cost_price !== null &&
            product.cost_price !== undefined
        ) {

            priceInput.value =
                formatQty(product.cost_price);

        } else {

            priceInput.value = '';

        }


        /*
        |--------------------------------------------------------------------------
        | Product Information
        |--------------------------------------------------------------------------
        */

        productInfo.innerHTML = `
            <strong>${escapeHtml(product.name)}</strong>
            ${
                product.sku
                    ? ' · SKU: ' +
                      escapeHtml(product.sku)
                    : ''
            }
        `;

        productInfo.style.display =
            'block';


        /*
        |--------------------------------------------------------------------------
        | Add Button
        |--------------------------------------------------------------------------
        */

        addButton.disabled =
            units.length === 0;

    }


    /*
    |--------------------------------------------------------------------------
    | Supplier Changed
    |--------------------------------------------------------------------------
    */

    supplierSelect.addEventListener(
        'change',
        function () {

            const supplier =
                findSupplier(this.value);


            /*
            |--------------------------------------------------------------------------
            | Supplier and Location must belong to same company
            |--------------------------------------------------------------------------
            */

            if (supplier) {

                const supplierCompanyId =
                    String(supplier.company_id);


                const currentLocation =
                    locationSelect.options[
                        locationSelect.selectedIndex
                    ];


                if (
                    currentLocation &&
                    currentLocation.value &&
                    String(
                        currentLocation.dataset.companyId
                    ) !== supplierCompanyId
                ) {

                    locationSelect.value = '';

                }


                filterLocationsForSupplier(supplierCompanyId);

            } else {

                filterLocationsForSupplier(null);

            }


            loadSupplierProducts(
                this.value,
                false
            );

        }
    );


    /*
    |--------------------------------------------------------------------------
    | Product Changed
    |--------------------------------------------------------------------------
    */

    productSelect.addEventListener(
        'change',
        function () {

            const product =
                findProduct(this.value);


            if (!product) {

                selectedProduct = null;

                unitSelect.innerHTML =
                    '<option value="">Select product first</option>';

                unitSelect.disabled = true;

                productInfo.style.display =
                    'none';

                priceInput.value = '';

                addButton.disabled = true;

                return;

            }


            selectProduct(product);

        }
    );


    /*
    |--------------------------------------------------------------------------
    | Location Changed
    |--------------------------------------------------------------------------
    */

    locationSelect.addEventListener(
        'change',
        function () {

            const supplier =
                findSupplier(
                    supplierSelect.value
                );


            if (!supplier || !this.value) {
                return;
            }


            const location =
                this.options[
                    this.selectedIndex
                ];


            if (
                String(location.dataset.companyId) !==
                String(supplier.company_id)
            ) {

                alert(
                    'The receiving location must belong to the same company as the selected supplier.'
                );

                this.value = '';

            }

        }
    );


    /*
    |--------------------------------------------------------------------------
    | Add Product
    |--------------------------------------------------------------------------
    */

    addButton.addEventListener(
        'click',
        function () {

            if (!selectedProduct) {

                alert(
                    'Please select a product.'
                );

                productSelect.focus();

                return;

            }


            if (!unitSelect.value) {

                alert(
                    'Please select a unit.'
                );

                unitSelect.focus();

                return;

            }


            const quantity =
                parseFloat(
                    quantityInput.value
                );


            const price =
                parseFloat(
                    priceInput.value
                );


            if (
                !quantity ||
                quantity <= 0
            ) {

                alert(
                    'Quantity must be greater than zero.'
                );

                quantityInput.focus();

                return;

            }


            if (
                isNaN(price) ||
                price < 0
            ) {

                alert(
                    'Unit price cannot be negative.'
                );

                priceInput.focus();

                return;

            }


            /*
            |--------------------------------------------------------------------------
            | Prevent duplicate products
            |--------------------------------------------------------------------------
            */

            const duplicate =
                Array.from(
                    itemsBody.querySelectorAll(
                        'input[name$="[product_id]"]'
                    )
                ).some(function (input) {

                    return String(input.value) ===
                        String(selectedProduct.id);

                });


            if (duplicate) {

                alert(
                    'This product has already been added to the order.'
                );

                return;

            }


            addOrderRow(
                selectedProduct,
                unitSelect.value,
                quantity,
                price
            );


            /*
            |--------------------------------------------------------------------------
            | Reset Product Entry
            |--------------------------------------------------------------------------
            */

            productSelect.value = '';

            selectedProduct = null;

            unitSelect.innerHTML =
                '<option value="">Select product first</option>';

            unitSelect.disabled = true;

            quantityInput.value = '1';

            priceInput.value = '';

            productInfo.innerHTML = '';

            productInfo.style.display = 'none';

            addButton.disabled = true;

            productSelect.focus();

        }
    );


    /*
    |--------------------------------------------------------------------------
    | Add Order Row
    |--------------------------------------------------------------------------
    */

    function addOrderRow(
        product,
        productUnitId,
        quantity,
        price
    ) {

        const index =
            itemIndex++;


        const productUnit =
            getUnit(
                product,
                productUnitId
            );


        /*
        |--------------------------------------------------------------------------
        | Do not restore an invalid product unit
        |--------------------------------------------------------------------------
        */

        if (!productUnit) {

            return;

        }


        const unitName =
            productUnit &&
            productUnit.unit_of_measure
                ? productUnit.unit_of_measure.name
                : 'Unit';


        const conversion =
            productUnit &&
            productUnit.conversion_factor
                ? productUnit.conversion_factor
                : null;


        const row =
            document.createElement('tr');


        row.dataset.index =
            index;


        row.innerHTML = `

            <td>

                <div class="product-cell-name">
                    ${escapeHtml(product.name)}
                </div>

                ${
                    product.sku
                        ? `
                            <div class="product-cell-sku">
                                SKU: ${escapeHtml(product.sku)}
                            </div>
                        `
                        : ''
                }

                <input
                    type="hidden"
                    name="items[${index}][product_id]"
                    value="${product.id}"
                >

            </td>


            <td>

                <div class="unit-detail">
                    ${escapeHtml(unitName)}
                </div>

                ${
                    conversion
                        ? `
                            <div class="unit-conversion">
                                ${escapeHtml(conversion)}x base unit
                            </div>
                        `
                        : ''
                }

                <input
                    type="hidden"
                    name="items[${index}][product_unit_id]"
                    value="${productUnitId}"
                >

            </td>


            <td>

                <div class="qty-control">

                    <input
                        type="number"
                        name="items[${index}][quantity_ordered]"
                        value="${quantity}"
                        min="0.0001"
                        step="0.0001"
                        class="line-quantity"
                        required
                    >

                </div>

            </td>


            <td>

                <input
                    type="number"
                    name="items[${index}][unit_price]"
                    value="${formatQty(price)}"
                    min="0"
                    step="0.0001"
                    class="line-price"
                    required
                >

            </td>


            <td>

                <span class="line-total">
                    0.00
                </span>

            </td>


            <td>

                <button
                    type="button"
                    class="remove-line"
                >
                    Remove
                </button>

            </td>

        `;


        itemsBody.appendChild(row);


        row.querySelector('.line-quantity')
            .addEventListener(
                'input',
                updateTotals
            );


        row.querySelector('.line-price')
            .addEventListener(
                'input',
                updateTotals
            );


        row.querySelector('.remove-line')
            .addEventListener(
                'click',
                function () {

                    row.remove();

                    updateTotals();

                }
            );


        updateTotals();

    }


    /*
    |--------------------------------------------------------------------------
    | Totals
    |--------------------------------------------------------------------------
    */

    function updateTotals() {

        const rows =
            Array.from(
                itemsBody.querySelectorAll('tr')
            );


        let total = 0;

        let quantityTotal = 0;


        rows.forEach(function (row) {

            const quantity =
                parseFloat(
                    row.querySelector(
                        '.line-quantity'
                    ).value
                ) || 0;


            const price =
                parseFloat(
                    row.querySelector(
                        '.line-price'
                    ).value
                ) || 0;


            const lineTotal =
                quantity * price;


            total += lineTotal;

            quantityTotal += quantity;


            row.querySelector(
                '.line-total'
            ).textContent =
                lineTotal.toFixed(2);

        });


        totalElement.textContent =
            total.toFixed(2);


        summaryItems.textContent =
            rows.length;


        summaryQuantity.textContent =
            formatQty(quantityTotal);


        itemCountElement.textContent =
            rows.length === 1
                ? '1 item'
                : rows.length + ' items';


        emptyOrders.style.display =
            rows.length === 0
                ? 'block'
                : 'none';

    }


    /*
    |--------------------------------------------------------------------------
    | Restore Old Items After Validation Error
    |--------------------------------------------------------------------------
    */

    function restoreOldItems() {

        if (!Array.isArray(oldItems) || !oldItems.length) {
            return;
        }


        const supplierCompanyId =
            getSupplierCompanyId();


        if (!supplierCompanyId) {
            return;
        }


        oldItems.forEach(function (item) {

            const product =
                findProduct(
                    item.product_id
                );


            if (!product) {
                return;
            }


            /*
            |--------------------------------------------------------------------------
            | Only restore products belonging to the selected supplier company.
            |--------------------------------------------------------------------------
            */

            if (
                String(product.company_id) !==
                supplierCompanyId
            ) {

                return;

            }


            const productUnit =
                getUnit(
                    product,
                    item.product_unit_id
                );


            if (!productUnit) {
                return;

            }


            addOrderRow(
                product,
                item.product_unit_id,
                parseFloat(
                    item.quantity_ordered
                ) || 0,
                parseFloat(
                    item.unit_price
                ) || 0
            );

        });

    }


    /*
    |--------------------------------------------------------------------------
    | Initial State
    |--------------------------------------------------------------------------
    */

    const initialSupplierId =
        supplierSelect.value;


    if (initialSupplierId) {

        /*
        |--------------------------------------------------------------------------
        | Validation failure:
        |
        | The supplier is already restored by old().
        | Load its products first, then restore old PO items.
        |--------------------------------------------------------------------------
        */

        const initialSupplier =
            findSupplier(initialSupplierId);

        filterLocationsForSupplier(
            initialSupplier
                ? String(initialSupplier.company_id)
                : null
        );

        loadSupplierProducts(
            initialSupplierId,
            true
        );

    } else {

        filterLocationsForSupplier(null);

        resetProductEntry();

    }


    updateTotals();

});

</script>

@endsection
