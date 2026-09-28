@extends('layouts.app')

@section('title', 'Units of Measure')

@section('content')
<div class="page-header">
    <h1>Units of Measure</h1>

    @if (auth()->user()->isAdmin())
        <div style="display: flex; gap: 8px;">
            <a href="{{ route('units-of-measure.trashed') }}" class="btn btn-secondary">
                Recently Deleted
            </a>

            <a href="{{ route('units-of-measure.create') }}" class="btn btn-primary">
                Add Unit
            </a>
        </div>
    @endif
</div>

@if ($units->count())
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
                @foreach ($units as $unit)
                    <tr>
                        <td>{{ $unit->id }}</td>
                        <td>{{ $unit->name }}</td>
                        <td>{{ $unit->code }}</td>
                        <td>{{ $unit->description ?: '-' }}</td>

                        <td>
                            <div class="actions">
                                <a
                                    href="{{ route('units-of-measure.show', ['units_of_measure' => $unit->id]) }}"
                                    class="btn btn-primary"
                                >
                                    View
                                </a>

                                @if (auth()->user()->isAdmin())
                                    <a
                                        href="{{ route('units-of-measure.edit', ['units_of_measure' => $unit->id]) }}"
                                        class="btn btn-secondary"
                                    >
                                        Edit
                                    </a>

                                    <form
                                        action="{{ route('units-of-measure.destroy', ['units_of_measure' => $unit->id]) }}"
                                        method="POST"
                                        data-confirm="Delete this unit of measure? This can be undone later by an admin."
                                        data-confirm-title="Delete unit of measure"
                                    >
                                        @csrf
                                        @method('DELETE')

                                        <button
                                            type="submit"
                                            class="btn btn-danger"
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
        {{ $units->links() }}
    </div>
@else
    <div class="empty-state">
        <p>No units of measure found.</p>

        @if (auth()->user()->isAdmin())
            <a href="{{ route('units-of-measure.create') }}" class="btn btn-primary">
                Add Unit
            </a>
        @endif
    </div>
@endif
@endsection