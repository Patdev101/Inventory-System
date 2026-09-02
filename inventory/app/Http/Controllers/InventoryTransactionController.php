<?php

namespace App\Http\Controllers;

use App\Models\InventoryTransaction;
use App\Models\Product;
use App\Models\Location;
use Illuminate\Http\Request;

class InventoryTransactionController extends Controller
{
    /**
     * Display all inventory transactions.
     *
     * Transactions are permanent audit records.
     * They are intentionally not deletable.
     */
    public function index(Request $request)
    {
        $query = InventoryTransaction::with([
            'product',
            'location',
            'productUnit.unitOfMeasure',
            'inventory',
        ]);

        /*
         * Search
         *
         * Searches:
         * - product name
         * - product SKU
         * - location name
         * - location code
         * - transaction reference
         */
        if ($request->filled('search')) {
            $search = trim($request->input('search'));

            $query->where(function ($q) use ($search) {

                $q->where(
                    'reference',
                    'like',
                    "%{$search}%"
                )

                ->orWhereHas(
                    'product',
                    function ($productQuery) use ($search) {

                        $productQuery
                            ->where(
                                'name',
                                'like',
                                "%{$search}%"
                            )
                            ->orWhere(
                                'sku',
                                'like',
                                "%{$search}%"
                            );
                    }
                )

                ->orWhereHas(
                    'location',
                    function ($locationQuery) use ($search) {

                        $locationQuery
                            ->where(
                                'name',
                                'like',
                                "%{$search}%"
                            )
                            ->orWhere(
                                'code',
                                'like',
                                "%{$search}%"
                            );
                    }
                );
            });
        }

        /*
         * Product filter
         */
        if ($request->filled('product_id')) {
            $query->where(
                'product_id',
                $request->input('product_id')
            );
        }

        /*
         * Location filter
         */
        if ($request->filled('location_id')) {
            $query->where(
                'location_id',
                $request->input('location_id')
            );
        }

        /*
         * Transaction type filter
         */
        if (
            $request->filled('type')
            && in_array(
                $request->input('type'),
                ['in', 'out'],
                true
            )
        ) {
            $query->where(
                'type',
                $request->input('type')
            );
        }

        /*
         * Date range
         */
        if ($request->filled('date_from')) {
            $query->whereDate(
                'created_at',
                '>=',
                $request->input('date_from')
            );
        }

        if ($request->filled('date_to')) {
            $query->whereDate(
                'created_at',
                '<=',
                $request->input('date_to')
            );
        }

        /*
         * Newest transaction first.
         */
        $transactions = $query
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        /*
         * Filter dropdown data.
         */
        $products = Product::orderBy('name')->get();

        $locations = Location::orderBy('name')->get();

        return view(
            'inventory-transactions.index',
            compact(
                'transactions',
                'products',
                'locations'
            )
        );
    }

    /**
     * Display one transaction.
     *
     * This still works even if the inventory
     * has already been deleted.
     */
    public function show(
        InventoryTransaction $transaction
    ) {
        $transaction->load([
            'product',
            'location',
            'productUnit.unitOfMeasure',
            'inventory',
        ]);

        return view(
            'inventory-transactions.show',
            compact('transaction')
        );
    }
}
