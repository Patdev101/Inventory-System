@extends('layouts.app')

@section('title', 'Add Location')

@section('content')

<div class="page-header" style="align-items: center;">
    <div>
        <h1>Add Location</h1>
        <p style="margin: 6px 0 0; color: #64748b;">
            Register a new warehouse or receiving location.
        </p>
    </div>

    <a href="{{ route('locations.index') }}" class="btn btn-secondary" style="align-self: center;">
        Back to Locations
    </a>
</div>

<div class="card" style="max-width: 640px; margin: 0 auto;">

    @if ($errors->any())
        <div class="alert-error">
            <ul style="margin: 0; padding-left: 20px;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('locations.store') }}" method="POST">
        @csrf

        <div class="form-group">
            <label for="company_id">Company</label>

            <select id="company_id" name="company_id" required>
                <option value="">Select Company</option>

                @foreach ($companies as $company)
                    <option
                        value="{{ $company->id }}"
                        {{ old('company_id') == $company->id ? 'selected' : '' }}
                    >
                        {{ $company->name }} ({{ $company->code }})
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="name">Location Name</label>

            <input
                type="text"
                id="name"
                name="name"
                value="{{ old('name') }}"
                maxlength="300"
                required
            >
        </div>

        <div class="form-group">
            <label for="code">Location Code</label>

            <input
                type="text"
                id="code"
                name="code"
                value="{{ old('code') }}"
                maxlength="100"
                required
            >
        </div>

        <div class="form-group">
            <label for="address">Address</label>

            <textarea
                id="address"
                name="address"
                maxlength="510"
                rows="4"
            >{{ old('address') }}</textarea>
        </div>

        <div class="actions" style="margin-top: 20px;">
            <button type="submit" class="btn btn-success">
                Save Location
            </button>

            <a href="{{ route('locations.index') }}" class="btn btn-secondary">
                Cancel
            </a>
        </div>
    </form>
</div>
@endsection
