@extends('layouts.app')

@section('title', 'Recently Deleted Product Categories')

@section('content')

<div class="page-header">
    <div>
        <h1>Recently Deleted Product Categories</h1>
        <p>Deleted categories are kept, not destroyed, and can be restored here.</p>
    </div>

    <a href="{{ route('product-categories.index') }}" class="btn btn-secondary">
        Back to Product Categories
    </a>
</div>

@if ($categories->count())
    <div class="card">
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Code</th>
                        <th>Deleted</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($categories as $category)
                        <tr>
                            <td><strong>{{ $category->name }}</strong></td>
                            <td>{{ $category->code }}</td>
                            <td>{{ format_datetime($category->deleted_at) }}</td>
                            <td>
                                <form action="{{ route('product-categories.restore', $category->id) }}" method="POST">
                                    @csrf
                                    @method('PATCH')

                                    <button type="submit" class="btn btn-primary">
                                        Restore
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div style="margin-top: 20px;">
        {{ $categories->links() }}
    </div>
@else
    <div class="empty-state">
        <p>No deleted product categories.</p>
    </div>
@endif

@endsection
