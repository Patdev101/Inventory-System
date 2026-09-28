@extends('layouts.app')

@section('title', 'Recently Deleted Locations')

@section('content')

<div class="page-header">
    <div>
        <h1>Recently Deleted Locations</h1>
        <p style="margin: 6px 0 0; color: #64748b;">
            Deleted locations are kept, not destroyed, and can be restored here.
        </p>
    </div>

    <a href="{{ route('locations.index') }}" class="btn btn-secondary">
        Back to Locations
    </a>
</div>


@if ($locations->count())

    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Location</th>
                    <th>Company</th>
                    <th>Code</th>
                    <th>Deleted</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>
                @foreach ($locations as $location)
                    <tr>
                        <td>{{ $location->name }}</td>

                        <td>
                            @if ($location->company)
                                <span class="code-pill">{{ $location->company->name }}</span>
                            @else
                                —
                            @endif
                        </td>

                        <td>
                            <span class="code-pill">{{ $location->code }}</span>
                        </td>

                        <td>{{ format_datetime($location->deleted_at) }}</td>

                        <td>
                            <form action="{{ route('locations.restore', $location->id) }}" method="POST">
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
        {{ $locations->links() }}
    </div>

@else

    <div class="empty-state">
        <p>No deleted locations.</p>
    </div>

@endif


<style>

    .code-pill {
        display: inline-flex;
        padding: 3px 10px;
        border-radius: 999px;
        background: #f1f5f9;
        color: #475569;
        font-size: 12px;
        font-weight: 700;
        letter-spacing: .02em;
    }

</style>

@endsection
