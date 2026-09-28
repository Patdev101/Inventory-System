@extends('layouts.app')

@section('title', 'Email Purchase Order')

@section('content')

<div class="page-header">

    <div>
        <h1>Send Purchase Order</h1>
        <p style="margin: 6px 0 0; color: #64748b;">
            Email {{ $purchaseOrder->po_number }} to the supplier, with the PDF attached.
        </p>
    </div>

    <a href="{{ route('purchase-orders.show', $purchaseOrder) }}" class="btn btn-secondary">
        Back to Purchase Order
    </a>

</div>


@if ($errors->any())

    <div class="alert-error">
        <strong>Please fix the following:</strong>

        <ul style="margin: 8px 0 0 20px;">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>

@endif


<div class="card" style="max-width: 720px; margin: 0 auto;">

    <form action="{{ route('purchase-orders.email.send', $purchaseOrder) }}" method="POST">

        @csrf

        <div class="form-group">
            <label for="subject">Subject</label>

            <input
                type="text"
                id="subject"
                name="subject"
                value="{{ old('subject', 'Purchase Order from ' . ($purchaseOrder->location?->company?->name ?? config('app.name')) . ' (' . $purchaseOrder->po_number . ')') }}"
                maxlength="255"
                required
            >
        </div>

        <div class="email-row">

            <div class="form-group" style="margin-bottom: 0;">
                <label for="to_email">Send To</label>

                <input
                    type="email"
                    id="to_email"
                    name="to_email"
                    value="{{ old('to_email', $purchaseOrder->supplier?->email) }}"
                    maxlength="255"
                    required
                >
            </div>

            <button type="button" id="toggle-cc-bcc" class="cc-bcc-toggle">
                CC / BCC
            </button>

        </div>

        <div id="cc-bcc-fields" class="cc-bcc-fields" style="{{ old('cc_email') || old('bcc_email') ? '' : 'display:none;' }}">

            <div class="form-group">
                <label for="cc_email">CC</label>

                <input
                    type="email"
                    id="cc_email"
                    name="cc_email"
                    value="{{ old('cc_email') }}"
                    maxlength="255"
                    placeholder="Optional"
                >
            </div>

            <div class="form-group">
                <label for="bcc_email">BCC</label>

                <input
                    type="email"
                    id="bcc_email"
                    name="bcc_email"
                    value="{{ old('bcc_email') }}"
                    maxlength="255"
                    placeholder="Optional"
                >
            </div>

        </div>

        <div class="form-group">
            <label for="body">Message</label>

            <textarea
                id="body"
                name="body"
                rows="6"
                maxlength="5000"
                required
            >{{ old('body', "Dear " . ($purchaseOrder->supplier?->name ?? 'Supplier') . ",\n\nPlease find attached purchase order {$purchaseOrder->po_number}. Kindly go through it and confirm the order.\n\nWe look forward to working with you.\n\nRegards,") }}</textarea>
        </div>


        {{-- PREVIEW --}}
        <div class="mail-preview">

            <div class="mail-preview-label">
                Preview
            </div>

            <div class="mail-preview-card">

                <div class="mail-preview-row">
                    <span>Purchase Order #</span>
                    <strong>{{ $purchaseOrder->po_number }}</strong>
                </div>

                <div class="mail-preview-row">
                    <span>Order Date</span>
                    <strong>{{ format_date($purchaseOrder->created_at) ?? '—' }}</strong>
                </div>

                <div class="mail-preview-row">
                    <span>Amount</span>
                    <strong>₱{{ number_format((float) $purchaseOrder->total, 2) }}</strong>
                </div>

            </div>

            <p class="mail-preview-note">
                The full PDF will be attached automatically.
            </p>

        </div>


        <div class="actions" style="margin-top: 20px;">

            <button type="submit" class="btn btn-primary">
                Send Email
            </button>

            <a href="{{ route('purchase-orders.show', $purchaseOrder) }}" class="btn btn-secondary">
                Cancel
            </a>

        </div>

    </form>

</div>


<style>

    .email-row {
        display: flex;
        align-items: flex-end;
        gap: 12px;
        margin-bottom: 18px;
    }

    .email-row .form-group {
        flex: 1;
    }

    .cc-bcc-toggle {
        border: 1px solid #d1d5db;
        background: #fff;
        color: #475569;
        padding: 10px 14px;
        border-radius: 6px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        white-space: nowrap;
    }

    .cc-bcc-toggle:hover {
        background: #f8fafc;
    }

    .cc-bcc-fields {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 16px;
        margin-bottom: 18px;
    }

    .mail-preview {
        margin-top: 22px;
    }

    .mail-preview-label {
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .04em;
        color: #94a3b8;
        margin-bottom: 8px;
    }

    .mail-preview-card {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 14px 16px;
    }

    .mail-preview-row {
        display: flex;
        justify-content: space-between;
        padding: 5px 0;
        font-size: 13px;
        color: #475569;
    }

    .mail-preview-note {
        margin: 8px 0 0;
        font-size: 12px;
        color: #94a3b8;
    }

    @media (max-width: 600px) {

        .email-row {
            flex-direction: column;
            align-items: stretch;
        }

        .cc-bcc-fields {
            grid-template-columns: 1fr;
        }

    }

</style>


<script>
    document.getElementById('toggle-cc-bcc').addEventListener('click', function () {
        var fields = document.getElementById('cc-bcc-fields');
        fields.style.display = fields.style.display === 'none' ? 'grid' : 'none';
    });
</script>

@endsection
