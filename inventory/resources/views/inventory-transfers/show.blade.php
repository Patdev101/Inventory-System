@extends('layouts.app')

@section('title', 'Transfer Details')

@section('content')

<div class="page-header">
    <div>
        <h1>Transfer Details</h1>

        <p style="margin: 6px 0 0; color: #64748b;">
            View details of this inventory transfer.
        </p>
    </div>

    <div class="actions">

        <a
            href="{{ route('inventory-transfers.index') }}"
            class="btn btn-secondary"
        >
            Back to Transfer History
        </a>

        @if (auth()->user()->hasRole('admin', 'manager'))
        <a
            href="{{ route('inventory-transfers.create') }}"
            class="btn btn-primary"
        >
            New Transfer
        </a>
        @endif

    </div>
</div>


<div class="card">

    <h2 style="margin-top: 0; margin-bottom: 25px;">
        Transfer #{{ $transfer->id }}
    </h2>


    <div style="
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 20px;
    ">

        <div>
            <strong>Date</strong>

            <div style="margin-top: 6px; color: #475569;">
                {{ $transfer->created_at->format('Y-m-d H:i:s') }}
            </div>
        </div>


        <div>
            <strong>Product</strong>

            <div style="margin-top: 6px; color: #475569;">
                {{ $transfer->product->name ?? 'N/A' }}
            </div>
        </div>


        <div>
            <strong>From Location</strong>

            <div style="margin-top: 6px; color: #475569;">
                {{ $transfer->sourceInventory->location->name ?? 'N/A' }}
            </div>
        </div>


        <div>
            <strong>To Location</strong>

            <div style="margin-top: 6px; color: #475569;">
                {{ $transfer->destinationInventory->location->name ?? 'N/A' }}
            </div>
        </div>


        <div>
            <strong>Transfer Unit</strong>

            <div style="margin-top: 6px; color: #475569;">
                {{ $transfer->productUnit->unitOfMeasure->name ?? 'N/A' }}

                @if ($transfer->productUnit->unitOfMeasure->code ?? false)
                    ({{ $transfer->productUnit->unitOfMeasure->code }})
                @endif
            </div>
        </div>


        <div>
            <strong>Conversion Factor</strong>

            <div style="margin-top: 6px; color: #475569;">
                {{ number_format((float) $transfer->conversion_factor, 4) }}
            </div>
        </div>


        <div>
            <strong>Quantity Transferred</strong>

            <div style="margin-top: 6px; color: #475569;">
                {{ number_format((float) $transfer->quantity, 4) }}

                {{ $transfer->productUnit->unitOfMeasure->code ?? '' }}
            </div>
        </div>


        <div>
            <strong>Base Quantity</strong>

            <div style="margin-top: 6px; color: #475569;">
                {{ number_format((float) $transfer->base_quantity, 4) }}

                base units
            </div>
        </div>


        <div>
            <strong>Reference</strong>

            <div style="margin-top: 6px; color: #475569;">
                {{ $transfer->reference ?? '—' }}
            </div>
        </div>


        <div>
            <strong>Notes</strong>

            <div style="margin-top: 6px; color: #475569;">
                {{ $transfer->notes ?? '—' }}
            </div>
        </div>

    </div>

</div>


