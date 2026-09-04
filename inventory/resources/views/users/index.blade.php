@extends('layouts.app')

@section('title', 'Users')

@section('content')

<div class="card">

    <div style="display: flex; justify-content: space-between; align-items: center;">
        <h1>Users</h1>

        <a href="{{ route('users.create') }}" class="btn btn-primary">
            + Add User
        </a>
    </div>

    @if($users->count())

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
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
                        <td>{{ $user->is_active ? 'Active' : 'Deactivated' }}</td>
                        <td>
                            <div class="actions">
                                @if ($user->id !== auth()->id())
                                    <a href="{{ route('users.edit', $user) }}" class="btn btn-secondary">
                                        Edit
                                    </a>

                                    <a href="{{ route('users.reset-password', $user) }}" class="btn btn-secondary">
                                        Reset Password
                                    </a>

                                    @if ($user->is_active)
                                        <form action="{{ route('users.deactivate', $user) }}"
                                              method="POST"
                                              onsubmit="return confirm('Deactivate this account?');">
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

        <div style="margin-top: 20px;">
            {{ $users->links() }}
        </div>

    @else

        <p>No users found.</p>

    @endif

</div>

@endsection
