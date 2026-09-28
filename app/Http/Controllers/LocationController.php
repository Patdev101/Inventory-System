<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Inventory;
use App\Models\Location;
use App\Models\PurchaseOrder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LocationController extends Controller
{
    /**
     * Display location list.
     */
    public function index()
    {
        $locations = Location::with('company')
            ->latest()
            ->paginate(10);

        return view('locations.index', compact('locations'));
    }

    /**
     * Show soft-deleted locations, admin-only, so they can be restored.
     */
    public function trashed()
    {
        $locations = Location::onlyTrashed()
            ->with('company')
            ->latest('deleted_at')
            ->paginate(10);

        return view('locations.trashed', compact('locations'));
    }

    /**
     * Restore a soft-deleted location.
     */
    public function restore(int $location)
    {
        $location = Location::onlyTrashed()->findOrFail($location);
        $location->restore();

        return redirect()
            ->route('locations.trashed')
            ->with('success', 'Location "' . $location->name . '" restored.');
    }

    /**
     * Show create location form.
     */
    public function create()
    {
        $companies = Company::orderBy('name')->get();

        return view('locations.create', compact('companies'));
    }

    /**
     * Store a new location.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_id' => [
                'required',
                'integer',
                'exists:companies,id',
            ],

            'name' => [
                'required',
                'string',
                'max:300',
            ],

            'code' => [
                'required',
                'string',
                'max:100',
            ],

            'address' => [
                'nullable',
                'string',
                'max:510',
            ],
        ]);

        /*
         * Normalize location code.
         *
         * Example:
         *
         * ph   -> PH
         * Ph   -> PH
         *  ph  -> PH
         */
        $validated['code'] = strtoupper(
            trim($validated['code'])
        );

        /*
         * Check whether this company already
         * has the same location code.
         */
        $duplicate = Location::where(
            'company_id',
            $validated['company_id']
        )
            ->whereRaw(
                'UPPER(code) = ?',
                [$validated['code']]
            )
            ->exists();

        if ($duplicate) {
            return back()
                ->withInput()
                ->withErrors([
                    'code' =>
                        'This location code already exists for the selected company.',
                ]);
        }

        Location::create($validated);

        return redirect()
            ->route('locations.index')
            ->with(
                'success',
                'Location created successfully.'
            );
    }

    /**
     * Display one location.
     */
    public function show(Location $location)
    {
        $location->load('company');

        return view(
            'locations.show',
            compact('location')
        );
    }

    /**
     * Show edit location form.
     */
    public function edit(Location $location)
    {
        $companies = Company::orderBy('name')->get();

        return view(
            'locations.edit',
            compact(
                'location',
                'companies'
            )
        );
    }

    /**
     * Update an existing location.
     */
    public function update(
        Request $request,
        Location $location
    ) {
        $validated = $request->validate([
            'company_id' => [
                'required',
                'integer',
                'exists:companies,id',
            ],

            'name' => [
                'required',
                'string',
                'max:300',
            ],

            'code' => [
                'required',
                'string',
                'max:100',
            ],

            'address' => [
                'nullable',
                'string',
                'max:510',
            ],
        ]);

        /*
         * Normalize location code.
         */
        $validated['code'] = strtoupper(
            trim($validated['code'])
        );

        /*
         * Check for another location using
         * the same code within the selected company.
         *
         * The current location is excluded.
         */
        $duplicate = Location::where(
            'company_id',
            $validated['company_id']
        )
            ->whereRaw(
                'UPPER(code) = ?',
                [$validated['code']]
            )
            ->where(
                'id',
                '!=',
                $location->id
            )
            ->exists();

        if ($duplicate) {
            return back()
                ->withInput()
                ->withErrors([
                    'code' =>
                        'This location code already exists for the selected company.',
                ]);
        }

        $location->update($validated);

        return redirect()
            ->route('locations.index')
            ->with(
                'success',
                'Location updated successfully.'
            );
    }

    /**
     * Delete a location.
     */
    public function destroy(Location $location)
    {
        $hasInventory = Inventory::where('location_id', $location->id)->exists();
        $hasPurchaseOrders = PurchaseOrder::where('location_id', $location->id)->exists();

        if ($hasInventory || $hasPurchaseOrders) {
            return redirect()
                ->route('locations.index')
                ->with(
                    'error',
                    'This location cannot be deleted because it has inventory or purchase order history.'
                );
        }

        $location->delete();

        return redirect()
            ->route('locations.index')
            ->with(
                'success',
                'Location deleted. It can be restored by an admin if needed.'
            );
    }
}
