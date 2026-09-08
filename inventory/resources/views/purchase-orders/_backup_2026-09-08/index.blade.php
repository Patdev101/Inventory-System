@extends('layouts.app')

@section('title', 'Purchase Orders')

@section('content')

@php

    /*
    |--------------------------------------------------------------------------
    | STATUS CONFIGURATION
    |--------------------------------------------------------------------------
    */

    $statusOptions = [
        '' => 'All Orders',
        'draft' => 'Draft',
        'pending_approval' => 'Pending Approval',
        'approved' => 'Approved',
        'rejected' => 'Rejected',
        'ordered' => 'Ordered',
        'partially_received' => 'Partially Received',
        'received' => 'Received',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
    ];

    $statusStyles = [

        'draft' => [
            'label' => 'Draft',
            'color' => '#64748b',
            'background' => '#f8fafc',
            'border' => '#cbd5e1',
            'soft' => '#f1f5f9',
            'gradient' => 'linear-gradient(135deg, #f8fafc, #ffffff)',
        ],

        'pending_approval' => [
            'label' => 'Pending Approval',
            'color' => '#d97706',
            'background' => '#fffbeb',
            'border' => '#fbbf24',
            'soft' => '#fef3c7',
            'gradient' => 'linear-gradient(135deg, #fffbeb, #ffffff)',
        ],

        'approved' => [
            'label' => 'Approved',
            'color' => '#2563eb',
            'background' => '#eff6ff',
            'border' => '#60a5fa',
            'soft' => '#dbeafe',
            'gradient' => 'linear-gradient(135deg, #eff6ff, #ffffff)',
        ],

        'rejected' => [
            'label' => 'Rejected',
            'color' => '#dc2626',
            'background' => '#fef2f2',
            'border' => '#f87171',
            'soft' => '#fee2e2',
            'gradient' => 'linear-gradient(135deg, #fef2f2, #ffffff)',
        ],

        'ordered' => [
            'label' => 'Ordered',
            'color' => '#7c3aed',
            'background' => '#f5f3ff',
            'border' => '#a78bfa',
            'soft' => '#ede9fe',
            'gradient' => 'linear-gradient(135deg, #f5f3ff, #ffffff)',
        ],

        'partially_received' => [
            'label' => 'Partially Received',
            'color' => '#ea580c',
            'background' => '#fff7ed',
            'border' => '#fb923c',
            'soft' => '#ffedd5',
            'gradient' => 'linear-gradient(135deg, #fff7ed, #ffffff)',
        ],

        'received' => [
            'label' => 'Received',
            'color' => '#059669',
            'background' => '#ecfdf5',
            'border' => '#34d399',
            'soft' => '#d1fae5',
            'gradient' => 'linear-gradient(135deg, #ecfdf5, #ffffff)',
        ],

        'completed' => [
            'label' => 'Completed',
            'color' => '#16a34a',
            'background' => '#f0fdf4',
            'border' => '#4ade80',
            'soft' => '#dcfce7',
            'gradient' => 'linear-gradient(135deg, #dcfce7, #f0fdf4, #ffffff)',
        ],

        'cancelled' => [
            'label' => 'Cancelled',
            'color' => '#dc2626',
            'background' => '#fef2f2',
            'border' => '#f87171',
            'soft' => '#fee2e2',
            'gradient' => 'linear-gradient(135deg, #fef2f2, #ffffff)',
        ],
    ];


    /*
    |--------------------------------------------------------------------------
    | WORKFLOW
    |--------------------------------------------------------------------------
    */

    $workflow = [
        'draft' => 'Draft',
        'pending_approval' => 'Pending',
        'approved' => 'Approved',
        'ordered' => 'Ordered',
        'received' => 'Received',
    ];

    $workflowStage = function ($status) {

        return match ($status) {

            'draft' => 0,

            'pending_approval' => 1,

            'approved' => 2,

            'ordered',
            'partially_received' => 3,

            'received',
            'completed' => 4,

            default => null,

        };

    };

@endphp


<style>

/* ========================================================================
   PAGE
======================================================================== */

.po-page {
    --bg: #f4f7fb;
    --card: #ffffff;
    --text: #172033;
    --muted: #718096;
    --border: #e4e9f0;

    min-height: calc(100vh - 80px);

    padding-bottom: 40px;

    color: var(--text);
}

.po-page *,
.po-page *::before,
.po-page *::after {
    box-sizing: border-box;
}


/* ========================================================================
   HEADER
======================================================================== */

.po-header {
    display: flex;
    align-items: center;
    justify-content: space-between;

    gap: 20px;

    margin-bottom: 24px;
}

.po-header-copy h1 {
    margin: 0;

    font-size: 30px;
    font-weight: 800;

    letter-spacing: -0.035em;

    color: #111827;
}

.po-header-copy p {
    margin: 7px 0 0;

    color: #7b8798;

    font-size: 13px;
}


/* ========================================================================
   CREATE BUTTON
======================================================================== */

.po-primary-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;

    gap: 9px;

    min-height: 43px;

    padding: 0 17px;

    border: 0;
    border-radius: 10px;

    background:
        linear-gradient(
            135deg,
            #16a34a,
            #15803d
        );

    color: #ffffff;

    font-size: 12px;
    font-weight: 800;

    text-decoration: none;

    box-shadow:
        0 5px 14px rgba(22, 163, 74, .20);

    transition: all .18s ease;
}

.po-primary-btn:hover {
    color: #ffffff;

    transform: translateY(-2px);

    box-shadow:
        0 9px 22px rgba(22, 163, 74, .28);
}

.po-primary-btn:active {
    transform: translateY(0);
}


/* ========================================================================
   SUMMARY CARDS
======================================================================== */

.po-summary {
    display: grid;

    grid-template-columns:
        repeat(4, minmax(0, 1fr));

    gap: 12px;

    margin-bottom: 18px;
}

