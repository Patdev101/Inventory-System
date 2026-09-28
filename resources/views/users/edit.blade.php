@extends('layouts.app')

@section('title', 'Edit User')

@section('content')

<div class="card">

    <h1>Edit User</h1>

    <form action="{{ route('users.update', $user) }}" method="POST">

        @csrf
        @method('PUT')

        <div class="form-group">
            <label for="name">Name</label>

            <input
                type="text"
                id="name"
                name="name"
                value="{{ old('name', $user->name) }}"
                maxlength="150"
                required
            >

            @error('name')
                <div class="error">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="email">Email</label>

            <input
                type="email"
                id="email"
                name="email"
                value="{{ old('email', $user->email) }}"
                maxlength="150"
                required
            >

            @error('email')
                <div class="error">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="role">Role</label>

            <select id="role" name="role" required>
                @foreach($assignableRoles as $role)
                    <option value="{{ $role }}" @selected(old('role', $user->role) === $role)>
                        {{ ucfirst($role) }}
                    </option>
                @endforeach
            </select>

            @error('role')
                <div class="error">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label class="checkbox-label" style="display: inline-flex; align-items: center; gap: 7px; font-weight: normal;">
                <input
                    type="checkbox"
                    name="is_active"
                    value="1"
                    style="width: auto; padding: 0;"
                    @checked(old('is_active', $user->is_active))
                >
                Active
            </label>
        </div>

        <button type="submit" class="btn btn-primary">
            Save Changes
        </button>

        <a href="{{ route('users.index') }}" class="btn btn-secondary">
            Cancel
        </a>

    </form>

</div>


<div class="card">

    <h2>Password</h2>

    <p style="color: #6b7280;">
        This user's password is never shown or stored in a way that can
        be viewed. If they've forgotten it, reset it below.
    </p>

    <a href="{{ route('users.reset-password', $user) }}" class="btn btn-secondary">
        Reset Password
    </a>

</div>

@endsection
