@extends('layouts.app')

@section('content')

<style>
    /*
    |--------------------------------------------------------------------------
    | Purchase Order Receiving Page
    |--------------------------------------------------------------------------
    */

    .po-receive-page {
        width: 100%;
        max-width: 1200px;
        margin: 0 auto;
        padding: 30px;
        box-sizing: border-box;
        color: #1f2937;
        background: #f9fafb;
    }

    /*
    |--------------------------------------------------------------------------
    | Header
    |--------------------------------------------------------------------------
    */

    .po-receive-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 20px;
        margin-bottom: 25px;
    }

    .po-receive-header-content {
        min-width: 0;
    }

    .po-receive-header h1 {
        margin: 0;
        font-size: 28px;
        line-height: 1.3;
        font-weight: 700;
        color: #111827 !important;
    }

    .po-receive-header p {
        margin: 6px 0 0;
        font-size: 15px;
        color: #6b7280 !important;
    }

    /*
    |--------------------------------------------------------------------------
    | Buttons
    |--------------------------------------------------------------------------
    */

    .po-receive-btn {
        display: inline-flex !important;
        align-items: center;
        justify-content: center;

        min-height: 42px;
        padding: 10px 18px !important;

        border-radius: 6px !important;
        border: 1px solid transparent !important;

        font-family: inherit;
        font-size: 14px !important;
        line-height: 1.2 !important;
        font-weight: 700 !important;

        text-decoration: none !important;
        cursor: pointer;

        box-sizing: border-box;

        transition:
            background-color 0.15s ease,
            border-color 0.15s ease,
            color 0.15s ease,
            box-shadow 0.15s ease;
    }

    /*
    |--------------------------------------------------------------------------
    | Back Button
    |--------------------------------------------------------------------------
    */

    .po-receive-btn-back {
        background-color: #e5e7eb !important;
        color: #111827 !important;
        border-color: #d1d5db !important;
    }

    .po-receive-btn-back:hover {
        background-color: #d1d5db !important;
        color: #111827 !important;
    }

    /*
    |--------------------------------------------------------------------------
    | Submit Button
    |--------------------------------------------------------------------------
    */

    .po-receive-btn-submit {
        background-color: #15803d !important;
        color: #ffffff !important;
        border-color: #15803d !important;
    }

    .po-receive-btn-submit:hover {
        background-color: #166534 !important;
        color: #ffffff !important;
        border-color: #166534 !important;
    }

    .po-receive-btn-submit:focus {
        outline: none !important;
        color: #ffffff !important;
        box-shadow: 0 0 0 3px rgba(21, 128, 61, 0.25);
    }

    .po-receive-btn-submit:active {
        background-color: #14532d !important;
        color: #ffffff !important;
    }

    /*
    |--------------------------------------------------------------------------
    | Cancel Button
    |--------------------------------------------------------------------------
    */

    .po-receive-btn-cancel {
        background-color: #ffffff !important;
        color: #374151 !important;
        border-color: #d1d5db !important;
    }

    .po-receive-btn-cancel:hover {
        background-color: #f3f4f6 !important;
        color: #111827 !important;
    }

    /*
    |--------------------------------------------------------------------------
    | Error Messages
    |--------------------------------------------------------------------------
    */

    .po-receive-errors {
        background-color: #fef2f2 !important;
        border: 1px solid #fecaca !important;
        color: #991b1b !important;

        padding: 15px 18px;
        border-radius: 8px;

        margin-bottom: 20px;
        box-sizing: border-box;
    }

    .po-receive-errors strong {
        color: #991b1b !important;
    }

    .po-receive-errors ul {
        margin: 8px 0 0;
        padding-left: 20px;
    }

    .po-receive-errors li {
        color: #991b1b !important;
        margin-bottom: 4px;
    }

    /*
    |--------------------------------------------------------------------------
    | Information Cards
    |--------------------------------------------------------------------------
    */

    .po-receive-info {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 15px;
        margin-bottom: 20px;
    }

    .po-receive-info-card {
        background-color: #ffffff !important;
        border: 1px solid #d1d5db !important;
        border-radius: 8px;

        padding: 18px;
        box-sizing: border-box;

        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
    }

    .po-receive-label {
        margin-bottom: 6px;

        font-size: 13px;
        line-height: 1.4;

        color: #6b7280 !important;
    }

    .po-receive-value {
        font-size: 16px;
        line-height: 1.4;
        font-weight: 700;

        color: #111827 !important;
        word-break: break-word;
    }

    /*
    |--------------------------------------------------------------------------
    | Main Card
    |--------------------------------------------------------------------------
    */

    .po-receive-card {
        width: 100%;

        background-color: #ffffff !important;
        border: 1px solid #d1d5db !important;
        border-radius: 8px;

        margin-bottom: 20px;
        overflow: hidden;

        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    }

    /*
    |--------------------------------------------------------------------------
    | Card Header
    |--------------------------------------------------------------------------
    */

    .po-receive-card-header {
        padding: 20px;

        background-color: #ffffff !important;

        border-bottom: 1px solid #e5e7eb !important;
    }

    .po-receive-card-header h2 {
        margin: 0;

        font-size: 20px;
        line-height: 1.4;
        font-weight: 700;

        color: #111827 !important;
    }

    .po-receive-card-header p {
        margin: 6px 0 0;

        font-size: 14px;
        line-height: 1.5;

        color: #6b7280 !important;
    }

    /*
    |--------------------------------------------------------------------------
    | Table
    |--------------------------------------------------------------------------
    */

    .po-receive-table-wrapper {
        width: 100%;
        overflow-x: auto;
        background-color: #ffffff;
    }

    .po-receive-table {
        width: 100%;
        min-width: 900px;

        border-collapse: collapse;
        border-spacing: 0;

        background-color: #ffffff !important;
    }

    .po-receive-table th {
        padding: 13px 15px;

        background-color: #f3f4f6 !important;
        color: #374151 !important;

        border-bottom: 1px solid #d1d5db !important;

        font-size: 12px;
        line-height: 1.4;
        font-weight: 700;

        text-align: left;
        white-space: nowrap;
    }

    .po-receive-table td {
        padding: 15px;

        background-color: #ffffff !important;
        color: #1f2937 !important;

        border-bottom: 1px solid #e5e7eb !important;

        font-size: 14px;
        line-height: 1.5;

        vertical-align: middle;
    }

    .po-receive-table tbody tr:last-child td {
        border-bottom: none !important;
    }

    .po-receive-table tbody tr:hover td {
        background-color: #f9fafb !important;
    }

    /*
    |--------------------------------------------------------------------------
    | Product
    |--------------------------------------------------------------------------
    */

    .po-receive-product {
        font-size: 14px;
        line-height: 1.4;
        font-weight: 700;

        color: #111827 !important;
    }

    .po-receive-sku {
        margin-top: 4px;

        font-size: 12px;
        line-height: 1.4;

        color: #6b7280 !important;
    }

    /*
    |--------------------------------------------------------------------------
    | Numbers
    |--------------------------------------------------------------------------
    */

    .po-receive-number {
        text-align: right !important;
        white-space: nowrap;
    }

    .po-receive-remaining {
        font-weight: 700 !important;
        color: #111827 !important;
    }

    /*
    |--------------------------------------------------------------------------
    | Inputs
    |--------------------------------------------------------------------------
    */

    .po-receive-input {
        width: 120px;

        min-height: 40px;

        padding: 9px 10px;

        box-sizing: border-box;

        background-color: #ffffff !important;
        color: #111827 !important;

        border: 1px solid #9ca3af !important;
        border-radius: 6px !important;

        font-family: inherit;
        font-size: 14px;
        line-height: 1.3;

        color-scheme: light;
    }

    .po-receive-input::placeholder {
        color: #9ca3af !important;
        opacity: 1;
    }

    .po-receive-input:hover {
        border-color: #6b7280 !important;
    }

    .po-receive-input:focus {
        outline: none !important;

        background-color: #ffffff !important;
        color: #111827 !important;

        border-color: #2563eb !important;

        box-shadow:
            0 0 0 2px rgba(37, 99, 235, 0.15);
    }

    .po-receive-input:disabled {
        background-color: #f3f4f6 !important;
        color: #9ca3af !important;

        border-color: #d1d5db !important;

        cursor: not-allowed;
    }

    .po-receive-notes-input {
        width: 180px;
    }

    /*
    |--------------------------------------------------------------------------
    | Receiving Notes
    |--------------------------------------------------------------------------
    */

    .po-receive-section {
        padding: 20px;

        background-color: #ffffff !important;

        border-top: 1px solid #e5e7eb !important;
    }

    .po-receive-section label {
        display: block;

        margin-bottom: 7px;

        font-size: 14px;
        line-height: 1.4;
        font-weight: 700;

        color: #374151 !important;
    }

    .po-receive-textarea {
        display: block;

        width: 100%;
        min-height: 90px;

        padding: 10px 12px;

        box-sizing: border-box;

        background-color: #ffffff !important;
        color: #111827 !important;

        border: 1px solid #9ca3af !important;
        border-radius: 6px !important;

        font-family: inherit;
        font-size: 14px;
        line-height: 1.5;

        resize: vertical;

        color-scheme: light;
    }

    .po-receive-textarea::placeholder {
        color: #9ca3af !important;
        opacity: 1;
    }

    .po-receive-textarea:focus {
        outline: none !important;

        background-color: #ffffff !important;
        color: #111827 !important;

        border-color: #2563eb !important;

        box-shadow:
            0 0 0 2px rgba(37, 99, 235, 0.15);
    }

    /*
    |--------------------------------------------------------------------------
    | Bottom Actions
    |--------------------------------------------------------------------------
    */

    .po-receive-actions {
        display: flex;
        justify-content: flex-end;
        align-items: center;
        gap: 10px;

        padding: 18px 20px;

        background-color: #f9fafb !important;

        border-top: 1px solid #e5e7eb !important;
    }

    /*
    |--------------------------------------------------------------------------
    | Empty State
    |--------------------------------------------------------------------------
    */

    .po-receive-empty {
        padding: 30px 20px;

        text-align: center;

        color: #6b7280 !important;
        background-color: #ffffff !important;
    }

    /*
    |--------------------------------------------------------------------------
    | Responsive
    |--------------------------------------------------------------------------
    */

    @media (max-width: 900px) {

        .po-receive-info {
            grid-template-columns: 1fr;
        }

        .po-receive-header {
            align-items: flex-start;
        }

    }

    @media (max-width: 768px) {

        .po-receive-page {
            padding: 15px;
        }

        .po-receive-header {
            display: block;
        }

        .po-receive-header .po-receive-btn {
            margin-top: 15px;
        }

        .po-receive-actions {
            flex-direction: column-reverse;
            align-items: stretch;
        }

        .po-receive-actions .po-receive-btn {
            width: 100%;
        }

    }

    @media (max-width: 480px) {

        .po-receive-page {
            padding: 10px;
        }

        .po-receive-header h1 {
            font-size: 24px;
        }

        .po-receive-card-header,
        .po-receive-section {
            padding: 15px;
        }

        .po-receive-actions {
            padding: 15px;
        }

    }
