@extends('layouts.app')

@section('title', 'Purchase Order ' . $purchaseOrder->po_number)

@section('content')

<style> .po-show-page { max-width: 1180px; margin: 0 auto; padding: 20px; color: #334155; }
.po-show-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 20px;
    margin-bottom: 24px;
}

.po-show-title {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
}

.po-show-title h1 {
    margin: 0;
    font-size: 28px;
    color: #0f172a;
    font-weight: 700;
}

.po-show-subtitle {
    margin: 7px 0 0;
    color: #64748b;
    font-size: 14px;
}

/* Buttons */

.po-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 40px;
    padding: 9px 16px;
    border-radius: 7px;
    border: 1px solid transparent;
    font-size: 14px;
    font-weight: 600;
    text-decoration: none !important;
    cursor: pointer;
    box-sizing: border-box;
    transition: .15s ease;
}

.po-btn-primary {
    background: #2563eb;
    color: #fff !important;
    border-color: #2563eb;
}

.po-btn-primary:hover {
    background: #1d4ed8;
    border-color: #1d4ed8;
}

.po-btn-success {
    background: #15803d;
    color: #fff !important;
    border-color: #15803d;
}

.po-btn-success:hover {
    background: #166534;
    border-color: #166534;
}

.po-btn-danger {
    background: #dc2626;
    color: #fff !important;
    border-color: #dc2626;
}

.po-btn-danger:hover {
    background: #b91c1c;
    border-color: #b91c1c;
}

.po-btn-secondary {
    background: #e5e7eb;
    color: #111827 !important;
    border-color: #d1d5db;
}

.po-btn-secondary:hover {
    background: #d1d5db;
}

/* Alerts */

.po-alert-success {
    margin-bottom: 20px;
    padding: 13px 16px;
    border-radius: 7px;
    border: 1px solid #86efac;
    background: #f0fdf4;
    color: #166534;
    font-size: 14px;
}

.po-alert-error {
    margin-bottom: 20px;
    padding: 15px 18px;
    border-radius: 7px;
    border: 1px solid #fecaca;
    background: #fef2f2;
    color: #991b1b;
    font-size: 14px;
}

.po-alert-error ul {
    margin: 8px 0 0;
    padding-left: 20px;
}

/* Sections */

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
    font-weight: 700;
}

.po-section-header p {
    margin: 5px 0 0;
    color: #64748b;
    font-size: 13px;
}

.po-section-body {
    padding: 20px;
}

/* Information */

.po-info-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 20px;
}

.po-info-item {
    padding: 13px 15px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
}

.po-info-label {
    color: #64748b;
    font-size: 12px;
    margin-bottom: 5px;
}

.po-info-value {
    color: #0f172a;
    font-weight: 600;
    font-size: 14px;
}

/* Status */

.status-badge {
    display: inline-flex;
    align-items: center;
    padding: 5px 10px;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 700;
    text-transform: capitalize;
    white-space: nowrap;
}

.status-draft {
    background: #e2e8f0;
    color: #334155;
}

.status-pending {
    background: #fef3c7;
    color: #92400e;
}

.status-approved {
    background: #dcfce7;
    color: #166534;
}

.status-rejected {
    background: #fee2e2;
    color: #991b1b;
}

.status-ordered {
    background: #dbeafe;
    color: #1e40af;
}

.status-partial {
    background: #fef3c7;
    color: #92400e;
}

.status-completed {
    background: #dcfce7;
    color: #166534;
}

.status-cancelled {
    background: #fee2e2;
    color: #991b1b;
}

/* Table */

.order-table-wrapper {
    overflow-x: auto;
}

.order-table {
    width: 100%;
    min-width: 850px;
    border-collapse: collapse;
}

.order-table th {
    padding: 13px 15px;
    text-align: left;
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
    color: #475569;
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .03em;
    white-space: nowrap;
}

.order-table td {
    padding: 15px;
    border-bottom: 1px solid #f1f5f9;
    vertical-align: middle;
    color: #334155;
    font-size: 14px;
}

.order-table tbody tr:last-child td {
    border-bottom: none;
}

.product-name {
    font-weight: 700;
    color: #1e293b;
}

.product-sku {
    margin-top: 4px;
    color: #94a3b8;
    font-size: 12px;
}

.unit-name {
    font-weight: 600;
    color: #475569;
}

.unit-conversion {
    margin-top: 3px;
    color: #94a3b8;
    font-size: 11px;
}

