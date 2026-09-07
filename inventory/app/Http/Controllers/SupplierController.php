<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    /**
     * List suppliers with search.
     */
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search'));

        $suppliers = Supplier::with('company')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', '%' . $search . '%')
                        ->orWhere('contact_name', 'like', '%' . $search . '%')
                        ->orWhere('email', 'like', '%' . $search . '%')
                        ->orWhere('phone', 'like', '%' . $search . '%');
                });
            })
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view(
            'suppliers.index',
            compact('suppliers', 'search')
        );
    }

    /**
     * Show the supplier creation form.
     */
    public function create()
    {
        $companies = Company::orderBy('name')->get();

        return view(
            'suppliers.create',
            compact('companies')
        );
    }

    /**
     * Create a new supplier.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_id' => ['required', 'integer', 'exists:companies,id'],
            'name' => ['required', 'string', 'max:200'],
            'contact_name' => ['nullable', 'string', 'max:200'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:200'],
            'address' => ['nullable', 'string', 'max:500'],
        ]);

        $validated['is_active'] = true;

        $supplier = Supplier::create($validated);

        return redirect()
            ->route('suppliers.index')
            ->with(
                'success',
                'Supplier "' . $supplier->name . '" created successfully.'
            );
    }

    /**
     * Show a single supplier, including purchase order history.
     */
    public function show(Supplier $supplier)
    {
        $supplier->load('company');

        $purchaseOrders = $supplier->purchaseOrders()
            ->with('location')
            ->latest()
            ->paginate(10);

        return view(
            'suppliers.show',
            compact('supplier', 'purchaseOrders')
        );
    }

    /**
     * Show the supplier edit form.
     */
    public function edit(Supplier $supplier)
    {
        $companies = Company::orderBy('name')->get();

        return view(
            'suppliers.edit',
            compact('supplier', 'companies')
        );
    }

    /**
     * Update an existing supplier.
     */
    public function update(Request $request, Supplier $supplier)
    {
        $validated = $request->validate([
            'company_id' => ['required', 'integer', 'exists:companies,id'],
            'name' => ['required', 'string', 'max:200'],
            'contact_name' => ['nullable', 'string', 'max:200'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:200'],
            'address' => ['nullable', 'string', 'max:500'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        $supplier->update($validated);

        return redirect()
            ->route('suppliers.index')
            ->with(
                'success',
                'Supplier "' . $supplier->name . '" updated successfully.'
            );
    }

    /**
     * Deactivate a supplier without deleting the record.
     */
    public function deactivate(Supplier $supplier)
    {
        if (! $supplier->is_active) {
            return redirect()
                ->route('suppliers.index')
                ->with(
                    'error',
                    'This supplier is already inactive.'
                );
        }

        $supplier->update([
            'is_active' => false,
        ]);

        return redirect()
            ->route('suppliers.index')
            ->with(
                'success',
                'Supplier "' . $supplier->name . '" deactivated.'
            );
    }

    /**
     * Activate an inactive supplier.
     */
    public function activate(Supplier $supplier)
    {
        if ($supplier->is_active) {
            return redirect()
                ->route('suppliers.index')
                ->with(
                    'error',
                    'This supplier is already active.'
                );
        }

        $supplier->update([
            'is_active' => true,
        ]);

        return redirect()
            ->route('suppliers.index')
            ->with(
                'success',
                'Supplier "' . $supplier->name . '" activated successfully.'
            );
    }
}
