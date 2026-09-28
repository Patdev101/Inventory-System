@extends('layouts.app')

@section('title', 'Sent Emails')

@section('content')

<div class="page-header">
    <div>
        <h1>Sent Emails</h1>
        <p style="margin: 6px 0 0; color: #64748b;">
            Every purchase order email sent, across all suppliers and accounts.
        </p>
    </div>
</div>


<div class="card" style="margin-bottom: 20px;">

    <form action="{{ route('purchase-order-emails.index') }}" method="GET" class="email-search-form">

        <div class="form-group" style="flex: 1; min-width: 240px; margin-bottom: 0;">
            <label for="search">Search</label>

            <input
                type="text"
                id="search"
                name="search"
                value="{{ $search }}"
                placeholder="Search by PO number, recipient, or subject..."
            >
        </div>

        <div style="display: flex; gap: 8px;">
            <button type="submit" class="btn btn-primary">Search</button>

            @if ($search !== '')
                <a href="{{ route('purchase-order-emails.index') }}" class="btn btn-secondary">Clear</a>
            @endif
        </div>

    </form>

</div>


@if ($emails->count())

    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Purchase Order</th>
                    <th>Sent To</th>
                    <th>Subject</th>
                    <th>Sent By</th>
                    <th>Sent At</th>
                    <th></th>
                </tr>
            </thead>

            <tbody>
                @foreach ($emails as $email)
                    <tr>
                        <td>
                            @if ($email->purchaseOrder)
                                <strong>{{ $email->purchaseOrder->po_number }}</strong>
                            @else
                                <span style="color:#94a3b8;">Deleted PO</span>
                            @endif
                        </td>

                        <td>
                            {{ $email->to_email }}

                            @if ($email->cc_email || $email->bcc_email)
                                <div style="font-size: 11px; color: #94a3b8; margin-top: 2px;">
                                    @if ($email->cc_email) CC: {{ $email->cc_email }} @endif
                                    @if ($email->bcc_email) BCC: {{ $email->bcc_email }} @endif
                                </div>
                            @endif
                        </td>

                        <td>{{ $email->subject }}</td>

                        <td>{{ $email->sentBy?->name ?? 'System' }}</td>

                        <td>{{ format_datetime($email->sent_at) ?? '—' }}</td>

                        <td>
                            @if ($email->purchaseOrder)
                                <a href="{{ route('purchase-orders.show', $email->purchaseOrder) }}" class="btn btn-secondary">
                                    View PO
                                </a>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div style="margin-top: 20px;">
        {{ $emails->onEachSide(1)->links() }}
    </div>

@else

    <div class="empty-state">
        <p>
            @if ($search !== '')
                No emails found matching "{{ $search }}".
            @else
                No purchase order emails have been sent yet.
            @endif
        </p>
    </div>

@endif


<style>

    .email-search-form {
        display: flex;
        gap: 10px;
        align-items: flex-end;
        flex-wrap: wrap;
    }

</style>

@endsection
