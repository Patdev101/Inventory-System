@extends('layouts.app')

@section('title', 'Location Details')

@section('content')

<div class="page-header">
    <div>
        <h1>Location Details</h1>
        <p style="margin: 6px 0 0; color: #64748b;">
            Receiving location profile.
        </p>
    </div>

    <div class="actions">
        @if (auth()->user()->isAdmin())
            <a href="{{ route('locations.edit', $location) }}" class="btn btn-primary">
                Edit
            </a>
        @endif

        <a href="{{ route('locations.index') }}" class="btn btn-secondary">
            Back
        </a>
    </div>
</div>


<div class="entity-hero">

    <span class="entity-hero-avatar">
        {{ strtoupper(substr($location->name, 0, 1)) }}
    </span>

    <div class="entity-hero-body">

        <h2 class="entity-hero-name">{{ $location->name }}</h2>

        <div class="entity-hero-sub">
            <span class="code-pill">{{ $location->code }}</span>

            @if ($location->company)
                <span class="company-pill">{{ $location->company->name }}</span>
            @endif
        </div>

        <div class="entity-hero-meta">

            <div class="meta-item" style="grid-column: 1 / -1;">
                <span class="meta-label">Address</span>
                <span class="meta-value">{{ $location->address ?: 'No address provided' }}</span>
            </div>

        </div>

    </div>

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
        background: linear-gradient(135deg, #0891b2, #06b6d4);
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
        margin-bottom: 18px;
    }

    .entity-hero-meta {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
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
        white-space: pre-wrap;
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

    .company-pill {
        display: inline-flex;
        padding: 3px 10px;
        border-radius: 999px;
        background: #eff6ff;
        color: #1d4ed8;
        font-size: 12px;
        font-weight: 700;
    }

    @media (max-width: 700px) {

        .entity-hero {
            flex-direction: column;
        }

    }

</style>

@endsection