.po-summary-card {
    position: relative;

    overflow: hidden;

    padding: 16px;

    border: 1px solid #e5eaf0;

    border-radius: 12px;

    background: #ffffff;

    box-shadow:
        0 2px 8px rgba(15, 23, 42, .035);

    transition: .18s ease;
}

.po-summary-card:hover {
    transform: translateY(-2px);

    box-shadow:
        0 8px 20px rgba(15, 23, 42, .07);
}

.po-summary-card::before {
    content: '';

    position: absolute;

    left: 0;
    top: 0;
    bottom: 0;

    width: 4px;

    background: var(--summary-color);
}

.po-summary-top {
    display: flex;
    align-items: center;
    justify-content: space-between;

    margin-bottom: 9px;
}

.po-summary-label {
    color: #8a95a5;

    font-size: 10px;
    font-weight: 800;

    text-transform: uppercase;

    letter-spacing: .07em;
}

.po-summary-icon {
    display: flex;
    align-items: center;
    justify-content: center;

    width: 29px;
    height: 29px;

    border-radius: 8px;

    background: var(--summary-bg);

    color: var(--summary-color);

    font-size: 14px;
    font-weight: 800;
}

.po-summary-value {
    color: #1f2937;

    font-size: 22px;
    font-weight: 800;

    letter-spacing: -.03em;
}


/* ========================================================================
   FILTER PANEL
======================================================================== */

.po-filter-panel {
    position: sticky;

    top: 10px;

    z-index: 20;

    margin-bottom: 16px;

    padding: 15px;

    border: 1px solid #e2e8f0;

    border-radius: 13px;

    background:
        rgba(255, 255, 255, .96);

    box-shadow:
        0 5px 18px rgba(15, 23, 42, .055);

    backdrop-filter: blur(12px);
}

.po-filter-heading {
    display: flex;
    justify-content: space-between;
    align-items: center;

    margin-bottom: 10px;
}

.po-filter-heading-title {
    color: #6b7280;

    font-size: 10px;
    font-weight: 800;

    text-transform: uppercase;

    letter-spacing: .08em;
}

.po-filter-count {
    color: #98a2b3;

    font-size: 11px;
    font-weight: 600;
}

.po-status-scroll {
    display: flex;

    gap: 7px;

    overflow-x: auto;

    padding-bottom: 2px;

    scrollbar-width: thin;
}

.po-status-scroll::-webkit-scrollbar {
    height: 4px;
}

.po-status-scroll::-webkit-scrollbar-thumb {
    background: #d7dee7;

    border-radius: 99px;
}

.po-status-btn {
    flex: 0 0 auto;

    display: inline-flex;
    align-items: center;

    gap: 7px;

    min-height: 34px;

    padding: 0 12px;

    border: 1px solid #e4e8ee;

    border-radius: 8px;

    background: #ffffff;

    color: #667085;

    font-size: 11px;
    font-weight: 700;

    text-decoration: none;

    transition: .16s ease;
}

.po-status-btn:hover {
    color: #1f2937;

    background: #f8fafc;

    border-color: #cbd5e1;

    transform: translateY(-1px);
}

.po-status-btn.is-active {
    color: #15803d;

    border-color: #86efac;

    background: #f0fdf4;

    box-shadow:
        0 0 0 2px rgba(34, 197, 94, .08);
}

.po-status-dot {
    width: 6px;
    height: 6px;

    border-radius: 50%;

    background: currentColor;
}


/* ========================================================================
   FILTER BOTTOM
======================================================================== */

.po-filter-bottom {
    display: flex;
    align-items: center;

    gap: 9px;

    margin-top: 13px;
}

.po-select {
    height: 36px;

    min-width: 170px;

    padding: 0 11px;

    border: 1px solid #e1e6ec;

    border-radius: 8px;

    background: #ffffff;

    color: #4b5563;

    font-size: 11px;

    outline: none;

    cursor: pointer;
}

.po-select:focus {
    border-color: #86efac;

    box-shadow:
        0 0 0 3px rgba(34, 197, 94, .08);
}

.po-reset {
    display: inline-flex;
    align-items: center;

    height: 36px;

    padding: 0 11px;

    border-radius: 8px;

    color: #7b8494;

    font-size: 11px;
    font-weight: 700;

    text-decoration: none;
}

.po-reset:hover {
    background: #f4f6f8;

    color: #374151;
}


/* ========================================================================
   TOOLBAR
======================================================================== */

.po-toolbar {
    display: flex;

    align-items: center;

    gap: 9px;

    margin-bottom: 14px;
}

.po-search {
    position: relative;

    flex: 1;
}

.po-search-icon {
    position: absolute;

    left: 13px;
    top: 50%;

    width: 15px;
    height: 15px;

    transform: translateY(-50%);

    color: #9aa4b2;

    pointer-events: none;
}

.po-search input {
    width: 100%;

    height: 41px;

    padding: 0 13px 0 38px;

    border: 1px solid #e1e6ec;

    border-radius: 9px;

    background: #ffffff;

    color: #1f2937;

    font-size: 12px;

    outline: none;

    transition: .16s ease;
}

.po-search input:focus {
    border-color: #86efac;

    box-shadow:
        0 0 0 3px rgba(34, 197, 94, .08);
}

.po-search-btn {
    height: 41px;

    padding: 0 15px;

    border: 1px solid #dfe5eb;

    border-radius: 9px;

    background: #ffffff;

    color: #475467;

    font-size: 11px;
    font-weight: 800;

    cursor: pointer;
}

.po-search-btn:hover {
    background: #f8fafc;

    border-color: #cbd5e1;
}


/* ========================================================================
   VIEW BUTTONS
======================================================================== */

.po-view-actions {
    display: flex;

    gap: 5px;
}

.po-icon-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;

    width: 38px;
    height: 38px;

    border: 1px solid #e1e6ec;

    border-radius: 8px;

    background: #ffffff;

    color: #8a94a4;

    cursor: pointer;

    transition: .16s ease;
}

.po-icon-btn:hover,
.po-icon-btn.active {
    color: #15803d;

    background: #f0fdf4;

    border-color: #86efac;
}


