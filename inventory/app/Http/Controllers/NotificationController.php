<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Mark a single notification as read, then send the user to the
     * relevant page (Stock Approvals for a stock movement request).
     */
    public function read(Request $request, string $notification): RedirectResponse
    {
        $record = $request->user()
            ->notifications()
            ->whereKey($notification)
            ->first();

        if ($record) {
            $record->markAsRead();
        }

        if ($record && isset($record->data['stock_movement_request_id'])) {
            return redirect()->route('stock-movement-requests.index');
        }

        return redirect()->route('dashboard');
    }

    /**
     * Mark every unread notification as read.
     */
    public function readAll(Request $request): RedirectResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        return back()->with('success', 'All notifications marked as read.');
    }
}
