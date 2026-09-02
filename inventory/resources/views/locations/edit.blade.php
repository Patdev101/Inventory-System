@extends('layouts.app')

@section('title', 'Edit Location')

@section('content')
<div class="page-header">
    <h1>Edit Location</h1>
</div>

<div class="card">

    @if ($errors->any())
        <div class="alert-error">
            <ul style="margin: 0; padding-left: 20px;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form
        action="{{ route('locations.update', $location) }}"
        method="POST"
    >
        @csrf
        @method('PUT')

        <div class="form-group">
            <label for="company_id">Company</label>

            <select id="company_id" name="company_id" required>
                <option value="">Select Company</option>

                @foreach ($companies as $company)
                    <option
                        value="{{ $company->id }}"
                        {{ old('company_id', $location->company_id) == $company->id ? 'selected' : '' }}
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
                value="{{ old('name', $location->name) }}"
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
                value="{{ old('code', $location->code) }}"
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
            >{{ old('address', $location->address) }}</textarea>
        </div>

        <button type="submit" class="btn btn-success">
            Update Location
        </button>

        <a href="{{ route('locations.index') }}" class="btn btn-secondary">
            Cancel
        </a>
    </form>
</div>
@endsection