@extends('layouts.app')

@section('title', 'Locations')

@section('content')

<div class="page-header">
    <div>
        <h1>Locations</h1>
        <p style="margin: 6px 0 0; color: #64748b;">
            Manage warehouses and receiving locations.
        </p>
    </div>

    @if (auth()->user()->isAdmin())
        <a href="{{ route('locations.create') }}" class="btn btn-primary">
            + Add Location
        </a>
    @endif
</div>


@if ($locations->count())

    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Location</th>
                    <th>Company</th>
                    <th>Code</th>
                    <th>Address</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>
                @foreach ($locations as $location)
                    <tr>
                        <td>
                            <div class="entity-cell">
                                <span class="entity-avatar">
                                    {{ strtoupper(substr($location->name, 0, 1)) }}
                                </span>
                                <span class="entity-name">{{ $location->name }}</span>
                            </div>
                        </td>

                        <td>
                            @if ($location->company)
                                <span class="company-pill">{{ $location->company->name }}</span>
                            @else
                                —
                            @endif
                        </td>

                        <td>
                            <span class="code-pill">{{ $location->code }}</span>
                        </td>

                        <td>{{ $location->address ?: '—' }}</td>

                        <td>
                            <div class="actions">
                                <a href="{{ route('locations.show', $location) }}" class="btn btn-primary">
                                    View
                                </a>

                                @if (auth()->user()->isAdmin())
                                    <a href="{{ route('locations.edit', $location) }}" class="btn btn-secondary">
                                        Edit
                                    </a>

                                    <form action="{{ route('locations.destroy', $location) }}" method="POST">
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
                + Add Location
            </a>
        @endif
    </div>

@endif


<style>

    .entity-cell {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .entity-avatar {
        flex-shrink: 0;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #0891b2, #06b6d4);
        color: #fff;
        font-size: 13px;
        font-weight: 700;
    }

    .entity-name {
        font-weight: 600;
        color: #0f172a;
    }

    .company-pill {
        display: inline-flex;
        padding: 3px 10px;
        border-radius: 999px;
        background: #eff6ff;
        color: #1d4ed8;
        font-size: 12px;
        font-weight: 700;
    }

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
