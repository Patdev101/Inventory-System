<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        @yield('title', 'Inventory System')
    </title>

    @vite([
        'resources/css/app.css',
        'resources/js/app.js'
    ])

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, Helvetica, sans-serif;
            background: #f1f5f9;
            color: #1e293b;
        }

        /* Navigation */

        .navbar {
            background: #1e293b;
            color: white;
            min-height: 64px;
            display: flex;
            align-items: center;
            padding: 0 32px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.12);
            gap: 30px;
        }

        .navbar-brand {
            color: white;
            text-decoration: none;
            font-size: 20px;
            font-weight: bold;
            white-space: nowrap;
        }

        .navbar-brand:hover {
            color: white;
        }

        .navbar-links {
            display: flex;
            align-items: center;
            gap: 5px;
            flex-wrap: wrap;
        }

        .navbar-links a {
            color: #cbd5e1;
            text-decoration: none;
            padding: 10px 13px;
            border-radius: 6px;
            font-size: 14px;
            transition:
                background 0.15s ease,
                color 0.15s ease;
            white-space: nowrap;
        }

        .navbar-links a:hover {
            background: #334155;
            color: white;
        }

        .navbar-links a.active {
            background: #2563eb;
            color: white;
        }

        .navbar-user {
            margin-left: auto;
            display: flex;
            align-items: center;
            gap: 12px;
            color: #cbd5e1;
            font-size: 13px;
        }

        .navbar-user button {
            border: 1px solid #64748b;
            border-radius: 6px;
            padding: 8px 11px;
            background: transparent;
            color: white;
            cursor: pointer;
        }

        /* Main */

        .container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 32px 20px 50px;
        }

        /* Page Header */

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            margin-bottom: 24px;
        }

        .page-header h1,
        .page-header h2 {
            margin: 0;
            color: #0f172a;
        }

        h1 {
            font-size: 28px;
        }

        h2 {
            font-size: 24px;
        }

        /* Cards */

        .card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow:
                0 2px 10px rgba(15, 23, 42, 0.08);
            border: 1px solid #e2e8f0;
        }

        /* Buttons */

        .btn {
            display: inline-block;
            padding: 9px 15px;
            border-radius: 6px;
            text-decoration: none;
            border: none;
            cursor: pointer;
            font-size: 14px;
            font-family: inherit;
        }

        .btn:disabled {
            opacity: 0.55;
            cursor: not-allowed;
        }

        .btn-primary {
            background: #2563eb;
            color: white;
        }

        .btn-primary:hover {
            background: #1d4ed8;
            color: white;
        }

        .btn-secondary {
            background: #64748b;
            color: white;
        }

        .btn-secondary:hover {
            background: #475569;
            color: white;
        }

        .btn-success {
            background: #16a34a;
            color: white;
        }

        .btn-success:hover {
            background: #15803d;
            color: white;
        }

        .btn-danger {
            background: #dc2626;
            color: white;
        }

        .btn-danger:hover {
            background: #b91c1c;
            color: white;
        }

        /* Tables */

        .table-wrapper {
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            overflow-x: auto;
            box-shadow:
                0 2px 10px rgba(15, 23, 42, 0.06);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 0;
        }

        th,
        td {
            padding: 14px 16px;
            border-bottom: 1px solid #e2e8f0;
            text-align: left;
            vertical-align: middle;
        }

        th {
            background: #f8fafc;
            color: #334155;
            font-size: 14px;
            font-weight: bold;
        }

        td {
            color: #475569;
            font-size: 14px;
        }

        tbody tr:hover {
            background: #f8fafc;
        }

        tbody tr:last-child td {
            border-bottom: none;
        }

        /* Forms */

        .form-group {
            margin-bottom: 18px;
        }

        label {
            display: block;
            margin-bottom: 7px;
            font-weight: bold;
            color: #334155;
        }

        input,
        textarea,
        select {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            background: white;
            color: #1e293b;
            font-family: inherit;
            font-size: 14px;
        }

        input:focus,
        textarea:focus,
        select:focus {
            outline: none;
            border-color: #2563eb;
            box-shadow:
                0 0 0 3px rgba(37, 99, 235, 0.12);
        }

        input:disabled,
        select:disabled,
        textarea:disabled {
            background: #f1f5f9;
            color: #64748b;
            cursor: not-allowed;
        }

        textarea {
            min-height: 110px;
            resize: vertical;
        }

        /* Alerts */

        .alert-success {
            background: #dcfce7;
            color: #166534;
            padding: 13px 16px;
            border-radius: 7px;
            border: 1px solid #bbf7d0;
            margin-bottom: 20px;
        }

        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            padding: 13px 16px;
            border-radius: 7px;
            border: 1px solid #fecaca;
            margin-bottom: 20px;
        }

        .error {
            color: #dc2626;
            margin-top: 5px;
        }

        /* Toast notifications */

        .toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 3000;
            display: flex;
            flex-direction: column;
            gap: 10px;
            max-width: 380px;
        }

        .toast {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            padding: 14px 16px;
            border-radius: 9px;
            box-shadow: 0 10px 25px rgba(15, 23, 42, .15);
            font-size: 13.5px;
            line-height: 1.4;
            animation: toast-in .25s ease;
        }

        .toast.toast-hiding {
            animation: toast-out .2s ease forwards;
        }

        .toast-success {
            background: #16a34a;
            color: white;
        }

        .toast-error {
            background: #dc2626;
            color: white;
        }

        .toast-icon {
            flex-shrink: 0;
            margin-top: 1px;
        }

        .toast-close {
            margin-left: auto;
            background: transparent;
            border: 0;
            color: inherit;
            opacity: .75;
            cursor: pointer;
            font-size: 15px;
            line-height: 1;
            padding: 0 0 0 8px;
        }

        .toast-close:hover {
            opacity: 1;
        }

        @keyframes toast-in {
            from { opacity: 0; transform: translateX(24px); }
            to { opacity: 1; transform: translateX(0); }
        }

        @keyframes toast-out {
            from { opacity: 1; transform: translateX(0); }
            to { opacity: 0; transform: translateX(24px); }
        }

        /* Button loading state */

        .btn.is-loading {
            position: relative;
            color: transparent !important;
            pointer-events: none;
        }

        .btn.is-loading::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 15px;
            height: 15px;
            margin: -7.5px 0 0 -7.5px;
            border: 2px solid rgba(255, 255, 255, .4);
            border-top-color: #fff;
            border-radius: 50%;
            animation: btn-spin .6s linear infinite;
        }

        .btn-secondary.is-loading::after,
        .btn-danger.is-loading::after {
            border: 2px solid rgba(15, 23, 42, .15);
            border-top-color: #1e293b;
        }

        @keyframes btn-spin {
            to { transform: rotate(360deg); }
        }

        /* Confirm modal */

        .confirm-modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, .5);
            z-index: 4000;
            align-items: center;
            justify-content: center;
        }

        .confirm-modal-overlay.is-open {
            display: flex;
        }

        .confirm-modal-box {
            background: white;
            border-radius: 12px;
            padding: 24px;
            width: 100%;
            max-width: 380px;
            box-shadow: 0 20px 50px rgba(15, 23, 42, .25);
        }

        .confirm-modal-box h3 {
            margin: 0 0 8px;
            font-size: 16px;
        }

        .confirm-modal-box p {
            margin: 0 0 20px;
            color: #64748b;
            font-size: 13.5px;
            line-height: 1.5;
        }

        .confirm-modal-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        /* Dev/test mode banner */

        .dev-mode-banner {
            background: repeating-linear-gradient(
                135deg,
                #78350f,
                #78350f 10px,
                #92400e 10px,
                #92400e 20px
            );
            color: #fef3c7;
            font-size: 12.5px;
            font-weight: 700;
            text-align: center;
            padding: 7px 12px;
            letter-spacing: .02em;
        }

        /* Links */

        a {
            color: #2563eb;
        }

        a:hover {
            color: #1d4ed8;
        }

        /* Actions */

        .actions {
            display: flex;
            gap: 8px;
            align-items: center;
            flex-wrap: wrap;
        }

        .actions form {
            margin: 0;
        }

        /* Empty State */

        .empty-state {
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 45px 25px;
            text-align: center;
            color: #64748b;
        }

        .empty-state p {
            margin: 0 0 20px;
            font-size: 16px;
        }

        /* Pagination */

        nav[aria-label="Pagination Navigation"] {
            display: flex;
            justify-content: center;
        }

        /* Mobile */

        @media (max-width: 850px) {

            .navbar {
                padding: 14px 16px;
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }

            .navbar-links {
                width: 100%;
            }

            .container {
                padding: 24px 15px 40px;
            }

            .page-header {
                flex-direction: column;
                align-items: flex-start;
            }

        }

        @media (max-width: 600px) {

            .navbar-links a {
                font-size: 13px;
                padding: 8px 10px;
            }

            th,
            td {
                padding: 10px;
            }

            /*
             * Safety net: makes every table scroll horizontally on a
             * narrow screen even if a page's own markup forgot to wrap
             * it in .table-wrapper (which already handles this via
             * overflow-x on its container). Without this, a wide table
             * on a phone either overflows the page or gets squashed
             * illegibly.
             */
            table {
                display: block;
                overflow-x: auto;
                white-space: nowrap;
                -webkit-overflow-scrolling: touch;
            }

        }

    </style>

