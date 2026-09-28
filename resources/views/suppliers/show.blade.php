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
            <a href="{{ route('suppliers.edit', $supplier) }}" class="btn btn-secondary">
                Edit
            </a>
        @endif

        <a href="{{ route('suppliers.index') }}" class="btn btn-secondary">
            Back to Suppliers
        </a>
    </div>
</div>


{{-- HERO --}}
<div class="entity-hero">

    <span class="entity-hero-avatar">
        {{ strtoupper(substr($supplier->name, 0, 1)) }}
    </span>

    <div class="entity-hero-body">

        <div class="entity-hero-top">
            @if ($supplier->is_active)
                <span class="status-badge status-active">Active</span>
            @else
                <span class="status-badge status-inactive">Inactive</span>
            @endif

            @if ($supplier->company)
                <span class="company-pill">{{ $supplier->company->name }}</span>
            @endif
        </div>

        <h2 class="entity-hero-name">{{ $supplier->name }}</h2>

        <div class="entity-hero-meta">

            <div class="meta-item">
                <span class="meta-label">Contact Name</span>
                <span class="meta-value">{{ $supplier->contact_name ?: '—' }}</span>
            </div>

            <div class="meta-item">
                <span class="meta-label">Phone</span>
                <span class="meta-value">{{ $supplier->phone ?: '—' }}</span>
            </div>

            <div class="meta-item">
                <span class="meta-label">Email</span>
                <span class="meta-value">{{ $supplier->email ?: '—' }}</span>
            </div>

            <div class="meta-item" style="grid-column: 1 / -1;">
                <span class="meta-label">Address</span>
                <span class="meta-value">{{ $supplier->address ?: '—' }}</span>
            </div>

        </div>

    </div>

</div>


{{-- PURCHASE ORDER HISTORY --}}
<div class="card section-card">

    <h2 class="section-title">Purchase Order History</h2>

    <p style="margin: -8px 0 15px; color: #64748b; font-size: 13px;">
        Purchase orders associated with this supplier.
    </p>

    @if ($purchaseOrders->count())

        <div class="table-wrapper" style="border: none; box-shadow: none;">
            <table>
                <thead>
                    <tr>
                        <th>PO Number</th>
                        <th>Status</th>
                        <th>Location</th>
                        <th>Expected Delivery</th>
                        <th>Created</th>
                        <th style="text-align: right;">Action</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($purchaseOrders as $purchaseOrder)
                        <tr>
                            <td><strong>{{ $purchaseOrder->po_number }}</strong></td>

                            <td>
                                <span class="code-pill">
                                    {{ ucwords(str_replace('_', ' ', $purchaseOrder->status)) }}
                                </span>
                            </td>

                            <td>{{ $purchaseOrder->location->name ?? '—' }}</td>

                            <td>
                                {{ format_date($purchaseOrder->expected_delivery_date) ?? '—' }}
                            </td>

                            <td>{{ format_date($purchaseOrder->created_at) }}</td>

                            <td style="text-align: right;">
                                <a href="{{ route('purchase-orders.show', $purchaseOrder) }}" class="btn btn-secondary">
                                    View
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if ($purchaseOrders->hasPages())
            <div style="margin-top: 15px;">
                {{ $purchaseOrders->onEachSide(1)->links() }}
            </div>
        @endif

    @else

        <div class="empty-state" style="box-shadow: none;">
            <p><strong>No purchase orders yet.</strong><br>Purchase orders for this supplier will appear here.</p>
        </div>

    @endif

</div>


<style>

    .entity-hero {
        display: flex;
        gap: 22px;
        align-items: flex-start;
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        box-shadow: 0 4px 16px rgba(15, 23, 42, .05);
        padding: 26px;
        margin-bottom: 20px;
    }

    .entity-hero-avatar {
        flex-shrink: 0;
        width: 64px;
        height: 64px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #7c3aed, #a78bfa);
        color: #fff;
        font-size: 26px;
        font-weight: 800;
    }

    .entity-hero-body {
        flex: 1;
        min-width: 0;
    }

    .entity-hero-top {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 10px;
    }

    .entity-hero-name {
        margin: 0 0 16px;
        font-size: 22px;
        color: #0f172a;
        font-weight: 800;
    }

    .entity-hero-meta {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 14px;
    }

    .meta-item {
        display: flex;
        flex-direction: column;
        gap: 5px;
        padding: 12px 14px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
    }

    .meta-label {
        color: #94a3b8;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .04em;
    }

    .meta-value {
        color: #0f172a;
        font-size: 14px;
        font-weight: 700;
    }

    .company-pill {
        display: inline-flex;
        padding: 4px 10px;
        border-radius: 999px;
        background: #eff6ff;
        color: #1d4ed8;
        font-size: 12px;
        font-weight: 700;
    }

    .code-pill {
        display: inline-flex;
        padding: 4px 9px;
        background: #f1f5f9;
        color: #334155;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 600;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        padding: 5px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 700;
        white-space: nowrap;
    }

    .status-active {
        background: #dcfce7;
        color: #166534;
    }

    .status-inactive {
        background: #fee2e2;
        color: #991b1b;
    }

    .section-card {
        margin-top: 20px;
    }

    .section-title {
        margin: 0 0 4px;
        font-size: 18px;
        color: #0f172a;
    }

    @media (max-width: 700px) {

        .entity-hero {
            flex-direction: column;
        }

        .entity-hero-meta {
            grid-template-columns: 1fr;
        }

    }

</style>

@endsection
