@extends('layouts.app')

@section('title', 'Add Product Category')

@section('content')

<div class="page-header">
    <div>
        <h1>Add Product Category</h1>
        <p>Create a category for organizing products.</p>
    </div>
</div>

<div class="card">
    <form action="{{ route('product-categories.store') }}" method="POST">
        @csrf

        <div class="form-group">
            <label for="name">Name</label><br>
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
            <label for="code">Code</label><br>
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
            <label for="description">Description</label><br>
            <textarea
                id="description"
                name="description"
                maxlength="1000"
                rows="4"
            >{{ old('description') }}</textarea>
        </div>

        <div class="actions">
            <button type="submit" class="btn btn-primary">Save Category</button>
            <a href="{{ route('product-categories.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
@endsection
