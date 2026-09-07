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
        <a
            href="{{ route('suppliers.create') }}"
            class="btn btn-primary"
        >
            Add Supplier
        </a>
    @endif
</div>

{{-- SUCCESS MESSAGE --}}
@if (session('success'))
    <div
        style="
            background: #ecfdf5;
            border: 1px solid #a7f3d0;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
            color: #065f46;
        "
    >
        {{ session('success') }}
    </div>
@endif

{{-- ERROR MESSAGE --}}
@if (session('error'))
    <div
        style="
            background: #fef2f2;
            border: 1px solid #fecaca;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
            color: #991b1b;
        "
    >
        {{ session('error') }}
    </div>
@endif

@if ($errors->any())
    <div
        style="
            background: #ffeeee;
            border: 1px solid #f5c6cb;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
            color: #721c24;
        "
    >
        <strong>Please fix the following:</strong>

        <ul style="margin: 10px 0 0; padding-left: 20px;">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="card">

    {{-- =========================
         SEARCH
    ========================== --}}
    <form
        action="{{ route('suppliers.index') }}"
        method="GET"
        style="
            display: flex;
            gap: 10px;
            align-items: flex-end;
            flex-wrap: wrap;
            margin-bottom: 20px;
        "
    >
        <div style="flex: 1; min-width: 240px;">

            <label
                for="search"
                style="
                    font-weight: bold;
                    display: block;
                    margin-bottom: 6px;
                "
            >
                Search Suppliers
            </label>

            <input
                type="text"
                id="search"
                name="search"
                value="{{ $search }}"
                placeholder="Search by supplier, contact, email, or phone..."
                style="
                    width: 100%;
                    padding: 8px;
                    border: 1px solid #cbd5e1;
                    border-radius: 6px;
                "
            >

        </div>

        <div
            style="
                display: flex;
                gap: 8px;
                flex-wrap: wrap;
            "
        >

            <button
                type="submit"
                class="btn btn-primary"
            >
                Search
            </button>

            @if ($search !== '')
                <a
                    href="{{ route('suppliers.index') }}"
                    class="btn btn-secondary"
                    style="text-decoration: none;"
                >
                    Clear
                </a>
            @endif

        </div>
    </form>


    {{-- =========================
         RESULTS INFO
    ========================== --}}
    <div
        style="
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 12px;
            padding: 10px 12px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
        "
    >

        <div
            style="
                font-size: 13px;
                color: #475569;
            "
        >

            @if ($suppliers->total() > 0)

                Showing
                {{ $suppliers->firstItem() }}-{{ $suppliers->lastItem() }}
                of
                {{ $suppliers->total() }}
                suppliers

            @else

                No suppliers found

            @endif

        </div>

        <div
            style="
                font-size: 13px;
                color: #475569;
            "
        >
            {{ $suppliers->total() }} total
        </div>

    </div>


    {{-- =========================
         SUPPLIER TABLE
    ========================== --}}
    <div style="overflow-x: auto;">

        <table
            style="
                width: 100%;
                border-collapse: collapse;
                text-align: left;
            "
        >

            <thead>

                <tr
                    style="
                        background: #f8fafc;
                        border-bottom: 2px solid #e2e8f0;
                    "
                >

                    <th style="padding: 10px;">
                        Supplier
                    </th>

                    <th style="padding: 10px;">
                        Contact
                    </th>

                    <th style="padding: 10px;">
                        Phone
                    </th>

                    <th style="padding: 10px;">
                        Email
                    </th>

                    <th style="padding: 10px;">
                        Company
                    </th>

                    <th
                        style="
                            padding: 10px;
                            text-align: center;
                        "
                    >
                        Status
                    </th>

                    <th
                        style="
                            padding: 10px;
                            text-align: right;
                        "
                    >
                        Actions
                    </th>

                </tr>

            </thead>


            <tbody>

                @forelse ($suppliers as $supplier)

                    <tr
                        style="
                            border-bottom: 1px solid #e2e8f0;
                        "
                    >

                        {{-- =========================
                             SUPPLIER
                        ========================== --}}
                        <td
                            style="
                                padding: 12px;
                                vertical-align: top;
                            "
                        >

                            <div
                                style="
                                    font-weight: 600;
                                    color: #1e293b;
                                "
                            >
                                {{ $supplier->name }}
                            </div>

                            <div
                                style="
                                    font-size: 12px;
                                    color: #64748b;
                                    margin-top: 2px;
                                "
                            >
                                Supplier #{{ $supplier->id }}
                            </div>

                        </td>


                        {{-- =========================
                             CONTACT
                        ========================== --}}
                        <td
                            style="
                                padding: 12px;
                                vertical-align: top;
                            "
                        >

                            @if ($supplier->contact_name)

                                <div style="color: #334155;">
                                    {{ $supplier->contact_name }}
                                </div>

                            @else

                                <span style="color: #94a3b8;">
                                    —
                                </span>

                            @endif

                        </td>


                        {{-- =========================
                             PHONE
                        ========================== --}}
                        <td
                            style="
                                padding: 12px;
                                vertical-align: top;
                            "
                        >

                            @if ($supplier->phone)

                                {{ $supplier->phone }}

                            @else

                                <span style="color: #94a3b8;">
                                    —
                                </span>

                            @endif

                        </td>


                        {{-- =========================
                             EMAIL
                        ========================== --}}
                        <td
                            style="
                                padding: 12px;
                                vertical-align: top;
                            "
                        >

                            @if ($supplier->email)

                                {{ $supplier->email }}

                            @else

                                <span style="color: #94a3b8;">
                                    —
                                </span>

                            @endif

                        </td>


                        {{-- =========================
                             COMPANY
                        ========================== --}}
                        <td
                            style="
                                padding: 12px;
                                vertical-align: top;
                            "
                        >

                            @if ($supplier->company)

                                {{ $supplier->company->name }}

                            @else

                                <span style="color: #94a3b8;">
                                    —
                                </span>

                            @endif

                        </td>


                        {{-- =========================
                             STATUS
                        ========================== --}}
                        <td
                            style="
                                padding: 12px;
                                vertical-align: top;
                                text-align: center;
                            "
                        >

                            @if ($supplier->is_active)

                                <span
                                    style="
                                        display: inline-block;
                                        padding: 4px 10px;
                                        background: #dcfce7;
                                        color: #166534;
                                        border-radius: 999px;
                                        font-size: 12px;
                                        font-weight: 600;
                                    "
                                >
                                    Active
                                </span>

                            @else

                                <span
                                    style="
                                        display: inline-block;
                                        padding: 4px 10px;
                                        background: #fee2e2;
                                        color: #991b1b;
                                        border-radius: 999px;
                                        font-size: 12px;
                                        font-weight: 600;
                                    "
                                >
                                    Inactive
                                </span>

                            @endif

                        </td>


                        {{-- =========================
                             ACTIONS
                        ========================== --}}
                        <td
                            style="
                                padding: 12px;
                                vertical-align: top;
                                text-align: right;
                            "
                        >

                            <div
                                style="
                                    display: flex;
                                    justify-content: flex-end;
                                    gap: 6px;
                                    flex-wrap: wrap;
                                "
                            >

                                {{-- VIEW --}}
                                <a
                                    href="{{ route('suppliers.show', $supplier) }}"
                                    class="btn btn-secondary"
                                    style="
                                        text-decoration: none;
                                    "
                                >
                                    View
                                </a>


                                @if (auth()->user()->hasRole('admin'))

                                    {{-- EDIT --}}
                                    <a
                                        href="{{ route('suppliers.edit', $supplier) }}"
                                        class="btn btn-secondary"
                                        style="
                                            text-decoration: none;
                                        "
                                    >
                                        Edit
                                    </a>


                                    {{-- =========================
                                         ACTIVE SUPPLIER
                                         SHOW DEACTIVATE
                                    ========================== --}}
                                    @if ($supplier->is_active)

                                        <form
                                            action="{{ route('suppliers.deactivate', $supplier) }}"
                                            method="POST"
                                            style="display: inline;"
                                            onsubmit="return confirm('Deactivate this supplier?');"
                                        >

                                            @csrf
                                            @method('PATCH')

                                            <button
                                                type="submit"
                                                class="btn"
                                                style="
                                                    background: #dc2626;
                                                    color: #ffffff;
                                                    border: 1px solid #dc2626;
                                                    padding: 7px 12px;
                                                    border-radius: 6px;
                                                    cursor: pointer;
                                                    font-weight: 500;
                                                "
                                            >
                                                Deactivate
                                            </button>

                                        </form>


                                    {{-- =========================
                                         INACTIVE SUPPLIER
                                         SHOW ACTIVATE
                                    ========================== --}}
                                    @else

                                        <form
                                            action="{{ route('suppliers.activate', $supplier) }}"
                                            method="POST"
                                            style="display: inline;"
                                        >

                                            @csrf
                                            @method('PATCH')

                                            <button
                                                type="submit"
                                                class="btn"
                                                style="
                                                    background: #16a34a;
                                                    color: #ffffff;
                                                    border: 1px solid #16a34a;
                                                    padding: 7px 12px;
                                                    border-radius: 6px;
                                                    cursor: pointer;
                                                    font-weight: 500;
                                                "
                                            >
                                                Activate
                                            </button>

                                        </form>

                                    @endif

                                @endif

                            </div>

                        </td>

                    </tr>


                @empty

                    {{-- =========================
                         NO RESULTS
                    ========================== --}}
                    <tr>

                        <td
                            colspan="7"
                            style="
                                padding: 40px 20px;
                                text-align: center;
                                color: #64748b;
                            "
                        >

                            @if ($search !== '')

                                <div
                                    style="
                                        font-weight: 600;
                                        color: #334155;
                                        margin-bottom: 6px;
                                    "
                                >
                                    No suppliers found.
                                </div>

                                <div style="font-size: 13px;">
                                    Try a different search term.
                                </div>

                            @else

                                <div
                                    style="
                                        font-weight: 600;
                                        color: #334155;
                                        margin-bottom: 6px;
                                    "
                                >
                                    No suppliers yet.
                                </div>

                                <div style="font-size: 13px;">
                                    Add your first supplier to start creating purchase orders.
                                </div>

                            @endif

                        </td>

                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>


    {{-- =========================
         PAGINATION
    ========================== --}}
    @if ($suppliers->hasPages())

        <div
            style="
                display: flex;
                justify-content: center;
                align-items: center;
                gap: 6px;
                flex-wrap: wrap;
                margin-top: 20px;
            "
        >

            {{-- PREVIOUS --}}
            @if ($suppliers->onFirstPage())

                <span
                    class="btn btn-secondary"
                    style="
                        opacity: 0.5;
                        cursor: not-allowed;
                    "
                >
                    ← Previous
                </span>

            @else

                <a
                    href="{{ $suppliers->previousPageUrl() }}"
                    class="btn btn-secondary"
                    style="text-decoration: none;"
                >
                    ← Previous
                </a>

            @endif


            {{-- PAGE NUMBERS --}}
            @foreach (
                $suppliers->getUrlRange(
                    max(1, $suppliers->currentPage() - 2),
                    min($suppliers->lastPage(), $suppliers->currentPage() + 2)
                )
                as $page => $url
            )

                @if ($page == $suppliers->currentPage())

                    <span
                        style="
                            min-width: 36px;
                            padding: 7px 10px;
                            border: 1px solid #2563eb;
                            border-radius: 6px;
                            background: #2563eb;
                            color: #ffffff;
                            text-align: center;
                        "
                    >
                        {{ $page }}
                    </span>

                @else

                    <a
                        href="{{ $url }}"
                        style="
                            min-width: 36px;
                            padding: 7px 10px;
                            border: 1px solid #cbd5e1;
                            border-radius: 6px;
                            background: #ffffff;
                            color: #334155;
                            text-align: center;
                            text-decoration: none;
                        "
                    >
                        {{ $page }}
                    </a>

                @endif

            @endforeach


            {{-- NEXT --}}
            @if ($suppliers->hasMorePages())

                <a
                    href="{{ $suppliers->nextPageUrl() }}"
                    class="btn btn-secondary"
                    style="text-decoration: none;"
                >
                    Next →
                </a>

            @else

                <span
                    class="btn btn-secondary"
                    style="
                        opacity: 0.5;
                        cursor: not-allowed;
                    "
                >
                    Next →
                </span>

            @endif

        </div>

    @endif

</div>

@endsection
