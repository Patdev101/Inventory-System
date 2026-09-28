@extends('layouts.app')

@section('title', 'Companies')

@section('content')

<div class="page-header">
    <div>
        <h1>Companies</h1>
        <p style="margin: 6px 0 0; color: #64748b;">
            Manage the companies operating within this system.
        </p>
    </div>

    @if (auth()->user()->isAdmin())
        <div style="display: flex; gap: 8px;">
            <a href="{{ route('companies.trashed') }}" class="btn btn-secondary">
                Recently Deleted
            </a>

            <a href="{{ route('companies.create') }}" class="btn btn-primary">
                + Add Company
            </a>
        </div>
    @endif
</div>


@if ($companies->count())

    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Company</th>
                    <th>Code</th>
                    <th>Phone</th>
                    <th>Email</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>
                @foreach ($companies as $company)
                    <tr>
                        <td>
                            <div class="entity-cell">
                                <span class="entity-avatar">
                                    {{ strtoupper(substr($company->name, 0, 1)) }}
                                </span>
                                <span class="entity-name">{{ $company->name }}</span>
                            </div>
                        </td>

                        <td>
                            <span class="code-pill">{{ $company->code }}</span>
                        </td>

                        <td>{{ $company->phone ?? '—' }}</td>
                        <td>{{ $company->email ?? '—' }}</td>

                        <td>
                            <div class="actions">

                                <a href="{{ route('companies.show', $company) }}" class="btn btn-primary">
                                    View
                                </a>

                                @if (auth()->user()->isAdmin())
                                    <a href="{{ route('companies.edit', $company) }}" class="btn btn-secondary">
                                        Edit
                                    </a>

                                    <form
                                        action="{{ route('companies.destroy', $company) }}"
                                        method="POST"
                                        data-confirm="Delete this company? This can be undone later by an admin."
                                        data-confirm-title="Delete company"
                                    >
                                        @csrf
                                        @method('DELETE')

                                        <button type="submit" class="btn btn-danger">
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
        {{ $companies->links() }}
    </div>

@else

    <div class="empty-state">
        <p>No companies have been registered yet.</p>

        @if (auth()->user()->isAdmin())
            <a href="{{ route('companies.create') }}" class="btn btn-primary">
                + Add Company
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
        background: linear-gradient(135deg, #2563eb, #3b82f6);
        color: #fff;
        font-size: 13px;
        font-weight: 700;
    }

    .entity-name {
        font-weight: 600;
        color: #0f172a;
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
