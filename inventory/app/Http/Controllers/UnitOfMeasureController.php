<?php

namespace App\Http\Controllers;

use App\Models\UnitOfMeasure;
use Illuminate\Http\Request;

class UnitOfMeasureController extends Controller
{
    public function index()
    {
        $units = UnitOfMeasure::latest()->paginate(10);

        return view('units_of_measure.index', compact('units'));
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

        $unit->delete();

        return redirect()
            ->route('units-of-measure.index')
            ->with('success', 'Unit of measure deleted successfully.');
    }
}