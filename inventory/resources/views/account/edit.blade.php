@extends('layouts.app')

@section('title', 'My Account')

@section('content')

<div class="card">

    <h1>My Account</h1>

    <p style="color: #6b7280; margin-top: -8px;">
        Name: <strong>{{ $user->name }}</strong><br>
        Current Email: <strong>{{ $user->email }}</strong><br>
        Role: <strong>{{ ucfirst($user->role) }}</strong>
    </p>

</div>


<div class="card">

    <h2>Change Email</h2>

    <form action="{{ route('account.email.update') }}" method="POST">

        @csrf
        @method('PUT')

        <div class="form-group">
            <label for="email">New Email</label>

            <input
                type="email"
                id="email"
                name="email"
                value="{{ old('email') }}"
                maxlength="150"
                required
            >

            @error('email')
                <div class="error">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="current_password_email">Current Password</label>

            <input
                type="password"
                id="current_password_email"
                name="current_password"
                required
            >

            @error('current_password')
                <div class="error">{{ $message }}</div>
            @enderror
        </div>

        <button type="submit" class="btn btn-primary">
            Save Email
        </button>

    </form>

</div>


<div class="card">

    <h2>Change Password</h2>

    <form action="{{ route('account.password.update') }}" method="POST">

        @csrf
        @method('PUT')

        <div class="form-group">
            <label for="current_password">Current Password</label>

            <input
                type="password"
                id="current_password"
                name="current_password"
                required
            >

            @error('current_password')
                <div class="error">{{ $message }}</div>
            @enderror
        </div>

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

        <button type="submit" class="btn btn-primary">
            Change Password
        </button>

    </form>

</div>

@endsection
