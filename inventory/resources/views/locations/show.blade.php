@extends('layouts.app')

@section('title', 'Location Details')

@section('content')
<div class="page-header">
    <h1>Location Details</h1>
</div>

<div class="card">

    <p>
        <strong>ID:</strong>
        {{ $location->id }}
    </p>

    <p>
        <strong>Company:</strong>
        {{ $location->company->name ?? '-' }}
    </p>

    <p>
        <strong>Location Name:</strong>
        {{ $location->name }}
    </p>

    <p>
        <strong>Code:</strong>
        {{ $location->code }}
    </p>

    <p>
        <strong>Address:</strong>
        {{ $location->address ?: 'No address provided' }}
    </p>

    <div style="margin-top: 25px;">
        @if (auth()->user()->isAdmin())
            <a
                href="{{ route('locations.edit', $location) }}"
                class="btn btn-primary"
            >
                Edit
            </a>
        @endif

        <a
            href="{{ route('locations.index') }}"
            class="btn btn-secondary"
        >
            Back
        </a>
    </div>

</div>
@endsection