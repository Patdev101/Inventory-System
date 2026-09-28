<?php

namespace App\Http\Controllers;

use App\Models\AccountAuditLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AccountAuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->only(['event', 'date_from', 'date_to']);

        $logs = AccountAuditLog::query()
            ->with('user:id,name,email')
            ->when($filters['event'] ?? null, function ($query, $event) {
                $query->where('event', 'like', '%' . $event . '%');
            })
            ->when($filters['date_from'] ?? null, function ($query, $from) {
                $query->whereDate('created_at', '>=', $from);
            })
            ->when($filters['date_to'] ?? null, function ($query, $to) {
                $query->whereDate('created_at', '<=', $to);
            })
            ->latest('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('account-audit-log.index', compact('logs', 'filters'));
    }
}