.number-cell {
    white-space: nowrap;
}

/* Summary */

.summary {
    display: flex;
    justify-content: flex-end;
    margin-top: 20px;
}

.summary-box {
    width: 360px;
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
    font-size: 19px;
    font-weight: 700;
    color: #0f172a;
}

/* Notes */

.notes-box {
    padding: 15px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    color: #475569;
    white-space: pre-wrap;
    line-height: 1.6;
}

/* Empty */

.empty-state {
    padding: 35px;
    text-align: center;
    color: #64748b;
}

/* Actions */

.actions {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    margin-top: 20px;
}

/* Approval */

.approval-box {
    margin-top: 15px;
    padding: 15px;
    border-radius: 8px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    color: #475569;
}

.approval-box:first-child {
    margin-top: 0;
}

.approval-box strong {
    color: #334155;
}

/* Receiving History */

.receipt-card {
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    overflow: hidden;
    margin-bottom: 16px;
}

.receipt-card:last-child {
    margin-bottom: 0;
}

.receipt-card-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 15px;
    flex-wrap: wrap;
    padding: 13px 15px;
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
}

.receipt-card-header strong {
    color: #0f172a;
    font-size: 14px;
}

.receipt-meta {
    display: block;
    margin-top: 3px;
    color: #64748b;
    font-size: 12px;
}

.receipt-total-qty {
    display: inline-flex;
    align-items: center;
    padding: 4px 10px;
    border-radius: 999px;
    background: #dbeafe;
    color: #1e40af;
    font-size: 12px;
    font-weight: 700;
    white-space: nowrap;
}

.receipt-notes {
    padding: 12px 15px;
    border-top: 1px solid #f1f5f9;
    color: #475569;
    font-size: 13px;
    white-space: pre-wrap;
}

/* Activity */

.po-activity-timeline {
    position: relative;
    margin: 0;
    padding-left: 34px;
}

.po-activity-timeline::before {
    content: "";
    position: absolute;
    top: 5px;
    bottom: 5px;
    left: 9px;
    width: 2px;
    background: #e2e8f0;
}

.po-activity-item {
    position: relative;
    padding-bottom: 22px;
}

.po-activity-item:last-child {
    padding-bottom: 0;
}

.po-activity-dot {
    position: absolute;
    top: 4px;
    left: -30px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background: #2563eb;
    border: 3px solid #dbeafe;
    box-sizing: content-box;
}

.po-activity-content {
    padding: 13px 15px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
}

.po-activity-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 15px;
    flex-wrap: wrap;
}

.po-activity-header strong {
    color: #0f172a;
    font-size: 14px;
    font-weight: 700;
}

.po-activity-header span {
    color: #94a3b8;
    font-size: 12px;
    white-space: nowrap;
}

.po-activity-user {
    margin-top: 4px;
    color: #64748b;
    font-size: 12px;
}

.po-activity-description {
    margin-top: 9px;
    color: #475569;
    font-size: 13px;
    line-height: 1.5;
    white-space: pre-wrap;
}

.po-activity-metadata {
    margin-top: 10px;
    padding-top: 9px;
    border-top: 1px solid #e2e8f0;
    color: #64748b;
    font-size: 12px;
}

.po-activity-metadata-row {
    display: flex;
    gap: 8px;
    margin-top: 3px;
}

.po-activity-metadata-row:first-child {
    margin-top: 0;
}

.po-activity-metadata-key {
    font-weight: 600;
    color: #475569;
}

/* Modals */

.po-modal-overlay {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(15, 23, 42, .5);
    align-items: center;
    justify-content: center;
    z-index: 1000;
}

.po-modal-box {
    background: #fff;
    border-radius: 10px;
    padding: 20px;
    width: 100%;
    max-width: 420px;
    box-sizing: border-box;
    margin: 20px;
}

.po-modal-box h3 {
    margin: 0 0 5px;
    color: #0f172a;
    font-size: 17px;
}

.po-modal-box p {
    margin: 0 0 15px;
    color: #64748b;
    font-size: 13px;
}

.po-modal-box textarea {
    width: 100%;
    padding: 9px;
    border: 1px solid #cbd5e1;
    border-radius: 6px;
    box-sizing: border-box;
    font-family: inherit;
    font-size: 13px;
    resize: vertical;
}

.po-modal-actions {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    margin-top: 15px;
}

/* Responsive */