/* ========================================================================
   RESULTS BAR
======================================================================== */

.po-results-bar {
    display: flex;

    justify-content: space-between;
    align-items: center;

    margin-bottom: 9px;
}

.po-results-title {
    color: #344054;

    font-size: 12px;
    font-weight: 800;
}

.po-results-meta {
    color: #98a2b3;

    font-size: 10px;
}


/* ========================================================================
   SCROLLABLE ORDER LIST
======================================================================== */

.po-list-wrapper {
    max-height: 650px;

    overflow-y: auto;

    padding: 3px 7px 12px 2px;

    scrollbar-width: thin;

    scrollbar-color:
        #cbd5e1
        transparent;
}

.po-list-wrapper::-webkit-scrollbar {
    width: 7px;
}

.po-list-wrapper::-webkit-scrollbar-track {
    background: transparent;
}

.po-list-wrapper::-webkit-scrollbar-thumb {
    background: #cbd5e1;

    border-radius: 99px;
}

.po-list-wrapper::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}

.po-list {
    display: flex;

    flex-direction: column;

    gap: 11px;
}


/* ========================================================================
   CARD
======================================================================== */

.po-card {
    position: relative;

    overflow: hidden;

    border: 1px solid #e2e7ed;

    border-radius: 13px;

    background: #ffffff;

    box-shadow:
        0 2px 8px rgba(15, 23, 42, .035);

    transition:
        transform .18s ease,
        box-shadow .18s ease,
        border-color .18s ease;
}

.po-card:hover {
    transform: translateY(-2px);

    border-color: #cbd5e1;

    box-shadow:
        0 9px 24px rgba(15, 23, 42, .08);
}


/* ========================================================================
   STATUS ACCENT
======================================================================== */

.po-card-status-accent {
    position: absolute;

    left: 0;
    top: 0;
    bottom: 0;

    width: 5px;

    background: var(--status-color);
}


/* ========================================================================
   COMPLETED CARD
======================================================================== */

.po-card.is-completed {
    border-color: #86efac;

    background:
        linear-gradient(
            135deg,
            #f0fdf4 0%,
            #ffffff 46%,
            #ffffff 100%
        );

    box-shadow:
        0 3px 13px rgba(22, 163, 74, .09);
}

.po-card.is-completed:hover {
    border-color: #4ade80;

    box-shadow:
        0 10px 28px rgba(22, 163, 74, .14);
}


/* ========================================================================
   CARD MAIN
======================================================================== */

.po-card-main {
    padding: 17px 19px 15px 22px;
}

.po-card-header {
    display: flex;

    align-items: flex-start;

    justify-content: space-between;

    gap: 16px;
}

.po-card-heading {
    min-width: 0;
}

.po-number-row {
    display: flex;

    align-items: center;

    gap: 8px;

    flex-wrap: wrap;
}

.po-number {
    color: #172033;

    font-size: 15px;
    font-weight: 900;

    letter-spacing: -.015em;
}

.po-reference {
    color: #9aa4b2;

    font-size: 10px;
}

.po-route {
    display: flex;

    align-items: center;

    gap: 7px;

    margin-top: 6px;

    color: #687386;

    font-size: 11px;
}

.po-route strong {
    color: #475467;

    font-weight: 700;
}

.po-route-arrow {
    color: #a8b0bc;
}


/* ========================================================================
   COMPLETED CHECK
======================================================================== */

.po-completed-check {
    display: inline-flex;

    align-items: center;
    justify-content: center;

    width: 21px;
    height: 21px;

    border-radius: 50%;

    background: #16a34a;

    color: #ffffff;

    font-size: 11px;
    font-weight: 900;

    box-shadow:
        0 2px 6px rgba(22, 163, 74, .25);
}


/* ========================================================================
   RIGHT SIDE
======================================================================== */

.po-card-right {
    display: flex;

    align-items: center;

    gap: 8px;

    flex-shrink: 0;
}

.po-total {
    color: #172033;

    font-size: 16px;
    font-weight: 900;

    white-space: nowrap;
}

.po-status-badge {
    display: inline-flex;

    align-items: center;

    min-height: 25px;

    padding: 0 9px;

    border: 1px solid;

    border-radius: 999px;

    font-size: 9px;
    font-weight: 900;

    letter-spacing: .025em;

    text-transform: uppercase;

    white-space: nowrap;
}

.po-more {
    display: inline-flex;

    align-items: center;
    justify-content: center;

    width: 29px;
    height: 29px;

    border: 0;

    border-radius: 7px;

    background: transparent;

    color: #98a2b3;

    font-size: 18px;

    cursor: pointer;
}

.po-more:hover {
    background: #f1f5f9;

    color: #374151;
}


/* ========================================================================
   DROPDOWN
======================================================================== */

.po-menu-wrapper {
    position: relative;
}

.po-menu {
    position: absolute;

    right: 0;
    top: calc(100% + 5px);

    z-index: 100;

    width: 175px;

    padding: 5px;

    border: 1px solid #e2e8f0;

    border-radius: 9px;

    background: #ffffff;

    box-shadow:
        0 10px 28px rgba(15, 23, 42, .13);

    display: none;
}

.po-menu.show {
    display: block;
}

.po-menu a,
.po-menu button {
    display: flex;

    width: 100%;

    align-items: center;

    gap: 8px;

    padding: 8px 9px;

    border: 0;

    border-radius: 6px;

    background: transparent;

    color: #475467;

    font-size: 10px;
    font-weight: 700;

    text-align: left;

    text-decoration: none;

    cursor: pointer;
}

.po-menu a:hover,
.po-menu button:hover {
    background: #f5f7fa;

    color: #111827;
}


/* ========================================================================
   META
======================================================================== */

.po-meta {
    display: flex;

    flex-wrap: wrap;

    gap: 6px 18px;

    margin-top: 14px;
}

.po-meta-item {
    display: inline-flex;

    align-items: center;

    gap: 6px;

    color: #7b8494;

    font-size: 10px;
}

.po-meta-label {
    color: #a1a9b7;
}

