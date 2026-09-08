<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrderEmail;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PurchaseOrderEmailController extends Controller
{
    /**
     * List every purchase order email ever sent, across all POs,
     * for auditing. Searchable by recipient, PO number, or sender.
     */
    public function index(Request $request): View
    {
        $search = trim((string) $request->input('search'));

        $emails = PurchaseOrderEmail::with([
            'purchaseOrder',
            'sentBy',
        ])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('to_email', 'like', '%' . $search . '%')
                        ->orWhere('subject', 'like', '%' . $search . '%')
                        ->orWhereHas('purchaseOrder', function ($query) use ($search) {
                            $query->where('po_number', 'like', '%' . $search . '%');
                        });
                });
            })
            ->latest('sent_at')
            ->paginate(20)
            ->withQueryString();

        return view(
            'purchase-order-emails.index',
            compact('emails', 'search')
        );
    }
}
