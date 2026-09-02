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