@media (max-width: 700px) {
    .po-show-page {
        padding: 15px;
    }

    .po-show-header {
        flex-direction: column;
    }

    .po-info-grid {
        grid-template-columns: 1fr;
    }

    .po-section-body {
        padding: 15px;
    }

    .po-show-title h1 {
        font-size: 24px;
    }

    .po-activity-header {
        align-items: flex-start;
        flex-direction: column;
        gap: 4px;
    }

    .po-activity-header span {
        white-space: normal;
    }
}

</style> <div class="po-show-page">
{{-- HEADER --}}
<div class="po-show-header">

    <div>
        <div class="po-show-title">

            <h1>{{ $purchaseOrder->po_number }}</h1>

            @php
                $statusClass = match ($purchaseOrder->status) {
                    'draft' => 'status-draft',
                    'pending_approval' => 'status-pending',
                    'approved' => 'status-approved',
                    'rejected' => 'status-rejected',
                    'ordered' => 'status-ordered',
                    'partially_received' => 'status-partial',
                    'received', 'completed' => 'status-completed',
                    'cancelled' => 'status-cancelled',
                    default => 'status-draft',
                };
            @endphp

            <span class="status-badge {{ $statusClass }}">
                {{ str_replace('_', ' ', $purchaseOrder->status) }}
            </span>

        </div>

        <p class="po-show-subtitle">
            Purchase Order details and item summary.
        </p>
    </div>

    <div style="display:flex; gap:10px; flex-wrap:wrap;">

        <a href="{{ route('purchase-orders.pdf', $purchaseOrder) }}" class="po-btn po-btn-secondary">
            Download PDF
        </a>

        <a href="{{ route('purchase-orders.email.compose', $purchaseOrder) }}" class="po-btn po-btn-primary">
            Send Email
        </a>

        <a href="{{ route('purchase-orders.index') }}" class="po-btn po-btn-secondary">
            Back to Purchase Orders
        </a>

    </div>

</div>

{{-- SUCCESS --}}

{{-- ERRORS --}}
@if ($errors->any())
    <div class="po-alert-error">

        <strong>Please fix the following:</strong>

        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>

    </div>
@endif

{{-- PURCHASE ORDER DETAILS --}}
<div class="po-section">

    <div class="po-section-header">
        <h2>Purchase Order Details</h2>
        <p>Supplier, receiving location and order information.</p>
    </div>

    <div class="po-section-body">

        <div class="po-info-grid">

            <div class="po-info-item">
                <div class="po-info-label">Supplier</div>
                <div class="po-info-value">
                    {{ $purchaseOrder->supplier?->name ?? '—' }}
                </div>
            </div>

            <div class="po-info-item">
                <div class="po-info-label">Receiving Location</div>

                <div class="po-info-value">
                    {{ $purchaseOrder->location?->name ?? '—' }}

                    @if ($purchaseOrder->location?->company)
                        — {{ $purchaseOrder->location->company->name }}
                    @endif
                </div>
            </div>

            <div class="po-info-item">
                <div class="po-info-label">Expected Delivery</div>

                <div class="po-info-value">
                    @if ($purchaseOrder->expected_delivery_date)
                        {{ format_date($purchaseOrder->expected_delivery_date) }}
                    @else
                        —
                    @endif
                </div>
            </div>

            <div class="po-info-item">
                <div class="po-info-label">Supplier Reference</div>

                <div class="po-info-value">
                    {{ $purchaseOrder->reference ?: '—' }}
                </div>
            </div>

            <div class="po-info-item">
                <div class="po-info-label">Created By</div>

                <div class="po-info-value">
                    {{ $purchaseOrder->createdBy?->name ?? '—' }}
                </div>
            </div>

            <div class="po-info-item">
                <div class="po-info-label">Created</div>

                <div class="po-info-value">
                    {{ format_datetime($purchaseOrder->created_at) ?? '—' }}
                </div>
            </div>

        </div>

    </div>
</div>

