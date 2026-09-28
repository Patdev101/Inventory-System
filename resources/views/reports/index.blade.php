@extends('layouts.app')

@section('title', 'Reports')

@section('content')

<div class="page-header">
    <div>
        <h1>Reports</h1>
        <p style="margin: 6px 0 0; color: #64748b;">Stock movement, transfer, and low-stock reporting.</p>
    </div>
</div>

<div class="reports-grid">

    <a href="{{ route('reports.stock-movements') }}" class="card report-card">
        <h2>Stock Movements</h2>
        <p>Every IN and OUT transaction, filterable by date range, type, product, or location.</p>
    </a>

    <a href="{{ route('reports.transfers') }}" class="card report-card">
        <h2>Transfers</h2>
        <p>History of stock moved between locations, filterable by date range and product.</p>
    </a>

    <a href="{{ route('reports.low-stock') }}" class="card report-card">
        <h2>Low Stock</h2>
        <p>
            <span class="report-count report-count-critical">{{ $outOfStockCount }}</span> out of stock,
            <span class="report-count report-count-critical">{{ $criticalStockCount }}</span> critical,
            <span class="report-count report-count-low">{{ $lowStockCount }}</span> low stock.
        </p>
    </a>

</div>

<style>
    .reports-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 18px; }
    .report-card { text-decoration: none; color: inherit; transition: box-shadow .15s ease, transform .15s ease; }
    .report-card:hover { box-shadow: 0 6px 20px rgba(15, 23, 42, .12); transform: translateY(-2px); }
    .report-card h2 { margin: 0 0 10px; font-size: 18px; color: #0f172a; }
    .report-card p { margin: 0; color: #64748b; font-size: 14px; }
    .report-count { font-weight: 700; }
    .report-count-critical { color: #b91c1c; }
    .report-count-low { color: #b45309; }
</style>

@endsection
