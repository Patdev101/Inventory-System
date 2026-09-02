@extends('layouts.app')

@section('title', 'Product Category Details')

@section('content')
<div class="page-header">
    <div>
        <h1>Product Category Details</h1>
        <p>Review category information and assigned products.</p>
    </div>

    <div class="actions">
        @if (auth()->user()->isAdmin())
            <a href="{{ route('product-categories.edit', $productCategory) }}" class="btn btn-primary">Edit</a>
        @endif
        <a href="{{ route('product-categories.index') }}" class="btn btn-secondary">Back</a>
    </div>
</div>

<div class="card">
    <table>
        <tr>
            <th style="width: 220px;">ID</th>
            <td>{{ $productCategory->id }}</td>
        </tr>
        <tr>
            <th>Name</th>
            <td>{{ $productCategory->name }}</td>
        </tr>
        <tr>
            <th>Code</th>
            <td>{{ $productCategory->code }}</td>
        </tr>
        <tr>
            <th>Description</th>
            <td>{{ $productCategory->description ?: 'No description' }}</td>
        </tr>
    </table>
</div>

@if ($productCategory->products->count())
    <div class="card">
        <h2>Products in this Category</h2>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Code</th>
                    </tr>
                </thead>
                <tbody>
                @foreach ($productCategory->products as $product)
                    <tr>
                        <td>{{ $product->name }}</td>
                        <td>{{ $product->code ?: '-' }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif
@endsection