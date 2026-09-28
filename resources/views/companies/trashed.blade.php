@extends('layouts.app')

@section('title', 'Recently Deleted Companies')

@section('content')

<div class="page-header">
    <div>
        <h1>Recently Deleted Companies</h1>
        <p style="margin: 6px 0 0; color: #64748b;">
            Deleted companies are kept, not destroyed, and can be restored here.
        </p>
    </div>

    <a href="{{ route('companies.index') }}" class="btn btn-secondary">
        Back to Companies
    </a>
</div>


@if ($companies->count())

    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Company</th>
                    <th>Code</th>
                    <th>Deleted</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>
                @foreach ($companies as $company)
                    <tr>
                        <td>{{ $company->name }}</td>

                        <td>{{ $company->code }}</td>

                        <td>{{ format_datetime($company->deleted_at) }}</td>

                        <td>
                            <form action="{{ route('companies.restore', $company->id) }}" method="POST">
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
        {{ $companies->links() }}
    </div>

@else

    <div class="empty-state">
        <p>No deleted companies.</p>
    </div>

@endif

@endsection
