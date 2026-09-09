@extends('layouts.app')

@section('title', 'Recently Deleted Units of Measure')

@section('content')
<div class="page-header">
    <div>
        <h1>Recently Deleted Units of Measure</h1>
        <p>Deleted units are kept, not destroyed, and can be restored here.</p>
    </div>

    <a href="{{ route('units-of-measure.index') }}" class="btn btn-secondary">
        Back to Units of Measure
    </a>
</div>

@if ($units->count())
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
                @foreach ($units as $unit)
                    <tr>
                        <td>{{ $unit->name }}</td>
                        <td>{{ $unit->code }}</td>
                        <td>{{ format_datetime($unit->deleted_at) }}</td>

                        <td>
                            <form
                                action="{{ route('units-of-measure.restore', ['units_of_measure' => $unit->id]) }}"
                                method="POST"
                            >
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

    <div style="margin-top: 20px;">
        {{ $units->links() }}
    </div>
@else
    <div class="empty-state">
        <p>No deleted units of measure.</p>
    </div>
@endif
@endsection