</style>


<div class="po-receive-page">

    {{-- ================================================================
         HEADER
         ================================================================ --}}

    <div class="po-receive-header">

        <div class="po-receive-header-content">

            <h1>
                Receive Purchase Order
            </h1>

            <p>
                {{ $purchaseOrder->po_number }}
            </p>

        </div>


        <a
            href="{{ route('purchase-orders.show', $purchaseOrder) }}"
            class="po-receive-btn po-receive-btn-back"
        >
            Back to Purchase Order
        </a>

    </div>


    {{-- ================================================================
         VALIDATION ERRORS
         ================================================================ --}}

    @if ($errors->any())

        <div class="po-receive-errors">

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


    {{-- ================================================================
         PO INFORMATION
         ================================================================ --}}

    <div class="po-receive-info">

        {{-- Supplier --}}

        <div class="po-receive-info-card">

            <div class="po-receive-label">
                Supplier
            </div>

            <div class="po-receive-value">
                {{ $purchaseOrder->supplier->name }}
            </div>

        </div>


        {{-- Receiving Location --}}

        <div class="po-receive-info-card">

            <div class="po-receive-label">
                Receiving Location
            </div>

            <div class="po-receive-value">
                {{ $purchaseOrder->location->name }}
            </div>

        </div>


        {{-- Status --}}

        <div class="po-receive-info-card">

            <div class="po-receive-label">
                Status
            </div>

            <div class="po-receive-value">
                {{ ucfirst(str_replace('_', ' ', $purchaseOrder->status)) }}
            </div>

        </div>

    </div>


    {{-- ================================================================
         RECEIVING FORM
         ================================================================ --}}

    <form
        method="POST"
        action="{{ route('purchase-orders.receive', $purchaseOrder) }}"
    >

        @csrf

        @method('PATCH')


        <div class="po-receive-card">


            {{-- ========================================================
                 ITEMS HEADER
                 ======================================================== --}}

            <div class="po-receive-card-header">

                <h2>
                    Items
                </h2>

                <p>
                    Enter the quantity actually received for each item.
                    You can receive the purchase order partially.
                </p>

            </div>


            {{-- ========================================================
                 ITEMS TABLE
                 ======================================================== --}}

            <div class="po-receive-table-wrapper">

                <table class="po-receive-table">

                    <thead>

                        <tr>

                            <th>
                                Product
                            </th>

                            <th>
                                Unit
                            </th>

                            <th class="po-receive-number">
                                Ordered
                            </th>

                            <th class="po-receive-number">
                                Received
                            </th>

                            <th class="po-receive-number">
                                Remaining
                            </th>

                            <th class="po-receive-number">
                                Receive Now
                            </th>

                            <th>
                                Notes
                            </th>

                        </tr>

                    </thead>


                    <tbody>

                        @forelse ($purchaseOrder->items as $index => $item)

                            @php

                                $ordered =
                                    (float) $item->quantity_ordered;

                                $received =
                                    (float) $item->quantity_received;

                                $remaining =
                                    max(
                                        0,
                                        $ordered - $received
                                    );

                                $formattedOrdered =
                                    rtrim(
                                        rtrim(
                                            number_format(
                                                $ordered,
                                                4,
                                                '.',
                                                ''
                                            ),
                                            '0'
                                        ),
                                        '.'
                                    );

                                $formattedReceived =
                                    rtrim(
                                        rtrim(
                                            number_format(
                                                $received,
                                                4,
                                                '.',
                                                ''
                                            ),
                                            '0'
                                        ),
                                        '.'
                                    );

                                $formattedRemaining =
                                    rtrim(
                                        rtrim(
                                            number_format(
                                                $remaining,
                                                4,
                                                '.',
                                                ''
                                            ),
                                            '0'
                                        ),
                                        '.'
                                    );

                            @endphp


                            <tr>

                                {{-- ==================================================
                                     PRODUCT
                                     ================================================== --}}

                                <td>

                                    <div class="po-receive-product">
                                        {{ $item->product->name }}
                                    </div>


                                    @if (!empty($item->product->sku))

                                        <div class="po-receive-sku">
                                            SKU: {{ $item->product->sku }}
                                        </div>

                                    @endif

                                </td>


                                {{-- ==================================================
                                     UNIT
                                     ================================================== --}}

                                <td>

                                    {{ $item->productUnit->unitOfMeasure->name ?? 'Unit' }}

                                </td>


                                {{-- ==================================================
                                     ORDERED
                                     ================================================== --}}

                                <td class="po-receive-number">

                                    {{ $formattedOrdered }}

                                </td>


                                {{-- ==================================================
                                     RECEIVED
                                     ================================================== --}}

                                <td class="po-receive-number">

                                    {{ $formattedReceived }}

                                </td>


                                {{-- ==================================================
                                     REMAINING
                                     ================================================== --}}

                                <td class="po-receive-number po-receive-remaining">

                                    {{ $formattedRemaining }}

                                </td>


                                {{-- ==================================================
                                     RECEIVE NOW
                                     ================================================== --}}

                                <td class="po-receive-number">

                                    <input
                                        type="hidden"
                                        name="lines[{{ $index }}][purchase_order_item_id]"
                                        value="{{ $item->id }}"
                                    >


                                    <input
                                        type="number"
                                        name="lines[{{ $index }}][quantity_received]"
                                        value="{{ old(
                                            "lines.$index.quantity_received",
                                            $remaining > 0 ? $remaining : 0
                                        ) }}"
                                        min="0"
                                        max="{{ $remaining }}"
                                        step="0.0001"
                                        class="po-receive-input"
                                        @disabled($remaining <= 0)
                                    >

                                </td>


                                {{-- ==================================================
                                     LINE NOTES
                                     ================================================== --}}

                                <td>

                                    <input
                                        type="text"
                                        name="lines[{{ $index }}][notes]"
                                        value="{{ old("lines.$index.notes") }}"
                                        placeholder="Optional"
                                        class="po-receive-input po-receive-notes-input"
                                        @disabled($remaining <= 0)
                                    >

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td
                                    colspan="7"
                                    class="po-receive-empty"
                                >
                                    No items were found on this purchase order.
                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>


            {{-- ========================================================
                 RECEIVING NOTES
                 ======================================================== --}}

            <div class="po-receive-section">

                <label for="notes">
                    Receiving Notes
                </label>


                <textarea
                    id="notes"
                    name="notes"
                    class="po-receive-textarea"
                    placeholder="Optional receiving notes..."
                >{{ old('notes') }}</textarea>

            </div>


            {{-- ========================================================
                 ACTIONS
                 ======================================================== --}}

            <div class="po-receive-actions">

                <a
                    href="{{ route('purchase-orders.show', $purchaseOrder) }}"
                    class="po-receive-btn po-receive-btn-cancel"
                >
                    Cancel
                </a>


                <button
                    type="submit"
                    class="po-receive-btn po-receive-btn-submit"
                >
                    <span>
                        Record Receiving
                    </span>
                </button>

            </div>

        </div>

    </form>

</div>

@endsection