{{-- ORDER ITEMS --}}
<div class="po-section">

    <div class="po-section-header">
        <h2>Order Items</h2>
        <p>Products and quantities included in this purchase order.</p>
    </div>

    <div class="po-section-body" style="padding:0;">

        @if ($purchaseOrder->items->count())

            <div class="order-table-wrapper">

                <table class="order-table">

                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Unit</th>
                            <th>Ordered</th>
                            <th>Received</th>
                            <th>Remaining</th>
                            <th>Unit Price</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>

                    <tbody>

                        @foreach ($purchaseOrder->items as $item)

                            <tr>

                                <td>
                                    <div style="display:flex; align-items:center; gap:10px;">

                                        @if ($item->product?->image_url)
                                            <img
                                                src="{{ $item->product->image_url }}"
                                                alt="{{ $item->product->name }}"
                                                style="width:36px; height:36px; object-fit:cover; border-radius:6px; border:1px solid #e2e8f0; flex-shrink:0;"
                                            >
                                        @endif

                                        <div>
                                            <div class="product-name">
                                                {{ $item->product?->name ?? 'Unknown Product' }}
                                            </div>
                                        </div>

                                    </div>

                                    @if ($item->product?->sku)
                                        <div class="product-sku">
                                            SKU: {{ $item->product->sku }}
                                        </div>
                                    @endif
                                </td>

                                <td>
                                    <div class="unit-name">
                                        {{ $item->productUnit?->unitOfMeasure?->name ?? 'Unit' }}
                                    </div>

                                    @if ($item->productUnit?->conversion_factor)
                                        <div class="unit-conversion">
                                            {{ format_qty($item->productUnit->conversion_factor) }} x base unit
                                        </div>
                                    @endif
                                </td>

                                <td class="number-cell">
                                    {{ rtrim(rtrim(format_qty((float) $item->quantity_ordered), '0'), '.') }}
                                </td>

                                <td class="number-cell">
                                    {{ rtrim(rtrim(format_qty((float) $item->quantity_received), '0'), '.') }}
                                </td>

                                <td class="number-cell">
                                    {{ rtrim(rtrim(format_qty((float) $item->remaining_quantity), '0'), '.') }}
                                </td>

                                <td class="number-cell">
                                    ₱{{ number_format((float) $item->unit_price, 2) }}
                                </td>

                                <td class="number-cell">
                                    ₱{{ number_format((float) $item->subtotal, 2) }}
                                </td>

                            </tr>

                        @endforeach

                    </tbody>

                </table>

            </div>

            <div class="summary" style="padding:0 20px 20px;">

                <div class="summary-box">

                    <div class="summary-row">
                        <span>Items</span>
                        <strong>{{ $purchaseOrder->items->count() }}</strong>
                    </div>

                    <div class="summary-row">
                        <span>Total Quantity</span>

                        <strong>
                            {{ rtrim(rtrim(format_qty($purchaseOrder->items->sum('quantity_ordered')), '0'), '.') }}
                        </strong>
                    </div>

                    <div class="summary-row total">
                        <span>Order Total</span>
                        <span>₱{{ number_format((float) $purchaseOrder->total, 2) }}</span>
                    </div>

                </div>

            </div>

        @else

            <div class="empty-state">
                No items found on this purchase order.
            </div>

        @endif

    </div>
</div>

{{-- NOTES --}}
@if ($purchaseOrder->notes)

    <div class="po-section">

        <div class="po-section-header">
            <h2>Notes</h2>
        </div>

        <div class="po-section-body">
            <div class="notes-box">
                {{ $purchaseOrder->notes }}
            </div>
        </div>

    </div>

@endif

{{-- APPROVAL INFORMATION --}}
@if (
    $purchaseOrder->approvedBy ||
    $purchaseOrder->approval_notes ||
    $purchaseOrder->rejection_reason
)

    <div class="po-section">

        <div class="po-section-header">
            <h2>Approval Information</h2>
        </div>

        <div class="po-section-body">

            @if ($purchaseOrder->approvedBy)

                <div class="approval-box">

                    <strong>Approved By:</strong>

                    {{ $purchaseOrder->approvedBy->name }}

                    @if ($purchaseOrder->approved_at)
                        <span style="color:#64748b;">
                            on {{ format_datetime($purchaseOrder->approved_at) }}
                        </span>
                    @endif

                </div>

            @endif

            @if ($purchaseOrder->approval_notes)

                <div class="approval-box">

                    <strong>Approval Notes</strong>

                    <div style="margin-top:7px;">
                        {{ $purchaseOrder->approval_notes }}
                    </div>

                </div>

            @endif

            @if ($purchaseOrder->rejection_reason)

                <div class="approval-box">

                    <strong>Rejection Reason</strong>

                    <div style="margin-top:7px;">
                        {{ $purchaseOrder->rejection_reason }}
                    </div>

                </div>

            @endif

        </div>

    </div>

@endif