.po-meta-value {
    color: #596579;

    font-weight: 700;
}


/* ========================================================================
   WORKFLOW
======================================================================== */

.po-workflow {
    display: flex;

    align-items: flex-start;

    margin-top: 19px;

    padding: 0 4px;
}

.po-stage {
    position: relative;

    flex: 1;
}

.po-stage:not(:last-child)::after {
    content: '';

    position: absolute;

    top: 5px;

    left: calc(50% + 7px);
    right: calc(-50% + 7px);

    height: 2px;

    background: #e6eaf0;
}

.po-stage.is-complete:not(:last-child)::after {
    background: #86efac;
}

.po-stage-inner {
    position: relative;

    z-index: 2;

    display: flex;

    flex-direction: column;

    align-items: center;

    gap: 6px;
}

.po-stage-dot {
    width: 11px;
    height: 11px;

    border: 2px solid #d6dce4;

    border-radius: 50%;

    background: #ffffff;
}

.po-stage.is-complete .po-stage-dot {
    border-color: #16a34a;

    background: #16a34a;

    box-shadow:
        0 0 0 3px #dcfce7;
}

.po-stage.is-current .po-stage-dot {
    width: 13px;
    height: 13px;

    margin-top: -1px;

    border-color: var(--status-color);

    box-shadow:
        0 0 0 3px var(--status-soft);
}

.po-stage-label {
    color: #a0a8b5;

    font-size: 9px;
    font-weight: 700;
}

.po-stage.is-complete .po-stage-label {
    color: #15803d;
}

.po-stage.is-current .po-stage-label {
    color: var(--status-color);
}


/* ========================================================================
   RECEIVING
======================================================================== */

.po-receiving {
    margin-top: 14px;

    padding: 10px 11px;

    border: 1px solid var(--status-border);

    border-radius: 8px;

    background: var(--status-bg);
}

.po-receiving-top {
    display: flex;

    justify-content: space-between;

    gap: 10px;

    margin-bottom: 7px;
}

.po-receiving-title {
    color: #64748b;

    font-size: 9px;
    font-weight: 800;

    text-transform: uppercase;

    letter-spacing: .05em;
}

.po-receiving-value {
    color: var(--status-color);

    font-size: 9px;
    font-weight: 900;
}

.po-progress {
    height: 6px;

    overflow: hidden;

    border-radius: 999px;

    background: rgba(148, 163, 184, .18);
}

.po-progress-bar {
    height: 100%;

    border-radius: inherit;

    background:
        linear-gradient(
            90deg,
            var(--status-color),
            var(--status-color)
        );

    transition: width .4s ease;
}


/* ========================================================================
   CARD FOOTER
======================================================================== */

.po-card-footer {
    display: flex;

    align-items: center;

    justify-content: space-between;

    gap: 15px;

    padding: 11px 19px 11px 22px;

    border-top: 1px solid rgba(226, 232, 240, .75);

    background: rgba(248, 250, 252, .65);
}

.po-card-status-text {
    display: flex;

    flex-direction: column;

    gap: 3px;
}

.po-card-status-title {
    color: #4b5563;

    font-size: 10px;
    font-weight: 800;
}

.po-card-status-subtitle {
    color: #9aa3b1;

    font-size: 9px;
}

.po-card-actions {
    display: flex;

    align-items: center;

    gap: 6px;
}

.po-secondary-btn,
.po-receive-btn {
    display: inline-flex;

    align-items: center;
    justify-content: center;

    min-height: 31px;

    padding: 0 11px;

    border-radius: 7px;

    font-size: 10px;
    font-weight: 800;

    text-decoration: none;

    transition: .16s ease;
}

.po-secondary-btn {
    border: 1px solid #dfe4ea;

    background: #ffffff;

    color: #475467;
}

.po-secondary-btn:hover {
    border-color: #cbd5e1;

    background: #f8fafc;

    color: #111827;
}

.po-receive-btn {
    border: 1px solid #86efac;

    background: #f0fdf4;

    color: #15803d;
}

.po-receive-btn:hover {
    background: #dcfce7;

    border-color: #4ade80;

    color: #166534;
}


/* ========================================================================
   EMPTY
======================================================================== */

.po-empty {
    padding: 60px 20px;

    border: 1px dashed #d7dee7;

    border-radius: 13px;

    background: #ffffff;

    text-align: center;
}

.po-empty-icon {
    display: inline-flex;

    align-items: center;
    justify-content: center;

    width: 48px;
    height: 48px;

    margin-bottom: 13px;

    border-radius: 12px;

    background: #f0fdf4;

    color: #16a34a;
}

.po-empty h3 {
    margin: 0 0 5px;

    color: #334155;

    font-size: 15px;
}

.po-empty p {
    max-width: 430px;

    margin: auto;

    color: #94a3b8;

    font-size: 11px;

    line-height: 1.6;
}


/* ========================================================================
   PAGINATION
======================================================================== */

.po-pagination {
    margin-top: 16px;
}


/* ========================================================================
   LIST VIEW
======================================================================== */

.po-list-mode .po-card {
    border-radius: 8px;
}

.po-list-mode .po-workflow {
    display: none;
}

.po-list-mode .po-receiving {
    margin-top: 10px;
}


/* ========================================================================
   RESPONSIVE
======================================================================== */

@media (max-width: 950px) {

    .po-summary {
        grid-template-columns:
            repeat(2, minmax(0, 1fr));
    }

    .po-card-header {
        flex-direction: column;
    }

    .po-card-right {
        width: 100%;

        justify-content: space-between;
    }

}

