@extends('layouts.app')

@section('title', 'Edit Supplier')

@section('content')

<div class="page-header"> <div> <h1>Edit Supplier</h1> <p style="margin: 6px 0 0; color: #64748b;"> Update supplier information and account status. </p> </div>
<a
    href="{{ route('suppliers.show', $supplier) }}"
    class="btn btn-secondary"
    style="text-decoration: none;"
>
    ← Back to Supplier
</a>

</div>

@if ($errors->any())
<div style=" background: #ffeeee; border: 1px solid #f5c6cb; padding: 12px; border-radius: 6px; margin-bottom: 20px; color: #721c24; " >
<strong>Please fix the following:</strong>

    <ul style="margin: 10px 0 0; padding-left: 20px;">
        @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>


@endif

<div class="card">
<form
    action="{{ route('suppliers.update', $supplier) }}"
    method="POST"
>

    @csrf
    @method('PUT')

    <div
        style="
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 20px;
        "
    >

        {{-- COMPANY --}}
        <div>
            <label
                for="company_id"
                style="
                    font-weight: 600;
                    display: block;
                    margin-bottom: 6px;
                    color: #334155;
                "
            >
                Company
                <span style="color: #dc2626;">*</span>
            </label>

            <select
                id="company_id"
                name="company_id"
                required
                style="
                    width: 100%;
                    padding: 9px 10px;
                    border: 1px solid #cbd5e1;
                    border-radius: 6px;
                    background: #ffffff;
                "
            >
                <option value="">Select Company</option>

                @foreach ($companies as $company)
                    <option
                        value="{{ $company->id }}"
                        {{ old('company_id', $supplier->company_id) == $company->id ? 'selected' : '' }}
                    >
                        {{ $company->name }}
                    </option>
                @endforeach
            </select>

            @error('company_id')
                <div
                    style="
                        color: #dc2626;
                        font-size: 13px;
                        margin-top: 5px;
                    "
                >
                    {{ $message }}
                </div>
            @enderror
        </div>


        {{-- SUPPLIER NAME --}}
        <div>
            <label
                for="name"
                style="
                    font-weight: 600;
                    display: block;
                    margin-bottom: 6px;
                    color: #334155;
                "
            >
                Supplier Name
                <span style="color: #dc2626;">*</span>
            </label>

            <input
                type="text"
                id="name"
                name="name"
                value="{{ old('name', $supplier->name) }}"
                maxlength="200"
                required
                placeholder="Enter supplier name"
                style="
                    width: 100%;
                    padding: 9px 10px;
                    border: 1px solid #cbd5e1;
                    border-radius: 6px;
                "
            >

            @error('name')
                <div
                    style="
                        color: #dc2626;
                        font-size: 13px;
                        margin-top: 5px;
                    "
                >
                    {{ $message }}
                </div>
            @enderror
        </div>


        {{-- CONTACT NAME --}}
        <div>
            <label
                for="contact_name"
                style="
                    font-weight: 600;
                    display: block;
                    margin-bottom: 6px;
                    color: #334155;
                "
            >
                Contact Name
            </label>

            <input
                type="text"
                id="contact_name"
                name="contact_name"
                value="{{ old('contact_name', $supplier->contact_name) }}"
                maxlength="200"
                placeholder="Primary contact person"
                style="
                    width: 100%;
                    padding: 9px 10px;
                    border: 1px solid #cbd5e1;
                    border-radius: 6px;
                "
            >

            @error('contact_name')
                <div
                    style="
                        color: #dc2626;
                        font-size: 13px;
                        margin-top: 5px;
                    "
                >
                    {{ $message }}
                </div>
            @enderror
        </div>


        {{-- PHONE --}}
        <div>
            <label
                for="phone"
                style="
                    font-weight: 600;
                    display: block;
                    margin-bottom: 6px;
                    color: #334155;
                "
            >
                Phone
            </label>

            <input
                type="text"
                id="phone"
                name="phone"
                value="{{ old('phone', $supplier->phone) }}"
                maxlength="50"
                placeholder="Phone number"
                style="
                    width: 100%;
                    padding: 9px 10px;
                    border: 1px solid #cbd5e1;
                    border-radius: 6px;
                "
            >

            @error('phone')
                <div
                    style="
                        color: #dc2626;
                        font-size: 13px;
                        margin-top: 5px;
                    "
                >
                    {{ $message }}
                </div>
            @enderror
        </div>


        {{-- EMAIL --}}
        <div>
            <label
                for="email"
                style="
                    font-weight: 600;
                    display: block;
                    margin-bottom: 6px;
                    color: #334155;
                "
            >
                Email
            </label>

            <input
                type="email"
                id="email"
                name="email"
                value="{{ old('email', $supplier->email) }}"
                maxlength="200"
                placeholder="supplier@example.com"
                style="
                    width: 100%;
                    padding: 9px 10px;
                    border: 1px solid #cbd5e1;
                    border-radius: 6px;
                "
            >

            @error('email')
                <div
                    style="
                        color: #dc2626;
                        font-size: 13px;
                        margin-top: 5px;
                    "
                >
                    {{ $message }}
                </div>
            @enderror
        </div>


        {{-- STATUS --}}
        <div>
            <label
                for="is_active"
                style="
                    font-weight: 600;
                    display: block;
                    margin-bottom: 6px;
                    color: #334155;
                "
            >
                Status
            </label>

            <label
                style="
                    display: flex;
                    align-items: center;
                    gap: 8px;
                    padding: 9px 10px;
                    border: 1px solid #cbd5e1;
                    border-radius: 6px;
                    background: #ffffff;
                    cursor: pointer;
                "
            >
                <input
                    type="checkbox"
                    id="is_active"
                    name="is_active"
                    value="1"
                    {{ old('is_active', $supplier->is_active) ? 'checked' : '' }}
                >

                <span style="color: #334155;">
                    Supplier is active
                </span>
            </label>

            @error('is_active')
                <div
                    style="
                        color: #dc2626;
                        font-size: 13px;
                        margin-top: 5px;
                    "
                >
                    {{ $message }}
                </div>
            @enderror
        </div>


        {{-- ADDRESS --}}
        <div style="grid-column: 1 / -1;">
            <label
                for="address"
                style="
                    font-weight: 600;
                    display: block;
                    margin-bottom: 6px;
                    color: #334155;
                "
            >
                Address
            </label>

            <textarea
                id="address"
                name="address"
                rows="4"
                maxlength="500"
                placeholder="Supplier address"
                style="
                    width: 100%;
                    padding: 9px 10px;
                    border: 1px solid #cbd5e1;
                    border-radius: 6px;
                    resize: vertical;
                "
            >{{ old('address', $supplier->address) }}</textarea>

            @error('address')
                <div
                    style="
                        color: #dc2626;
                        font-size: 13px;
                        margin-top: 5px;
                    "
                >
                    {{ $message }}
                </div>
            @enderror
        </div>

    </div>


    {{-- ACTIONS --}}
    <div
        style="
            display: flex;
            gap: 8px;
            margin-top: 24px;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
        "
    >

        <button
            type="submit"
            class="btn btn-primary"
        >
            Update Supplier
        </button>

        <a
            href="{{ route('suppliers.show', $supplier) }}"
            class="btn btn-secondary"
            style="text-decoration: none;"
        >
            Cancel
        </a>

    </div>

</form>

</div>

@endsection