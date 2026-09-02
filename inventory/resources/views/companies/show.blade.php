@extends('layouts.app')

@section('title', 'Company Details')

@section('content')

<div class="card">

    <div style="display: flex; justify-content: space-between; align-items: center;">
        <h1>Company Details</h1>

        <div class="actions">
            @if (auth()->user()->isAdmin())
                <a href="{{ route('companies.edit', $company) }}"
                   class="btn btn-primary">
                    Edit
                </a>
            @endif

            <a href="{{ route('companies.index') }}"
               class="btn btn-secondary">
                Back
            </a>
        </div>
    </div>

    <hr>

    <table>
        <tr>
            <th style="width: 200px;">ID</th>
            <td>{{ $company->id }}</td>
        </tr>

        <tr>
            <th>Company Name</th>
            <td>{{ $company->name }}</td>
        </tr>

        <tr>
            <th>Company Code</th>
            <td>{{ $company->code }}</td>
        </tr>

        <tr>
            <th>Address</th>
            <td>{{ $company->address ?? '-' }}</td>
        </tr>

        <tr>
            <th>Phone</th>
            <td>{{ $company->phone ?? '-' }}</td>
        </tr>

        <tr>
            <th>Email</th>
            <td>{{ $company->email ?? '-' }}</td>
        </tr>

        <tr>
            <th>Created</th>
            <td>{{ $company->created_at?->format('Y-m-d H:i:s') }}</td>
        </tr>

        <tr>
            <th>Last Updated</th>
            <td>{{ $company->updated_at?->format('Y-m-d H:i:s') }}</td>
        </tr>
    </table>

    <h2 style="margin-top: 30px;">Locations</h2>

    @if($company->locations->count())

        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Code</th>
                    <th>Address</th>
                </tr>
            </thead>

            <tbody>
                @foreach($company->locations as $location)
                    <tr>
                        <td>{{ $location->name }}</td>
                        <td>{{ $location->code }}</td>
                        <td>{{ $location->address ?? '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

    @else

        <p>No locations registered for this company.</p>

    @endif

</div>

@endsection