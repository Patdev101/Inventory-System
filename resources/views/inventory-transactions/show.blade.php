@extends('layouts.app')

@section('title')
    Transaction #{{ $transaction->id }}
@endsection

@section('content')

    <style>



        .card {
            margin-bottom: 20px;
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        td {
            white-space: nowrap;
        }



        .badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: bold;
        }

        .badge-in {
            background: #dcfce7;
            color: #166534;
        }

        .badge-out {
            background: #fee2e2;
            color: #991b1b;
        }

        .deleted {
            color: #6b7280;
            font-style: italic;
        }
    </style>


    <div class="page-header">

        <h1>
            Transaction #{{ $transaction->id }}
        </h1>

    </div>

    <div class="card">

        <table>

            <tr>
                <th>ID</th>
                <td>
                    {{ $transaction->id }}
                </td>
            </tr>

            <tr>
                <th>Date</th>
                <td>
                    {{ format_datetime($transaction->created_at) }}
                </td>
            </tr>

            <tr>
                <th>Product</th>
                <td>

                    @if ($transaction->product)

                        {{ $transaction->product->name }}

                        @if ($transaction->product->sku)
                            ({{ $transaction->product->sku }})
                        @endif

                    @else

                        <span class="deleted">
                            Product deleted
                        </span>

                    @endif

                </td>
            </tr>

            <tr>
                <th>Location</th>
                <td>

                    @if ($transaction->location)

                        {{ $transaction->location->name }}

                        @if ($transaction->location->code)
                            ({{ $transaction->location->code }})
                        @endif

                    @else

                        <span class="deleted">
                            Location deleted
                        </span>

                    @endif

                </td>
            </tr>

            <tr>
                <th>Type</th>
                <td>

                    @if ($transaction->type === 'in')

                        <span class="badge badge-in">
                            IN
                        </span>

                    @else

                        <span class="badge badge-out">
                            OUT
                        </span>

                    @endif

                </td>
            </tr>

            <tr>
                <th>Quantity</th>
                <td>

                    {{ format_qty((float) $transaction->quantity) }}

                    @if ($transaction->productUnit?->unitOfMeasure)
                        {{ $transaction->productUnit->unitOfMeasure->code }}
                    @endif

                </td>
            </tr>

            <tr>
                <th>Base Quantity</th>
                <td>

                    @if ($transaction->type === 'in')
                        +
                    @else
                        -
                    @endif

                    {{ format_qty((float) $transaction->base_quantity) }}

                    Base unit

                </td>
            </tr>

            <tr>
                <th>Unit</th>
                <td>

                    @if ($transaction->productUnit)

                        {{ $transaction->productUnit->name ?? 'Unit' }}

                        @if ($transaction->productUnit->unitOfMeasure)
                            ({{ $transaction->productUnit->unitOfMeasure->code }})
                        @endif

                    @else

                        <span class="deleted">
                            Unit deleted
                        </span>

                    @endif

                </td>
            </tr>

            <tr>
                <th>Reference</th>
                <td>
                    {{ $transaction->reference ?? '-' }}
                </td>
            </tr>

            <tr>
                <th>Inventory</th>
                <td>

                    @if ($transaction->inventory)

                        <a
                            href="{{ route(
                                'inventories.show',
                                $transaction->inventory
                            ) }}"
                        >
                            Inventory #{{ $transaction->inventory->id }}
                        </a>

                    @else

                        <span class="deleted">
                            Inventory deleted
                        </span>

                    @endif

                </td>
            </tr>

            @if ($transaction->notes)

                <tr>
                    <th>Notes</th>
                    <td>
                        {{ $transaction->notes }}
                    </td>
                </tr>

            @endif

        </table>

    </div>

@endsection