{{-- RECEIVING HISTORY --}}
@if ($purchaseOrder->receipts->count())

    <div class="po-section">

        <div class="po-section-header">

            <h2>Receiving History</h2>

            <p>
                Each delivery recorded against this purchase order.
            </p>

        </div>

        <div class="po-section-body">

            @foreach ($purchaseOrder->receipts->sortByDesc('received_at') as $receipt)

                <div class="receipt-card">

                    <div class="receipt-card-header">

                        <div>
                            <strong>
                                Delivery #{{ $purchaseOrder->receipts->count() - $loop->index }}
                            </strong>

                            <span class="receipt-meta">
                                {{ format_datetime($receipt->received_at) ?? '—' }}
                                &middot; Received by {{ $receipt->receivedBy?->name ?? 'System' }}
                            </span>
                        </div>

                        <span class="receipt-total-qty">
                            {{ rtrim(rtrim(format_qty($receipt->total_quantity), '0'), '.') }} units
                        </span>

                    </div>

                    <div class="order-table-wrapper">

                        <table class="order-table">

                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Unit</th>
                                    <th>Quantity Received</th>
                                    <th>Notes</th>
                                </tr>
                            </thead>

                            <tbody>

                                @foreach ($receipt->items as $receiptItem)

                                    <tr>
                                        <td>
                                            {{ $receiptItem->purchaseOrderItem?->product?->name ?? 'Unknown Product' }}
                                        </td>

                                        <td>
                                            {{ $receiptItem->purchaseOrderItem?->productUnit?->unitOfMeasure?->name ?? 'Unit' }}
                                        </td>

                                        <td class="number-cell">
                                            {{ rtrim(rtrim(format_qty((float) $receiptItem->quantity_received), '0'), '.') }}
                                        </td>

                                        <td>
                                            {{ $receiptItem->notes ?: '—' }}
                                        </td>
                                    </tr>

                                @endforeach

                            </tbody>

                        </table>

                    </div>

                    @if ($receipt->notes)

                        <div class="receipt-notes">
                            {{ $receipt->notes }}
                        </div>

                    @endif

                </div>

            @endforeach

        </div>

    </div>

@endif

{{-- ACTIVITY TIMELINE --}}
<div class="po-section">

    <div class="po-section-header">

        <h2>Activity</h2>

        <p>
            History of actions performed on this purchase order.
        </p>

    </div>

    <div class="po-section-body">

        @if ($purchaseOrder->activityLogs->count())

            <div class="po-activity-timeline">

                @foreach ($purchaseOrder->activityLogs->sortByDesc('created_at') as $activity)

                    <div class="po-activity-item">

                        <div class="po-activity-dot"></div>

                        <div class="po-activity-content">

                            <div class="po-activity-header">

                                <strong>
                                    {{ ucwords(str_replace('_', ' ', $activity->action)) }}
                                </strong>

                                <span>
                                    {{ format_datetime($activity->created_at) ?? '—' }}
                                </span>

                            </div>

                            <div class="po-activity-user">
                                By: {{ $activity->user?->name ?? 'System' }}
                            </div>

                            @if ($activity->description)

                                <div class="po-activity-description">
                                    {{ $activity->description }}
                                </div>

                            @endif

                            @if (is_array($activity->metadata) && count($activity->metadata))

                                <div class="po-activity-metadata">

                                    @foreach ($activity->metadata as $key => $value)

                                        @if ($key === 'lines' && is_array($value))

                                            <div class="po-activity-metadata-row" style="align-items: flex-start;">

                                                <span class="po-activity-metadata-key">
                                                    Items Received:
                                                </span>

                                                <span>

                                                    @foreach ($value as $line)

                                                        @php
                                                            $lineProduct = $purchaseOrder->items
                                                                ->firstWhere('id', $line['purchase_order_item_id'] ?? null)
                                                                ?->product;
                                                        @endphp

                                                        <div>
                                                            {{ $lineProduct->name ?? ('Product #' . ($line['product_id'] ?? '?')) }}
                                                            —
                                                            {{ $line['quantity_received'] ?? 0 }} received
                                                        </div>

                                                    @endforeach

                                                </span>

                                            </div>

                                        @else

                                            <div class="po-activity-metadata-row">

                                                <span class="po-activity-metadata-key">
                                                    {{ ucwords(str_replace('_', ' ', $key)) }}:
                                                </span>

                                                <span>

                                                    @if (is_array($value))

                                                        {{ json_encode($value) }}

                                                    @elseif (is_bool($value))

                                                        {{ $value ? 'Yes' : 'No' }}

                                                    @else

                                                        {{ $value }}

                                                    @endif

                                                </span>

                                            </div>

                                        @endif

                                    @endforeach

                                </div>

                            @endif

                        </div>

                    </div>

                @endforeach

            </div>

        @else

            <div class="empty-state">
                No activity has been recorded for this purchase order yet.
            </div>

        @endif

    </div>