<div class="card" style="margin-top: 20px;">

    <h2 style="margin-top: 0; margin-bottom: 20px;">
        Receiving & Audit Status
    </h2>

    <div style="
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 20px;
        margin-bottom: 20px;
    ">

        <div>
            <strong>Status</strong>
            <div style="margin-top: 6px;">
                @if ($transfer->status === 'completed')
                    <span style="background: #dcfce7; color: #166534; padding: 4px 10px; border-radius: 999px; font-size: 12px; font-weight: 600;">Completed</span>
                @elseif ($transfer->status === 'rejected')
                    <span style="background: #fee2e2; color: #991b1b; padding: 4px 10px; border-radius: 999px; font-size: 12px; font-weight: 600;">Rejected</span>
                @else
                    <span style="background: #fef9c3; color: #854d0e; padding: 4px 10px; border-radius: 999px; font-size: 12px; font-weight: 600;">Pending</span>
                @endif
            </div>
        </div>

        <div>
            <strong>Assigned Receiver</strong>
            <div style="margin-top: 6px; color: #475569;">
                {{ $transfer->receiver->name ?? 'N/A' }}
                @if ($transfer->receiver_role)
                    ({{ ucfirst($transfer->receiver_role) }})
                @endif
            </div>
        </div>

        <div>
            <strong>Audit Result</strong>
            <div style="margin-top: 6px;">
                @if ($transfer->audit_status === 'passed')
                    <span style="background: #dcfce7; color: #166534; padding: 4px 10px; border-radius: 999px; font-size: 12px; font-weight: 600;">Passed</span>
                @elseif ($transfer->audit_status === 'failed')
                    <span style="background: #fee2e2; color: #991b1b; padding: 4px 10px; border-radius: 999px; font-size: 12px; font-weight: 600;">Failed</span>
                @else
                    <span style="background: #f1f5f9; color: #475569; padding: 4px 10px; border-radius: 999px; font-size: 12px; font-weight: 600;">Awaiting Audit</span>
                @endif
            </div>
        </div>

        <div>
            <strong>Audited By</strong>
            <div style="margin-top: 6px; color: #475569;">
                {{ $transfer->auditedBy->name ?? '—' }}
                @if ($transfer->audited_at)
                    <div style="font-size: 12px; color: #94a3b8; margin-top: 2px;">
                        {{ $transfer->audited_at->format('Y-m-d H:i:s') }}
                    </div>
                @endif
            </div>
        </div>

        @if ($transfer->audit_notes)
            <div style="grid-column: 1 / -1;">
                <strong>Audit Notes</strong>
                <div style="margin-top: 6px; color: #475569;">
                    {{ $transfer->audit_notes }}
                </div>
            </div>
        @endif

        <div>
            <strong>Received By</strong>
            <div style="margin-top: 6px; color: #475569;">
                {{ $transfer->receivedBy->name ?? '—' }}
                @if ($transfer->received_at)
                    <div style="font-size: 12px; color: #94a3b8; margin-top: 2px;">
                        {{ $transfer->received_at->format('Y-m-d H:i:s') }}
                    </div>
                @endif
            </div>
        </div>

    </div>

    @if ($transfer->status === 'pending' && (int) $transfer->receiver_id === (int) auth()->id())

        @if ($transfer->audit_status === 'pending')
            {{-- Awaiting audit: show pass/fail form --}}
            <hr style="border: 0; border-top: 1px solid #e2e8f0; margin: 20px 0;">

            <h3 style="margin: 0 0 12px 0; font-size: 16px;">Inspect This Transfer</h3>
            <p style="margin: 0 0 16px 0; color: #64748b; font-size: 13px;">
                Confirm the items arrived in good condition before they're added to the destination stock.
            </p>

            <form
                action="{{ route('inventory-transfers.audit', $transfer) }}"
                method="POST"
            >
                @csrf
                @method('PATCH')

                <div class="form-group" style="margin-bottom: 16px;">
                    <label for="audit_notes" style="font-weight: bold; display: block; margin-bottom: 6px;">
                        Audit Notes
                    </label>
                    <textarea
                        id="audit_notes"
                        name="audit_notes"
                        rows="3"
                        placeholder="Optional notes about the condition of the items..."
                        style="width: 100%; padding: 8px; border: 1px solid #cbd5e1; border-radius: 6px;"
                    ></textarea>
                </div>

                <div style="display: flex; gap: 10px;">
                    <button
                        type="submit"
                        name="result"
                        value="pass"
                        class="btn btn-primary"
                        style="background: #16a34a; border-color: #16a34a;"
                    >
                        Pass Audit
                    </button>

                    <button
                        type="submit"
                        name="result"
                        value="fail"
                        class="btn btn-secondary"
                        style="background: #dc2626; border-color: #dc2626; color: #fff;"
                        onclick="return confirm('This will mark the item as failed and return the stock to the source location. Continue?');"
                    >
                        Fail Audit
                    </button>
                </div>
            </form>

        @elseif ($transfer->audit_status === 'passed')
            {{-- Passed audit, not yet received: show receive button --}}
            <hr style="border: 0; border-top: 1px solid #e2e8f0; margin: 20px 0;">

            <div style="
                background: #f0fdf4;
                border: 1px solid #bbf7d0;
                border-radius: 8px;
                padding: 16px;
                display: flex;
                justify-content: space-between;
                align-items: center;
                flex-wrap: wrap;
                gap: 12px;
            ">
                <div style="color: #166534;">
                    This transfer passed audit. Mark it received to add the stock to the destination location.
                </div>

                <form
                    action="{{ route('inventory-transfers.receive', $transfer) }}"
                    method="POST"
                >
                    @csrf
                    @method('PATCH')

                    <button
                        type="submit"
                        class="btn btn-primary"
                    >
                        Mark Received
                    </button>
                </form>
            </div>
        @endif

    @elseif ($transfer->status === 'pending' && (int) $transfer->receiver_id !== (int) auth()->id())
        <hr style="border: 0; border-top: 1px solid #e2e8f0; margin: 20px 0;">
        <div style="color: #94a3b8; font-size: 13px;">
            Only the assigned receiver ({{ $transfer->receiver->name ?? 'N/A' }}) can audit or receive this transfer.
        </div>
    @endif

</div>


<div class="card" style="margin-top: 20px;">

    <h2 style="margin-top: 0;">
        Transfer Summary
    </h2>

    <div style="
        background: #eff6ff;
        border: 1px solid #bfdbfe;
        border-radius: 8px;
        padding: 18px;
        color: #1e3a8a;
    ">

        <strong>
            {{ number_format((float) $transfer->quantity, 4) }}

            {{ $transfer->productUnit->unitOfMeasure->code ?? '' }}
        </strong>

        was transferred from

        <strong>
            {{ $transfer->sourceInventory->location->name ?? 'N/A' }}
        </strong>

        to

        <strong>
            {{ $transfer->destinationInventory->location->name ?? 'N/A' }}
        </strong>

        for a total movement of

        <strong>
            {{ number_format((float) $transfer->base_quantity, 4) }}

            base units.
        </strong>

    </div>

</div>

@endsection