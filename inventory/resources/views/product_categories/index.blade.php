@extends('layouts.app')

@section('title', 'Product Categories')

@section('content')

<div class="page-header">
    <div>
        <h1>Product Categories</h1>
        <p>Organize products into manageable groups.</p>
    </div>

    @if (auth()->user()->isAdmin())
        <a href="{{ route('product-categories.create') }}" class="btn btn-primary">
            + Add Category
        </a>
    @endif
</div>

@if ($categories->count())
    <div class="card">
        <div class="table-wrapper">
            <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Code</th>
                    <th>Description</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>
                @foreach ($categories as $category)
                    <tr>
                        <td>{{ $category->id }}</td>
                        <td><strong>{{ $category->name }}</strong></td>
                        <td>{{ $category->code }}</td>
                        <td>{{ $category->description ?: '-' }}</td>
                        <td>
                            <div class="actions">
                                <a href="{{ route('product-categories.show', $category) }}" class="btn btn-secondary">View</a>
                                @if (auth()->user()->isAdmin())
                                    <a href="{{ route('product-categories.edit', $category) }}" class="btn btn-primary">Edit</a>
                                    <form action="{{ route('product-categories.destroy', $category) }}" method="POST" onsubmit="return confirm('Delete this product category?');">
                                    @csrf
                                    @method('DELETE')
                                        <button type="submit" class="btn btn-danger">Delete</button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
            </table>
        </div>

        <div style="margin-top: 20px;">
            {{ $categories->links() }}
        </div>
    </div>
@else
    <div class="empty-state">
        <p>No product categories have been registered yet.</p>
        @if (auth()->user()->isAdmin())
            <a href="{{ route('product-categories.create') }}" class="btn btn-primary">Create the first category</a>
        @endif
    </div>
@endif

@endsection