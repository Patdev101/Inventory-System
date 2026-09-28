@extends('layouts.app')

@section('title', 'Company Details')

@section('content')

<div class="page-header">
    <div>
        <h1>Company Details</h1>
        <p style="margin: 6px 0 0; color: #64748b;">
            Company profile and its registered locations.
        </p>
    </div>

    <div class="actions">
        @if (auth()->user()->isAdmin())
            <a href="{{ route('companies.edit', $company) }}" class="btn btn-primary">
                Edit
            </a>
        @endif

        <a href="{{ route('companies.index') }}" class="btn btn-secondary">
            Back
        </a>
    </div>
</div>


{{-- HERO --}}
<div class="entity-hero">

    <span class="entity-hero-avatar">
        {{ strtoupper(substr($company->name, 0, 1)) }}
    </span>

    <div class="entity-hero-body">

        <h2 class="entity-hero-name">{{ $company->name }}</h2>

        <div class="entity-hero-sub">
            <span class="code-pill">{{ $company->code }}</span>
            <span>Registered {{ format_date($company->created_at) ?? '—' }}</span>
        </div>

        <div class="entity-hero-meta">

            <div class="meta-item">
                <span class="meta-label">Address</span>
                <span class="meta-value">{{ $company->address ?? '—' }}</span>
            </div>

            <div class="meta-item">
                <span class="meta-label">Phone</span>
                <span class="meta-value">{{ $company->phone ?? '—' }}</span>
            </div>

            <div class="meta-item">
                <span class="meta-label">Email</span>
                <span class="meta-value">{{ $company->email ?? '—' }}</span>
            </div>

            <div class="meta-item">
                <span class="meta-label">Last Updated</span>
                <span class="meta-value">{{ format_datetime($company->updated_at) ?? '—' }}</span>
            </div>

        </div>

    </div>

</div>


{{-- LOCATIONS --}}
<div class="card section-card">

    <h2 class="section-title">Locations</h2>

    @if ($company->locations->count())

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Code</th>
                        <th>Address</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($company->locations as $location)
                        <tr>
                            <td>{{ $location->name }}</td>
                            <td><span class="code-pill">{{ $location->code }}</span></td>
                            <td>{{ $location->address ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

    @else

        <p style="color: #64748b;">No locations registered for this company.</p>

    @endif

</div>


<style>

    .entity-hero {
        display: flex;
        gap: 22px;
        align-items: flex-start;
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        box-shadow: 0 4px 16px rgba(15, 23, 42, .05);
        padding: 26px;
        margin-bottom: 20px;
    }

    .entity-hero-avatar {
        flex-shrink: 0;
        width: 64px;
        height: 64px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #2563eb, #3b82f6);
        color: #fff;
        font-size: 26px;
        font-weight: 800;
    }

    .entity-hero-body {
        flex: 1;
        min-width: 0;
    }

    .entity-hero-name {
        margin: 0 0 8px;
        font-size: 22px;
        color: #0f172a;
        font-weight: 800;
    }

    .entity-hero-sub {
        display: flex;
        align-items: center;
        gap: 10px;
        color: #64748b;
        font-size: 13px;
        margin-bottom: 18px;
    }

    .entity-hero-meta {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 14px;
    }

    .meta-item {
        display: flex;
        flex-direction: column;
        gap: 5px;
        padding: 12px 14px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
    }

    .meta-label {
        color: #94a3b8;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .04em;
    }

    .meta-value {
        color: #0f172a;
        font-size: 14px;
        font-weight: 700;
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

    .section-card {
        margin-bottom: 20px;
    }

    .section-title {
        margin: 0 0 14px;
        font-size: 18px;
        color: #0f172a;
    }

    @media (max-width: 700px) {

        .entity-hero {
            flex-direction: column;
        }

        .entity-hero-meta {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

    }

</style>

@endsection
