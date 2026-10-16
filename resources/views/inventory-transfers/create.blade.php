@extends('layouts.app')

@section('title', 'Transfer Inventory')

@section('content')

<div class="page-header">
    <div>
        <h1>Transfer Inventory</h1>
        <p style="margin: 6px 0 0; color: #64748b;">
            Choose where the stock goes and who receives it, then add the products to move.
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
             WHERE IT GOES + WHO RECEIVES IT (side by side)
        ========================== --}}
        <div class="transfer-grid">

            <div class="form-group">
                <label for="destination_location_id">
                    Destination Location <span style="color: #dc2626;">*</span>
                </label>

                <select id="destination_location_id" name="destination_location_id" required>
                    <option value="">Select destination</option>

                    @foreach ($locations as $location)
                        <option
                            value="{{ $location->id }}"
                            {{ old('destination_location_id') == $location->id ? 'selected' : '' }}
                        >
                            {{ $location->name }}{{ $location->code ? ' (' . $location->code . ')' : '' }}{{ $location->company ? ' - ' . $location->company->name : '' }}
                        </option>
                    @endforeach
                </select>

                <small id="destination-helper">Where the stock is going.</small>
            </div>

            <div class="form-group">
                <label for="receiver_id">
                    Receiver <span style="color: #dc2626;">*</span>
                </label>

                {{-- Only people who work at the chosen destination are offered (see the script below). --}}
                <select id="receiver_id" name="receiver_id" required disabled>
                    <option value="">Select a destination first</option>

                    @foreach ($receivers as $receiver)
                        <option
                            value="{{ $receiver->id }}"
                            data-location="{{ $receiver->location_id }}"
                            data-admin="{{ $receiver->isAdmin() ? '1' : '0' }}"
                            {{ old('receiver_id') == $receiver->id ? 'selected' : '' }}
                        >
                            {{ $receiver->name }} ({{ ucfirst($receiver->role) }})
                        </option>
                    @endforeach
                </select>

                <small id="receiver-helper">Someone at the destination who will check and accept the stock.</small>
            </div>

        </div>

        <style>
            .transfer-grid {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 0 24px;
            }

            .transfer-grid small {
                display: block;
                margin-top: 6px;
            }

            #receiver-helper.is-warning {
                color: #b45309;
            }

            @media (max-width: 800px) {
                .transfer-grid {
                    grid-template-columns: 1fr;
                }
            }
        </style>

        <hr style="border: 0; border-top: 1px solid #e2e8f0; margin: 6px 0 22px;">

        {{-- =========================
             ITEMS TO TRANSFER
             Pick a product from the dropdown, press Add, then set its unit
             and quantity in the table. Each row is sent as items[n][...].
        ========================== --}}

        @php
            // Everything the picker needs, in one small list (no stock record is sent twice).
            $transferOptions = $inventories
                ->filter(fn ($inventory) => (float) $inventory->base_quantity > 0 && $inventory->product && $inventory->location)
                ->map(fn ($inventory) => [
                    'id' => $inventory->id,
                    'location_id' => $inventory->location_id,
                    'product' => $inventory->product->name,
                    'sku' => $inventory->product->sku,
                    'location' => $inventory->location->name,
                    'available' => (float) $inventory->base_quantity,
                    'base' => $inventory->product->counting_unit,
                    'units' => $inventory->product->productUnits
                        ->filter(fn ($productUnit) => $productUnit->unitOfMeasure)
                        ->map(fn ($productUnit) => [
                            'id' => $productUnit->id,
                            'name' => $productUnit->unitOfMeasure->name,
                            'code' => $productUnit->unitOfMeasure->code,
                            'factor' => (float) $productUnit->conversion_factor,
                        ])
                        ->sortBy('factor')
                        ->values(),
                ])
                ->values();
        @endphp

        <h3 style="margin: 0 0 4px; font-size: 16px;">Items to transfer</h3>

        <p style="margin: 0 0 14px; color: #64748b; font-size: 13px;">
            Choose a product and press <strong>Add</strong>. Then set the unit and quantity for each item in the table.
        </p>

        <div class="transfer-picker">

            <div class="transfer-picker-field">
                <label for="transfer-product-picker">Product</label>

                <select id="transfer-product-picker" disabled>
                    <option value="">Select a destination first</option>
                </select>
            </div>

            <button type="button" class="btn btn-primary" id="transfer-add-button" disabled>
                Add
            </button>

        </div>

        <div class="table-wrapper transfer-items-wrapper">

            <table class="transfer-items-table">

                <thead>
                    <tr>
                        <th>Product</th>
                        <th style="width: 220px;">Unit</th>
                        <th style="width: 150px;">Quantity</th>
                        <th style="width: 170px;">Moves</th>
                        <th style="width: 90px;"></th>
                    </tr>
                </thead>

                <tbody id="transfer-items-body">
                    <tr class="transfer-items-empty">
                        <td colspan="5">
                            No items yet. Choose a product above and press Add.
                        </td>
                    </tr>
                </tbody>

            </table>

        </div>

        <div id="transfer-items-summary" class="transfer-items-summary">0 items</div>

        <style>

            .transfer-picker {
                display: flex;
                align-items: flex-end;
                gap: 10px;
                margin-bottom: 14px;
            }

            .transfer-picker-field {
                flex: 1;
                min-width: 0;
            }

            .transfer-picker .btn {
                min-height: 40px;
                min-width: 90px;
            }

            .transfer-items-table td {
                vertical-align: top;
            }

            .transfer-items-table select,
            .transfer-items-table input {
                width: 100%;
            }

            .transfer-item-name {
                font-weight: 600;
                color: #0f172a;
            }

            .transfer-item-meta {
                margin-top: 2px;
                color: #64748b;
                font-size: 12.5px;
            }

            .transfer-item-moves {
                padding-top: 10px;
                color: #334155;
                font-size: 13px;
            }

            .transfer-item-error {
                display: block;
                margin-top: 4px;
                color: #b91c1c;
                font-size: 12.5px;
            }

            .transfer-items-empty td {
                padding: 26px 16px;
                text-align: center;
                color: #64748b;
            }

            .transfer-items-summary {
                margin: 8px 0 18px;
                color: #475569;
                font-size: 13px;
            }

            @media (max-width: 700px) {
                .transfer-picker {
                    flex-direction: column;
                    align-items: stretch;
                }
            }

        </style>

        <hr style="border: 0; border-top: 1px solid #e2e8f0; margin: 0 0 24px;">

        {{-- =========================
             REFERENCE + NOTES (side by side)
        ========================== --}}
        <div class="transfer-grid">

            <div class="form-group">
                <label for="reference">Reference</label>

                <input
                    type="text"
                    id="reference"
                    name="reference"
                    value="{{ old('reference') }}"
                    maxlength="255"
                    placeholder="e.g. Weekly restock for Pasig"
                >
            </div>

            <div class="form-group">
                <label for="notes">Notes</label>

                <textarea
                    id="notes"
                    name="notes"
                    rows="2"
                    style="min-height: 42px;"
                    placeholder="Optional notes for the receiver..."
                >{{ old('notes') }}</textarea>
            </div>

        </div>

        {{-- =========================
             BUTTONS
        ========================== --}}
        <div
            style="display: flex; gap: 10px; flex-wrap: wrap; margin-top: 8px;"
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
                href="{{ route('inventory-transfers.index') }}"
                class="btn btn-secondary"
            >
                Cancel
            </a>
        </div>

    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    /*
     * Stock that can be moved: one entry per stock record (product at a
     * location), with the units it can be counted in.
     */
    const options = @json($transferOptions);
    const oldItems = @json(old('items', []));

    const form = document.getElementById('transfer-form');
    const destinationSelect = document.getElementById('destination_location_id');
    const receiverSelect = document.getElementById('receiver_id');
    const picker = document.getElementById('transfer-product-picker');
    const addButton = document.getElementById('transfer-add-button');
    const body = document.getElementById('transfer-items-body');
    const summary = document.getElementById('transfer-items-summary');
    const submitButton = document.getElementById('submit-button');

    const added = []; // stock record ids in the table, in order

    /*
     * The receiver must work at the destination: people assigned to that
     * location, plus admins who aren't tied to any one location. Anyone
     * else is hidden (the server checks the same rule).
     */
    const receiverOptions = Array.from(receiverSelect.options).filter(o => o.value);
    const receiverHelper = document.getElementById('receiver-helper');

    function renderReceivers() {
        const destination = destinationSelect.value;
        const keep = receiverSelect.value;

        receiverSelect.innerHTML = '';

        if (!destination) {
            receiverSelect.add(new Option('Select a destination first', ''));
            receiverSelect.disabled = true;
            receiverHelper.textContent = 'Someone at the destination who will check and accept the stock.';
            receiverHelper.classList.remove('is-warning');
            return;
        }

        const allowed = receiverOptions.filter(o =>
            o.dataset.location ? o.dataset.location === String(destination) : o.dataset.admin === '1'
        );

        receiverSelect.add(new Option(allowed.length ? 'Select receiver' : 'No one works at this location yet', ''));
        allowed.forEach(o => receiverSelect.add(o));
        receiverSelect.value = allowed.some(o => o.value === keep) ? keep : '';
        receiverSelect.disabled = allowed.length === 0;

        receiverHelper.textContent = allowed.length
            ? 'Only people at ' + destinationSelect.options[destinationSelect.selectedIndex].text.trim().split(' (')[0] + ' can receive this.'
            : 'Assign a staff member or manager to this location under Users first.';
        receiverHelper.classList.toggle('is-warning', allowed.length === 0);
    }

    destinationSelect.addEventListener('change', renderReceivers);
    renderReceivers();
    function fmt(value) {
        return parseFloat(Number(value || 0).toFixed(2)).toString();
    }

    function esc(text) {
        const div = document.createElement('div');
        div.textContent = text == null ? '' : String(text);
        return div.innerHTML;
    }

    function option(id) {
        return options.find(o => String(o.id) === String(id));
    }

    /* The dropdown lists stock that isn't already at the destination or already in the table. */
    function renderPicker() {
        const destination = destinationSelect.value;

        picker.innerHTML = '';

        if (!destination) {
            picker.add(new Option('Select a destination first', ''));
            picker.disabled = true;
            addButton.disabled = true;
            return;
        }

        const choices = options.filter(o =>
            String(o.location_id) !== String(destination) && !added.includes(o.id)
        );

        picker.add(new Option(choices.length ? 'Select a product to transfer...' : 'No more stock available to transfer', ''));

        const byLocation = {};

        choices.forEach(o => {
            (byLocation[o.location] = byLocation[o.location] || []).push(o);
        });

        Object.keys(byLocation).sort().forEach(location => {
            const group = document.createElement('optgroup');
            group.label = 'From ' + location;

            byLocation[location].forEach(o => {
                group.appendChild(new Option(
                    o.product + (o.sku ? ' (' + o.sku + ')' : '') + ' - ' + fmt(o.available) + ' ' + o.base + ' available',
                    o.id
                ));
            });

            picker.appendChild(group);
        });

        picker.disabled = choices.length === 0;
        addButton.disabled = true;
    }

    function addRow(id, unitId, quantity) {
        const o = option(id);

        if (!o || added.includes(o.id)) {
            return;
        }

        added.push(o.id);

        const row = document.createElement('tr');
        row.className = 'transfer-item-row';
        row.dataset.id = o.id;

        const unitOptions = o.units.map(u =>
            '<option value="' + u.id + '" data-factor="' + u.factor + '" data-code="' + esc(u.code) + '">' +
            esc(u.name) + ' (' + esc(u.code) + ')' + (u.factor !== 1 ? ' = ' + fmt(u.factor) + ' ' + esc(o.base) : '') +
            '</option>'
        ).join('');

        row.innerHTML =
            '<td>' +
                '<div class="transfer-item-name">' + esc(o.product) + '</div>' +
                '<div class="transfer-item-meta">From ' + esc(o.location) + ' &middot; ' + fmt(o.available) + ' ' + esc(o.base) + ' available</div>' +
                '<input type="hidden" data-field="source_inventory_id" value="' + o.id + '">' +
            '</td>' +
            '<td><select data-field="product_unit_id" required>' + unitOptions + '</select></td>' +
            '<td>' +
                '<input type="text" inputmode="decimal" autocomplete="off" data-numeric step="0.1" min="0.1" data-field="quantity" placeholder="0" required>' +
                '<small class="transfer-item-error" hidden></small>' +
            '</td>' +
            '<td class="transfer-item-moves">-</td>' +
            '<td><button type="button" class="btn btn-danger transfer-item-remove">Remove</button></td>';

        body.appendChild(row);

        const unitSelect = row.querySelector('[data-field="product_unit_id"]');
        const qtyInput = row.querySelector('[data-field="quantity"]');

        if (unitId && unitSelect.querySelector('option[value="' + unitId + '"]')) {
            unitSelect.value = String(unitId);
        }

        if (quantity) {
            qtyInput.value = quantity;
        }

        unitSelect.addEventListener('change', refresh);
        qtyInput.addEventListener('input', refresh);

        row.querySelector('.transfer-item-remove').addEventListener('click', function () {
            added.splice(added.indexOf(o.id), 1);
            row.remove();
            renderPicker();
            refresh();
        });

        if (!quantity) {
            qtyInput.focus();
        }
    }

    /* Check each row, name its fields items[n][...], and decide whether the form can be sent. */
    function refresh() {
        const rows = Array.from(body.querySelectorAll('.transfer-item-row'));
        let allValid = rows.length > 0;

        body.querySelector('.transfer-items-empty').hidden = rows.length > 0;

        rows.forEach((row, index) => {
            const o = option(row.dataset.id);
            const unit = row.querySelector('[data-field="product_unit_id"]');
            const qty = row.querySelector('[data-field="quantity"]');
            const error = row.querySelector('.transfer-item-error');
            const moves = row.querySelector('.transfer-item-moves');

            row.querySelectorAll('[data-field]').forEach(field => {
                field.name = 'items[' + index + '][' + field.dataset.field + ']';
            });

            const factor = Number(unit.selectedOptions[0] ? unit.selectedOptions[0].dataset.factor : 0);
            const amount = Number(qty.value);
            const base = amount * factor;
            let message = '';

            if (qty.value === '' || !(amount > 0)) {
                message = qty.value === '' ? '' : 'Enter more than 0.';
                allValid = false;
                moves.textContent = '-';
            } else {
                moves.textContent = fmt(base) + ' ' + o.base;

                if (base > o.available + 0.0000001) {
                    message = 'Only ' + fmt(o.available) + ' ' + o.base + ' available.';
                    allValid = false;
                }
            }

            error.textContent = message;
            error.hidden = message === '';
        });

        summary.textContent = rows.length + (rows.length === 1 ? ' item' : ' items');

        submitButton.disabled = !(allValid && destinationSelect.value && receiverSelect.value);
    }

    picker.addEventListener('change', function () {
        addButton.disabled = !picker.value;
    });

    addButton.addEventListener('click', function () {
        if (!picker.value) {
            return;
        }

        addRow(picker.value);
        renderPicker();
        refresh();
    });

    /* Changing the destination drops any item that is already at the new destination. */
    destinationSelect.addEventListener('change', function () {
        Array.from(body.querySelectorAll('.transfer-item-row')).forEach(row => {
            const o = option(row.dataset.id);

            if (String(o.location_id) === String(destinationSelect.value)) {
                added.splice(added.indexOf(o.id), 1);
                row.remove();
            }
        });

        renderPicker();
        refresh();
    });

    receiverSelect.addEventListener('change', refresh);

    form.addEventListener('submit', function (event) {
        refresh();

        if (submitButton.disabled) {
            event.preventDefault();
        }
    });

    /* After a failed save, put back what was entered. */
    (Array.isArray(oldItems) ? oldItems : Object.values(oldItems)).forEach(item => {
        if (item && item.source_inventory_id) {
            addRow(item.source_inventory_id, item.product_unit_id, item.quantity);
        }
    });

    renderPicker();
    refresh();
});
</script>

@endsection
