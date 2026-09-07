@extends('layouts.app')

@section('title', 'Supplier Details')

@section('content')

<div class="page-header">
<div>
    <h1>{{ $supplier->name }}</h1>

    <p style="margin: 6px 0 0; color: #64748b;">
        Supplier #{{ $supplier->id }}
    </p>
</div>

<div style="display: flex; gap: 8px; flex-wrap: wrap;">

    @if (auth()->user()->hasRole('admin'))
        <a
            href="{{ route('suppliers.edit', $supplier) }}"
            class="btn btn-secondary"
        >
            Edit
        </a>
    @endif

    <a
        href="{{ route('suppliers.index') }}"
        class="btn btn-secondary"
    >
        Back to Suppliers
    </a>

</div>

</div>

{{-- =========================
SUPPLIER INFORMATION
========================== --}}

<div class="card">
<h2>Supplier Information</h2>

<div
    style="
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 20px;
        margin-top: 20px;
    "
>

    <div>
        <div style="font-size: 12px; color: #64748b;">
            Supplier Name
        </div>

        <div style="font-weight: 600; margin-top: 4px;">
            {{ $supplier->name }}
        </div>
    </div>


    <div>
        <div style="font-size: 12px; color: #64748b;">
            Company
        </div>

        <div style="font-weight: 600; margin-top: 4px;">
            {{ $supplier->company->name ?? '—' }}
        </div>
    </div>


    <div>
        <div style="font-size: 12px; color: #64748b;">
            Contact Name
        </div>

        <div style="margin-top: 4px;">
            {{ $supplier->contact_name ?: '—' }}
        </div>
    </div>


    <div>
        <div style="font-size: 12px; color: #64748b;">
            Phone
        </div>

        <div style="margin-top: 4px;">
            {{ $supplier->phone ?: '—' }}
        </div>
    </div>


    <div>
        <div style="font-size: 12px; color: #64748b;">
            Email
        </div>

        <div style="margin-top: 4px;">
            {{ $supplier->email ?: '—' }}
        </div>
    </div>


    <div>
        <div style="font-size: 12px; color: #64748b;">
            Status
        </div>

        <div style="margin-top: 4px;">

            @if ($supplier->is_active)

                <span class="status-badge status-active">
                    Active
                </span>

            @else

                <span class="status-badge status-inactive">
                    Inactive
                </span>

            @endif

        </div>
    </div>


    <div style="grid-column: 1 / -1;">

        <div style="font-size: 12px; color: #64748b;">
            Address
        </div>

        <div style="margin-top: 4px;">
            {{ $supplier->address ?: '—' }}
        </div>

    </div>

</div>

</div>

{{-- =========================
PURCHASE ORDER HISTORY
========================== --}}

<div class="card" style="margin-top: 20px;">
<div
    style="
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
        margin-bottom: 15px;
    "
>
    <div>
        <h2 style="margin: 0;">
            Purchase Order History
        </h2>

        <p
            style="
                margin: 5px 0 0;
                color: #64748b;
                font-size: 13px;
            "
        >
            Purchase orders associated with this supplier.
        </p>
    </div>
</div>


@if ($purchaseOrders->count())

    <div style="overflow-x: auto;">

        <table
            style="
                width: 100%;
                border-collapse: collapse;
                text-align: left;
            "
        >

            <thead>

                <tr
                    style="
                        background: #f8fafc;
                        border-bottom: 2px solid #e2e8f0;
                    "
                >

                    <th style="padding: 10px;">
                        PO Number
                    </th>

                    <th style="padding: 10px;">
                        Status
                    </th>

                    <th style="padding: 10px;">
                        Location
                    </th>

                    <th style="padding: 10px;">
                        Expected Delivery
                    </th>

                    <th style="padding: 10px;">
                        Created
                    </th>

                    <th style="padding: 10px; text-align: right;">
                        Action
                    </th>

                </tr>

            </thead>

            <tbody>

                @foreach ($purchaseOrders as $purchaseOrder)

                    <tr
                        style="
                            border-bottom: 1px solid #e2e8f0;
                        "
                    >

                        <td style="padding: 12px;">

                            <strong>
                                {{ $purchaseOrder->po_number }}
                            </strong>

                        </td>


                        <td style="padding: 12px;">

                            <span
                                style="
                                    display: inline-block;
                                    padding: 4px 9px;
                                    background: #f1f5f9;
                                    color: #334155;
                                    border-radius: 999px;
                                    font-size: 12px;
                                    font-weight: 600;
                                "
                            >
                                {{ ucwords(str_replace('_', ' ', $purchaseOrder->status)) }}
                            </span>

                        </td>


                        <td style="padding: 12px;">

                            {{ $purchaseOrder->location->name ?? '—' }}

                        </td>


                        <td style="padding: 12px;">

                            @if ($purchaseOrder->expected_delivery_date)
                                {{ $purchaseOrder->expected_delivery_date->format('M d, Y') }}
                            @else
                                —
                            @endif

                        </td>


                        <td style="padding: 12px;">

                            {{ $purchaseOrder->created_at->format('M d, Y') }}

                        </td>


                        <td
                            style="
                                padding: 12px;
                                text-align: right;
                            "
                        >

                            <a
                                href="{{ route('purchase-orders.show', $purchaseOrder) }}"
                                class="btn btn-secondary"
                                style="text-decoration: none;"
                            >
                                View
                            </a>

                        </td>

                    </tr>

                @endforeach

            </tbody>

        </table>

    </div>


    {{-- Pagination --}}

    @if ($purchaseOrders->hasPages())

        <div
            style="
                margin-top: 20px;
                display: flex;
                justify-content: center;
            "
        >
            {{ $purchaseOrders->onEachSide(1)->links() }}
        </div>

    @endif

@else

    <div
        style="
            padding: 40px 20px;
            text-align: center;
            color: #64748b;
            background: #f8fafc;
            border-radius: 6px;
        "
    >

        <div
            style="
                font-weight: 600;
                color: #334155;
                margin-bottom: 6px;
            "
        >
            No purchase orders yet.
        </div>

        <div style="font-size: 13px;">
            Purchase orders for this supplier will appear here.
        </div>

    </div>

@endif

</div> <style> .status-badge { display: inline-flex; align-items: center; padding: 5px 10px; border-radius: 999px; font-size: 12px; font-weight: 700; white-space: nowrap; } .status-active { background: #dcfce7; color: #166534; } .status-inactive { background: #f1f5f9; color: #64748b; } </style>

@endsection