@media (max-width: 650px) {

    .po-header {
        align-items: stretch;

        flex-direction: column;
    }

    .po-primary-btn {
        width: 100%;
    }

    .po-summary {
        grid-template-columns: 1fr;
    }

    .po-toolbar {
        align-items: stretch;

        flex-direction: column;
    }

    .po-view-actions {
        justify-content: flex-end;
    }

    .po-filter-bottom {
        flex-wrap: wrap;
    }

    .po-select {
        flex: 1;

        min-width: 0;
    }

    .po-card-main {
        padding: 15px 15px 13px 19px;
    }

    .po-card-footer {
        align-items: stretch;

        flex-direction: column;

        padding-left: 19px;
    }

    .po-card-actions {
        width: 100%;
    }

    .po-secondary-btn,
    .po-receive-btn {
        flex: 1;
    }

    .po-list-wrapper {
        max-height: 600px;
    }

}

</style>


<div class="po-page">

    {{-- ====================================================================
         HEADER
    ===================================================================== --}}

    <div class="po-header">

        <div class="po-header-copy">

            <h1>Purchase Orders</h1>

            <p>
                Manage supplier orders, approvals, deliveries, and receiving.
            </p>

        </div>

        @if (
            auth()->user()->hasRole('admin') ||
            auth()->user()->hasRole('manager') ||
            auth()->user()->hasRole('staff')
        )

            <a
                href="{{ route('purchase-orders.create') }}"
                class="po-primary-btn"
            >

                <span style="font-size:18px;">
                    +
                </span>

                New Purchase Order

            </a>

        @endif

    </div>


    {{-- ====================================================================
         SUMMARY
    ===================================================================== --}}

    @php

        $summaryCounts = [
            'pending' => \App\Models\PurchaseOrder::whereIn(
                'status',
                ['pending_approval']
            )->count(),

            'ordered' => \App\Models\PurchaseOrder::where(
                'status',
                'ordered'
            )->count(),

            'receiving' => \App\Models\PurchaseOrder::whereIn(
                'status',
                ['partially_received']
            )->count(),

            'completed' => \App\Models\PurchaseOrder::where(
                'status',
                'completed'
            )->count(),
        ];

    @endphp


    <div class="po-summary">

        <div
            class="po-summary-card"
            style="
                --summary-color:#d97706;
                --summary-bg:#fffbeb;
            "
        >

            <div class="po-summary-top">

                <span class="po-summary-label">
                    Pending
                </span>

                <span class="po-summary-icon">
                    !
                </span>

            </div>

            <div class="po-summary-value">
                {{ $summaryCounts['pending'] }}
            </div>

        </div>


        <div
            class="po-summary-card"
            style="
                --summary-color:#7c3aed;
                --summary-bg:#f5f3ff;
            "
        >

            <div class="po-summary-top">

                <span class="po-summary-label">
                    Ordered
                </span>

                <span class="po-summary-icon">
                    ↑
                </span>

            </div>

            <div class="po-summary-value">
                {{ $summaryCounts['ordered'] }}
            </div>

        </div>


        <div
            class="po-summary-card"
            style="
                --summary-color:#ea580c;
                --summary-bg:#fff7ed;
            "
        >

            <div class="po-summary-top">

                <span class="po-summary-label">
                    Receiving
                </span>

                <span class="po-summary-icon">
                    ↓
                </span>

            </div>

            <div class="po-summary-value">
                {{ $summaryCounts['receiving'] }}
            </div>

        </div>


        <div
            class="po-summary-card"
            style="
                --summary-color:#16a34a;
                --summary-bg:#f0fdf4;
            "
        >

            <div class="po-summary-top">

                <span class="po-summary-label">
                    Completed
                </span>

                <span class="po-summary-icon">
                    ✓
                </span>

            </div>

            <div class="po-summary-value">
                {{ $summaryCounts['completed'] }}
            </div>

        </div>

    </div>


    {{-- ====================================================================
         ERRORS
    ===================================================================== --}}

    @if ($errors->any())

        <div
            style="
                margin-bottom:18px;
                padding:13px 15px;
                border:1px solid #fecaca;
                border-radius:10px;
                background:#fff7f7;
                color:#991b1b;
                font-size:12px;
            "
        >

            <strong>
                Please fix the following:
            </strong>

            <ul style="margin:7px 0 0;padding-left:18px;">

                @foreach ($errors->all() as $error)

                    <li>
                        {{ $error }}
                    </li>

                @endforeach

            </ul>

        </div>

    @endif


    {{-- ====================================================================
         FILTER
    ===================================================================== --}}

    <div class="po-filter-panel">

        <div class="po-filter-heading">

            <span class="po-filter-heading-title">
                Purchase Status
            </span>

            <span class="po-filter-count">

                {{ $purchaseOrders->total() }}

                {{ Str::plural('order', $purchaseOrders->total()) }}

            </span>

        </div>


        <div class="po-status-scroll">

            @foreach ($statusOptions as $value => $label)

                @php

                    $filterStyle =
                        $value && isset($statusStyles[$value])
                            ? $statusStyles[$value]
                            : null;

                @endphp

                <a
                    href="{{ route('purchase-orders.index', array_filter([
                        'search' => $search,
                        'status' => $value,
                    ], fn ($item) => $item !== null && $item !== '')) }}"
                    class="po-status-btn {{ $status === $value ? 'is-active' : '' }}"
                >

                    <span
                        class="po-status-dot"
                        @if ($filterStyle)
                            style="color: {{ $filterStyle['color'] }};"
                        @endif
                    ></span>

                    {{ $label }}

                </a>

            @endforeach

        </div>


        <form
            action="{{ route('purchase-orders.index') }}"
            method="GET"
            class="po-filter-bottom"
        >

            <select
                name="status"
                class="po-select"
                onchange="this.form.submit()"
            >

                <option value="">
                    Filter by status
                </option>

                @foreach ($statusOptions as $value => $label)

                    @if ($value !== '')

                        <option
                            value="{{ $value }}"
                            @selected($status === $value)
                        >
                            {{ $label }}
                        </option>

                    @endif

                @endforeach

            </select>


            @if ($search)

                <input
                    type="hidden"
                    name="search"
                    value="{{ $search }}"
                >

            @endif


            @if ($search !== '' || $status)

                <a
                    href="{{ route('purchase-orders.index') }}"
                    class="po-reset"
                >
                    Clear filters
                </a>

            @endif

        </form>

    </div>


    {{-- ====================================================================
         SEARCH
    ===================================================================== --}}

    <form
        action="{{ route('purchase-orders.index') }}"
        method="GET"
        class="po-toolbar"
        id="poSearchForm"
    >

        @if ($status)

            <input
                type="hidden"
                name="status"
                value="{{ $status }}"
            >

        @endif


        <div class="po-search">

            <svg
                class="po-search-icon"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
            >

                <circle
                    cx="11"
                    cy="11"
                    r="7"
                />

                <path
                    d="m20 20-3.5-3.5"
                />

            </svg>


            <input
                id="poSearchInput"
                type="text"
                name="search"
                value="{{ $search }}"
                placeholder="Search PO number, supplier, reference..."
                autocomplete="off"
            >

        </div>


        <button
            type="submit"
            class="po-search-btn"
        >
            Search
        </button>


        <div class="po-view-actions">

            <button
                type="button"
                id="listViewBtn"
                class="po-icon-btn"
                title="List view"
            >
                ☷
            </button>

            <button
                type="button"
                id="cardViewBtn"
                class="po-icon-btn active"
                title="Card view"
            >
                ▦
            </button>

        </div>

    </form>


    {{-- ====================================================================
         RESULTS BAR
    ===================================================================== --}}

    <div class="po-results-bar">

        <div class="po-results-title">

            @if ($purchaseOrders->total())

                Purchase Orders

            @else

                No Purchase Orders

            @endif

        </div>


        @if ($purchaseOrders->total())

            <div class="po-results-meta">

                Showing

                {{ $purchaseOrders->firstItem() }}

                –

                {{ $purchaseOrders->lastItem() }}

                of

                {{ $purchaseOrders->total() }}

            </div>

        @endif

    </div>


    {{-- ====================================================================
         ORDERS
    ===================================================================== --}}

    @if ($purchaseOrders->count())

        <div
            class="po-list-wrapper"
            id="poListWrapper"
        >

            <div
                class="po-list"
                id="poList"
            >

                @foreach ($purchaseOrders as $purchaseOrder)

                    @php

                        $currentStatus =
                            $purchaseOrder->status;

                        $statusStyle =
                            $statusStyles[$currentStatus]
                            ?? [
                                'label' => ucwords(
                                    str_replace(
                                        '_',
                                        ' ',
                                        $currentStatus
                                    )
                                ),
                                'color' => '#64748b',
                                'background' => '#f8fafc',
                                'border' => '#cbd5e1',
                                'soft' => '#f1f5f9',
                                'gradient' => 'linear-gradient(135deg,#f8fafc,#ffffff)',
                            ];


                        $stage =
                            $workflowStage(
                                $currentStatus
                            );


                        $totalOrdered =
                            (float) $purchaseOrder
                                ->items
                                ->sum(
                                    'quantity_ordered'
                                );


                        $totalReceived =
                            (float) $purchaseOrder
                                ->items
                                ->sum(
                                    'quantity_received'
                                );


                        $receivingPercentage =
                            $totalOrdered > 0
                                ? min(
                                    100,
                                    max(
                                        0,
                                        (
                                            $totalReceived
                                            /
                                            $totalOrdered
                                        ) * 100
                                    )
                                )
                                : 0;


                        $isReceiving =
                            in_array(
                                $currentStatus,
                                [
                                    'ordered',
                                    'partially_received',
                                    'received',
                                    'completed',
                                ],
                                true
                            );


                        $isCompleted =
                            $currentStatus === 'completed';


                        $statusMessage =
                            match ($currentStatus) {

                                'draft' =>
                                    'Order is still being prepared.',

                                'pending_approval' =>
                                    'Waiting for approval.',

                                'approved' =>
                                    'Approved and ready to be ordered.',

                                'rejected' =>
                                    'Purchase order was rejected.',

                                'ordered' =>
                                    'Order placed with supplier.',

                                'partially_received' =>
                                    'Order is currently being received.',

                                'received' =>
                                    'All ordered goods have been received.',

                                'completed' =>
                                    'Purchase order completed successfully.',

                                'cancelled' =>
                                    'Purchase order has been cancelled.',

                                default =>
                                    'Purchase order status updated.',
                            };


                        $showWorkflow =
                            !in_array(
                                $currentStatus,
                                [
                                    'rejected',
                                    'cancelled',
                                ],
                                true
                            );

                    @endphp


                    <article
                        class="
                            po-card
                            {{ $isCompleted ? 'is-completed' : '' }}
                        "
                        style="
                            --status-color: {{ $statusStyle['color'] }};
                            --status-bg: {{ $statusStyle['background'] }};
                            --status-border: {{ $statusStyle['border'] }};
                            --status-soft: {{ $statusStyle['soft'] }};
                        "
                    >

                        <div
                            class="po-card-status-accent"
                        ></div>


                        <div class="po-card-main">

                            {{-- HEADER --}}

                            <div class="po-card-header">

                                <div class="po-card-heading">

                                    <div class="po-number-row">

                                        @if ($isCompleted)

                                            <span
                                                class="po-completed-check"
                                                title="Completed"
                                            >
                                                ✓
                                            </span>

                                        @endif


                                        <span class="po-number">

                                            {{ $purchaseOrder->po_number }}

                                        </span>


                                        @if ($purchaseOrder->reference)

                                            <span class="po-reference">

                                                Ref:
                                                {{ $purchaseOrder->reference }}

                                            </span>

                                        @endif

                                    </div>


                                    <div class="po-route">

                                        <strong>

                                            {{ $purchaseOrder->supplier->name ?? 'Unknown Supplier' }}

                                        </strong>

                                        <span class="po-route-arrow">
                                            →
                                        </span>

                                        <strong>

                                            {{ $purchaseOrder->location->name ?? 'Unknown Location' }}

                                        </strong>

                                    </div>

                                </div>


                                <div class="po-card-right">

                                    <div class="po-total">

                                        {{ number_format((float) $purchaseOrder->total, 2) }}

                                    </div>


                                    <span
                                        class="po-status-badge"
                                        style="
                                            color: {{ $statusStyle['color'] }};
                                            background: {{ $statusStyle['background'] }};
                                            border-color: {{ $statusStyle['border'] }};
                                        "
                                    >

                                        {{ $statusStyle['label'] }}

                                    </span>


                                    <div class="po-menu-wrapper">

                                        <button
                                            type="button"
                                            class="po-more"
                                            onclick="togglePOMenu(this)"
                                            aria-label="More actions"
                                        >
                                            ⋯
                                        </button>


                                        <div class="po-menu">

                                            <a
                                                href="{{ route('purchase-orders.show', $purchaseOrder) }}"
                                            >
                                                👁
                                                View Purchase Order
                                            </a>


                                            @if (
                                                in_array(
                                                    $currentStatus,
                                                    [
                                                        'ordered',
                                                        'partially_received',
                                                    ],
                                                    true
                                                )
                                            )

                                                <a
                                                    href="{{ route('purchase-orders.receive.form', $purchaseOrder) }}"
                                                >
                                                    📦
                                                    Receive Stock
                                                </a>

                                            @endif


                                            <button
                                                type="button"
                                                onclick="copyPONumber('{{ $purchaseOrder->po_number }}')"
                                            >
                                                ⧉
                                                Copy PO Number
                                            </button>

                                        </div>

                                    </div>

                                </div>

                            </div>


                            {{-- META --}}

                            <div class="po-meta">

                                <span class="po-meta-item">

                                    <span class="po-meta-label">
                                        Items
                                    </span>

                                    <span class="po-meta-value">
                                        {{ $purchaseOrder->items->count() }}
                                    </span>

                                </span>


                                <span class="po-meta-item">

                                    <span class="po-meta-label">
                                        Quantity
                                    </span>

                                    <span class="po-meta-value">

                                        {{ number_format($totalOrdered, 2, '.', '') }}

                                    </span>

                                </span>


                                <span class="po-meta-item">

                                    <span class="po-meta-label">
                                        Expected
                                    </span>

                                    <span class="po-meta-value">

                                        @if (
                                            $purchaseOrder->expected_delivery_date
                                        )

                                            {{ $purchaseOrder->expected_delivery_date->format('d M Y') }}

                                        @else

                                            Not specified

                                        @endif

                                    </span>

                                </span>


                                @if ($purchaseOrder->createdBy)

                                    <span class="po-meta-item">

                                        <span class="po-meta-label">
                                            Created by
                                        </span>

                                        <span class="po-meta-value">

                                            {{ $purchaseOrder->createdBy->name }}

                                        </span>

                                    </span>

                                @endif

                            </div>


                            {{-- WORKFLOW --}}

                            @if (
                                $showWorkflow &&
                                $stage !== null
                            )

                                <div class="po-workflow">

                                    @foreach (
                                        [
                                            'draft',
                                            'pending_approval',
                                            'approved',
                                            'ordered',
                                            'received'
                                        ]
                                        as $index => $workflowStatus
                                    )

                                        @php

                                            $isComplete =
                                                $index < $stage;

                                            $isCurrent =
                                                $index === $stage;


                                            if (
                                                $currentStatus === 'partially_received'
                                                &&
                                                $workflowStatus === 'received'
                                            ) {

                                                $isComplete = false;

                                                $isCurrent = true;

                                            }


                                            if (
                                                in_array(
                                                    $currentStatus,
                                                    [
                                                        'received',
                                                        'completed',
                                                    ],
                                                    true
                                                )
                                                &&
                                                $workflowStatus === 'received'
                                            ) {

                                                $isComplete = true;

                                                $isCurrent = false;

                                            }

                                        @endphp


                                        <div
                                            class="
                                                po-stage
                                                {{ $isComplete ? 'is-complete' : '' }}
                                                {{ $isCurrent ? 'is-current' : '' }}
                                            "
                                        >

                                            <div class="po-stage-inner">

                                                <span class="po-stage-dot"></span>

                                                <span class="po-stage-label">

                                                    {{ $workflow[$workflowStatus] }}

                                                </span>

                                            </div>

                                        </div>

                                    @endforeach

                                </div>

                            @endif


                            {{-- RECEIVING --}}

                            @if ($isReceiving)

                                <div class="po-receiving">

                                    <div class="po-receiving-top">

                                        <span class="po-receiving-title">

                                            Receiving Progress

                                        </span>


                                        <span class="po-receiving-value">

                                            {{ number_format($receivingPercentage, 0) }}%

                                            ·

                                            {{ number_format($totalReceived, 2) }}

                                            /

                                            {{ number_format($totalOrdered, 2) }}

                                        </span>

                                    </div>


                                    <div class="po-progress">

                                        <div
                                            class="po-progress-bar"
                                            style="
                                                width: {{ $receivingPercentage }}%;
                                            "
                                        ></div>

                                    </div>

                                </div>

                            @endif

                        </div>


                        {{-- FOOTER --}}

                        <div class="po-card-footer">

                            <div class="po-card-status-text">

                                <span class="po-card-status-title">

                                    @if ($isCompleted)

                                        ✓
                                        Completed successfully

                                    @else

                                        {{ $statusMessage }}

                                    @endif

                                </span>


                                @if (
                                    $purchaseOrder->expected_delivery_date
                                )

                                    <span class="po-card-status-subtitle">

                                        Expected delivery:

                                        {{ $purchaseOrder->expected_delivery_date->format('d M Y') }}

                                    </span>

                                @endif

                            </div>


                            <div class="po-card-actions">

                                <a
                                    href="{{ route('purchase-orders.show', $purchaseOrder) }}"
                                    class="po-secondary-btn"
                                >
                                    View PO
                                </a>


                                @if (
                                    in_array(
                                        $currentStatus,
                                        [
                                            'ordered',
                                            'partially_received',
                                        ],
                                        true
                                    )
                                )

                                    <a
                                        href="{{ route('purchase-orders.receive.form', $purchaseOrder) }}"
                                        class="po-receive-btn"
                                    >
                                        📦 Receive Stock
                                    </a>

                                @endif

                            </div>

                        </div>

                    </article>

                @endforeach

            </div>

        </div>

    @else

        {{-- EMPTY --}}

        <div class="po-empty">

            <div class="po-empty-icon">

                <svg
                    width="22"
                    height="22"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8"
                >

                    <path d="M6 3h12v18H6z"></path>

                    <path d="M9 7h6"></path>

                    <path d="M9 11h6"></path>

                    <path d="M9 15h4"></path>

                </svg>

            </div>


            @if ($search !== '' || $status)

                <h3>
                    No purchase orders found
                </h3>

                <p>
                    No purchase orders match your current search or filter.
                    Try clearing the filters.
                </p>


                <div style="margin-top:17px;">

                    <a
                        href="{{ route('purchase-orders.index') }}"
                        class="po-secondary-btn"
                    >
                        Clear Filters
                    </a>

                </div>

            @else

                <h3>
                    No purchase orders yet
                </h3>

                <p>
                    Create your first purchase order to begin managing
                    supplier orders and inventory receiving.
                </p>


                @if (
                    auth()->user()->hasRole('admin') ||
                    auth()->user()->hasRole('manager') ||
                    auth()->user()->hasRole('staff')
                )

                    <div style="margin-top:17px;">

                        <a
                            href="{{ route('purchase-orders.create') }}"
                            class="po-primary-btn"
                        >
                            +
                            Create Purchase Order
                        </a>

                    </div>

                @endif

            @endif

        </div>

    @endif


    {{-- PAGINATION --}}

    @if ($purchaseOrders->hasPages())

        <div class="po-pagination">

            {{ $purchaseOrders->links() }}

        </div>

    @endif

