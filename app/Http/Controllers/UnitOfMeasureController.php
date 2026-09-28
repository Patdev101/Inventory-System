<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductUnit;
use App\Models\UnitOfMeasure;
use Illuminate\Http\Request;

class UnitOfMeasureController extends Controller
{
    public function index()
    {
        $units = UnitOfMeasure::latest()->paginate(10);

        return view('units_of_measure.index', compact('units'));
    }

    /**
     * Show soft-deleted units of measure, admin-only, so they can be
     * restored.
     */
    public function trashed()
    {
        $units = UnitOfMeasure::onlyTrashed()
            ->latest('deleted_at')
            ->paginate(10);

        return view('units_of_measure.trashed', compact('units'));
    }

    /**
     * Restore a soft-deleted unit of measure.
     */
    public function restore(int $units_of_measure)
    {
        $unit = UnitOfMeasure::onlyTrashed()->findOrFail($units_of_measure);
        $unit->restore();

        return redirect()
            ->route('units-of-measure.trashed')
            ->with('success', 'Unit of measure "' . $unit->name . '" restored.');
    }

    public function create()
    {
        return view('units_of_measure.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:200'],
            'code' => ['required', 'string', 'max:40'],
            'description' => ['nullable', 'string', 'max:510'],
        ]);

        UnitOfMeasure::create($validated);

        return redirect()
            ->route('units-of-measure.index')
            ->with('success', 'Unit of measure created successfully.');
    }

    public function show(string $units_of_measure)
    {
        $unit = UnitOfMeasure::findOrFail($units_of_measure);

        return view('units_of_measure.show', compact('unit'));
    }

    public function edit(string $units_of_measure)
    {
        $unit = UnitOfMeasure::findOrFail($units_of_measure);

        return view('units_of_measure.edit', compact('unit'));
    }

    public function update(Request $request, string $units_of_measure)
    {
        $unit = UnitOfMeasure::findOrFail($units_of_measure);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:200'],
            'code' => ['required', 'string', 'max:40'],
            'description' => ['nullable', 'string', 'max:510'],
        ]);

        $unit->update($validated);

        return redirect()
            ->route('units-of-measure.index')
            ->with('success', 'Unit of measure updated successfully.');
    }

    public function destroy(string $units_of_measure)
    {
        $unit = UnitOfMeasure::findOrFail($units_of_measure);

        $isUsedAsProductUnit = ProductUnit::where('unit_of_measure_id', $unit->id)->exists();
        $isUsedAsBaseUnit = Product::where('base_unit_id', $unit->id)->exists();

        if ($isUsedAsProductUnit || $isUsedAsBaseUnit) {
            return redirect()
                ->route('units-of-measure.index')
                ->with(
                    'error',
                    'This unit of measure cannot be deleted because it is still used by one or more products.'
                );
        }

        $unit->delete();

        return redirect()
            ->route('units-of-measure.index')
            ->with('success', 'Unit of measure deleted. It can be restored by an admin if needed.');
    }
}