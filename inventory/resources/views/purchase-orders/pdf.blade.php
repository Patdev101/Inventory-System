<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $purchaseOrder->po_number }}</title>

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            font-family: "DejaVu Sans", sans-serif;
            color: #1e293b;
            font-size: 12px;
            margin: 0;
            padding: 30px;
        }

        .doc-header {
            display: table;
            width: 100%;
            margin-bottom: 24px;
        }

        .doc-header-left,
        .doc-header-right {
            display: table-cell;
            vertical-align: top;
        }

        .doc-header-right {
            text-align: right;
        }

        .doc-title {
            font-size: 22px;
            font-weight: bold;
            color: #0f172a;
            margin: 0 0 4px;
        }

        .doc-subtitle {
            color: #64748b;
            font-size: 11px;
        }

        .po-number {
            font-size: 16px;
            font-weight: bold;
            color: #0f172a;
        }

        .status-badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 10px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            background: #e2e8f0;
            color: #334155;
            margin-top: 4px;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 22px;
        }

        .info-table td {
            vertical-align: top;
            padding: 10px 12px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            width: 50%;
        }

        .info-label {
            display: block;
            color: #64748b;
            font-size: 9px;
            text-transform: uppercase;
            margin-bottom: 3px;
        }

        .info-value {
            font-weight: bold;
            color: #0f172a;
        }

        table.items {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 18px;
        }

        table.items th {
            background: #f1f5f9;
            border: 1px solid #e2e8f0;
            padding: 7px 8px;
            text-align: left;
            font-size: 10px;
            text-transform: uppercase;
            color: #475569;
        }

        table.items td {
            border: 1px solid #e2e8f0;
            padding: 7px 8px;
            font-size: 11px;
        }

        .text-right {
            text-align: right;
        }

        .totals {
            width: 260px;
            margin-left: auto;
            margin-bottom: 24px;
        }

        .totals-row {
            display: table;
            width: 100%;
            padding: 4px 0;
        }

        .totals-row span {
            display: table-cell;
        }

        .totals-row .label {
            color: #64748b;
        }

        .totals-row .value {
            text-align: right;
            font-weight: bold;
        }

        .totals-row.grand-total {
            border-top: 2px solid #cbd5e1;
            margin-top: 6px;
            padding-top: 8px;
            font-size: 14px;
        }

        .notes-box {
            border: 1px solid #e2e8f0;
            background: #f8fafc;
            padding: 10px 12px;
            margin-bottom: 20px;
            white-space: pre-wrap;
        }

        .section-title {
            font-size: 11px;
            text-transform: uppercase;
            color: #64748b;
            margin-bottom: 6px;
            font-weight: bold;
        }

        .signatures {
            width: 100%;
            margin-top: 50px;
        }

        .signatures td {
            width: 33.33%;
            text-align: center;
            padding-top: 30px;
            border-top: 1px solid #94a3b8;
            font-size: 10px;
            color: #475569;
        }

        .signatures-spacer td {
            border-top: none;
            padding-top: 0;
        }

        .footer-note {
            margin-top: 30px;
            font-size: 9px;
            color: #94a3b8;
            text-align: center;
        }

    </style>
</head>
<body>

    <div class="doc-header">

        <div class="doc-header-left">
            <p class="doc-title">Purchase Order</p>
            <p class="doc-subtitle">
                {{ $purchaseOrder->location?->company?->name ?? config('app.name') }}
            </p>
        </div>

        <div class="doc-header-right">
            <div class="po-number">{{ $purchaseOrder->po_number }}</div>
            <span class="status-badge">
                {{ str_replace('_', ' ', $purchaseOrder->status) }}
            </span>
        </div>

    </div>


    <table class="info-table">
        <tr>
            <td>
                <span class="info-label">Supplier</span>
                <span class="info-value">{{ $purchaseOrder->supplier?->name ?? '-' }}</span>
            </td>
            <td>
                <span class="info-label">Receiving Location</span>
                <span class="info-value">
                    {{ $purchaseOrder->location?->name ?? '-' }}
                    @if ($purchaseOrder->location?->company)
                        &mdash; {{ $purchaseOrder->location->company->name }}
                    @endif
                </span>
            </td>
        </tr>
        <tr>
            <td>
                <span class="info-label">Order Date</span>
                <span class="info-value">
                    {{ format_date($purchaseOrder->created_at) ?? '-' }}
                </span>
            </td>
            <td>
                <span class="info-label">Expected Delivery</span>
                <span class="info-value">
                    {{ format_date($purchaseOrder->expected_delivery_date) ?? '-' }}
                </span>
            </td>
        </tr>
        <tr>
            <td>
                <span class="info-label">Supplier Reference</span>
                <span class="info-value">{{ $purchaseOrder->reference ?: '-' }}</span>
            </td>
            <td>
                <span class="info-label">Prepared By</span>
                <span class="info-value">{{ $purchaseOrder->createdBy?->name ?? '-' }}</span>
            </td>
        </tr>
    </table>


    <p class="section-title">Order Items</p>

    <table class="items">
        <thead>
            <tr>
                <th>Product</th>
                <th>Unit</th>
                <th class="text-right">Ordered</th>
                <th class="text-right">Unit Price</th>
                <th class="text-right">Subtotal</th>
            </tr>
        </thead>
        <tbody>

            @foreach ($purchaseOrder->items as $item)

                <tr>
                    <td>
                        {{ $item->product?->name ?? 'Unknown Product' }}
                        @if ($item->product?->sku)
                            <br><span style="color:#94a3b8;font-size:9px;">SKU: {{ $item->product->sku }}</span>
                        @endif
                    </td>
                    <td>{{ $item->productUnit?->unitOfMeasure?->name ?? 'Unit' }}</td>
                    <td class="text-right">
                        {{ rtrim(rtrim(format_qty((float) $item->quantity_ordered), '0'), '.') }}
                    </td>
                    <td class="text-right">
                        &#8369;{{ number_format((float) $item->unit_price, 2) }}
                    </td>
                    <td class="text-right">
                        &#8369;{{ number_format((float) $item->subtotal, 2) }}
                    </td>
                </tr>

            @endforeach

        </tbody>
    </table>


    <div class="totals">

        <div class="totals-row">
            <span class="label">Items</span>
            <span class="value">{{ $purchaseOrder->items->count() }}</span>
        </div>

        <div class="totals-row grand-total">
            <span class="label">Total</span>
            <span class="value">&#8369;{{ number_format((float) $purchaseOrder->total, 2) }}</span>
        </div>

    </div>


    @if ($purchaseOrder->notes)

        <p class="section-title">Notes</p>

        <div class="notes-box">
            {{ $purchaseOrder->notes }}
        </div>

    @endif


    @if ($purchaseOrder->approvedBy)

        <p class="section-title">Approval</p>

        <div class="notes-box">
            Approved by {{ $purchaseOrder->approvedBy->name }}
            @if ($purchaseOrder->approved_at)
                on {{ format_datetime($purchaseOrder->approved_at) }}
            @endif

            @if ($purchaseOrder->approval_notes)
                <br>{{ $purchaseOrder->approval_notes }}
            @endif
        </div>

    @endif


    <table class="signatures">
        <tr>
            <td>Prepared By</td>
            <td>Approved By</td>
            <td>Received By</td>
        </tr>
    </table>


    <p class="footer-note">
        Generated on {{ now()->format('d/m/Y H:i') }}
    </p>

</body>
</html>
