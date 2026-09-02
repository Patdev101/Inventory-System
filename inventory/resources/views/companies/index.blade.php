@extends('layouts.app')

@section('title', 'Companies')

@section('content')

<div class="card">

    <div style="display: flex; justify-content: space-between; align-items: center;">
        <h1>Companies</h1>

        @if (auth()->user()->isAdmin())
            <a href="{{ route('companies.create') }}" class="btn btn-primary">
                + Add Company
            </a>
        @endif
    </div>

    @if($companies->count())

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Code</th>
                    <th>Phone</th>
                    <th>Email</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>
                @foreach($companies as $company)
                    <tr>
                        <td>{{ $company->id }}</td>
                        <td>{{ $company->name }}</td>
                        <td>{{ $company->code }}</td>
                        <td>{{ $company->phone ?? '-' }}</td>
                        <td>{{ $company->email ?? '-' }}</td>

                        <td>
                            <div class="actions">

                                <a href="{{ route('companies.show', $company) }}"
                                   class="btn btn-secondary">
                                    View
                                </a>

                                @if (auth()->user()->isAdmin())
                                    <a href="{{ route('companies.edit', $company) }}"
                                       class="btn btn-primary">
                                        Edit
                                    </a>

                                    <form action="{{ route('companies.destroy', $company) }}"
                                          method="POST"
                                          onsubmit="return confirm('Delete this company?');">

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

        <div style="margin-top: 20px;">
            {{ $companies->links() }}
        </div>

    @else

        <p>No companies have been registered yet.</p>

    @endif

</div>

@endsection