</div>

{{-- PURCHASE ORDER ACTIONS --}}
<div class="po-section">

    <div class="po-section-body">

        <h2 style="margin:0 0 5px;color:#0f172a;font-size:18px;">
            Purchase Order Actions
        </h2>

        <p style="margin:0;color:#64748b;font-size:13px;">
            Available actions depend on the current purchase order status.
        </p>

        <div class="actions">

            {{-- DRAFT --}}
            @if ($purchaseOrder->status === 'draft')

                <form
                    method="POST"
                    action="{{ route('purchase-orders.submit', $purchaseOrder) }}"
                >

                    @csrf
                    @method('PATCH')

                    <button type="submit" class="po-btn po-btn-primary">
                        Submit for Approval
                    </button>

                </form>

            @endif

            {{-- PENDING APPROVAL --}}
            @if ($purchaseOrder->status === 'pending_approval')

                <button
                    type="button"
                    class="po-btn po-btn-success"
                    onclick="document.getElementById('approve-modal').style.display='flex'"
                >
                    Approve
                </button>

                <button
                    type="button"
                    class="po-btn po-btn-danger"
                    onclick="document.getElementById('reject-modal').style.display='flex'"
                >
                    Reject
                </button>

            @endif

            {{-- APPROVED --}}
            @if ($purchaseOrder->status === 'approved')

                <form
                    method="POST"
                    action="{{ route('purchase-orders.mark-ordered', $purchaseOrder) }}"
                >

                    @csrf
                    @method('PATCH')

                    <button type="submit" class="po-btn po-btn-primary">
                        Mark as Ordered
                    </button>

                </form>

            @endif

            {{-- ORDERED / PARTIALLY RECEIVED --}}
            @if (
                in_array(
                    $purchaseOrder->status,
                    ['ordered', 'partially_received'],
                    true
                )
            )

                <a
                    href="{{ route('purchase-orders.receive.form', $purchaseOrder) }}"
                    class="po-btn po-btn-success"
                >
                    Receive Goods
                </a>

            @endif

        </div>

    </div>

</div>

{{-- APPROVE + REJECT MODALS --}}
@if ($purchaseOrder->status === 'pending_approval')

    <div id="approve-modal" class="po-modal-overlay">

        <div class="po-modal-box">

            <h3>Approve Purchase Order</h3>

            <p>
                Optionally add a note for this approval.
            </p>

            <form
                method="POST"
                action="{{ route('purchase-orders.approve', $purchaseOrder) }}"
            >

                @csrf
                @method('PATCH')

                <textarea
                    name="approval_notes"
                    rows="3"
                    placeholder="Optional approval notes..."
                ></textarea>

                <div class="po-modal-actions">

                    <button
                        type="button"
                        class="po-btn po-btn-secondary"
                        onclick="document.getElementById('approve-modal').style.display='none'"
                    >
                        Cancel
                    </button>

                    <button
                        type="submit"
                        class="po-btn po-btn-success"
                    >
                        Confirm Approval
                    </button>

                </div>

            </form>

        </div>

    </div>

    <div id="reject-modal" class="po-modal-overlay">

        <div class="po-modal-box">

            <h3>Reject Purchase Order</h3>

            <p>
                Please explain why this purchase order is being rejected.
            </p>

            <form
                method="POST"
                action="{{ route('purchase-orders.reject', $purchaseOrder) }}"
            >

                @csrf
                @method('PATCH')

                <textarea
                    name="rejection_reason"
                    rows="3"
                    placeholder="Reason for rejection (required)..."
                    required
                ></textarea>

                <div class="po-modal-actions">

                    <button
                        type="button"
                        class="po-btn po-btn-secondary"
                        onclick="document.getElementById('reject-modal').style.display='none'"
                    >
                        Cancel
                    </button>

                    <button
                        type="submit"
                        class="po-btn po-btn-danger"
                    >
                        Confirm Rejection
                    </button>

                </div>

            </form>

        </div>

    </div>

@endif

</div>
@endsection