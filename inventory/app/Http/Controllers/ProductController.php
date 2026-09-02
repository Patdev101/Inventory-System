<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Inventory;
use App\Models\InventoryTransaction;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\UnitOfMeasure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with([
            'category',
            'company',
            'baseUnit',
            'productUnits.unitOfMeasure',
        ])
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view(
            'products.index',
            compact('products')
        );
    }

    public function create()
    {
        $categories = ProductCategory::orderBy('name')->get();

        $companies = Company::orderBy('name')->get();

        $units = UnitOfMeasure::orderBy('name')->get();

        return view(
            'products.create',
            compact(
                'categories',
                'companies',
                'units'
            )
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_category_id' => [
                'required',
                'integer',
                'exists:product_categories,id',
            ],

            'company_id' => [
                'required',
                'integer',
                'exists:companies,id',
            ],

            'name' => [
                'required',
                'string',
                'max:200',
            ],

            'sku' => [
                'nullable',
                'string',
                'max:100',
                'unique:products,sku',
            ],

            'description' => [
                'nullable',
                'string',
                'max:500',
            ],

            'base_unit_id' => [
                'required',
                'integer',
                'exists:units_of_measure,id',
            ],

            'reorder_point' => [
                'required',
                'numeric',
                'gte:0',
            ],

            'is_active' => [
                'nullable',
                'boolean',
            ],

            'units' => [
                'required',
                'array',
                'min:1',
            ],

            'units.*.unit_of_measure_id' => [
                'required',
                'integer',
                'distinct',
                'exists:units_of_measure,id',
            ],

            'units.*.conversion_factor' => [
                'required',
                'numeric',
                'gt:0',
            ],
        ]);

        $this->validateBaseUnit(
            $validated['units'],
            $validated['base_unit_id']
        );

        $this->validateBaseConversionFactor(
            $validated['units'],
            $validated['base_unit_id']
        );

        DB::transaction(function () use ($validated) {
            $product = Product::create([
                'product_category_id' =>
                    $validated['product_category_id'],

                'name' =>
                    $validated['name'],

                'sku' =>
                    $validated['sku'] ?? null,

                'description' =>
                    $validated['description'] ?? null,

                'base_unit_id' =>
                    $validated['base_unit_id'],

                'company_id' =>
                    $validated['company_id'],

                'reorder_point' =>
                    $validated['reorder_point'],

                'is_active' =>
                    $validated['is_active'] ?? true,
            ]);

            foreach ($validated['units'] as $unit) {
                $product->productUnits()->create([
                    'unit_of_measure_id' =>
                        $unit['unit_of_measure_id'],

                    'conversion_factor' =>
                        $unit['conversion_factor'],

                    'is_default' =>
                        (int) $unit['unit_of_measure_id']
                        === (int) $validated['base_unit_id'],
                ]);
            }
        });

        return redirect()
            ->route('products.index')
            ->with(
                'success',
                'Product created successfully.'
            );
    }

    public function show(Product $product)
    {
        $product->load([
            'category',
            'company',
            'baseUnit',
            'productUnits.unitOfMeasure',
        ]);

        return view(
            'products.show',
            compact('product')
        );
    }

    public function edit(Product $product)
    {
        $product->load([
            'category',
            'company',
            'baseUnit',
            'productUnits.unitOfMeasure',
        ]);

        $categories = ProductCategory::orderBy('name')->get();

        $companies = Company::orderBy('name')->get();

        $units = UnitOfMeasure::orderBy('name')->get();

        $hasInventoryHistory =
            Inventory::where(
                'product_id',
                $product->id
            )->exists()
            ||
            InventoryTransaction::where(
                'product_id',
                $product->id
            )->exists();

        return view(
            'products.edit',
            compact(
                'product',
                'categories',
                'companies',
                'units',
                'hasInventoryHistory'
            )
        );
    }

    public function update(
        Request $request,
        Product $product
    ) {
        $validated = $request->validate([
            'product_category_id' => [
                'required',
                'integer',
                'exists:product_categories,id',
            ],

            'company_id' => [
                'required',
                'integer',
                'exists:companies,id',
            ],

            'name' => [
                'required',
                'string',
                'max:200',
            ],

            'sku' => [
                'nullable',
                'string',
                'max:100',
                'unique:products,sku,' . $product->id,
            ],

            'description' => [
                'nullable',
                'string',
                'max:500',
            ],

            'base_unit_id' => [
                'required',
                'integer',
                'exists:units_of_measure,id',
            ],

            'reorder_point' => [
                'required',
                'numeric',
                'gte:0',
            ],

            'is_active' => [
                'nullable',
                'boolean',
            ],

            'units' => [
                'required',
                'array',
                'min:1',
            ],

            'units.*.unit_of_measure_id' => [
                'required',
                'integer',
                'distinct',
                'exists:units_of_measure,id',
            ],

            'units.*.conversion_factor' => [
                'required',
                'numeric',
                'gt:0',
            ],
        ]);

        $this->validateBaseUnit(
            $validated['units'],
            $validated['base_unit_id']
        );

        $this->validateBaseConversionFactor(
            $validated['units'],
            $validated['base_unit_id']
        );

        $hasInventoryHistory =
            Inventory::where(
                'product_id',
                $product->id
            )->exists()
            ||
            InventoryTransaction::where(
                'product_id',
                $product->id
            )->exists();

        if ($hasInventoryHistory) {
            $oldBaseUnitId =
                (int) $product->base_unit_id;

            $newBaseUnitId =
                (int) $validated['base_unit_id'];

            if ($oldBaseUnitId !== $newBaseUnitId) {
                throw ValidationException::withMessages([
                    'base_unit_id' =>
                        'The base unit cannot be changed because this product already has inventory or transaction history.',
                ]);
            }

            $existingProductUnits =
                $product->productUnits()
                    ->get()
                    ->keyBy('unit_of_measure_id');

            foreach ($validated['units'] as $unit) {
                $unitId =
                    (int) $unit['unit_of_measure_id'];

                $newConversion =
                    (float) $unit['conversion_factor'];

                if (!$existingProductUnits->has($unitId)) {
                    continue;
                }

                $existingProductUnit =
                    $existingProductUnits->get($unitId);

                $oldConversion =
                    (float) $existingProductUnit->conversion_factor;

                if (
                    abs(
                        $oldConversion -
                        $newConversion
                    ) > 0.0000001
                ) {
                    throw ValidationException::withMessages([
                        'units' =>
                            'A unit conversion factor cannot be changed because this product already has inventory or transaction history.',
                    ]);
                }
            }

            foreach (
                $existingProductUnits
                as $unitId => $existingUnit
            ) {
                $stillExists =
                    collect($validated['units'])
                        ->contains(function ($unit) use ($unitId) {
                            return
                                (int) $unit['unit_of_measure_id']
                                === (int) $unitId;
                        });

                if (!$stillExists) {
                    $usedInInventory =
                        Inventory::where(
                            'product_id',
                            $product->id
                        )
                            ->where(
                                'product_unit_id',
                                $existingUnit->id
                            )
                            ->exists();

                    $usedInTransactions =
                        InventoryTransaction::where(
                            'product_id',
                            $product->id
                        )
                            ->where(
                                'product_unit_id',
                                $existingUnit->id
                            )
                            ->exists();

                    if (
                        $usedInInventory ||
                        $usedInTransactions
                    ) {
                        throw ValidationException::withMessages([
                            'units' =>
                                'A unit cannot be removed because it is already used by inventory or transaction history.',
                        ]);
                    }
                }
            }
        }

        DB::transaction(function () use (
            $validated,
            $product,
            $hasInventoryHistory
        ) {
            $product->update([
                'product_category_id' =>
                    $validated['product_category_id'],

                'name' =>
                    $validated['name'],

                'sku' =>
                    $validated['sku'] ?? null,

                'description' =>
                    $validated['description'] ?? null,

                'base_unit_id' =>
                    $validated['base_unit_id'],

                'company_id' =>
                    $validated['company_id'],

                'reorder_point' =>
                    $validated['reorder_point'],

                'is_active' =>
                    $validated['is_active'] ?? false,
            ]);

            $existingUnits =
                $product->productUnits()
                    ->get()
                    ->keyBy('unit_of_measure_id');

            $submittedUnitIds = [];

            foreach ($validated['units'] as $unit) {
                $unitId =
                    (int) $unit['unit_of_measure_id'];

                $submittedUnitIds[] = $unitId;

                $conversionFactor =
                    (float) $unit['conversion_factor'];

                $isDefault =
                    $unitId ===
                    (int) $validated['base_unit_id'];

                if ($existingUnits->has($unitId)) {
                    $productUnit =
                        $existingUnits->get($unitId);

                    $productUnit->update([
                        'conversion_factor' =>
                            $hasInventoryHistory
                                ? $productUnit->conversion_factor
                                : $conversionFactor,

                        'is_default' =>
                            $isDefault,
                    ]);
                } else {
                    $product->productUnits()->create([
                        'unit_of_measure_id' =>
                            $unitId,

                        'conversion_factor' =>
                            $conversionFactor,

                        'is_default' =>
                            $isDefault,
                    ]);
                }
            }

            foreach (
                $existingUnits
                as $unitId => $existingUnit
            ) {
                if (
                    in_array(
                        (int) $unitId,
                        $submittedUnitIds,
                        true
                    )
                ) {
                    continue;
                }

                $usedInInventory =
                    Inventory::where(
                        'product_id',
                        $product->id
                    )
                        ->where(
                            'product_unit_id',
                            $existingUnit->id
                        )
                        ->exists();

                $usedInTransactions =
                    InventoryTransaction::where(
                        'product_id',
                        $product->id
                    )
                        ->where(
                            'product_unit_id',
                            $existingUnit->id
                        )
                        ->exists();

                if (
                    !$usedInInventory &&
                    !$usedInTransactions
                ) {
                    $existingUnit->delete();
                }
            }
        });

        return redirect()
            ->route('products.index')
            ->with(
                'success',
                'Product updated successfully.'
            );
    }

    public function deactivate(Product $product)
    {
        if (!$product->is_active) {
            return redirect()
                ->route('products.index')
                ->with(
                    'error',
                    'This product is already inactive.'
                );
        }

        $hasStock =
            Inventory::where(
                'product_id',
                $product->id
            )
                ->where(
                    'base_quantity',
                    '>',
                    0
                )
                ->exists();

        if ($hasStock) {
            return redirect()
                ->route('products.index')
                ->with(
                    'error',
                    'This product cannot be deactivated while it still has inventory. Remove or transfer the remaining stock first.'
                );
        }

        $product->update([
            'is_active' => false,
        ]);

        return redirect()
            ->route('products.index')
            ->with(
                'success',
                'Product deactivated successfully.'
            );
    }

    public function activate(Product $product)
    {
        if ($product->is_active) {
            return redirect()
                ->route('products.index')
                ->with(
                    'error',
                    'This product is already active.'
                );
        }

        $product->update([
            'is_active' => true,
        ]);

        return redirect()
            ->route('products.index')
            ->with(
                'success',
                'Product activated successfully.'
            );
    }

    public function destroy(Product $product)
    {
        $hasInventory =
            Inventory::where(
                'product_id',
                $product->id
            )->exists();

        $hasTransactions =
            InventoryTransaction::where(
                'product_id',
                $product->id
            )->exists();

        if (
            $hasInventory ||
            $hasTransactions
        ) {
            return redirect()
                ->route('products.index')
                ->with(
                    'error',
                    'This product cannot be permanently deleted because it has inventory or transaction history. Deactivate it instead.'
                );
        }

        DB::transaction(function () use ($product) {
            $product->productUnits()->delete();

            $product->delete();
        });

        return redirect()
            ->route('products.index')
            ->with(
                'success',
                'Product permanently deleted successfully.'
            );
    }

    private function validateBaseUnit(
        array $units,
        int|string $baseUnitId
    ): void {
        $baseUnitExists =
            collect($units)
                ->contains(function ($unit) use ($baseUnitId) {
                    return
                        (int) $unit['unit_of_measure_id']
                        === (int) $baseUnitId;
                });

        if (!$baseUnitExists) {
            throw ValidationException::withMessages([
                'base_unit_id' =>
                    'The base unit must be included in the available units.',
            ]);
        }
    }

    private function validateBaseConversionFactor(
        array $units,
        int|string $baseUnitId
    ): void {
        foreach ($units as $unit) {
            if (
                (int) $unit['unit_of_measure_id']
                !== (int) $baseUnitId
            ) {
                continue;
            }

            if (
                abs(
                    (float) $unit['conversion_factor'] - 1
                ) > 0.0000001
            ) {
                throw ValidationException::withMessages([
                    'units' =>
                        'The base unit must have a conversion factor of 1.',
                ]);
            }
        }
    }
}
