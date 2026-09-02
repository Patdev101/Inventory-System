@extends('layouts.app')

@section('title', 'Edit Unit of Measure')

@section('content')
<div class="container">
    <h1>Edit Unit of Measure</h1>

    @if ($errors->any())
        <div class="error" style="margin-bottom: 20px;">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card">
        <form
            action="{{ route('units-of-measure.update', ['units_of_measure' => $unit->id]) }}"
            method="POST"
        >
            @csrf
            @method('PUT')

            <div class="form-group">
                <label for="name">Name</label>

                <input
                    type="text"
                    id="name"
                    name="name"
                    value="{{ old('name', $unit->name) }}"
                    maxlength="200"
                    required
                >
            </div>

            <div class="form-group">
                <label for="code">Code</label>

                <input
                    type="text"
                    id="code"
                    name="code"
                    value="{{ old('code', $unit->code) }}"
                    maxlength="40"
                    required
                >
            </div>

            <div class="form-group">
                <label for="description">Description</label>

                <textarea
                    id="description"
                    name="description"
                    maxlength="510"
                    rows="4"
                >{{ old('description', $unit->description) }}</textarea>
            </div>

            <button type="submit" class="btn btn-primary">
                Update Unit
            </button>

            <a
                href="{{ route('units-of-measure.index') }}"
                class="btn btn-secondary"
            >
                Cancel
            </a>
        </form>
    </div>
</div>
@endsection