</head>


<body>

@unless (app()->environment('production'))

    <div class="dev-mode-banner">
        ⚠ Development / Test Mode — data here is not real. Real deployment has this banner turned off automatically.
    </div>

@endunless


<div class="system-shell">

    @include('layouts.sidebar')

    <main class="system-main">

        <div class="container">

            @yield('content')

        </div>

    </main>

</div>


<div class="toast-container" id="toast-container">

    @if(session('success'))
        <div class="toast toast-success" data-toast data-toast-timeout="4000">
            <span class="toast-icon">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"></path></svg>
            </span>
            <span>{{ session('success') }}</span>
            <button type="button" class="toast-close" data-toast-close aria-label="Dismiss">&times;</button>
        </div>
    @endif

    @if(session('error'))
        <div class="toast toast-error" data-toast data-toast-timeout="6000">
            <span class="toast-icon">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><path d="M12 8v5"></path><path d="M12 16h.01"></path></svg>
            </span>
            <span>{{ session('error') }}</span>
            <button type="button" class="toast-close" data-toast-close aria-label="Dismiss">&times;</button>
        </div>
    @endif

</div>


<div class="confirm-modal-overlay" id="confirm-modal-overlay">

    <div class="confirm-modal-box">

        <h3 id="confirm-modal-title">Are you sure?</h3>
        <p id="confirm-modal-message">This action cannot be undone.</p>

        <div class="confirm-modal-actions">
            <button type="button" class="btn btn-secondary" id="confirm-modal-cancel">Cancel</button>
            <button type="button" class="btn btn-danger" id="confirm-modal-confirm">Confirm</button>
        </div>

    </div>

