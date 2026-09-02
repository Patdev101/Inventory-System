@extends('layouts.app')

@section('title', 'Inventory Details')

@section('content')

<style>
    .inventory-details {
        display: grid;
        grid-template-columns: 1fr;
        gap: 30px;
    }

    .inventory-summary {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 15px;
        margin-bottom: 20px;
    }

    .summary-card {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        padding: 18px;
    }

    .summary-label {
        color: #6b7280;
        font-size: 13px;
        margin-bottom: 7px;
    }

    .summary-value {
        color: #111827;
        font-size: 22px;
        font-weight: bold;
    }

    .summary-subtitle {
        color: #9ca3af;
        font-size: 12px;
        margin-top: 5px;
    }

    .status-badge {
        display: inline-block;
        padding: 5px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: bold;
    }

    .status-low {
        background: #fef3c7;
        color: #92400e;
    }

    .status-critical {
        background: #fed7aa;
        color: #9a3412;
    }

    .status-ok {
        background: #dcfce7;
        color: #166534;
    }

    .status-empty {
        background: #fee2e2;
        color: #991b1b;
    }

    .transaction-in {
        color: #15803d;
        font-weight: bold;
    }

    .transaction-out {
        color: #dc2626;
        font-weight: bold;
    }

    .balance-positive {
        color: #166534;
        font-weight: bold;
    }

    .balance-zero {
        color: #991b1b;
        font-weight: bold;
    }

    .reference-muted {
        color: #6b7280;
    }

    @media (max-width: 1000px) {
        .inventory-summary {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 600px) {
        .inventory-summary {
            grid-template-columns: 1fr;
        }
    }
</style>


<div class="page-header">

    <div>
        <h1>Inventory Details</h1>

        <p style="margin: 5px 0 0; color: #6b7280;">
            View current stock and complete transaction history.
        </p>
    </div>

    <div class="actions">

        @if (auth()->user()->hasRole('admin', 'manager', 'staff'))
        <a
            href="{{ route('inventories.edit', $inventory) }}"
            class="btn btn-secondary"
        >
            Edit
        </a>
        @endif

        <a
            href="{{ route('inventories.index') }}"
            class="btn btn-primary"
        >
            Back to Inventory
        </a>

    </div>

</div>


{{-- =========================
     CURRENT STOCK SUMMARY
========================= --}}

@php

    $baseQuantity = $inventory->getBaseQuantityValue();
    $reorderPoint = $inventory->getReorderPointValue();
    $stockStatus = $inventory->getStockStatus();
    $stockStatusClass = match ($stockStatus) {
        'Out of Stock' => 'status-empty',
        'Critical' => 'status-critical',
        'Low Stock' => 'status-low',
        default => 'status-ok',
    };

@endphp


<div class="inventory-summary">

    {{-- Product --}}

    <div class="summary-card">

        <div class="summary-label">
            Product
        </div>

        <div class="summary-value">

            {{ $inventory->product?->name ?? '-' }}

        </div>

        @if ($inventory->product?->code)

            <div class="summary-subtitle">
                {{ $inventory->product->code }}
            </div>

        @endif

    </div>


    {{-- Location --}}

    <div class="summary-card">

        <div class="summary-label">
            Location
        </div>

        <div class="summary-value">

            {{ $inventory->location?->name ?? '-' }}

        </div>

        @if ($inventory->location?->code)

            <div class="summary-subtitle">
                {{ $inventory->location->code }}
            </div>

        @endif

    </div>


    {{-- Current Stock --}}

    <div class="summary-card">

        <div class="summary-label">
            Current Stock
        </div>

        <div class="summary-value">

            {{ number_format(
                (float) $inventory->quantity,
                4
            ) }}

            @if ($inventory->productUnit?->unitOfMeasure)

                {{ $inventory->productUnit->unitOfMeasure->code }}

            @endif

        </div>

        <div class="summary-subtitle">

            {{ number_format($baseQuantity, 4) }}
            base units

        </div>

    </div>


    {{-- Status --}}

    <div class="summary-card">

        <div class="summary-label">
            Status
        </div>

        <div style="margin-top: 8px;">

            <span class="status-badge {{ $stockStatusClass }}">
                {{ $stockStatus }}
            </span>

        </div>

        <div class="summary-subtitle">

            Based on base quantity

            @if ($reorderPoint > 0)

                · Reorder point:
                {{ number_format($reorderPoint, 4) }}

            @endif

        </div>

    </div>

</div>


{{-- =========================
     INVENTORY INFORMATION
========================= --}}

<div class="table-wrapper">

    <table>

        <tbody>

            <tr>
                <th>ID</th>

                <td>
                    {{ $inventory->id }}
                </td>
            </tr>


            <tr>

                <th>
                    Product
                </th>

                <td>

                    {{ $inventory->product?->name ?? '-' }}

                    @if ($inventory->product?->code)

                        ({{ $inventory->product->code }})

                    @endif

                </td>

            </tr>


            <tr>

                <th>
                    Location
                </th>

                <td>

                    {{ $inventory->location?->name ?? '-' }}

                    @if ($inventory->location?->code)

                        ({{ $inventory->location->code }})

                    @endif

                </td>

            </tr>


            <tr>

                <th>
                    Current Unit
                </th>

                <td>

                    @if ($inventory->productUnit?->unitOfMeasure)

                        {{ $inventory->productUnit->unitOfMeasure->name }}

                        ({{ $inventory->productUnit->unitOfMeasure->code }})

                    @else

                        -

                    @endif

                </td>

            </tr>


            <tr>

                <th>
                    Current Quantity
                </th>

                <td>

                    <strong>

                        {{ number_format(
                            (float) $inventory->quantity,
                            4
                        ) }}

                    </strong>

                    @if ($inventory->productUnit?->unitOfMeasure)

                        {{ $inventory->productUnit->unitOfMeasure->code }}

                    @endif

                </td>

            </tr>


            <tr>

                <th>
                    Base Quantity
                </th>

                <td>

                    <strong>

                        {{ number_format(
                            (float) $inventory->base_quantity,
                            4
                        ) }}

                    </strong>

                    <small style="color: #666;">
                        Base units
                    </small>

                </td>

            </tr>


            <tr>

                <th>
                    Reorder Point
                </th>

                <td>

                    <strong>

                        {{ number_format(
                            $reorderPoint,
                            4
                        ) }}

                    </strong>

                    <small style="color: #666;">
                        Base units
                    </small>

                </td>

            </tr>


            <tr>

                <th>
                    Created
                </th>

                <td>

                    {{ $inventory->created_at?->format(
                        'Y-m-d H:i:s'
                    ) ?? '-' }}

                </td>

            </tr>


            <tr>

                <th>
                    Last Updated
                </th>

                <td>

                    {{ $inventory->updated_at?->format(
                        'Y-m-d H:i:s'
                    ) ?? '-' }}

                </td>

            </tr>

        </tbody>

    </table>

</div>


{{-- =========================
     TRANSACTION HISTORY
========================= --}}

<div style="margin-top: 30px;">

    <div class="page-header">

        <div>

            <h2>
                Transaction History
            </h2>

            <p style="margin: 5px 0 0; color: #6b7280;">
                Complete stock movements for this inventory record.
            </p>

        </div>

        <div>

            <strong>
                {{ $transactions->count() }}
            </strong>

            transaction(s)

        </div>

    </div>


    @if ($transactions->count())

        <div class="table-wrapper">

            <table>

                <thead>

                    <tr>

                        <th>
                            ID
                        </th>

                        <th>
                            Date
                        </th>

                        <th>
                            Type
                        </th>

                        <th>
                            Quantity
                        </th>

                        <th>
                            Base Quantity
                        </th>

                        <th>
                            Unit
                        </th>

                        <th>
                            Running Balance
                        </th>

                        <th>
                            Reference
                        </th>

                    </tr>

                </thead>


                <tbody>

                    @foreach ($transactions as $transaction)

                        @php

                            $transactionBase =
                                (float) $transaction->base_quantity;

                            $runningBalance =
                                (float) $transaction->running_balance;

                        @endphp


                        <tr>

                            {{-- ID --}}

                            <td>

                                <strong>
                                    #{{ $transaction->id }}
                                </strong>

                            </td>


                            {{-- DATE --}}

                            <td>

                                {{ $transaction->created_at?->format(
                                    'Y-m-d H:i:s'
                                ) ?? '-' }}

                            </td>


                            {{-- TYPE --}}

                            <td>

                                @if ($transaction->type === 'in')

                                    <span class="badge badge-in">
                                        IN
                                    </span>

                                @elseif ($transaction->type === 'out')

                                    <span class="badge badge-out">
                                        OUT
                                    </span>

                                @else

                                    <strong>
                                        {{ strtoupper(
                                            $transaction->type
                                        ) }}
                                    </strong>

                                @endif

                            </td>


                            {{-- QUANTITY --}}

                            <td>

                                <strong>

                                    {{ number_format(
                                        (float) $transaction->quantity,
                                        4
                                    ) }}

                                </strong>

                                @if ($transaction->productUnit?->unitOfMeasure)

                                    {{ $transaction
                                        ->productUnit
                                        ->unitOfMeasure
                                        ->code }}

                                @endif

                            </td>


                            {{-- BASE QUANTITY --}}

                            <td>

                                @if ($transaction->type === 'in')

                                    <span class="transaction-in">
                                        +
                                    </span>

                                @elseif ($transaction->type === 'out')

                                    <span class="transaction-out">
                                        -
                                    </span>

                                @endif


                                <strong>

                                    {{ number_format(
                                        $transactionBase,
                                        4
                                    ) }}

                                </strong>

                                <br>

                                <small style="color: #666;">
                                    Base units
                                </small>

                            </td>


                            {{-- UNIT --}}

                            <td>

                                @if ($transaction->productUnit?->unitOfMeasure)

                                    {{ $transaction
                                        ->productUnit
                                        ->unitOfMeasure
                                        ->name }}

                                    ({{ $transaction
                                        ->productUnit
                                        ->unitOfMeasure
                                        ->code }})

                                @else

                                    -

                                @endif

                            </td>


                            {{-- RUNNING BALANCE --}}

                            <td>

                                @if ($runningBalance > 0)

                                    <span class="balance-positive">

                                        {{ number_format(
                                            $runningBalance,
                                            4
                                        ) }}

                                    </span>

                                @else

                                    <span class="balance-zero">

                                        {{ number_format(
                                            $runningBalance,
                                            4
                                        ) }}

                                    </span>

                                @endif

                                <br>

                                <small style="color: #666;">
                                    Base units
                                </small>

                            </td>


                            {{-- REFERENCE --}}

                            <td>

                                @if ($transaction->reference)

                                    {{ $transaction->reference }}

                                @else

                                    <span class="reference-muted">
                                        -
                                    </span>

                                @endif

                            </td>

                        </tr>

                    @endforeach

                </tbody>

            </table>

        </div>

    @else

        <div class="empty-state">

            <p>
                No transactions found for this inventory.
            </p>

        </div>

    @endif

</div>


{{-- =========================
     DELETE
========================= --}}

@if (auth()->user()->isAdmin())
<div
    style="
        margin-top: 30px;
        padding-top: 20px;
        border-top: 1px solid #e5e7eb;
    "
>

    <form
        action="{{ route('inventories.destroy', $inventory) }}"
        method="POST"
    >

        @csrf
        @method('DELETE')

        <button
            type="submit"
            class="btn btn-danger"
            onclick="
                return confirm(
                    'Delete this inventory record? The remaining stock will be recorded as an OUT transaction.'
                )
            "
        >
            Delete Inventory
        </button>

    </form>

</div>
@endif

@endsection
