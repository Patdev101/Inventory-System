@extends('layouts.app')

@section('title', 'Account Audit Log')

@section('content')

<div class="page-header">
    <div>
        <h1>Account Audit Log</h1>
        <p style="margin: 6px 0 0; color: #64748b;">Who changed what on user accounts, and when.</p>
    </div>
    <a href="{{ route('users.index') }}" class="btn btn-secondary">Back to Users</a>
</div>

<div class="card" style="margin-bottom: 20px;">
    <form method="GET" action="{{ route('account-audit-log.index') }}" style="display: flex; gap: 12px; flex-wrap: wrap; align-items: end;">
        <div class="form-group" style="flex: 1; min-width: 180px; margin-bottom: 0;">
            <label for="event">Event</label>
            <input type="text" id="event" name="event" value="{{ $filters['event'] ?? '' }}" placeholder="e.g. password, role, email">
        </div>
        <div class="form-group" style="min-width: 160px; margin-bottom: 0;">
            <label for="date_from">From</label>
            <input type="date" id="date_from" name="date_from" value="{{ $filters['date_from'] ?? '' }}">
        </div>
        <div class="form-group" style="min-width: 160px; margin-bottom: 0;">
            <label for="date_to">To</label>
            <input type="date" id="date_to" name="date_to" value="{{ $filters['date_to'] ?? '' }}">
        </div>
        <button type="submit" class="btn btn-primary">Filter</button>
        @if (array_filter($filters))
            <a href="{{ route('account-audit-log.index') }}" class="btn btn-secondary">Reset</a>
        @endif
    </form>
</div>

@if ($logs->count())
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Date/Time</th>
                    <th>Event</th>
                    <th>User</th>
                    <th>Details</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($logs as $log)
                    @php
                        $labels = [
                            'account.email.changed_by_self' => 'Changed Own Email',
                            'account.name.changed_by_self' => 'Changed Own Name',
                            'account.password.changed_by_self' => 'Changed Own Password',
                            'account.email.changed_by_admin' => 'Email Changed by Admin',
                            'account.role.changed_by_admin' => 'Role Changed',
                            'account.status.changed_by_admin' => 'Account Status Changed',
                            'account.password.reset_by_admin' => 'Password Reset by Admin',
                            'account.password.reset_via_email_link' => 'Password Reset by Email',
                        ];

                        $c = $log->context ?? [];

                        $description = match ($log->event) {
                            'account.email.changed_by_self' => 'Changed their own email from ' . ($c['old_email'] ?? 'unknown') . ' to ' . ($c['new_email'] ?? 'unknown') . '.',
                            'account.name.changed_by_self' => 'Changed their own display name from "' . ($c['old_name'] ?? 'unknown') . '" to "' . ($c['new_name'] ?? 'unknown') . '".',
                            'account.password.changed_by_self' => 'Changed their own password.',
                            'account.email.changed_by_admin' => 'Changed another user\'s email from ' . ($c['old_email'] ?? 'unknown') . ' to ' . ($c['new_email'] ?? 'unknown') . '.',
                            'account.role.changed_by_admin' => 'Changed ' . ($c['target_email'] ?? 'a user') . '\'s role from ' . ($c['old_role'] ?? 'unknown') . ' to ' . ($c['new_role'] ?? 'unknown') . '.',
                            'account.status.changed_by_admin' => (($c['is_active'] ?? false) ? 'Reactivated' : 'Deactivated') . ' the account for ' . ($c['target_email'] ?? 'a user') . '.',
                            'account.password.reset_by_admin' => 'Reset the password for ' . ($c['target_email'] ?? 'a user') . (($c['must_change_password'] ?? false) ? ', and required them to set a new one on next login' : '') . '.',
                            'account.password.reset_via_email_link' => 'Reset their own password using the link emailed to them.',
                            default => $c ? json_encode($c) : '',
                        };
                    @endphp
                    <tr>
                        <td>{{ format_datetime($log->created_at) }}</td>
                        <td><span class="stock-status stock-ok" title="{{ $log->event }}">{{ $labels[$log->event] ?? $log->event }}</span></td>
                        <td>{{ $log->user ? $log->user->name . ' (' . $log->user->email . ')' : '—' }}</td>
                        <td style="max-width: 420px; white-space: normal; overflow-wrap: anywhere; font-size: 13px; color: #374151;">
                            {{ $description }}
                            @if ($c)
                                <details style="margin-top: 4px;">
                                    <summary style="cursor: pointer; font-size: 11px; color: #94a3b8;">Technical details</summary>
                                    <pre style="white-space: pre-wrap; font-size: 11px; color: #64748b; margin: 6px 0 0;">{{ json_encode($c, JSON_PRETTY_PRINT) }}</pre>
                                </details>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div style="margin-top: 20px;">
        {{ $logs->links() }}
    </div>
@else
    <div class="empty-state">
        <p>No account events match these filters.</p>
        <a href="{{ route('account-audit-log.index') }}" class="btn btn-secondary">Clear Filters</a>
    </div>
@endif

@endsection
