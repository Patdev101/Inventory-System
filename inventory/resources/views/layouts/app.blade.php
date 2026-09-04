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

        }

    </style>

</head>


<body>

<div class="system-shell">

    @include('layouts.sidebar')

    <main class="system-main">

        <div class="container">

    @if(session('success'))

        <div class="alert-success">
            {{ session('success') }}
        </div>

    @endif


    @if(session('error'))

        <div class="alert-error">
            {{ session('error') }}
        </div>

    @endif


            @yield('content')

        </div>

    </main>

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
</script>

</body>

</html>
