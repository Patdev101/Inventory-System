@extends('layouts.app')

@section('title', 'My Account')

@section('content')

<div class="account-hero">

    <div class="account-avatar">
        {{ strtoupper(substr($user->name, 0, 1)) }}
    </div>

    <div>
        <h1 class="account-hero-name">{{ $user->name }}</h1>

        <div class="account-hero-meta">
            <span class="role-pill role-{{ $user->role }}">{{ ucfirst($user->role) }}</span>
            <span class="account-hero-email">{{ $user->email }}</span>
        </div>
    </div>

</div>


{{-- =========================
     PROFILE
========================= --}}

<div class="account-section">

    <div class="account-section-label">Profile</div>

    <div class="card settings-card">

        <div class="settings-card-header">
            <span class="settings-card-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                    <circle cx="12" cy="7" r="4"></circle>
                </svg>
            </span>

            <div>
                <h2>Name</h2>
                <p>Update the name shown across the system.</p>
            </div>
        </div>

        <form action="{{ route('account.name.update') }}" method="POST">

            @csrf
            @method('PUT')

            <div class="form-group">
                <label for="name">Full Name</label>

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

            <button type="submit" class="btn btn-primary">
                Save Name
            </button>

        </form>

    </div>

</div>


{{-- =========================
     SECURITY
========================= --}}

<div class="account-section">

    <div class="account-section-label">Security</div>

    <div class="account-grid">

        <div class="card settings-card">

            <div class="settings-card-header">
                <span class="settings-card-icon">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="5" width="18" height="14" rx="2"></rect>
                        <path d="m3 7 9 6 9-6"></path>
                    </svg>
                </span>

                <div>
                    <h2>Email Address</h2>
                    <p>Changing your email requires your current password.</p>
                </div>
            </div>

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
                        placeholder="{{ $user->email }}"
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


        <div class="card settings-card">

            <div class="settings-card-header">
                <span class="settings-card-icon">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="4" y="10" width="16" height="10" rx="2"></rect>
                        <path d="M8 10V7a4 4 0 0 1 8 0v3"></path>
                    </svg>
                </span>

                <div>
                    <h2>Password</h2>
                    <p>Choose a strong password you don't use elsewhere.</p>
                </div>
            </div>

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

    </div>

</div>


@if ($user->isAdmin())

    {{-- =========================
         SYSTEM (admin only)
    ========================= --}}

    <div class="account-section">

        <div class="account-section-label">System</div>

        <div class="card settings-card system-card">

            <div class="settings-card-header">
                <span class="settings-card-icon system-icon">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="m4 4 7.07 17 2.51-7.39L21 11.07z"></path>
                    </svg>
                </span>

                <div>
                    <h2>Email Configuration</h2>
                    <p>Verify the system can actually deliver mail. Admin-only.</p>
                </div>
            </div>

            <div class="mail-driver-row">
                <span>Current mail driver</span>
                <span class="code-pill">{{ config('mail.default') }}</span>
            </div>

            @if (config('mail.default') === 'log')
                <p class="mail-driver-note">
                    Emails are written to <code>storage/logs/laravel.log</code> instead of being delivered.
                </p>
            @endif

            <form action="{{ route('account.test-email') }}" method="POST" style="margin-top: 16px;">
                @csrf

                <button type="submit" class="btn btn-secondary">
                    Send Test Email
                </button>
            </form>

        </div>

    </div>

@endif


<style>

    .account-hero {
        display: flex;
        align-items: center;
        gap: 20px;
        background: linear-gradient(135deg, #1e293b, #0f172a);
        border-radius: 14px;
        padding: 28px 30px;
        margin-bottom: 28px;
        box-shadow: 0 10px 25px rgba(15, 23, 42, 0.15);
    }

    .account-avatar {
        flex-shrink: 0;
        width: 68px;
        height: 68px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #3b82f6, #60a5fa);
        color: white;
        font-size: 26px;
        font-weight: 700;
        border: 3px solid rgba(255, 255, 255, 0.15);
    }

    .account-hero-name {
        margin: 0;
        font-size: 22px;
        color: white;
    }

    .account-hero-meta {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-top: 8px;
    }

    .account-hero-email {
        color: #94a3b8;
        font-size: 14px;
    }

    .role-pill {
        display: inline-flex;
        padding: 3px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 700;
    }

    .role-admin {
        background: rgba(248, 113, 113, .15);
        color: #fca5a5;
    }

    .role-manager {
        background: rgba(96, 165, 250, .15);
        color: #93c5fd;
    }

    .role-staff {
        background: rgba(74, 222, 128, .15);
        color: #86efac;
    }

    .account-section {
        margin-bottom: 32px;
    }

    .account-section-label {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .08em;
        color: #94a3b8;
        margin-bottom: 12px;
        padding-left: 2px;
    }

    .account-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
        gap: 20px;
    }

    .settings-card-header {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        margin-bottom: 20px;
        padding-bottom: 18px;
        border-bottom: 1px solid #f1f5f9;
    }

    .settings-card-icon {
        flex-shrink: 0;
        width: 36px;
        height: 36px;
        border-radius: 9px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #eff6ff;
        color: #2563eb;
    }

    .settings-card-icon.system-icon {
        background: #fffbeb;
        color: #d97706;
    }

    .settings-card-header h2 {
        margin: 0 0 3px;
        font-size: 15px;
    }

    .settings-card-header p {
        margin: 0;
        color: #64748b;
        font-size: 12.5px;
        line-height: 1.4;
    }

    .system-card {
        border-color: #fde68a;
        background: linear-gradient(180deg, #fffdf7, #ffffff 60px);
    }

    .mail-driver-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 10px 0;
        border-top: 1px solid #f1f5f9;
        border-bottom: 1px solid #f1f5f9;
    }

    .mail-driver-row span:first-child {
        color: #64748b;
        font-size: 13px;
    }

    .mail-driver-note {
        margin: 10px 0 0;
        font-size: 12px;
        color: #94a3b8;
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

    @media (max-width: 600px) {

        .account-hero {
            flex-direction: column;
            text-align: center;
            padding: 24px;
        }

        .account-hero-meta {
            justify-content: center;
        }

    }

</style>

@endsection
