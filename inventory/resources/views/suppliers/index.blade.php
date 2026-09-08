@extends('layouts.app')

@section('title', 'Suppliers')

@section('content')

<div class="page-header">
    <div>
        <h1>Suppliers</h1>
        <p style="margin: 6px 0 0; color: #64748b;">
            Manage your suppliers and view their purchase order history.
        </p>
    </div>

    @if (auth()->user()->hasRole('admin'))
        <a href="{{ route('suppliers.create') }}" class="btn btn-primary">
            + Add Supplier
        </a>
    @endif
</div>


@if ($errors->any())
    <div class="alert-error">
        <strong>Please fix the following:</strong>

        <ul style="margin: 10px 0 0; padding-left: 20px;">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="card">

    {{-- SEARCH --}}
    <form action="{{ route('suppliers.index') }}" method="GET" class="supplier-search-form">

        <div class="form-group" style="flex: 1; min-width: 240px; margin-bottom: 0;">
            <label for="search">Search Suppliers</label>

            <input
                type="text"
                id="search"
                name="search"
                value="{{ $search }}"
                placeholder="Search by supplier, contact, email, or phone..."
            >
        </div>

        <div style="display: flex; gap: 8px;">
            <button type="submit" class="btn btn-primary">Search</button>

            @if ($search !== '')
                <a href="{{ route('suppliers.index') }}" class="btn btn-secondary">Clear</a>
            @endif
        </div>
    </form>


    {{-- RESULTS INFO --}}
    <div class="results-info">
        <span>
            @if ($suppliers->total() > 0)
                Showing {{ $suppliers->firstItem() }}-{{ $suppliers->lastItem() }} of {{ $suppliers->total() }} suppliers
            @else
                No suppliers found
            @endif
        </span>
    </div>


    {{-- SUPPLIER TABLE --}}
    <div class="table-wrapper" style="border: none; box-shadow: none;">
        <table>
            <thead>
                <tr>
                    <th>Supplier</th>
                    <th>Contact</th>
                    <th>Phone</th>
                    <th>Email</th>
                    <th>Company</th>
                    <th style="text-align: center;">Status</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($suppliers as $supplier)
                    <tr>
                        <td>
                            <div class="entity-cell">
                                <span class="entity-avatar">
                                    {{ strtoupper(substr($supplier->name, 0, 1)) }}
                                </span>
                                <div>
                                    <div class="entity-name">{{ $supplier->name }}</div>
                                    <div class="entity-subtext">Supplier #{{ $supplier->id }}</div>
                                </div>
                            </div>
                        </td>

                        <td>{{ $supplier->contact_name ?: '—' }}</td>
                        <td>{{ $supplier->phone ?: '—' }}</td>
                        <td>{{ $supplier->email ?: '—' }}</td>
                        <td>{{ $supplier->company->name ?? '—' }}</td>

                        <td style="text-align: center;">
                            @if ($supplier->is_active)
                                <span class="status-badge status-active">Active</span>
                            @else
                                <span class="status-badge status-inactive">Inactive</span>
                            @endif
                        </td>

                        <td>
                            <div class="actions" style="justify-content: flex-end;">

                                <a href="{{ route('suppliers.show', $supplier) }}" class="btn btn-primary">
                                    View
                                </a>

                                @if (auth()->user()->hasRole('admin'))

                                    <a href="{{ route('suppliers.edit', $supplier) }}" class="btn btn-secondary">
                                        Edit
                                    </a>

                                    @if ($supplier->is_active)

                                        <form
                                            action="{{ route('suppliers.deactivate', $supplier) }}"
                                            method="POST"
                                            onsubmit="return confirm('Deactivate this supplier?');"
                                        >
                                            @csrf
                                            @method('PATCH')

                                            <button type="submit" class="btn btn-danger">
                                                Deactivate
                                            </button>
                                        </form>

                                    @else

                                        <form action="{{ route('suppliers.activate', $supplier) }}" method="POST">
                                            @csrf
                                            @method('PATCH')

                                            <button type="submit" class="btn btn-success">
                                                Activate
                                            </button>
                                        </form>

                                    @endif

                                @endif

                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">
                            <div class="empty-state" style="box-shadow: none; border: none;">
                                @if ($search !== '')
                                    <p><strong>No suppliers found.</strong><br>Try a different search term.</p>
                                @else
                                    <p><strong>No suppliers yet.</strong><br>Add your first supplier to start creating purchase orders.</p>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>


    {{-- PAGINATION --}}
    @if ($suppliers->hasPages())
        <div style="margin-top: 15px;">
            {{ $suppliers->onEachSide(1)->links() }}
        </div>
    @endif

</div>


<style>

    .supplier-search-form {
        display: flex;
        gap: 10px;
        align-items: flex-end;
        flex-wrap: wrap;
        margin-bottom: 18px;
    }

    .results-info {
        margin-bottom: 12px;
        padding: 10px 12px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        font-size: 13px;
        color: #475569;
    }

    .entity-cell {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .entity-avatar {
        flex-shrink: 0;
        width: 34px;
        height: 34px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #7c3aed, #a78bfa);
        color: #fff;
        font-size: 13px;
        font-weight: 700;
    }

    .entity-name {
        font-weight: 600;
        color: #1e293b;
    }

    .entity-subtext {
        font-size: 12px;
        color: #94a3b8;
        margin-top: 1px;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        padding: 4px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 700;
        white-space: nowrap;
    }

    .status-active {
        background: #dcfce7;
        color: #166534;
    }

    .status-inactive {
        background: #fee2e2;
        color: #991b1b;
    }

</style>

@endsection