</div>


<script>

/* =========================================================================
   PO DROPDOWN
========================================================================= */

function togglePOMenu(button) {

    const menu =
        button
            .parentElement
            .querySelector('.po-menu');

    document
        .querySelectorAll('.po-menu.show')
        .forEach(function (openMenu) {

            if (openMenu !== menu) {

                openMenu.classList.remove('show');

            }

        });

    menu.classList.toggle('show');
}


/* =========================================================================
   CLOSE DROPDOWNS
========================================================================= */

document.addEventListener('click', function (event) {

    if (!event.target.closest('.po-menu-wrapper')) {

        document
            .querySelectorAll('.po-menu.show')
            .forEach(function (menu) {

                menu.classList.remove('show');

            });

    }

});


/* =========================================================================
   COPY PO NUMBER
========================================================================= */

function copyPONumber(poNumber) {

    if (
        navigator.clipboard &&
        window.isSecureContext
    ) {

        navigator.clipboard
            .writeText(poNumber)
            .then(function () {

                showPOToast(
                    'PO number copied: ' + poNumber
                );

            });

        return;

    }


    const textarea =
        document.createElement('textarea');

    textarea.value = poNumber;

    textarea.style.position = 'fixed';

    textarea.style.opacity = '0';

    document.body.appendChild(textarea);

    textarea.select();

    document.execCommand('copy');

    textarea.remove();

    showPOToast(
        'PO number copied: ' + poNumber
    );
}


