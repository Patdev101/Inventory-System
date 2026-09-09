<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Location;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    public function index()
    {
        $companies = Company::latest()->paginate(10);

        return view('companies.index', compact('companies'));
    }

    /**
     * Show soft-deleted companies, admin-only, so they can be restored.
     */
    public function trashed()
    {
        $companies = Company::onlyTrashed()
            ->latest('deleted_at')
            ->paginate(10);

        return view('companies.trashed', compact('companies'));
    }

    /**
     * Restore a soft-deleted company.
     */
    public function restore(int $company)
    {
        $company = Company::onlyTrashed()->findOrFail($company);
        $company->restore();

        return redirect()
            ->route('companies.trashed')
            ->with('success', 'Company "' . $company->name . '" restored.');
    }

    public function create()
    {
        return view('companies.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'code' => ['required', 'string', 'max:50', 'unique:companies,code'],
            'address' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:150'],
        ]);

        Company::create($validated);

        return redirect()
            ->route('companies.index')
            ->with('success', 'Company created successfully.');
    }

    public function show(Company $company)
    {
        $company->load('locations');

        return view('companies.show', compact('company'));
    }

    public function edit(Company $company)
    {
        return view('companies.edit', compact('company'));
    }

    public function update(Request $request, Company $company)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'code' => [
                'required',
                'string',
                'max:50',
                'unique:companies,code,' . $company->id,
            ],
            'address' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:150'],
        ]);

        $company->update($validated);

        return redirect()
            ->route('companies.index')
            ->with('success', 'Company updated successfully.');
    }

    public function destroy(Company $company)
    {
        $hasLocations = Location::where('company_id', $company->id)->exists();
        $hasProducts = Product::where('company_id', $company->id)->exists();
        $hasSuppliers = Supplier::where('company_id', $company->id)->exists();

        if ($hasLocations || $hasProducts || $hasSuppliers) {
            return redirect()
                ->route('companies.index')
                ->with(
                    'error',
                    'This company cannot be deleted because it still has locations, products, or suppliers.'
                );
        }

        $company->delete();

        return redirect()
            ->route('companies.index')
            ->with('success', 'Company deleted. It can be restored by an admin if needed.');
    }
}