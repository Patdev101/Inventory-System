@extends('layouts.app')

@section('title', 'Edit Product Category')

@section('content')
<div class="page-header">
    <div>
        <h1>Edit Product Category</h1>
        <p>Update category information and organization details.</p>
    </div>
</div>

<div class="card">

    @if ($errors->any())
        <div style="color: red; margin-bottom: 15px;">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('product-categories.update', $productCategory->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label for="name">Category Name</label>
            <input
                type="text"
                id="name"
                name="name"
                value="{{ old('name', $productCategory->name) }}"
                maxlength="300"
                required
            >
        </div>

        <div class="form-group">
            <label for="code">Category Code</label>
            <input
                type="text"
                id="code"
                name="code"
                value="{{ old('code', $productCategory->code) }}"
                maxlength="100"
                required
            >
        </div>

        <div class="form-group">
            <label for="description">Description</label>
            <textarea
                id="description"
                name="description"
                maxlength="1000"
                rows="4"
            >{{ old('description', $productCategory->description) }}</textarea>
        </div>

        <div class="actions">
            <button type="submit" class="btn btn-primary">Update Category</button>

            <a href="{{ route('product-categories.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection