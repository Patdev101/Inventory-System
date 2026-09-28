@extends('layouts.app')

@section('title', 'Add Unit of Measure')

@section('content')
<div class="container">
    <h1>Add Unit of Measure</h1>

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
        <form action="{{ route('units-of-measure.store') }}" method="POST">
            @csrf

            <div class="form-group">
                <label for="name">Name</label>

                <input
                    type="text"
                    id="name"
                    name="name"
                    value="{{ old('name') }}"
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
                    value="{{ old('code') }}"
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
                >{{ old('description') }}</textarea>
            </div>

            <button type="submit" class="btn btn-primary">
                Save Unit
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