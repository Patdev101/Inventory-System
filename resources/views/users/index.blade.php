@extends('layouts.app')

@section('title', 'Users')

@section('content')

<div class="page-header">
    <div>
        <h1>Users</h1>
        <p>People who can sign in, and the location each one works at.</p>
    </div>

    <div class="actions">
        <a href="{{ route('account-audit-log.index') }}" class="btn btn-secondary">
                Audit Log
            </a>
            <a href="{{ route('users.create') }}" class="btn btn-primary">
                + Add User
            </a>
    </div>
</div>

<div class="card">

    @if($users->count())

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
<th>Location</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>
                @foreach($users as $user)
                    <tr>
                        <td>{{ $user->id }}</td>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>{{ ucfirst($user->role) }}</td>
<td>{{ $user->assignedLocation?->name ?? ($user->isAdmin() ? 'All locations' : 'Not assigned') }}</td>
                        <td>{{ $user->is_active ? 'Active' : 'Deactivated' }}</td>
                        <td>
                            <div class="actions">
                                @if ($user->id !== auth()->id())
                                    <a href="{{ route('users.edit', $user) }}" class="btn btn-primary">
                                        Edit
                                    </a>

                                    <a href="{{ route('users.reset-password', $user) }}" class="btn btn-secondary">
                                        Reset Password
                                    </a>

                                    @if ($user->is_active)
                                        <form action="{{ route('users.deactivate', $user) }}"
                                              method="POST"
                                              data-confirm="Deactivate this account? They won't be able to log in until reactivated."
                                              data-confirm-title="Deactivate account">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-danger">
                                                Deactivate
                                            </button>
                                        </form>
                                    @else
                                        <form action="{{ route('users.activate', $user) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-success">
                                                Activate
                                            </button>
                                        </form>
                                    @endif
                                @else
                                    <a href="{{ route('account.edit') }}" class="btn btn-secondary">
                                        My Account
                                    </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        @if ($users->hasPages())
            <div style="padding: 16px;">
                {{ $users->links() }}
            </div>
        @endif

    @else

        <p>No users found.</p>

    @endif

</div>

@endsection
