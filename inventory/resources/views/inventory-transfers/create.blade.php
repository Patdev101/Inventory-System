@extends('layouts.app')

@section('title', 'Transfer Inventory')

@section('content')

<div class="page-header">
    <div>
        <h1>Transfer Inventory</h1>
        <p style="margin: 6px 0 0; color: #64748b;">
            Select items from your checklist below, choose your transfer units, and specify quantities to move.
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
    <div
        class="alert-error"
        style="background: #ffeeee; border: 1px solid #f5c6cb; padding: 12px; border-radius: 6px; margin-bottom: 20px; color: #721c24;"
    >
        <strong>Please fix the following:</strong>

        <ul style="margin: 10px 0 0; padding-left: 20px;">
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
             DESTINATION LOCATION
        ========================== --}}
        <div class="form-group" style="margin-bottom: 24px;">
            <label
                for="destination_location_id"
                style="font-weight: bold; display: block; margin-bottom: 6px;"
            >
                Destination Location <span style="color: red;">*</span>
            </label>

            <select
                id="destination_location_id"
                name="destination_location_id"
                required
                style="width: 100%; padding: 8px; border: 1px solid #cbd5e1; border-radius: 6px;"
            >
                <option value="">-- Select Destination Location --</option>

                @foreach ($locations as $location)
                    <option
                        value="{{ $location->id }}"
                        {{ old('destination_location_id') == $location->id ? 'selected' : '' }}
                    >
                        {{ $location->name }}
                        {{ $location->code ? '(' . $location->code . ')' : '' }}

                        @if($location->company)
                            - {{ $location->company->name }}
                        @endif
                    </option>
                @endforeach
            </select>

            <small
                id="destination-helper"
                style="display: block; margin-top: 6px; color: #64748b;"
            >
                Select a destination location to unlock transfer options.
                Items in the same location will be disabled automatically.
            </small>
        </div>

        {{-- =========================
             RECEIVER
        ========================== --}}
        <div
            class="form-group"
            style="margin-bottom: 24px; display: grid; grid-template-columns: 1fr 1fr; gap: 16px;"
        >
            <div>
                <label
                    for="receiver_id"
                    style="font-weight: bold; display: block; margin-bottom: 6px;"
                >
                    Receiver <span style="color: red;">*</span>
                </label>

                <select
                    id="receiver_id"
                    name="receiver_id"
                    required
                    style="width: 100%; padding: 8px; border: 1px solid #cbd5e1; border-radius: 6px;"
                >
                    <option value="">-- Select Receiver --</option>

                    @foreach ($receivers as $receiver)
                        <option
                            value="{{ $receiver->id }}"
                            data-role="{{ $receiver->role }}"
                            {{ old('receiver_id') == $receiver->id ? 'selected' : '' }}
                        >
                            {{ $receiver->name }}
                            ({{ ucfirst($receiver->role) }})
                        </option>
                    @endforeach
                </select>

                <small
                    style="display: block; margin-top: 6px; color: #64748b;"
                >
                    This person will inspect and confirm the transferred stock
                    before it's added to the destination.
                </small>
            </div>

            <div>
                <label
                    for="receiver_role"
                    style="font-weight: bold; display: block; margin-bottom: 6px;"
                >
                    Receiver Role <span style="color: red;">*</span>
                </label>

                <select
                    id="receiver_role"
                    name="receiver_role"
                    required
                    style="width: 100%; padding: 8px; border: 1px solid #cbd5e1; border-radius: 6px;"
                >
                    <option value="">-- Select Role --</option>

                    <option
                        value="admin"
                        {{ old('receiver_role') == 'admin' ? 'selected' : '' }}
                    >
                        Admin
                    </option>

                    <option
                        value="manager"
                        {{ old('receiver_role') == 'manager' ? 'selected' : '' }}
                    >
                        Manager
                    </option>

                    <option
                        value="staff"
                        {{ old('receiver_role') == 'staff' ? 'selected' : '' }}
                    >
                        Staff
                    </option>
                </select>

                <small
                    style="display: block; margin-top: 6px; color: #64748b;"
                >
                    Auto-fills based on the selected receiver.
                </small>
            </div>
        </div>

        <hr
            style="border: 0; border-top: 1px solid #e2e8f0; margin: 24px 0;"
        >

        {{-- =========================
             AVAILABLE INVENTORY
        ========================== --}}
        <h3 style="margin: 0 0 12px 0; font-size: 18px;">
            Available Inventory Checklist
        </h3>

        <p
            style="margin: 0 0 16px 0; color: #64748b; font-size: 13px;"
        >
            Check the boxes for the products you want to transfer,
            configure their units, and enter quantities.
        </p>

        {{-- =========================
             PAGE INFO
        ========================== --}}
        <div
            style="
                display: flex;
                justify-content: space-between;
                align-items: center;
                gap: 12px;
                flex-wrap: wrap;
                margin-bottom: 12px;
                padding: 10px 12px;
                background: #f8fafc;
                border: 1px solid #e2e8f0;
                border-radius: 6px;
            "
        >
            <div
                id="checklist-page-info"
                style="font-size: 13px; color: #475569;"
            >
                Showing 0-0 of 0 items
            </div>

            <div
                id="checklist-selection-info"
                style="font-size: 13px; color: #059669; font-weight: 600;"
            >
                0 selected
            </div>
        </div>

        {{-- =========================
             CHECKLIST TABLE
        ========================== --}}
        <div style="overflow-x: auto; margin-bottom: 12px;">
            <table
                style="width: 100%; border-collapse: collapse; text-align: left;"
            >
                <thead>
                    <tr
                        style="background: #f8fafc; border-bottom: 2px solid #e2e8f0;"
                    >
                        <th
                            style="padding: 10px; width: 5%; text-align: center;"
                        >
                            <input
                                type="checkbox"
                                id="select-all-checkbox"
                                title="Select all items on this page"
                                disabled
                            >
                        </th>

                        <th style="padding: 10px; width: 35%;">
                            Product & Source Location
                        </th>

                        <th style="padding: 10px; width: 25%;">
                            Transfer Unit
                        </th>

                        <th style="padding: 10px; width: 35%;">
                            Quantity & Stock Info
                        </th>
                    </tr>
                </thead>

                <tbody id="checklist-body">

                    @foreach ($inventories as $inventory)

                        @if ((float) $inventory->base_quantity > 0)

                            @php
                                $productUnits =
                                    $inventory->product->product_units
                                    ?? $inventory->product->productUnits
                                    ?? [];
                            @endphp

                            <tr
                                class="checklist-row"
                                data-inventory-id="{{ $inventory->id }}"
                                data-location-id="{{ $inventory->location_id }}"
                                style="border-bottom: 1px solid #e2e8f0;"
                            >
                                {{-- CHECKBOX --}}
                                <td
                                    style="padding: 12px; text-align: center; vertical-align: top;"
                                >
                                    <input
                                        type="checkbox"
                                        class="item-checkbox"
                                        value="{{ $inventory->id }}"
                                        disabled
                                    >
                                </td>

                                {{-- PRODUCT --}}
                                <td
                                    style="padding: 12px; vertical-align: top;"
                                >
                                    <div
                                        style="font-weight: 600; color: #1e293b;"
                                        class="product-name-label"
                                    >
                                        {{ $inventory->product->name }}
                                    </div>

                                    <div
                                        style="font-size: 12px; color: #64748b; margin-top: 2px;"
                                    >
                                        Location:
                                        <strong>
                                            {{ $inventory->location->name }}
                                        </strong>
                                    </div>

                                    <div
                                        style="font-size: 12px; color: #059669; margin-top: 2px;"
                                    >
                                        Available:
                                        {{ number_format((float) $inventory->base_quantity, 4) }}
                                        base units
                                    </div>

                                    <input
                                        type="hidden"
                                        class="source-inventory-id-input"
                                        disabled
                                        value="{{ $inventory->id }}"
                                    >
                                </td>

                                {{-- UNIT --}}
                                <td
                                    style="padding: 12px; vertical-align: top;"
                                >
                                    <select
                                        class="unit-select"
                                        disabled
                                        style="width: 100%; padding: 8px; border: 1px solid #cbd5e1; border-radius: 6px; background: #fff;"
                                    >
                                        <option value="">
                                            -- Select Unit --
                                        </option>

                                        @foreach ($productUnits as $pu)

                                            @php
                                                $unit =
                                                    $pu->unit_of_measure
                                                    ?? $pu->unitOfMeasure;
                                            @endphp

                                            @if ($unit)

                                                <option
                                                    value="{{ $pu->id }}"
                                                    data-conversion="{{ $pu->conversion_factor }}"
                                                    data-unit-code="{{ $unit->code }}"
                                                >
                                                    {{ $unit->name }}
                                                    ({{ $unit->code }})
                                                    -
                                                    1 =
                                                    {{ number_format((float) $pu->conversion_factor, 4) }}
                                                    base
                                                </option>

                                            @endif

                                        @endforeach
                                    </select>

                                    <small
                                        class="unit-error"
                                        style="display: block; margin-top: 4px; color: #dc2626; font-size: 11px;"
                                    ></small>
                                </td>

                                {{-- QUANTITY --}}
                                <td
                                    style="padding: 12px; vertical-align: top;"
                                >
                                    <input
                                        type="number"
                                        class="quantity-input"
                                        min="0.0001"
                                        step="0.0001"
                                        disabled
                                        placeholder="Enter quantity"
                                        style="width: 100%; padding: 8px; border: 1px solid #cbd5e1; border-radius: 6px; background: #fff;"
                                    >

                                    <div
                                        class="row-preview"
                                        style="display: none; margin-top: 6px; font-size: 11px; color: #475569; background: #f1f5f9; padding: 6px; border-radius: 4px;"
                                    ></div>
                                </td>
                            </tr>

                        @endif

                    @endforeach

                </tbody>
            </table>
        </div>

        {{-- =========================
             PAGINATION
        ========================== --}}
        <div
            id="checklist-pagination"
            style="
                display: flex;
                justify-content: center;
                align-items: center;
                gap: 6px;
                flex-wrap: wrap;
                margin-bottom: 24px;
            "
        >
            <button
                type="button"
                id="checklist-prev"
                class="btn btn-secondary"
                disabled
            >
                ← Previous
            </button>

            <div
                id="checklist-page-buttons"
                style="display: flex; gap: 4px; flex-wrap: wrap; justify-content: center;"
            ></div>

            <button
                type="button"
                id="checklist-next"
                class="btn btn-secondary"
                disabled
            >
                Next →
            </button>
        </div>

        <hr
            style="border: 0; border-top: 1px solid #e2e8f0; margin: 24px 0;"
        >

        {{-- =========================
             REFERENCE
        ========================== --}}
        <div
            class="form-group"
            style="margin-bottom: 16px;"
        >
            <label
                for="reference"
                style="font-weight: bold; display: block; margin-bottom: 6px;"
            >
                Reference
            </label>

            <input
                type="text"
                id="reference"
                name="reference"
                value="{{ old('reference') }}"
                maxlength="255"
                placeholder="e.g. Multi-product checklist transfer batch"
                style="width: 100%; padding: 8px; border: 1px solid #cbd5e1; border-radius: 6px;"
            >
        </div>

        {{-- =========================
             NOTES
        ========================== --}}
        <div
            class="form-group"
            style="margin-bottom: 20px;"
        >
            <label
                for="notes"
                style="font-weight: bold; display: block; margin-bottom: 6px;"
            >
                Notes
            </label>

            <textarea
                id="notes"
                name="notes"
                rows="3"
                placeholder="Optional transfer notes..."
                style="width: 100%; padding: 8px; border: 1px solid #cbd5e1; border-radius: 6px;"
            >{{ old('notes') }}</textarea>
        </div>

        {{-- =========================
             BUTTONS
        ========================== --}}
        <div
            style="display: flex; gap: 10px; flex-wrap: wrap; margin-top: 25px;"
        >
            <button
                type="submit"
                class="btn btn-primary"
                id="submit-button"
                disabled
            >
                Transfer Selected Stock
            </button>

            <a
                href="{{ route('inventory-transfers.index') }}"
                class="btn btn-secondary"
            >
                Cancel
            </a>
        </div>

    </form>
