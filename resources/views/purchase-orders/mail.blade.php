<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $purchaseOrder->po_number }}</title>
</head>
<body style="margin:0; padding:0; background:#f1f5f9; font-family: Arial, Helvetica, sans-serif; color:#1e293b;">

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f1f5f9; padding:30px 0;">
        <tr>
            <td align="center">

                <table role="presentation" width="580" cellpadding="0" cellspacing="0" style="background:#ffffff; border-radius:10px; overflow:hidden; border:1px solid #e2e8f0;">

                    <tr>
                        <td style="background:#0f172a; padding:22px 30px;">
                            <span style="color:#ffffff; font-size:18px; font-weight:bold;">
                                {{ $purchaseOrder->location?->company?->name ?? config('app.name') }}
                            </span>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:28px 30px;">

                            <p style="margin:0 0 18px; font-size:14px; line-height:1.6; white-space:pre-wrap;">
                                {{ $messageBody }}
                            </p>

                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px;">

                                <tr>
                                    <td style="padding:14px 18px;">
                                        <span style="color:#64748b; font-size:11px; text-transform:uppercase;">Purchase Order #</span><br>
                                        <strong style="font-size:15px; color:#0f172a;">{{ $purchaseOrder->po_number }}</strong>
                                    </td>
                                </tr>

                                <tr>
                                    <td style="padding:0 18px 14px;">
                                        <span style="color:#64748b; font-size:11px; text-transform:uppercase;">Supplier</span><br>
                                        <strong style="font-size:14px; color:#0f172a;">{{ $purchaseOrder->supplier?->name ?? '—' }}</strong>
                                    </td>
                                </tr>

                                <tr>
                                    <td style="padding:0 18px 14px;">
                                        <span style="color:#64748b; font-size:11px; text-transform:uppercase;">Order Date</span><br>
                                        <strong style="font-size:14px; color:#0f172a;">{{ format_date($purchaseOrder->created_at) ?? '—' }}</strong>
                                    </td>
                                </tr>

                                <tr>
                                    <td style="padding:0 18px 18px;">
                                        <span style="color:#64748b; font-size:11px; text-transform:uppercase;">Total Amount</span><br>
                                        <strong style="font-size:16px; color:#0f172a;">
                                            &#8369;{{ number_format((float) $purchaseOrder->total, 2) }}
                                        </strong>
                                    </td>
                                </tr>

                            </table>

                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-top:22px; border-collapse:collapse;">

                                <tr>
                                    <td colspan="4" style="padding:0 0 8px; font-size:11px; color:#64748b; text-transform:uppercase;">
                                        Items Ordered
                                    </td>
                                </tr>

                                <tr>
                                    <td style="padding:8px; border-bottom:2px solid #e2e8f0; font-size:11px; color:#64748b; text-transform:uppercase;">Product</td>
                                    <td style="padding:8px; border-bottom:2px solid #e2e8f0; font-size:11px; color:#64748b; text-transform:uppercase;">Unit</td>
                                    <td align="right" style="padding:8px; border-bottom:2px solid #e2e8f0; font-size:11px; color:#64748b; text-transform:uppercase;">Quantity</td>
                                    <td align="right" style="padding:8px; border-bottom:2px solid #e2e8f0; font-size:11px; color:#64748b; text-transform:uppercase;">Line Total</td>
                                </tr>

                                @foreach ($purchaseOrder->items as $item)
                                    <tr>
                                        <td style="padding:8px; border-bottom:1px solid #f1f5f9; font-size:13px; color:#1e293b;">
                                            {{ $item->product?->name ?? 'Product #' . $item->product_id }}
                                        </td>
                                        <td style="padding:8px; border-bottom:1px solid #f1f5f9; font-size:13px; color:#475569;">
                                            {{ $item->productUnit?->unitOfMeasure?->name ?? '—' }}
                                        </td>
                                        <td align="right" style="padding:8px; border-bottom:1px solid #f1f5f9; font-size:13px; color:#1e293b;">
                                            {{ format_qty($item->quantity_ordered) }}
                                        </td>
                                        <td align="right" style="padding:8px; border-bottom:1px solid #f1f5f9; font-size:13px; color:#1e293b;">
                                            &#8369;{{ number_format((float) $item->subtotal, 2) }}
                                        </td>
                                    </tr>
                                @endforeach

                            </table>

                            <p style="margin:22px 0 0; font-size:13px; color:#64748b;">
                                The full purchase order is attached as a PDF for your records.
                            </p>

                        </td>
                    </tr>

                    <tr>
                        <td style="padding:16px 30px; background:#f8fafc; border-top:1px solid #e2e8f0;">
                            <span style="font-size:11px; color:#94a3b8;">
                                This email was sent from {{ config('app.name') }}.
                            </span>
                        </td>
                    </tr>

                </table>

            </td>
        </tr>
    </table>

</body>
</html>
