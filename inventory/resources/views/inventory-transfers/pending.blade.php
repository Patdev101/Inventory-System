@extends('layouts.app')

@section('title', 'Audit Transfers')

@section('content')

<div class="page-header">

    <div>
        <h1>Audit Transfers</h1>

        <p style="margin: 6px 0 0; color: #64748b;">
            Transfers assigned to you that are awaiting audit or receipt.
        </p>
    </div>

    <a
        href="{{ route('inventory-transfers.index') }}"
        class="btn btn-secondary"
    >
        View Full Transfer History
    </a>

</div>


@if ($transfers->count() > 0)

    <div style="margin-bottom: 14px; color: #64748b; font-size: 14px;">
        <strong>{{ $transfers->total() }}</strong> transfer(s) waiting on you
    </div>

    <div class="table-wrapper">

        <table>

            <thead>
                <tr>
                    <th>ID</th>
                    <th>Date</th>
                    <th>Product</th>
                    <th>From</th>
                    <th>To</th>
                    <th>Quantity</th>
                    <th>Reference</th>
                    <th>Audit Status</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>

                @foreach ($transfers as $transfer)

                    <tr>

                        <td>#{{ $transfer->id }}</td>

                        <td>{{ $transfer->created_at->format('Y-m-d H:i:s') }}</td>

                        <td>
                            <strong>{{ $transfer->product->name ?? 'N/A' }}</strong>
                        </td>

                        <td>{{ $transfer->sourceInventory->location->name ?? 'N/A' }}</td>

                        <td>{{ $transfer->destinationInventory->location->name ?? 'N/A' }}</td>

                        <td>
                            {{ number_format((float) $transfer->quantity, 4) }}
                            {{ $transfer->productUnit?->unitOfMeasure?->code ?? '' }}
                        </td>

                        <td>{{ $transfer->reference ?? '—' }}</td>

                        <td>
                            @if ($transfer->audit_status === 'passed')
                                <span style="background: #dcfce7; color: #166534; padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: bold;">Passed — Ready to Receive</span>
                            @else
                                <span style="background: #fef9c3; color: #854d0e; padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: bold;">Awaiting Audit</span>
                            @endif
                        </td>

                        <td>
                            <a
                                href="{{ route('inventory-transfers.show', $transfer) }}"
                                class="btn btn-primary"
                                style="padding: 4px 8px; font-size: 12px;"
                            >
                                {{ $transfer->audit_status === 'passed' ? 'Receive' : 'Audit Now' }}
                            </a>
                        </td>

                    </tr>

                @endforeach

            </tbody>

        </table>

    </div>

    @if ($transfers->hasPages())
        <div style="margin-top: 20px;">
            {{ $transfers->links() }}
        </div>
    @endif

@else

    <div class="empty-state">
        <p>Nothing waiting on you right now — you're all caught up.</p>
    </div>

@endif

@endsection