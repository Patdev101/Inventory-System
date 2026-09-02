<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Transaction #{{ $transaction->id }}</title>

    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            background: #f5f5f5;
            margin: 0;
            padding: 30px;
            color: #222;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .card {
            background: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 14px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }

        th {
            width: 220px;
            background: #f3f4f6;
        }

        .button {
            display: inline-block;
            padding: 10px 16px;
            background: #2563eb;
            color: white;
            text-decoration: none;
            border-radius: 6px;
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
</head>

<body>

<div class="system-shell">

    @include('layouts.sidebar')

    <main class="system-main">
        <div class="container">

    <div class="header">

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
                    {{ $transaction->created_at?->format('Y-m-d H:i:s') }}
                </td>
            </tr>

            <tr>
                <th>Product</th>
                <td>

                    @if ($transaction->product)

                        {{ $transaction->product->name }}

                        @if ($transaction->product->code)
                            ({{ $transaction->product->code }})
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

                    {{ number_format(
                        (float) $transaction->quantity,
                        4
                    ) }}

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

                    {{ number_format(
                        (float) $transaction->base_quantity,
                        4
                    ) }}

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

        </div>
    </main>
</div>

</body>
</html>