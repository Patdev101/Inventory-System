@extends('layouts.app')

@section('title', 'Locations')

@section('content')
<div class="page-header">
    <h1>Locations</h1>

    @if (auth()->user()->isAdmin())
        <a href="{{ route('locations.create') }}" class="btn btn-primary">
            Add Location
        </a>
    @endif
</div>

@if ($locations->count())
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Company</th>
                    <th>Name</th>
                    <th>Code</th>
                    <th>Address</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>
                @foreach ($locations as $location)
                    <tr>
                        <td>{{ $location->id }}</td>

                        <td>
                            {{ $location->company->name ?? '—' }}
                        </td>

                        <td>{{ $location->name }}</td>

                        <td>{{ $location->code }}</td>

                        <td>{{ $location->address ?: '—' }}</td>

                        <td>
                            <div class="actions">
                                <a
                                    href="{{ route('locations.show', $location) }}"
                                    class="btn btn-primary"
                                >
                                    View
                                </a>

                                @if (auth()->user()->isAdmin())
                                    <a
                                        href="{{ route('locations.edit', $location) }}"
                                        class="btn btn-secondary"
                                    >
                                        Edit
                                    </a>

                                    <form
                                        action="{{ route('locations.destroy', $location) }}"
                                        method="POST"
                                    >
                                        @csrf
                                        @method('DELETE')

                                        <button
                                            type="submit"
                                            class="btn btn-danger"
                                            onclick="return confirm('Delete this location?')"
                                        >
                                            Delete
                                        </button>
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
        {{ $locations->links() }}
    </div>
@else
    <div class="empty-state">
        <p>No locations found.</p>

        @if (auth()->user()->isAdmin())
            <a href="{{ route('locations.create') }}" class="btn btn-primary">
                Add Location
            </a>
        @endif
    </div>
@endif
@endsection