/* =========================================================================
   TOAST
========================================================================= */

function showPOToast(message) {

    let toast =
        document.getElementById('poToast');

    if (!toast) {

        toast =
            document.createElement('div');

        toast.id = 'poToast';

        toast.style.position = 'fixed';

        toast.style.right = '22px';

        toast.style.bottom = '22px';

        toast.style.zIndex = '9999';

        toast.style.padding = '11px 15px';

        toast.style.borderRadius = '9px';

        toast.style.background = '#172033';

        toast.style.color = '#ffffff';

        toast.style.fontSize = '11px';

        toast.style.fontWeight = '700';

        toast.style.boxShadow =
            '0 8px 25px rgba(15,23,42,.18)';

        toast.style.opacity = '0';

        toast.style.transform =
            'translateY(8px)';

        toast.style.transition =
            'all .2s ease';

        document.body.appendChild(toast);

    }

    toast.textContent = message;

    requestAnimationFrame(function () {

        toast.style.opacity = '1';

        toast.style.transform =
            'translateY(0)';

    });


    clearTimeout(
        window.poToastTimer
    );


    window.poToastTimer =
        setTimeout(function () {

            toast.style.opacity = '0';

            toast.style.transform =
                'translateY(8px)';

        }, 2200);

}


/* =========================================================================
   LIST / CARD VIEW
========================================================================= */

