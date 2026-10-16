@extends('layouts.app')

@section('title', 'Expiring Stock')

@section('content')

<div class="page-header">
    <div>
        <h1>Expiring Stock</h1>
        <p>Stock of products that expire, which has expired or expires within {{ $days }} days. Oldest stock is assumed to go out first.</p>
    </div>
    <a href="{{ route('reports.index') }}" class="btn btn-secondary">Back to Reports</a>
</div>

<div class="card" style="margin-bottom: 20px;">
    <form method="GET" action="{{ route('reports.expiring') }}" style="display: flex; gap: 8px; flex-wrap: wrap; align-items: center;">
        <span style="color: #475569;">Show stock expiring within</span>

        @foreach ([7, 30, 60, 90] as $option)
            <a
                href="{{ route('reports.expiring', ['days' => $option]) }}"
                class="btn {{ $days === $option ? 'btn-primary' : 'btn-secondary' }}"
            >{{ $option }} days</a>
        @endforeach

        <span style="margin-left: auto; color: #475569;">
            <strong style="color: #b91c1c;">{{ $counts['expired'] }}</strong> expired &middot;
            <strong style="color: #b45309;">{{ $counts['expiring'] }}</strong> expiring soon
        </span>
    </form>
</div>

@if ($batches->count())
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Location</th>
                    <th>Quantity</th>
                    <th>Expiry Date</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($batches as $batch)
                    <tr>
                        <td>{{ $batch['inventory']->product?->name ?? 'Deleted product' }}</td>
                        <td>{{ $batch['inventory']->location?->name ?? 'Deleted location' }}</td>
                        <td>{{ format_qty($batch['base_quantity']) }} {{ $batch['inventory']->product?->counting_unit ?? 'units' }}</td>
                        <td>{{ $batch['expiry_date']->format('m/d/Y') }}</td>
                        <td>
                            @if ($batch['expired'])
                                <span class="expiry-status expiry-expired">Expired {{ abs($batch['days_left']) }} {{ abs($batch['days_left']) === 1 ? 'day' : 'days' }} ago</span>
                            @elseif ($batch['days_left'] === 0)
                                <span class="expiry-status expiry-expired">Expires today</span>
                            @else
                                <span class="expiry-status expiry-soon">{{ $batch['days_left'] }} {{ $batch['days_left'] === 1 ? 'day' : 'days' }} left</span>
                            @endif
                        </td>
                        <td><a href="{{ route('inventories.show', $batch['inventory']) }}" class="btn btn-secondary">View</a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@else
    <div class="empty-state">
        <p>Nothing has expired or expires within {{ $days }} days.</p>
    </div>
@endif

<style>
    .expiry-status { display: inline-block; padding: 4px 9px; border-radius: 999px; font-size: 12px; font-weight: 700; white-space: nowrap; }
    .expiry-soon { background: #fef3c7; color: #92400e; }
    .expiry-expired { background: #fee2e2; color: #991b1b; }
</style>

@endsection