</div>

<script>
    /*
    |--------------------------------------------------------------------------
    | Auto-refresh
    |--------------------------------------------------------------------------
    |
    | Stock changes constantly from the POS side. Pages that show live
    | stock numbers (dashboard, inventory list/detail) opt in by setting
    | the "autoRefreshSeconds" section to a number of seconds, instead of
    | requiring a manual reload to see what the POS already deducted.
    | Scroll position is restored so a long page doesn't jump back to
    | the top every reload.
    */
    (function () {
        var seconds = @yield('autoRefreshSeconds', 'null');

        if (!seconds) {
            return;
        }

        var storageKey = 'inventory-scroll:' + window.location.pathname + window.location.search;
        var savedScroll = sessionStorage.getItem(storageKey);

        if (savedScroll !== null) {
            window.scrollTo(0, parseInt(savedScroll, 10));
            sessionStorage.removeItem(storageKey);
        }

        setInterval(function () {
            if (document.hidden) {
                return;
            }

            sessionStorage.setItem(storageKey, String(window.scrollY));
            window.location.reload();
        }, seconds * 1000);
    })();

    /*
    |--------------------------------------------------------------------------
    | Toast notifications
    |--------------------------------------------------------------------------
    | Auto-dismisses each flash toast after its data-toast-timeout, and
    | lets the close button dismiss it early. Toasts stack in the
    | top-right corner instead of pushing page content down.
    */
    document.querySelectorAll('[data-toast]').forEach(function (toast) {
        var timeout = parseInt(toast.getAttribute('data-toast-timeout'), 10) || 4000;

        function dismiss() {
            toast.classList.add('toast-hiding');
            setTimeout(function () {
                toast.remove();
            }, 200);
        }

        var timer = setTimeout(dismiss, timeout);

        var closeBtn = toast.querySelector('[data-toast-close]');
        if (closeBtn) {
            closeBtn.addEventListener('click', function () {
                clearTimeout(timer);
                dismiss();
            });
        }
    });


    /*
    |--------------------------------------------------------------------------
    | Confirm modal
    |--------------------------------------------------------------------------
    | Replaces the browser's native confirm() popup for destructive
    | actions. Two ways to opt in:
    |
    |   1. <form data-confirm="message" [data-confirm-title="Title"]> —
    |      intercepted on submit, for forms with a single submit button.
    |
    |   2. <button data-confirm="message" [data-confirm-title="Title"]
    |      type="submit"> — intercepted on click instead, for forms with
    |      more than one submit button (e.g. "Pass" / "Fail") where only
    |      one of them needs confirmation.
    |
    | Either way, the real submission only happens once the user clicks
    | Confirm — via requestSubmit()/click(), which (unlike form.submit())
    | still fires real submit/click events so the double-submit guard
    | below still applies.
    */
    var confirmOverlay = document.getElementById('confirm-modal-overlay');
    var confirmTitleEl = document.getElementById('confirm-modal-title');
    var confirmMessageEl = document.getElementById('confirm-modal-message');
    var confirmConfirmBtn = document.getElementById('confirm-modal-confirm');
    var confirmCancelBtn = document.getElementById('confirm-modal-cancel');
    var pendingConfirmTarget = null;

    function closeConfirmModal() {
        confirmOverlay.classList.remove('is-open');
        pendingConfirmTarget = null;
    }

    confirmCancelBtn.addEventListener('click', closeConfirmModal);

    confirmOverlay.addEventListener('click', function (event) {
        if (event.target === confirmOverlay) {
            closeConfirmModal();
        }
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && confirmOverlay.classList.contains('is-open')) {
            closeConfirmModal();
        }
    });

    confirmConfirmBtn.addEventListener('click', function () {
        var target = pendingConfirmTarget;
        confirmOverlay.classList.remove('is-open');
        pendingConfirmTarget = null;

        if (!target) {
            return;
        }

        target.dataset.confirmed = 'true';

        if (target instanceof HTMLFormElement) {
            target.requestSubmit();
        } else {
            target.click();
        }
    });

    document.addEventListener('submit', function (event) {
        var form = event.target;

        if (!(form instanceof HTMLFormElement) || !form.dataset.confirm) {
            return;
        }

        if (form.dataset.confirmed === 'true') {
            return;
        }

        event.preventDefault();

        confirmTitleEl.textContent = form.dataset.confirmTitle || 'Are you sure?';
        confirmMessageEl.textContent = form.dataset.confirm;
        pendingConfirmTarget = form;
        confirmOverlay.classList.add('is-open');
    }, true);

    document.addEventListener('click', function (event) {
        var button = event.target.closest('button[data-confirm], input[type="submit"][data-confirm]');

        if (!button) {
            return;
        }

        if (button.dataset.confirmed === 'true') {
            delete button.dataset.confirmed;
            return;
        }

        event.preventDefault();

        confirmTitleEl.textContent = button.dataset.confirmTitle || 'Are you sure?';
        confirmMessageEl.textContent = button.dataset.confirm;
        pendingConfirmTarget = button;
        confirmOverlay.classList.add('is-open');
    }, true);


    /*
    |--------------------------------------------------------------------------
    | Double-submit guard + loading state
    |--------------------------------------------------------------------------
    | Disables a form's submit button the instant it actually submits (so
    | a double-click can't fire the same request twice) and shows a
    | spinner in place of its label, so "did that actually work?" never
    | happens on a slow connection. Checked against event.defaultPrevented
    | so a page's own client-side validation (which calls
    | preventDefault() to block a bad submission) is never left with a
    | permanently-disabled button — this only fires when the form is
    | genuinely on its way to the server.
    */
    document.addEventListener('submit', function (event) {
        if (event.defaultPrevented) {
            return;
        }

        var form = event.target;

        if (!(form instanceof HTMLFormElement)) {
            return;
        }

        form.querySelectorAll('button[type="submit"], input[type="submit"]').forEach(function (button) {
            button.disabled = true;
            button.classList.add('is-loading');
        });
    });
</script>

</body>

</html>
