@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')

    <style>

        /* =====================================================
           RESET
        ===================================================== */

        * {
            box-sizing: border-box;
        }

        html {
            scroll-behavior: auto;
        }

        body {
            margin: 0;
            font-family:
                Inter,
                -apple-system,
                BlinkMacSystemFont,
                "Segoe UI",
                Arial,
                Helvetica,
                sans-serif;

            background: #f5f7fb;
            color: #1f2937;
        }

        a {
            color: inherit;
        }

        button,
        input,
        select,
        textarea {
            font: inherit;
        }


        /* =====================================================
           APP LAYOUT
        ===================================================== */

        .app {
            min-height: 100vh;
            display: flex;
        }


        /* =====================================================
           SIDEBAR
        ===================================================== */

        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;

            width: 250px;

            background: #111827;
            color: #d1d5db;

            display: flex;
            flex-direction: column;

            z-index: 1000;

            border-right: 1px solid #1f2937;
        }


        /* =====================================================
           SIDEBAR BRAND
        ===================================================== */

        .sidebar-brand {
            height: 72px;

            display: flex;
            align-items: center;

            gap: 12px;

            padding: 0 20px;

            border-bottom: 1px solid #1f2937;

            text-decoration: none;

            color: #ffffff;
        }

        .sidebar-brand-icon {
            width: 36px;
            height: 36px;

            display: flex;
            align-items: center;
            justify-content: center;

            border-radius: 9px;

            background:
                linear-gradient(
                    135deg,
                    #2563eb,
                    #3b82f6
                );

            color: white;

            font-size: 18px;
            font-weight: 700;

            flex-shrink: 0;
        }

        .sidebar-brand-text {
            min-width: 0;
        }

        .sidebar-brand-title {
            font-size: 15px;
            font-weight: 700;

            color: #ffffff;

            white-space: nowrap;
        }

        .sidebar-brand-subtitle {
            margin-top: 2px;

            font-size: 11px;

            color: #9ca3af;
        }


        /* =====================================================
           SIDEBAR NAVIGATION
        ===================================================== */

        .sidebar-nav {
            flex: 1;

            padding: 18px 12px;

            overflow-y: auto;
        }

        .sidebar-section-label {
            padding:
                8px 10px
                7px;

            color: #6b7280;

            font-size: 10px;
            font-weight: 700;

            text-transform: uppercase;

            letter-spacing: 0.8px;
        }

        .sidebar-nav-list {
            display: flex;
            flex-direction: column;

            gap: 3px;

            margin: 0;
            padding: 0;

            list-style: none;
        }

        .sidebar-link {
            display: flex;
            align-items: center;

            gap: 11px;

            min-height: 42px;

            padding: 9px 11px;

            border-radius: 8px;

            text-decoration: none;

            color: #cbd5e1;

            font-size: 13px;
            font-weight: 500;

            transition:
                background 0.18s ease,
                color 0.18s ease;
        }

        .sidebar-link:hover {
            background: #1f2937;
            color: #ffffff;
        }

        .sidebar-link.active {
            background:
                linear-gradient(
                    90deg,
                    #1d4ed8,
                    #2563eb
                );

            color: #ffffff;

            box-shadow:
                0 4px 12px rgba(
                    37,
                    99,
                    235,
                    0.20
                );
        }

        .sidebar-link-icon {
            width: 20px;

            text-align: center;

            color: #94a3b8;

            font-size: 15px;

            flex-shrink: 0;
        }

        .sidebar-link.active .sidebar-link-icon,
        .sidebar-link:hover .sidebar-link-icon {
            color: #ffffff;
        }

        .sidebar-divider {
            height: 1px;

            background: #1f2937;

            margin:
                14px 8px;
        }


        /* =====================================================
           SIDEBAR USER
        ===================================================== */

        .sidebar-user {
            padding: 14px;

            border-top: 1px solid #1f2937;

            background: #0f172a;
        }

        .sidebar-user-info {
            display: flex;
            align-items: center;

            gap: 10px;

            padding: 7px 6px 12px;
        }

        .sidebar-user-avatar {
            width: 36px;
            height: 36px;

            border-radius: 50%;

            display: flex;
            align-items: center;
            justify-content: center;

            flex-shrink: 0;

            background:
                linear-gradient(
                    135deg,
                    #3b82f6,
                    #60a5fa
                );

            color: white;

            font-size: 14px;
            font-weight: 700;
        }

        .sidebar-user-details {
            min-width: 0;

            display: flex;
            flex-direction: column;
        }

        .sidebar-user-details strong {
            color: #ffffff;

            font-size: 13px;

            overflow: hidden;

            text-overflow: ellipsis;

            white-space: nowrap;
        }

        .sidebar-user-details span {
            margin-top: 3px;

            color: #94a3b8;

            font-size: 10px;

            overflow: hidden;

            text-overflow: ellipsis;

            white-space: nowrap;
        }

        .sidebar-logout {
            width: 100%;

            border: 0;

            background: transparent;

            color: #cbd5e1;

            display: flex;
            align-items: center;

            gap: 10px;

            padding: 9px 10px;

            border-radius: 7px;

            cursor: pointer;

            font-size: 12px;

            text-align: left;

            transition:
                background 0.18s ease,
                color 0.18s ease;
        }

        .sidebar-logout:hover {
            background: #1f2937;
            color: #fca5a5;
        }

        .sidebar-logout-icon {
            width: 20px;

            text-align: center;

            font-size: 15px;
        }


        /* =====================================================
           MAIN CONTENT
        ===================================================== */

        .main {
            width: calc(100% - 250px);

            margin-left: 250px;

            min-height: 100vh;

            padding: 30px;
        }

        .container {
            width: 100%;
            max-width: 1450px;

            margin: 0 auto;
        }


        /* =====================================================
           PAGE HEADER
        ===================================================== */

        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;

            gap: 20px;

            margin-bottom: 24px;
        }

        .header h1 {
            margin: 0 0 7px;

            color: #111827;

            font-size: 30px;
            line-height: 1.2;

            letter-spacing: -0.5px;
        }

        .header p {
            margin: 0;

            color: #6b7280;

            font-size: 14px;
        }


        /* =====================================================
           QUICK ACTIONS
        ===================================================== */

        .quick-actions {
            position: relative;

            overflow: hidden;

            background:
                linear-gradient(
                    135deg,
                    #ffffff 0%,
                    #f8fbff 100%
                );

            border:
                1px solid #e5eaf2;

            border-radius: 14px;

            padding: 23px;

            margin-bottom: 26px;

            box-shadow:
                0 4px 16px rgba(
                    15,
                    23,
                    42,
                    0.06
                );
        }

        .quick-actions::before {
            content: "";

            position: absolute;

            top: 0;
            left: 0;
            right: 0;

            height: 4px;

            background:
                linear-gradient(
                    90deg,
                    #2563eb,
                    #3b82f6,
                    #60a5fa
                );
        }

        .quick-actions-header {
            display: flex;
            align-items: center;
            justify-content: space-between;

            gap: 15px;

            margin-bottom: 18px;
        }

        .quick-actions-header h2 {
            margin: 0 0 5px;

            color: #111827;

            font-size: 19px;
        }

        .quick-actions-header p {
            margin: 0;

            color: #6b7280;

            font-size: 13px;
        }

        .quick-actions-label {
            display: inline-flex;
            align-items: center;

            padding: 6px 10px;

            border:
                1px solid #dbeafe;

            border-radius: 999px;

            background: #eff6ff;

            color: #2563eb;

            font-size: 10px;
            font-weight: 700;

            text-transform: uppercase;

            letter-spacing: 0.5px;

            white-space: nowrap;
        }

        .quick-action-grid {
            display: grid;

            grid-template-columns:
                repeat(4, minmax(0, 1fr));

            gap: 10px;
        }

        .quick-action {
            position: relative;

            display: block;

            min-height: 91px;

            padding: 15px;

            border:
                1px solid #e5e7eb;

            border-radius: 10px;

            background: #ffffff;

            color: #111827;

            text-decoration: none;

            transition:
                transform 0.18s ease,
                border-color 0.18s ease,
                box-shadow 0.18s ease,
                background 0.18s ease;
        }

        .quick-action::after {
            content: "→";

            position: absolute;

            top: 50%;
            right: 13px;

            transform:
                translateY(-50%);

            color: #cbd5e1;

            font-size: 17px;
            font-weight: 700;

            transition:
                right 0.18s ease,
                color 0.18s ease;
        }

        .quick-action:hover {
            transform: translateY(-2px);

            border-color: #93c5fd;

            background:
                linear-gradient(
                    135deg,
                    #eff6ff,
                    #ffffff
                );

            box-shadow:
                0 6px 16px rgba(
                    37,
                    99,
                    235,
                    0.09
                );
        }

        .quick-action:hover::after {
            right: 10px;
            color: #2563eb;
        }

        .quick-action-primary {
            border-color: #bfdbfe;

            background:
                linear-gradient(
                    135deg,
                    #eff6ff,
                    #ffffff
                );
        }

        .quick-action-title {
            padding-right: 25px;

            margin-bottom: 6px;

            color: #111827;

            font-size: 13px;
            font-weight: 700;
        }

        .quick-action-primary .quick-action-title {
            color: #1d4ed8;
        }

        .quick-action-description {
            padding-right: 20px;

            color: #6b7280;

            font-size: 11px;

            line-height: 1.45;
        }


        /* =====================================================
           KPI CARDS
        ===================================================== */

        .stats {
            display: grid;

            grid-template-columns:
                repeat(5, minmax(0, 1fr));

            gap: 15px;

            margin-bottom: 28px;
        }

        .stat-card {
            position: relative;

            overflow: hidden;

            background: #ffffff;

            border:
                1px solid #e8ebf0;

            border-radius: 11px;

            padding: 19px;

            box-shadow:
                0 2px 8px rgba(
                    15,
                    23,
                    42,
                    0.04
                );
        }

        .stat-card::before {
            content: "";

            position: absolute;

            top: 0;
            left: 0;

            width: 3px;
            height: 100%;

            background: #dbeafe;
        }

        .stat-title {
            margin-bottom: 9px;

            color: #6b7280;

            font-size: 12px;
            font-weight: 500;
        }

        .stat-value {
            color: #111827;

            font-size: 25px;
            font-weight: 700;

            letter-spacing: -0.5px;
        }

        .stat-description {
            margin-top: 5px;

            color: #9ca3af;

            font-size: 10px;
        }

        .stat-blue {
            color: #2563eb;
        }

        .stat-green {
            color: #16a34a;
        }

        .stat-red {
            color: #dc2626;
        }

        .stat-orange {
            color: #ea580c;
        }


        /* =====================================================
           SECTION
        ===================================================== */

        .section {
            margin-bottom: 28px;
        }

        .section-title {
            margin: 0 0 14px;

            color: #111827;

            font-size: 19px;
            font-weight: 700;
        }


        /* =====================================================
           STOCK ALERT SUMMARY
        ===================================================== */

        .stock-alert-summary {
            display: grid;

            grid-template-columns:
                repeat(4, minmax(0, 1fr));

            gap: 14px;

            margin-bottom: 28px;
        }

        .stock-alert-summary-card {
            background: #ffffff;

            border:
                1px solid #e8ebf0;

            border-radius: 10px;

            padding: 17px;

            box-shadow:
                0 2px 8px rgba(
                    15,
                    23,
                    42,
                    0.04
                );
        }

        .stock-alert-summary-card.alert-total {
            border-left:
                4px solid #2563eb;
        }

        .stock-alert-summary-card.alert-out {
            border-left:
                4px solid #dc2626;
        }

        .stock-alert-summary-card.alert-critical {
            border-left:
                4px solid #b91c1c;
        }

        .stock-alert-summary-card.alert-low {
            border-left:
                4px solid #d97706;
        }

        .stock-alert-summary-label {
            margin-bottom: 7px;

            color: #6b7280;

            font-size: 11px;
        }

        .stock-alert-summary-value {
            color: #111827;

            font-size: 25px;
            font-weight: 700;
        }

        .alert-total-value {
            color: #2563eb;
        }

        .alert-out-value {
            color: #dc2626;
        }

        .alert-critical-value {
            color: #b91c1c;
        }

        .alert-low-value {
            color: #d97706;
        }

        .stock-alert-summary-description {
            margin-top: 4px;

            color: #9ca3af;

            font-size: 10px;

            line-height: 1.4;
        }


        /* =====================================================
           STOCK ALERT CARDS
        ===================================================== */

        .stock-alert-card {
            background: #ffffff;

            border:
                1px solid #e8ebf0;

            border-radius: 11px;

            padding: 20px;

            margin-bottom: 17px;

            box-shadow:
                0 2px 8px rgba(
                    15,
                    23,
                    42,
                    0.04
                );
        }

        .alert-out-card {
            border-top:
                4px solid #dc2626;
        }

        .alert-critical-card {
            border-top:
                4px solid #b91c1c;
        }

        .alert-low-card {
            border-top:
                4px solid #d97706;
        }

        .stock-alert-header {
            display: flex;

            justify-content: space-between;
            align-items: center;

            gap: 15px;

            margin-bottom: 16px;
        }

        .stock-alert-header-content h2 {
            margin: 0 0 4px;

            color: #111827;

            font-size: 17px;
        }

        .stock-alert-header-content p {
            margin: 0;

            color: #6b7280;

            font-size: 11px;
        }

        .stock-alert-count {
            display: inline-flex;

            align-items: center;
            justify-content: center;

            min-width: 32px;
            height: 28px;

            padding: 0 9px;

            border-radius: 999px;

            font-size: 11px;
            font-weight: 700;
        }

        .stock-alert-count-out,
        .stock-alert-count-critical {
            background: #fee2e2;
            color: #991b1b;
        }

        .stock-alert-count-low {
            background: #fef3c7;
            color: #92400e;
        }


        /* =====================================================
           TABLES
        ===================================================== */

        .table-wrapper {
            width: 100%;

            overflow-x: auto;

            -webkit-overflow-scrolling: touch;
        }

        table {
            width: 100%;

            border-collapse: collapse;

            min-width: 720px;
        }

        th,
        td {
            padding: 11px 12px;

            border-bottom:
                1px solid #e5e7eb;

            text-align: left;

            white-space: nowrap;
        }

        th {
            background: #f8fafc;

            color: #4b5563;

            font-size: 11px;
            font-weight: 700;
        }

        td {
            color: #374151;

            font-size: 12px;
        }

        tbody tr {
            transition:
                background 0.15s ease;
        }

        tbody tr:hover {
            background: #f8fafc;
        }

        tr:last-child td {
            border-bottom: none;
        }


        /* =====================================================
           PRODUCT / ALERT TEXT
        ===================================================== */

        .alert-product-name {
            color: #111827;

            font-size: 12px;
            font-weight: 700;
        }

        .alert-product-sku {
            display: block;

            margin-top: 3px;

            color: #9ca3af;

            font-size: 9px;
        }

        .alert-location-name {
            color: #374151;
        }

        .alert-stock-danger {
            color: #dc2626;
            font-weight: 700;
        }

        .alert-stock-critical {
            color: #b91c1c;
            font-weight: 700;
        }

        .alert-stock-warning {
            color: #d97706;
            font-weight: 700;
        }

        .alert-reorder {
            color: #374151;
            font-weight: 600;
        }

        .alert-difference {
            color: #dc2626;
            font-weight: 700;
        }

        .deleted {
            color: #9ca3af;

            font-style: italic;
        }


        /* =====================================================
           BADGES
        ===================================================== */

        .badge,
        .warning-badge,
        .danger-badge,
        .success-badge,
        .transfer-badge {
            display: inline-block;

            padding: 4px 8px;

            border-radius: 999px;

            font-size: 10px;
            font-weight: 700;
        }

        .badge-in,
        .success-badge {
            background: #dcfce7;
            color: #166534;
        }

        .badge-out,
        .danger-badge {
            background: #fee2e2;
            color: #991b1b;
        }

        .warning-badge {
            background: #fef3c7;
            color: #92400e;
        }

        .transfer-badge {
            background: #dbeafe;
            color: #1e40af;
        }


        /* =====================================================
           LINKS
        ===================================================== */

        .view-link {
            color: #2563eb;

            text-decoration: none;

            font-size: 11px;
            font-weight: 600;
        }

        .view-link:hover {
            text-decoration: underline;
        }


        /* =====================================================
           EMPTY STATES
        ===================================================== */

        .empty {
            padding: 38px;

            color: #6b7280;

            text-align: center;

            font-size: 13px;
        }

        .stock-alert-empty {
            padding: 22px;

            border:
                1px solid #e5e7eb;

            border-radius: 8px;

            background: #f9fafb;

            color: #6b7280;

            text-align: center;

            font-size: 12px;
        }

        .stock-alert-all-clear {
            margin-bottom: 20px;

            padding: 24px;

            border:
                1px solid #bbf7d0;

            border-radius: 10px;

            background: #ecfdf5;

            color: #166534;

            text-align: center;
        }

        .stock-alert-all-clear-title {
            margin-bottom: 5px;

            font-size: 16px;
            font-weight: 700;
        }

        .stock-alert-all-clear-description {
            color: #15803d;

            font-size: 11px;
        }


        /* =====================================================
           ANALYTICS
        ===================================================== */

        .analytics-grid {
            display: grid;

            grid-template-columns:
                repeat(4, minmax(0, 1fr));

            gap: 15px;
        }

        .analytics-card {
            background: #ffffff;

            border:
                1px solid #e8ebf0;

            border-radius: 10px;

            padding: 18px;

            box-shadow:
                0 2px 8px rgba(
                    15,
                    23,
                    42,
                    0.04
                );
        }

        .analytics-label {
            margin-bottom: 8px;

            color: #6b7280;

            font-size: 11px;
        }

        .analytics-value {
            font-size: 27px;
            font-weight: 700;

            letter-spacing: -0.5px;
        }

        .analytics-description {
            margin-top: 5px;

            color: #9ca3af;

            font-size: 10px;
        }

        .analytics-in,
        .analytics-positive {
            color: #16a34a;
        }

        .analytics-out,
        .analytics-negative {
            color: #dc2626;
        }

        .analytics-transfer {
            color: #2563eb;
        }


        /* =====================================================
           CHART
        ===================================================== */

        .chart-card {
            background: #ffffff;

            border:
                1px solid #e8ebf0;

            border-radius: 11px;

            padding: 20px;

            box-shadow:
                0 2px 8px rgba(
                    15,
                    23,
                    42,
                    0.04
                );
        }

        .chart-header {
            display: flex;

            justify-content: space-between;
            align-items: flex-start;

            gap: 15px;

            margin-bottom: 18px;
        }

        .chart-header h2 {
            margin: 0 0 4px;

            color: #111827;

            font-size: 17px;
        }

        .chart-header p {
            margin: 0;

            color: #6b7280;

            font-size: 11px;
        }

        .chart-legend {
            display: flex;

            gap: 14px;

            flex-wrap: wrap;

            color: #6b7280;

            font-size: 11px;
        }

        .legend-item {
            display: flex;
            align-items: center;

            gap: 5px;
        }

        .legend-dot {
            width: 8px;
            height: 8px;

            border-radius: 50%;
        }

        .legend-in {
            background: #16a34a;
        }

        .legend-out {
            background: #dc2626;
        }

        .chart-wrapper {
            position: relative;

            width: 100%;
            height: 285px;

            overflow-x: auto;
        }

        .chart {
            position: relative;

            width: 100%;
            min-width: 650px;

            height: 270px;
        }

        .chart-grid {
            position: absolute;

            top: 10px;
            right: 10px;
            bottom: 35px;
            left: 40px;

            display: flex;
            flex-direction: column;

            justify-content: space-between;

            pointer-events: none;
        }

        .grid-line {
            width: 100%;

            border-top:
                1px dashed #e5e7eb;
        }

        .chart-bars {
            position: absolute;

            top: 10px;
            right: 10px;
            bottom: 35px;
            left: 40px;

            display: flex;

            align-items: flex-end;
            justify-content: space-around;

            gap: 10px;
        }

        .chart-day {
            position: relative;

            height: 100%;

            flex: 1;

            min-width: 70px;

            display: flex;
            align-items: flex-end;
            justify-content: center;

            gap: 4px;
        }

        .bar {
            width: 20px;

            min-height: 2px;

            border-radius:
                4px 4px 0 0;

            position: relative;

            transition:
                opacity 0.18s ease,
                transform 0.18s ease;
        }

        .bar:hover {
            opacity: 0.75;

            transform:
                translateY(-2px);
        }

        .bar-in {
            background: #16a34a;
        }

        .bar-out {
            background: #dc2626;
        }

        .bar-label {
            position: absolute;

            bottom: -25px;
            left: 50%;

            transform:
                translateX(-50%);

            color: #6b7280;

            font-size: 10px;

            white-space: nowrap;
        }

        .chart-empty {
            height: 270px;

            display: flex;
            align-items: center;
            justify-content: center;

            color: #9ca3af;

            font-size: 12px;
        }


        /* =====================================================
           GENERAL CARDS
        ===================================================== */

        .card {
            background: #ffffff;

            border:
                1px solid #e8ebf0;

            border-radius: 11px;

            padding: 20px;

            box-shadow:
                0 2px 8px rgba(
                    15,
                    23,
                    42,
                    0.04
                );
        }

        .card-header {
            display: flex;

            justify-content: space-between;
            align-items: center;

            gap: 15px;

            margin-bottom: 18px;
        }

        .card-header h2 {
            margin: 0;

            color: #111827;

            font-size: 17px;
        }

        .card-header p {
            margin: 4px 0 0;

            color: #6b7280;

            font-size: 11px;
        }


        /* =====================================================
           TRANSFER SUMMARY
        ===================================================== */

        .transfer-summary {
            display: grid;

            grid-template-columns:
                repeat(3, minmax(0, 1fr));

            gap: 12px;

            margin-bottom: 18px;
        }

        .transfer-summary-item {
            padding: 14px;

            border:
                1px solid #e5e7eb;

            border-radius: 8px;

            background: #f8fafc;
        }

        .transfer-summary-label {
            margin-bottom: 4px;

            color: #6b7280;

            font-size: 10px;
        }

        .transfer-summary-value {
            color: #111827;

            font-size: 20px;
            font-weight: 700;
        }


        /* =====================================================
           PAGINATION
        ===================================================== */

        .pagination {
            display: flex;

            align-items: center;
            justify-content: flex-end;

            gap: 5px;

            margin-top: 18px;

            flex-wrap: wrap;
        }

        .pagination a,
        .pagination span {
            min-width: 34px;
            height: 34px;

            display: inline-flex;
            align-items: center;
            justify-content: center;

            padding: 0 9px;

            border:
                1px solid #e5e7eb;

            border-radius: 6px;

            background: #ffffff;

            color: #374151;

            text-decoration: none;

            font-size: 11px;
        }

        .pagination a:hover {
            border-color: #93c5fd;

            background: #eff6ff;

            color: #2563eb;
        }

        .pagination .active {
            border-color: #2563eb;

            background: #2563eb;

            color: #ffffff;

            font-weight: 700;
        }

        .pagination .disabled {
            background: #f9fafb;

            color: #9ca3af;

            cursor: not-allowed;
        }


        /* =====================================================
           FOOTER
        ===================================================== */

        .footer {
            padding:
                5px 0 20px;

            color: #9ca3af;

            text-align: center;

            font-size: 10px;
        }


        /* =====================================================
           MOBILE SIDEBAR BUTTON
        ===================================================== */

        .mobile-sidebar-button {
            display: none;

            position: fixed;

            top: 15px;
            left: 15px;

            width: 40px;
            height: 40px;

            border: 0;

            border-radius: 8px;

            background: #111827;

            color: #ffffff;

            cursor: pointer;

            z-index: 1100;

            font-size: 18px;
        }


        /* =====================================================
           RESPONSIVE
        ===================================================== */

        @media (max-width: 1250px) {

            .stats {
                grid-template-columns:
                    repeat(3, minmax(0, 1fr));
            }

            .quick-action-grid {
                grid-template-columns:
                    repeat(3, minmax(0, 1fr));
            }

            .stock-alert-summary {
                grid-template-columns:
                    repeat(2, minmax(0, 1fr));
            }

        }


        @media (max-width: 950px) {

            .sidebar {
                transform:
                    translateX(-100%);

                transition:
                    transform 0.25s ease;
            }

            .sidebar.open {
                transform:
                    translateX(0);
            }

            .main {
                width: 100%;

                margin-left: 0;

                padding: 24px;
            }

            .mobile-sidebar-button {
                display: block;
            }

            .header {
                padding-top: 45px;
            }

            .analytics-grid {
                grid-template-columns:
                    repeat(2, minmax(0, 1fr));
            }

        }


        @media (max-width: 700px) {

            .main {
                padding: 18px;
            }

            .header h1 {
                font-size: 25px;
            }

            .quick-actions {
                padding: 18px;
            }

            .quick-actions-header {
                align-items: flex-start;

                flex-direction: column;
            }

            .quick-action-grid {
                grid-template-columns:
                    repeat(2, minmax(0, 1fr));
            }

            .stats {
                grid-template-columns:
                    repeat(2, minmax(0, 1fr));
            }

            .analytics-grid {
                grid-template-columns:
                    repeat(2, minmax(0, 1fr));
            }

            .stock-alert-summary {
                grid-template-columns:
                    repeat(2, minmax(0, 1fr));
            }

            .transfer-summary {
                grid-template-columns: 1fr;
            }

            .chart-header {
                flex-direction: column;
            }

        }


        @media (max-width: 500px) {

            .main {
                padding: 14px;
            }

            .header h1 {
                font-size: 23px;
            }

            .header p {
                font-size: 12px;
            }

            .quick-action-grid,
            .stats,
            .analytics-grid,
            .stock-alert-summary {
                grid-template-columns: 1fr;
            }

            .stat-value {
                font-size: 23px;
            }

            .analytics-value {
                font-size: 25px;
            }

            .pagination {
                justify-content: flex-start;
            }

        }

    </style>


            {{-- =================================================
                 HEADER
            ================================================== --}}

            <div class="header">

                <div>

                    <h1>
                        Inventory Dashboard
                    </h1>

                    <p>
                        Overview of your current inventory and activity.
                    </p>

                </div>

            </div>


            {{-- =================================================
                 QUICK ACTIONS
            ================================================== --}}

            <div class="quick-actions">

                <div class="quick-actions-header">

                    <div>

                        <h2>
                            Quick Actions
                        </h2>

                        <p>
                            Quickly access the main inventory management sections.
                        </p>

                    </div>

                    <div class="quick-actions-label">
                        Main Tools
                    </div>

                </div>


                <div class="quick-action-grid">


                    <a
                        href="{{ route('stock-alerts.index') }}"
                        class="quick-action quick-action-primary"
                    >

                        <div class="quick-action-title">
                            Stock Alert History
                        </div>

                        <div class="quick-action-description">
                            Acknowledge and resolve persistent stock alerts.
                        </div>

                    </a>


                    <a
                        href="{{ route('products.index') }}"
                        class="quick-action quick-action-primary"
                    >

                        <div class="quick-action-title">
                            Products
                        </div>

                        <div class="quick-action-description">
                            Manage products and product information.
                        </div>

                    </a>


                    <a
                        href="{{ route('inventories.index') }}"
                        class="quick-action quick-action-primary"
                    >

                        <div class="quick-action-title">
                            Inventory
                        </div>

                        <div class="quick-action-description">
                            View and manage current stock.
                        </div>

                    </a>


                    @if (auth()->user()->hasRole('admin', 'manager'))
                    <a
                        href="{{ route('inventory-transfers.create') }}"
                        class="quick-action quick-action-primary"
                    >

                        <div class="quick-action-title">
                            Transfer Inventory
                        </div>

                        <div class="quick-action-description">
                            Move stock between inventory locations.
                        </div>

                    </a>
                    @endif


                    <a
                        href="{{ route('inventory-transfers.index') }}"
                        class="quick-action"
                    >

                        <div class="quick-action-title">
                            Transfer History
                        </div>

                        <div class="quick-action-description">
                            View transfers between locations.
                        </div>

                    </a>


                    <a
                        href="{{ route('locations.index') }}"
                        class="quick-action"
                    >

                        <div class="quick-action-title">
                            Locations
                        </div>

                        <div class="quick-action-description">
                            Manage inventory storage locations.
                        </div>

                    </a>


                    <a
                        href="{{ route('companies.index') }}"
                        class="quick-action"
                    >

                        <div class="quick-action-title">
                            Companies
                        </div>

                        <div class="quick-action-description">
                            Manage companies and their information.
                        </div>

                    </a>


                    <a
                        href="{{ route('units-of-measure.index') }}"
                        class="quick-action"
                    >

                        <div class="quick-action-title">
                            Units of Measure
                        </div>

                        <div class="quick-action-description">
                            Manage PCS, BOX and other units.
                        </div>

                    </a>


                    <a
                        href="{{ route('inventory-transactions.index') }}"
                        class="quick-action"
                    >

                        <div class="quick-action-title">
                            Transactions
                        </div>

                        <div class="quick-action-description">
                            Review the complete inventory audit history.
                        </div>

                    </a>


                </div>

            </div>


            {{-- =================================================
                 KPI CARDS
            ================================================== --}}

            <div class="stats">

                <div class="stat-card">

                    <div class="stat-title">
                        Total Products
                    </div>

                    <div class="stat-value stat-blue">
                        {{ number_format($totalProducts) }}
                    </div>

                    <div class="stat-description">
                        Products in catalog
                    </div>

                </div>


                <div class="stat-card">

                    <div class="stat-title">
                        Inventory Items
                    </div>

                    <div class="stat-value">
                        {{ number_format($totalInventory) }}
                    </div>

                    <div class="stat-description">
                        Product/location records
                    </div>

                </div>


                <div class="stat-card">

                    <div class="stat-title">
                        Locations
                    </div>

                    <div class="stat-value">
                        {{ number_format($totalLocations) }}
                    </div>

                    <div class="stat-description">
                        Storage locations
                    </div>

                </div>


                <div class="stat-card">

                    <div class="stat-title">
                        Current Stock
                    </div>

                    <div class="stat-value stat-green">
                        {{ number_format(
                            (float) $totalBaseStock,
                            4
                        ) }}
                    </div>

                    <div class="stat-description">
                        Base units
                    </div>

                </div>


                <div class="stat-card">

                    <div class="stat-title">
                        Transactions
                    </div>

                    <div class="stat-value">
                        {{ number_format($totalTransactions) }}
                    </div>

                    <div class="stat-description">
                        Audit records
                    </div>

                </div>

            </div>


            {{-- =================================================
                 STOCK ALERT SUMMARY
            ================================================== --}}

            <div class="stock-alert-summary">

                <div class="stock-alert-summary-card alert-total">

                    <div class="stock-alert-summary-label">
                        Total Stock Alerts
                    </div>

                    <div class="
                        stock-alert-summary-value
                        alert-total-value
                    ">
                        {{ number_format($stockAlertCount) }}
                    </div>

                    <div class="stock-alert-summary-description">
                        Inventory records requiring attention
                    </div>

                </div>


                <div class="stock-alert-summary-card alert-out">

                    <div class="stock-alert-summary-label">
                        Out of Stock
                    </div>

                    <div class="
                        stock-alert-summary-value
                        alert-out-value
                    ">
                        {{ number_format($outOfStockCount) }}
                    </div>

                    <div class="stock-alert-summary-description">
                        Stock at or below zero
                    </div>

                </div>


                <div class="stock-alert-summary-card alert-critical">

                    <div class="stock-alert-summary-label">
                        Critical Stock
                    </div>

                    <div class="
                        stock-alert-summary-value
                        alert-critical-value
                    ">
                        {{ number_format($criticalStockCount) }}
                    </div>

                    <div class="stock-alert-summary-description">
                        At or below 50% of reorder point
                    </div>

                </div>


                <div class="stock-alert-summary-card alert-low">

                    <div class="stock-alert-summary-label">
                        Low Stock
                    </div>

                    <div class="
                        stock-alert-summary-value
                        alert-low-value
                    ">
                        {{ number_format($lowStockCount) }}
                    </div>

                    <div class="stock-alert-summary-description">
                        Above critical level and at reorder point
                    </div>

                </div>

            </div>


            {{-- =================================================
                 STOCK ALERTS
            ================================================== --}}

            <div class="section">

                <h2 class="section-title">
                    Stock Alerts
                </h2>


                @if ($stockAlertCount > 0)


                    {{-- OUT OF STOCK --}}

                    @if ($outOfStockCount > 0)

                        <div class="
                            stock-alert-card
                            alert-out-card
                        ">

                            <div class="stock-alert-header">

                                <div class="stock-alert-header-content">

                                    <h2>
                                        🔴 Out of Stock
                                    </h2>

                                    <p>
                                        Inventory records with zero or negative base quantity.
                                    </p>

                                </div>

                                <span class="
                                    stock-alert-count
                                    stock-alert-count-out
                                ">
                                    {{ number_format($outOfStockCount) }}
                                </span>

                            </div>


                            @if ($outOfStockInventories->count())

                                <div class="table-wrapper">

                                    <table>

                                        <thead>

                                            <tr>
                                                <th>Product</th>
                                                <th>Location</th>
                                                <th>Current Stock</th>
                                                <th>Reorder Point</th>
                                                <th>Status</th>
                                                <th>Action</th>
                                            </tr>

                                        </thead>

                                        <tbody>

                                        @foreach (
                                            $outOfStockInventories
                                            as $inventory
                                        )

                                            <tr>

                                                <td>

                                                    <span class="alert-product-name">

                                                        @if ($inventory->product)

                                                            {{ $inventory->product->name }}

                                                        @else

                                                            <span class="deleted">
                                                                Product deleted
                                                            </span>

                                                        @endif

                                                    </span>

                                                    @if ($inventory->product?->sku)

                                                        <span class="alert-product-sku">
                                                            SKU:
                                                            {{ $inventory->product->sku }}
                                                        </span>

                                                    @endif

                                                </td>


                                                <td>

                                                    @if ($inventory->location)

                                                        {{ $inventory->location->name }}

                                                    @else

                                                        <span class="deleted">
                                                            Location deleted
                                                        </span>

                                                    @endif

                                                </td>


                                                <td class="alert-stock-danger">

                                                    {{ number_format(
                                                        (float) $inventory->base_quantity,
                                                        4
                                                    ) }}

                                                    {{ $inventory->product?->baseUnit?->code ?? 'base units' }}

                                                </td>


                                                <td class="alert-reorder">

                                                    {{ number_format(
                                                        (float) (
                                                            $inventory->product?->reorder_point ?? 0
                                                        ),
                                                        4
                                                    ) }}

                                                </td>


                                                <td>

                                                    <span class="danger-badge">
                                                        Out of Stock
                                                    </span>

                                                </td>


                                                <td>

                                                    <a
                                                        href="{{ route(
                                                            'inventories.show',
                                                            $inventory
                                                        ) }}"
                                                        class="view-link"
                                                    >
                                                        View Inventory
                                                    </a>

                                                </td>

                                            </tr>

                                        @endforeach

                                        </tbody>

                                    </table>

                                </div>

                            @else

                                <div class="stock-alert-empty">
                                    No out-of-stock inventory records found.
                                </div>

                            @endif

                        </div>

                    @endif


                    {{-- CRITICAL STOCK --}}

                    @if ($criticalStockCount > 0)

                        <div class="
                            stock-alert-card
                            alert-critical-card
                        ">

                            <div class="stock-alert-header">

                                <div class="stock-alert-header-content">

                                    <h2>
                                        🔴 Critical Stock
                                    </h2>

                                    <p>
                                        Inventory at or below 50% of the reorder point.
                                    </p>

                                </div>

                                <span class="
                                    stock-alert-count
                                    stock-alert-count-critical
                                ">
                                    {{ number_format($criticalStockCount) }}
                                </span>

                            </div>


                            @if ($criticalStockInventories->count())

                                <div class="table-wrapper">

                                    <table>

                                        <thead>

                                            <tr>
                                                <th>Product</th>
                                                <th>Location</th>
                                                <th>Current Stock</th>
                                                <th>Reorder Point</th>
                                                <th>Status</th>
                                                <th>Action</th>
                                            </tr>

                                        </thead>

                                        <tbody>

                                        @foreach (
                                            $criticalStockInventories
                                            as $inventory
                                        )

                                            <tr>

                                                <td>

                                                    <span class="alert-product-name">

                                                        @if ($inventory->product)

                                                            {{ $inventory->product->name }}

                                                        @else

                                                            <span class="deleted">
                                                                Product deleted
                                                            </span>

                                                        @endif

                                                    </span>

                                                    @if ($inventory->product?->sku)

                                                        <span class="alert-product-sku">
                                                            SKU:
                                                            {{ $inventory->product->sku }}
                                                        </span>

                                                    @endif

                                                </td>


                                                <td>

                                                    @if ($inventory->location)

                                                        {{ $inventory->location->name }}

                                                    @else

                                                        <span class="deleted">
                                                            Location deleted
                                                        </span>

                                                    @endif

                                                </td>


                                                <td class="alert-stock-critical">

                                                    {{ number_format(
                                                        (float) $inventory->base_quantity,
                                                        4
                                                    ) }}

                                                    {{ $inventory->product?->baseUnit?->code ?? 'base units' }}

                                                </td>


                                                <td class="alert-reorder">

                                                    {{ number_format(
                                                        (float) (
                                                            $inventory->product?->reorder_point ?? 0
                                                        ),
                                                        4
                                                    ) }}

                                                </td>


                                                <td>

                                                    <span class="danger-badge">
                                                        Critical
                                                    </span>

                                                </td>


                                                <td>

                                                    <a
                                                        href="{{ route(
                                                            'inventories.show',
                                                            $inventory
                                                        ) }}"
                                                        class="view-link"
                                                    >
                                                        View Inventory
                                                    </a>

                                                </td>

                                            </tr>

                                        @endforeach

                                        </tbody>

                                    </table>

                                </div>

                            @else

                                <div class="stock-alert-empty">
                                    No critical-stock inventory records found.
                                </div>

                            @endif

                        </div>

                    @endif


                    {{-- LOW STOCK --}}

                    @if ($lowStockCount > 0)

                        <div class="
                            stock-alert-card
                            alert-low-card
                        ">

                            <div class="stock-alert-header">

                                <div class="stock-alert-header-content">

                                    <h2>
                                        🟡 Low Stock
                                    </h2>

                                    <p>
                                        Inventory above the critical level but at or below the reorder point.
                                    </p>

                                </div>

                                <span class="
                                    stock-alert-count
                                    stock-alert-count-low
                                ">
                                    {{ number_format($lowStockCount) }}
                                </span>

                            </div>


                            @if ($lowStockInventories->count())

                                <div class="table-wrapper">

                                    <table>

                                        <thead>

                                            <tr>
                                                <th>Product</th>
                                                <th>Location</th>
                                                <th>Current Stock</th>
                                                <th>Reorder Point</th>
                                                <th>Difference</th>
                                                <th>Status</th>
                                                <th>Action</th>
                                            </tr>

                                        </thead>

                                        <tbody>

                                        @foreach (
                                            $lowStockInventories
                                            as $inventory
                                        )

                                            @php

                                                $currentBaseQuantity =
                                                    (float) $inventory->base_quantity;

                                                $reorderPoint =
                                                    (float) (
                                                        $inventory
                                                            ->product
                                                            ?->reorder_point
                                                        ?? 0
                                                    );

                                                $stockDifference =
                                                    $reorderPoint -
                                                    $currentBaseQuantity;

                                            @endphp


                                            <tr>

                                                <td>

                                                    <span class="alert-product-name">

                                                        @if ($inventory->product)

                                                            {{ $inventory->product->name }}

                                                        @else

                                                            <span class="deleted">
                                                                Product deleted
                                                            </span>

                                                        @endif

                                                    </span>

                                                    @if ($inventory->product?->sku)

                                                        <span class="alert-product-sku">
                                                            SKU:
                                                            {{ $inventory->product->sku }}
                                                        </span>

                                                    @endif

                                                </td>


                                                <td>

                                                    @if ($inventory->location)

                                                        {{ $inventory->location->name }}

                                                    @else

                                                        <span class="deleted">
                                                            Location deleted
                                                        </span>

                                                    @endif

                                                </td>


                                                <td class="alert-stock-warning">

                                                    {{ number_format(
                                                        $currentBaseQuantity,
                                                        4
                                                    ) }}

                                                    {{ $inventory->product?->baseUnit?->code ?? 'base units' }}

                                                </td>


                                                <td class="alert-reorder">

                                                    {{ number_format(
                                                        $reorderPoint,
                                                        4
                                                    ) }}

                                                </td>


                                                <td class="alert-difference">

                                                    -{{ number_format(
                                                        $stockDifference,
                                                        4
                                                    ) }}

                                                </td>


                                                <td>

                                                    <span class="warning-badge">
                                                        Low Stock
                                                    </span>

                                                </td>


                                                <td>

                                                    <a
                                                        href="{{ route(
                                                            'inventories.show',
                                                            $inventory
                                                        ) }}"
                                                        class="view-link"
                                                    >
                                                        View Inventory
                                                    </a>

                                                </td>

                                            </tr>

                                        @endforeach

                                        </tbody>

                                    </table>

                                </div>

                            @else

                                <div class="stock-alert-empty">
                                    No low-stock inventory records found.
                                </div>

                            @endif

                        </div>

                    @endif


                @else

                    <div class="stock-alert-all-clear">

                        <div class="stock-alert-all-clear-title">
                            ✓ All Stock Levels Are Healthy
                        </div>

                        <div class="stock-alert-all-clear-description">
                            No inventory records currently require attention.
                        </div>

                    </div>

                @endif

            </div>


            {{-- =================================================
                 MOVEMENT ANALYTICS
            ================================================== --}}

            <div class="section">

                <h2 class="section-title">
                    Stock Movement Analytics
                </h2>


                <div class="analytics-grid">

                    <div class="analytics-card">

                        <div class="analytics-label">
                            Total IN
                        </div>

                        <div class="analytics-value analytics-in">
                            +{{ number_format(
                                (float) $totalIn,
                                4
                            ) }}
                        </div>

                        <div class="analytics-description">
                            Base units received
                        </div>

                    </div>


                    <div class="analytics-card">

                        <div class="analytics-label">
                            Total OUT
                        </div>

                        <div class="analytics-value analytics-out">
                            -{{ number_format(
                                (float) $totalOut,
                                4
                            ) }}
                        </div>

                        <div class="analytics-description">
                            Base units removed
                        </div>

                    </div>


                    <div class="analytics-card">

                        <div class="analytics-label">
                            Net Movement
                        </div>

                        <div class="
                            analytics-value
                            {{
                                $netMovement >= 0
                                    ? 'analytics-positive'
                                    : 'analytics-negative'
                            }}
                        ">

                            {{ $netMovement >= 0 ? '+' : '' }}

                            {{ number_format(
                                (float) $netMovement,
                                4
                            ) }}

                        </div>

                        <div class="analytics-description">
                            IN minus OUT
                        </div>

                    </div>


                    <div class="analytics-card">

                        <div class="analytics-label">
                            Current Total Stock
                        </div>

                        <div class="analytics-value analytics-positive">
                            {{ number_format(
                                (float) $totalBaseStock,
                                4
                            ) }}
                        </div>

                        <div class="analytics-description">
                            Current base units
                        </div>

                    </div>

                </div>

            </div>


            {{-- =================================================
                 STOCK MOVEMENT CHART
            ================================================== --}}

            <div class="chart-card section">

                <div class="chart-header">

                    <div>

                        <h2>
                            Stock Movement — Last 7 Days
                        </h2>

                        <p>
                            Daily inventory movement measured in base units.
                        </p>

                    </div>


                    <div class="chart-legend">

                        <div class="legend-item">

                            <span class="legend-dot legend-in"></span>

                            IN

                        </div>


                        <div class="legend-item">

                            <span class="legend-dot legend-out"></span>

                            OUT

                        </div>

                    </div>

                </div>


                @php

                    $maxChartValue = 0;

                    foreach ($chartIn as $key => $value) {

                        $maxChartValue = max(
                            $maxChartValue,
                            (float) $value,
                            (float) ($chartOut[$key] ?? 0)
                        );

                    }

                    if ($maxChartValue <= 0) {
                        $maxChartValue = 10;
                    }

                @endphp


                @if ($chartTransactions->count() ?? false)

                    <div class="chart-wrapper">

                        <div class="chart">

                            <div class="chart-grid">

                                <div class="grid-line"></div>
                                <div class="grid-line"></div>
                                <div class="grid-line"></div>
                                <div class="grid-line"></div>
                                <div class="grid-line"></div>

                            </div>


                            <div class="chart-bars">

                                @foreach (
                                    $chartDates
                                    as $key => $label
                                )

                                    @php

                                        $inValue =
                                            (float) (
                                                $chartIn[$key] ?? 0
                                            );

                                        $outValue =
                                            (float) (
                                                $chartOut[$key] ?? 0
                                            );

                                        $inHeight =
                                            (
                                                $inValue /
                                                $maxChartValue
                                            ) * 100;

                                        $outHeight =
                                            (
                                                $outValue /
                                                $maxChartValue
                                            ) * 100;

                                    @endphp


                                    <div class="chart-day">

                                        <div
                                            class="bar bar-in"
                                            style="
                                                height:
                                                {{ max(
                                                    2,
                                                    $inHeight
                                                ) }}%;
                                            "
                                            title="
                                                IN:
                                                {{ number_format(
                                                    $inValue,
                                                    4
                                                ) }}
                                                base units
                                            "
                                        ></div>


                                        <div
                                            class="bar bar-out"
                                            style="
                                                height:
                                                {{ max(
                                                    2,
                                                    $outHeight
                                                ) }}%;
                                            "
                                            title="
                                                OUT:
                                                {{ number_format(
                                                    $outValue,
                                                    4
                                                ) }}
                                                base units
                                            "
                                        ></div>


                                        <div class="bar-label">
                                            {{ $label }}
                                        </div>

                                    </div>

                                @endforeach

                            </div>

                        </div>

                    </div>

                @else

                    <div class="chart-empty">
                        No stock movement recorded during the last 7 days.
                    </div>

                @endif

            </div>


            {{-- =================================================
                 RECENT TRANSACTIONS
            ================================================== --}}

            <div
                class="card section"
                id="recent-transactions"
            >

                <div class="card-header">

                    <div>

                        <h2>
                            Recent Transactions
                        </h2>

                        <p>
                            Latest inventory movements and adjustments.
                        </p>

                    </div>


                    <a
                        href="{{ route(
                            'inventory-transactions.index'
                        ) }}"
                        class="view-link"
                    >
                        View All
                    </a>

                </div>


                @if ($recentTransactions->count())

                    <div class="table-wrapper">

                        <table>

                            <thead>

                                <tr>
                                    <th>ID</th>
                                    <th>Date</th>
                                    <th>Product</th>
                                    <th>Location</th>
                                    <th>Type</th>
                                    <th>Quantity</th>
                                    <th>Base Quantity</th>
                                    <th>Reference</th>
                                </tr>

                            </thead>


                            <tbody>

                            @foreach (
                                $recentTransactions
                                as $transaction
                            )

                                <tr>

                                    <td>
                                        <strong>
                                            {{ $transaction->id }}
                                        </strong>
                                    </td>


                                    <td>
                                        {{ $transaction
                                            ->created_at
                                            ?->format(
                                                'Y-m-d H:i:s'
                                            ) }}
                                    </td>


                                    <td>

                                        @if ($transaction->product)

                                            {{ $transaction
                                                ->product
                                                ->name }}

                                            @if (
                                                $transaction
                                                    ->product
                                                    ->sku
                                            )

                                                (
                                                {{
                                                    $transaction
                                                        ->product
                                                        ->sku
                                                }}
                                                )

                                            @endif

                                        @else

                                            <span class="deleted">
                                                Product deleted
                                            </span>

                                        @endif

                                    </td>


                                    <td>

                                        @if ($transaction->location)

                                            {{
                                                $transaction
                                                    ->location
                                                    ->name
                                            }}

                                            @if (
                                                $transaction
                                                    ->location
                                                    ->code
                                            )

                                                (
                                                {{
                                                    $transaction
                                                        ->location
                                                        ->code
                                                }}
                                                )

                                            @endif

                                        @else

                                            <span class="deleted">
                                                Location deleted
                                            </span>

                                        @endif

                                    </td>


                                    <td>

                                        @if (
                                            $transaction->type === 'in'
                                        )

                                            <span class="badge badge-in">
                                                IN
                                            </span>

                                        @else

                                            <span class="badge badge-out">
                                                OUT
                                            </span>

                                        @endif

                                    </td>


                                    <td>

                                        {{ number_format(
                                            (float)
                                            $transaction->quantity,
                                            4
                                        ) }}

                                        @if (
                                            $transaction
                                                ->productUnit
                                                ?->unitOfMeasure
                                        )

                                            {{
                                                $transaction
                                                    ->productUnit
                                                    ->unitOfMeasure
                                                    ->code
                                            }}

                                        @endif

                                    </td>


                                    <td>

                                        @if (
                                            $transaction->type === 'in'
                                        )
                                            +
                                        @else
                                            -
                                        @endif

                                        {{ number_format(
                                            (float)
                                            $transaction->base_quantity,
                                            4
                                        ) }}

                                    </td>


                                    <td>
                                        {{
                                            $transaction->reference
                                            ?? '-'
                                        }}
                                    </td>

                                </tr>

                            @endforeach

                            </tbody>

                        </table>

                    </div>


                    @if ($recentTransactions->hasPages())

                        <div class="pagination">

                            @if (
                                $recentTransactions
                                    ->onFirstPage()
                            )

                                <span class="disabled">
                                    ‹ Previous
                                </span>

                            @else

                                <a
                                    href="{{
                                        $recentTransactions
                                            ->previousPageUrl()
                                    }}#recent-transactions"
                                >
                                    ‹ Previous
                                </a>

                            @endif


                            @foreach (
                                $recentTransactions
                                    ->getUrlRange(
                                        1,
                                        $recentTransactions
                                            ->lastPage()
                                    )
                                as $page => $url
                            )

                                @if (
                                    $page ==
                                    $recentTransactions
                                        ->currentPage()
                                )

                                    <span class="active">
                                        {{ $page }}
                                    </span>

                                @else

                                    <a
                                        href="{{ $url }}#recent-transactions"
                                    >
                                        {{ $page }}
                                    </a>

                                @endif

                            @endforeach


                            @if (
                                $recentTransactions
                                    ->hasMorePages()
                            )

                                <a
                                    href="{{
                                        $recentTransactions
                                            ->nextPageUrl()
                                    }}#recent-transactions"
                                >
                                    Next ›
                                </a>

                            @else

                                <span class="disabled">
                                    Next ›
                                </span>

                            @endif

                        </div>

                    @endif

                @else

                    <div class="empty">
                        No inventory transactions found.
                    </div>

                @endif

            </div>


            {{-- =================================================
                 RECENT TRANSFERS
            ================================================== --}}

            <div
                class="card section"
                id="recent-transfers"
            >

                <div class="card-header">

                    <div>

                        <h2>
                            Recent Transfers
                        </h2>

                        <p>
                            Latest stock movements between locations.
                        </p>

                    </div>


                    <a
                        href="{{ route(
                            'inventory-transfers.index'
                        ) }}"
                        class="view-link"
                    >
                        View All
                    </a>

                </div>


                @if ($recentTransfers->count())

                    <div class="transfer-summary">

                        <div class="transfer-summary-item">

                            <div class="transfer-summary-label">
                                Total Transfers
                            </div>

                            <div class="transfer-summary-value">
                                {{ number_format(
                                    $totalTransfers
                                ) }}
                            </div>

                        </div>


                        <div class="transfer-summary-item">

                            <div class="transfer-summary-label">
                                Transferred Base Units
                            </div>

                            <div class="transfer-summary-value">
                                {{ number_format(
                                    (float)
                                    $totalTransferBaseQuantity,
                                    4
                                ) }}
                            </div>

                        </div>


                        <div class="transfer-summary-item">

                            <div class="transfer-summary-label">
                                Current Total Stock
                            </div>

                            <div class="transfer-summary-value">
                                {{ number_format(
                                    (float)
                                    $totalBaseStock,
                                    4
                                ) }}
                            </div>

                        </div>

                    </div>


                    <div class="table-wrapper">

                        <table>

                            <thead>

                                <tr>
                                    <th>ID</th>
                                    <th>Date</th>
                                    <th>Product</th>
                                    <th>From</th>
                                    <th>To</th>
                                    <th>Quantity</th>
                                    <th>Base Quantity</th>
                                    <th>Reference</th>
                                    <th>Action</th>
                                </tr>

                            </thead>


                            <tbody>

                            @foreach (
                                $recentTransfers
                                as $transfer
                            )

                                <tr>

                                    <td>
                                        <strong>
                                            #{{ $transfer->id }}
                                        </strong>
                                    </td>


                                    <td>
                                        {{ $transfer
                                            ->created_at
                                            ?->format(
                                                'Y-m-d H:i:s'
                                            ) }}
                                    </td>


                                    <td>

                                        @if ($transfer->product)

                                            {{
                                                $transfer
                                                    ->product
                                                    ->name
                                            }}

                                        @else

                                            <span class="deleted">
                                                Product deleted
                                            </span>

                                        @endif

                                    </td>


                                    <td>

                                        @if (
                                            $transfer
                                                ->sourceInventory
                                                ?->location
                                        )

                                            {{
                                                $transfer
                                                    ->sourceInventory
                                                    ->location
                                                    ->name
                                            }}

                                        @else

                                            <span class="deleted">
                                                Location deleted
                                            </span>

                                        @endif

                                    </td>


                                    <td>

                                        @if (
                                            $transfer
                                                ->destinationInventory
                                                ?->location
                                        )

                                            {{
                                                $transfer
                                                    ->destinationInventory
                                                    ->location
                                                    ->name
                                            }}

                                        @else

                                            <span class="deleted">
                                                Location deleted
                                            </span>

                                        @endif

                                    </td>


                                    <td>

                                        {{ number_format(
                                            (float)
                                            $transfer->quantity,
                                            4
                                        ) }}

                                        @if (
                                            $transfer
                                                ->productUnit
                                                ?->unitOfMeasure
                                        )

                                            {{
                                                $transfer
                                                    ->productUnit
                                                    ->unitOfMeasure
                                                    ->code
                                            }}

                                        @endif

                                    </td>


                                    <td>

                                        {{ number_format(
                                            (float)
                                            $transfer->base_quantity,
                                            4
                                        ) }}

                                        base units

                                    </td>


                                    <td>
                                        {{
                                            $transfer
                                                ->reference
                                            ?? '-'
                                        }}
                                    </td>


                                    <td>

                                        <a
                                            href="{{ route(
                                                'inventory-transfers.show',
                                                $transfer
                                            ) }}"
                                            class="view-link"
                                        >
                                            View
                                        </a>

                                    </td>

                                </tr>

                            @endforeach

                            </tbody>

                        </table>

                    </div>


                    @if ($recentTransfers->hasPages())

                        <div class="pagination">

                            @if (
                                $recentTransfers
                                    ->onFirstPage()
                            )

                                <span class="disabled">
                                    ‹ Previous
                                </span>

                            @else

                                <a
                                    href="{{
                                        $recentTransfers
                                            ->previousPageUrl()
                                    }}#recent-transfers"
                                >
                                    ‹ Previous
                                </a>

                            @endif


                            @foreach (
                                $recentTransfers
                                    ->getUrlRange(
                                        1,
                                        $recentTransfers
                                            ->lastPage()
                                    )
                                as $page => $url
                            )

                                @if (
                                    $page ==
                                    $recentTransfers
                                        ->currentPage()
                                )

                                    <span class="active">
                                        {{ $page }}
                                    </span>

                                @else

                                    <a
                                        href="{{ $url }}#recent-transfers"
                                    >
                                        {{ $page }}
                                    </a>

                                @endif

                            @endforeach


                            @if (
                                $recentTransfers
                                    ->hasMorePages()
                            )

                                <a
                                    href="{{
                                        $recentTransfers
                                            ->nextPageUrl()
                                    }}#recent-transfers"
                                >
                                    Next ›
                                </a>

                            @else

                                <span class="disabled">
                                    Next ›
                                </span>

                            @endif

                        </div>

                    @endif

                @else

                    <div class="empty">
                        No inventory transfers found.
                    </div>

                @endif

            </div>


            {{-- =================================================
                 FOOTER
            ================================================== --}}

            <div class="footer">
                Inventory Management System
            </div>


@endsection
