@extends('layouts.app')

@section('title', 'Reset Password')

@section('content')

<div class="card">

    <h1>Reset Password</h1>

    <p style="color: #6b7280;">
        User: <strong>{{ $user->name }}</strong><br>
        Email: <strong>{{ $user->email }}</strong>
    </p>

    <form action="{{ route('users.reset-password.store', $user) }}" method="POST">

        @csrf

        <div class="form-group">
            <label for="password">New Password</label>

            <input
                type="password"
                id="password"
                name="password"
                required
            >

            @error('password')
                <div class="error">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="password_confirmation">Confirm New Password</label>

            <input
                type="password"
                id="password_confirmation"
                name="password_confirmation"
                required
            >
        </div>

        <div class="form-group">
            <label class="checkbox-label" style="display: inline-flex; align-items: center; gap: 7px; font-weight: normal;">
                <input type="checkbox" name="require_password_change" value="1" style="width: auto; padding: 0;">
                Require password change on next login
            </label>
        </div>

        <button type="submit" class="btn btn-primary">
            Reset Password
        </button>

        <a href="{{ route('users.index') }}" class="btn btn-secondary">
            Cancel
        </a>

    </form>

</div>

@endsection
