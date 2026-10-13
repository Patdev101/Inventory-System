@extends('layouts.app')

@section('title', 'Unit of Measure Details')

@section('content')
<div class="container">
    <div class="page-header">
        <div>
            <h1>Unit of Measure Details</h1>
            <p>The name and code of this unit.</p>
        </div>
    </div>

    <div class="card">
        <div class="form-group">
            <label>ID</label>
            <div>{{ $unit->id }}</div>
        </div>

        <div class="form-group">
            <label>Name</label>
            <div>{{ $unit->name }}</div>
        </div>

        <div class="form-group">
            <label>Code</label>
            <div>{{ $unit->code }}</div>
        </div>

        <div class="form-group">
            <label>Description</label>
            <div>{{ $unit->description ?: '-' }}</div>
        </div>

        <div style="margin-top: 20px;">
            @if (auth()->user()->isAdmin())
                <a
                    href="{{ route('units-of-measure.edit', $unit) }}"
                    class="btn btn-primary"
                >
                    Edit
                </a>
            @endif

            <a
                href="{{ route('units-of-measure.index') }}"
                class="btn btn-secondary"
            >
                Back
            </a>
        </div>
    </div>
</div>
@endsection