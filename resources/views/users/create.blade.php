@extends('layouts.app')

@section('title', 'Add User')

@section('content')

<div class="card">

    <h1>Add User</h1>

    <form action="{{ route('users.store') }}" method="POST">

        @csrf

        <div class="form-group">
            <label for="name">Name</label>

            <input
                type="text"
                id="name"
                name="name"
                value="{{ old('name') }}"
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
                value="{{ old('email') }}"
                maxlength="150"
                required
            >

            @error('email')
                <div class="error">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="password">Password</label>

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
            <label for="password_confirmation">Confirm Password</label>

            <input
                type="password"
                id="password_confirmation"
                name="password_confirmation"
                required
            >
        </div>

        <div class="form-group">
            <label for="role">Role</label>

            <select id="role" name="role" required>
                <option value="">Select a role</option>

                @foreach($assignableRoles as $role)
                    <option value="{{ $role }}" @selected(old('role') === $role)>
                        {{ ucfirst($role) }}
                    </option>
                @endforeach
            </select>

            @error('role')
                <div class="error">{{ $message }}</div>
            @enderror
        </div>

        @if ($canChooseLocation)
            <div class="form-group">
                <label for="location_id">Location</label>

                <select id="location_id" name="location_id">
                    <option value="">No location (admins only)</option>

                    @foreach ($locations->groupBy(fn ($location) => $location->company?->name ?? 'No company') as $companyName => $companyLocations)
                        <optgroup label="{{ $companyName }}">
                            @foreach ($companyLocations as $location)
                                <option value="{{ $location->id }}" @selected((string) old('location_id', $selectedLocationId ?? '') === (string) $location->id)>
                                    {{ $location->name }}
                                </option>
                            @endforeach
                        </optgroup>
                    @endforeach
                </select>

                <small class="muted">Staff and managers only see and change this location's data. Admins can see everything.</small>

                @error('location_id')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>
        @else
            <div class="form-group">
                <label>Location</label>
                <div>{{ $actorLocationName ?? 'Your location' }}</div>
                <small class="muted">New people are placed at your location.</small>
            </div>
        @endif
        <button type="submit" class="btn btn-primary">
            Save User
        </button>

        <a href="{{ route('users.index') }}" class="btn btn-secondary">
            Cancel
        </a>

    </form>

</div>

@endsection