const listViewBtn =
    document.getElementById('listViewBtn');

const cardViewBtn =
    document.getElementById('cardViewBtn');

const poList =
    document.getElementById('poList');


if (
    listViewBtn &&
    cardViewBtn &&
    poList
) {

    listViewBtn.addEventListener(
        'click',
        function () {

            poList.classList.add(
                'po-list-mode'
            );

            listViewBtn.classList.add(
                'active'
            );

            cardViewBtn.classList.remove(
                'active'
            );

            localStorage.setItem(
                'po-view',
                'list'
            );

        }
    );


    cardViewBtn.addEventListener(
        'click',
        function () {

            poList.classList.remove(
                'po-list-mode'
            );

            cardViewBtn.classList.add(
                'active'
            );

            listViewBtn.classList.remove(
                'active'
            );

            localStorage.setItem(
                'po-view',
                'card'
            );

        }
    );


    const savedView =
        localStorage.getItem(
            'po-view'
        );


    if (savedView === 'list') {

        listViewBtn.click();

    }

}


/* =========================================================================
   SEARCH ENTER
========================================================================= */

const searchInput =
    document.getElementById(
        'poSearchInput'
    );


if (searchInput) {

    searchInput.addEventListener(
        'keydown',
        function (event) {

            if (
                event.key === 'Enter'
            ) {

                document
                    .getElementById(
                        'poSearchForm'
                    )
                    .submit();

            }

        }
    );

}

</script>

@endsection