</div>

{{-- =========================
     SAFE DATA CONTAINER
========================== --}}
<div
    id="transfer-data-container"
    data-inventories='@json($inventories)'
    style="display: none;"
></div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const dataContainer =
        document.getElementById('transfer-data-container');

    const inventories =
        JSON.parse(
            dataContainer.dataset.inventories || '[]'
        );

    const destinationSelect =
        document.getElementById('destination_location_id');

    const submitButton =
        document.getElementById('submit-button');

    const selectAllCheckbox =
        document.getElementById('select-all-checkbox');

    const checklistRows =
        Array.from(
            document.querySelectorAll('.checklist-row')
        );

    const receiverSelect =
        document.getElementById('receiver_id');

    const receiverRoleSelect =
        document.getElementById('receiver_role');

    const pageInfo =
        document.getElementById('checklist-page-info');

    const selectionInfo =
        document.getElementById('checklist-selection-info');

    const prevButton =
        document.getElementById('checklist-prev');

    const nextButton =
        document.getElementById('checklist-next');

    const pageButtons =
        document.getElementById('checklist-page-buttons');


    /* =========================
       PAGINATION SETTINGS
    ========================== */

    const ITEMS_PER_PAGE = 5;

    let currentPage = 1;


    /* =========================
       HELPERS
    ========================== */

    function formatNumber(value) {
        return Number(value || 0).toFixed(4);
    }


    function getInventoryById(id) {
        return inventories.find(
            inv => String(inv.id) === String(id)
        );
    }


    function getTotalPages() {
        return Math.max(
            1,
            Math.ceil(checklistRows.length / ITEMS_PER_PAGE)
        );
    }


    function getSelectedCount() {
        return checklistRows.filter(row => {
            const checkbox =
                row.querySelector('.item-checkbox');

            return checkbox.checked;
        }).length;
    }


    /* =========================
       UPDATE PAGE DISPLAY
    ========================== */

    function renderPagination() {

        const totalItems =
            checklistRows.length;

        const totalPages =
            getTotalPages();

        if (currentPage > totalPages) {
            currentPage = totalPages;
        }

        const startIndex =
            (currentPage - 1) * ITEMS_PER_PAGE;

        const endIndex =
            Math.min(
                startIndex + ITEMS_PER_PAGE,
                totalItems
            );


        /*
         * Show only 5 rows at a time.
         */
        checklistRows.forEach((row, index) => {

            if (
                index >= startIndex &&
                index < endIndex
            ) {
                row.style.display = 'table-row';
            } else {
                row.style.display = 'none';
            }

        });


        /*
         * Page information.
         */
        if (totalItems === 0) {

            pageInfo.textContent =
                'No inventory items available';

        } else {

            pageInfo.textContent =
                `Showing ${startIndex + 1}-${endIndex} of ${totalItems} items`;
        }


        /*
         * Selected count.
         *
         * This counts selections across ALL pages,
         * not just the current page.
         */
        selectionInfo.textContent =
            `${getSelectedCount()} selected`;


        /*
         * Previous / Next buttons.
         */
        prevButton.disabled =
            currentPage <= 1;

        nextButton.disabled =
            currentPage >= totalPages;


        /*
         * Page number buttons.
         */
        pageButtons.innerHTML = '';


        for (
            let page = 1;
            page <= totalPages;
            page++
        ) {

            const button =
                document.createElement('button');

            button.type = 'button';

            button.textContent = page;

            button.style.minWidth = '36px';
            button.style.padding = '7px 10px';
            button.style.border = '1px solid #cbd5e1';
            button.style.borderRadius = '6px';
            button.style.cursor = 'pointer';


            if (page === currentPage) {

                button.style.background =
                    '#2563eb';

                button.style.color =
                    '#fff';

                button.style.borderColor =
                    '#2563eb';

            } else {

                button.style.background =
                    '#fff';

                button.style.color =
                    '#334155';
            }


            button.addEventListener(
                'click',
                function () {

                    currentPage = page;

                    renderPagination();
                    updateSelectAllState();
                }
            );


            pageButtons.appendChild(button);
        }


        updateSelectAllState();
    }


    /* =========================
       UPDATE SELECT ALL
    ========================== */

    function updateSelectAllState() {

        const visibleRows =
            checklistRows.filter(
                row => row.style.display !== 'none'
            );


        const selectableRows =
            visibleRows.filter(
                row =>
                    !row.dataset.disabledSameLocation
            );


        if (
            !destinationSelect.value ||
            selectableRows.length === 0
        ) {

            selectAllCheckbox.disabled = true;
            selectAllCheckbox.checked = false;
            selectAllCheckbox.indeterminate = false;

            return;
        }


        selectAllCheckbox.disabled = false;


        const checkedCount =
            selectableRows.filter(row => {

                const checkbox =
                    row.querySelector('.item-checkbox');

                return checkbox.checked;

            }).length;


        if (
            checkedCount === selectableRows.length
        ) {

            selectAllCheckbox.checked = true;
            selectAllCheckbox.indeterminate = false;

        } else if (checkedCount > 0) {

            selectAllCheckbox.checked = false;
            selectAllCheckbox.indeterminate = true;

        } else {

            selectAllCheckbox.checked = false;
            selectAllCheckbox.indeterminate = false;
        }
    }


    /* =========================
       UPDATE INPUT INDEXING
    ========================== */

    function updateRowIndexing() {

        let activeIndex = 0;

        checklistRows.forEach(row => {

            const checkbox =
                row.querySelector('.item-checkbox');

            const sourceInput =
                row.querySelector('.source-inventory-id-input');

            const unitSelect =
                row.querySelector('.unit-select');

            const qtyInput =
                row.querySelector('.quantity-input');


            if (
                checkbox.checked &&
                !row.dataset.disabledSameLocation
            ) {

                sourceInput.disabled = false;

                sourceInput.name =
                    `items[${activeIndex}][source_inventory_id]`;

                unitSelect.name =
                    `items[${activeIndex}][product_unit_id]`;

                qtyInput.name =
                    `items[${activeIndex}][quantity]`;

                activeIndex++;

            } else {

                sourceInput.disabled = true;
                sourceInput.removeAttribute('name');

                unitSelect.removeAttribute('name');

                qtyInput.removeAttribute('name');
            }
        });
    }


    /* =========================
       DESTINATION CHANGE
    ========================== */

    function handleDestinationChange() {

        const destinationId =
            destinationSelect.value;


        if (!destinationId) {

            selectAllCheckbox.disabled = true;
            selectAllCheckbox.checked = false;
            selectAllCheckbox.indeterminate = false;

            checklistRows.forEach(row => {

                const checkbox =
                    row.querySelector('.item-checkbox');

                const unitSelect =
                    row.querySelector('.unit-select');

                const qtyInput =
                    row.querySelector('.quantity-input');

                const previewDiv =
                    row.querySelector('.row-preview');


                checkbox.disabled = true;
                checkbox.checked = false;

                unitSelect.disabled = true;
                unitSelect.value = '';

                qtyInput.disabled = true;
                qtyInput.value = '';

                row.style.opacity = '1';
                row.style.background = 'transparent';

                previewDiv.style.display = 'none';

                delete row.dataset.disabledSameLocation;
            });

            updateRowIndexing();

            evaluateFormState();

            renderPagination();

            return;
        }


        checklistRows.forEach(row => {

            const rowLocationId =
                row.dataset.locationId;

            const checkbox =
                row.querySelector('.item-checkbox');

            const unitSelect =
                row.querySelector('.unit-select');

            const qtyInput =
                row.querySelector('.quantity-input');

            const previewDiv =
                row.querySelector('.row-preview');


            if (
                String(rowLocationId) ===
                String(destinationId)
            ) {

                row.dataset.disabledSameLocation = 'true';

                checkbox.disabled = true;
                checkbox.checked = false;

                unitSelect.disabled = true;
                unitSelect.value = '';

                qtyInput.disabled = true;
                qtyInput.value = '';

                row.style.opacity = '0.45';
                row.style.background = '#f1f5f9';

                previewDiv.style.display = 'block';
                previewDiv.style.color = '#dc2626';

                previewDiv.textContent =
                    'Unavailable: Source is same as destination location.';

            } else {

                delete row.dataset.disabledSameLocation;

                checkbox.disabled = false;

                row.style.opacity = '1';

                if (!checkbox.checked) {

                    row.style.background = 'transparent';
                    previewDiv.style.display = 'none';

                } else {

                    row.style.background = '#f0fdf4';
                }
            }
        });


        updateRowIndexing();

        evaluateFormState();

        renderPagination();
    }


    /* =========================
       EVALUATE ROW
    ========================== */

    function evaluateRow(row) {

        if (row.dataset.disabledSameLocation) {
            return false;
        }


        const checkbox =
            row.querySelector('.item-checkbox');

        const unitSelect =
            row.querySelector('.unit-select');

        const qtyInput =
            row.querySelector('.quantity-input');

        const previewDiv =
            row.querySelector('.row-preview');

        const inventoryId =
            row.dataset.inventoryId;

        const inventory =
            getInventoryById(inventoryId);


        if (!checkbox.checked) {

            unitSelect.disabled = true;
            unitSelect.value = '';

            qtyInput.disabled = true;
            qtyInput.value = '';

            previewDiv.style.display = 'none';

            return false;
        }


        unitSelect.disabled = false;


        if (!unitSelect.value) {

            qtyInput.disabled = true;
            qtyInput.value = '';

            previewDiv.style.display = 'none';

            return false;
        }


        qtyInput.disabled = false;


        const quantity =
            Number(qtyInput.value);


        if (
            !Number.isFinite(quantity) ||
            quantity <= 0
        ) {

            previewDiv.style.display = 'none';

            return false;
        }


        const selectedOption =
            unitSelect.options[
                unitSelect.selectedIndex
            ];


        const conversion =
            Number(
                selectedOption.dataset.conversion || 1
            );


        const unitCode =
            selectedOption.dataset.unitCode || '';


        const requestedBase =
            quantity * conversion;


        previewDiv.style.display = 'block';


        if (
            requestedBase >
            Number(inventory.base_quantity)
        ) {

            previewDiv.style.color = '#dc2626';

            previewDiv.textContent =
                `Exceeds available stock (${formatNumber(requestedBase)} base requested > ${formatNumber(inventory.base_quantity)} available).`;

            return false;
        }


        previewDiv.style.color = '#059669';

        previewDiv.textContent =
            `Valid: ${quantity} ${unitCode} = ${formatNumber(requestedBase)} base units.`;

        return true;
    }


    /* =========================
       EVALUATE FORM
    ========================== */

    function evaluateFormState() {

        let validCount = 0;
        let allCheckedValid = true;
        let anyChecked = false;


        checklistRows.forEach(row => {

            if (row.dataset.disabledSameLocation) {
                return;
            }


            const checkbox =
                row.querySelector('.item-checkbox');


            if (checkbox.checked) {

                anyChecked = true;


                if (evaluateRow(row)) {

                    validCount++;

                } else {

                    allCheckedValid = false;
                }
            }
        });


        updateRowIndexing();


        const isDestinationSelected =
            destinationSelect.value !== '';


        submitButton.disabled =
            !(
                anyChecked &&
                allCheckedValid &&
                isDestinationSelected &&
                validCount > 0
            );


        selectionInfo.textContent =
            `${getSelectedCount()} selected`;


        updateSelectAllState();
    }


    /* =========================
       CHECKLIST EVENTS
    ========================== */

    checklistRows.forEach(row => {

        const checkbox =
            row.querySelector('.item-checkbox');

        const unitSelect =
            row.querySelector('.unit-select');

        const qtyInput =
            row.querySelector('.quantity-input');


        checkbox.addEventListener(
            'change',
            () => {

                if (
                    row.dataset.disabledSameLocation
                ) {
                    return;
                }


                if (checkbox.checked) {

                    row.style.background =
                        '#f0fdf4';

                } else {

                    row.style.background =
                        'transparent';
                }


                evaluateFormState();
                renderPagination();
            }
        );


        unitSelect.addEventListener(
            'change',
            evaluateFormState
        );


        qtyInput.addEventListener(
            'input',
            evaluateFormState
        );
    });


    /* =========================
       DESTINATION EVENT
    ========================== */

    destinationSelect.addEventListener(
        'change',
        handleDestinationChange
    );


    /* =========================
       SELECT ALL CURRENT PAGE
    ========================== */

    selectAllCheckbox.addEventListener(
        'change',
        () => {

            const isChecked =
                selectAllCheckbox.checked;


            checklistRows.forEach(row => {

                /*
                 * Only select rows currently visible
                 * on this page.
                 */
                if (
                    row.style.display === 'none'
                ) {
                    return;
                }


                if (
                    row.dataset.disabledSameLocation
                ) {
                    return;
                }


                const checkbox =
                    row.querySelector('.item-checkbox');


                checkbox.checked =
                    isChecked;


                row.style.background =
                    isChecked
                        ? '#f0fdf4'
                        : 'transparent';
            });


            evaluateFormState();
            renderPagination();
        }
    );


    /* =========================
       PREVIOUS PAGE
    ========================== */

    prevButton.addEventListener(
        'click',
        function () {

            if (currentPage <= 1) {
                return;
            }


            currentPage--;

            renderPagination();

            updateSelectAllState();

            /*
             * Keep the user near the checklist when
             * changing pages.
             */
            document
                .getElementById('checklist-body')
                .closest('table')
                .scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
        }
    );


    /* =========================
       NEXT PAGE
    ========================== */

    nextButton.addEventListener(
        'click',
        function () {

            const totalPages =
                getTotalPages();


            if (
                currentPage >= totalPages
            ) {
                return;
            }


            currentPage++;

            renderPagination();

            updateSelectAllState();

            document
                .getElementById('checklist-body')
                .closest('table')
                .scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
        }
    );


    /* =========================
       RECEIVER ROLE AUTO-FILL
    ========================== */

    if (
        receiverSelect &&
        receiverRoleSelect
    ) {

        receiverSelect.addEventListener(
            'change',
            function () {

                const selectedOption =
                    receiverSelect.options[
                        receiverSelect.selectedIndex
                    ];


                const role =
                    selectedOption
                        ? selectedOption.dataset.role
                        : '';


                if (role) {

                    receiverRoleSelect.value =
                        role;
                }
            }
        );
    }


    /* =========================
       INITIAL STATE
    ========================== */

    handleDestinationChange();

    renderPagination();


    /* =========================
       SUBMIT PROTECTION
    ========================== */

    document
        .getElementById('transfer-form')
        .addEventListener(
            'submit',
            function (e) {

                if (submitButton.disabled) {

                    e.preventDefault();

                    return;
                }


                /*
                 * Make sure every selected item has
                 * its correct input name before submit.
                 */
                updateRowIndexing();


                submitButton.disabled = true;

                submitButton.textContent =
                    'Transferring...';
            }
        );

});
</script>

@endsection
