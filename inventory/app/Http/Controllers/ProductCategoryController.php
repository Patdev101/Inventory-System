<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Http\Request;

class ProductCategoryController extends Controller
{
    public function index()
    {
        $categories = ProductCategory::latest()->paginate(10);

        return view('product_categories.index', compact('categories'));
    }

    /**
     * Show soft-deleted product categories, admin-only, so they can be
     * restored.
     */
    public function trashed()
    {
        $categories = ProductCategory::onlyTrashed()
            ->latest('deleted_at')
            ->paginate(10);

        return view('product_categories.trashed', compact('categories'));
    }

    /**
     * Restore a soft-deleted product category.
     */
    public function restore(int $productCategory)
    {
        $category = ProductCategory::onlyTrashed()->findOrFail($productCategory);
        $category->restore();

        return redirect()
            ->route('product-categories.trashed')
            ->with('success', 'Product category "' . $category->name . '" restored.');
    }

    public function create()
    {
        return view('product_categories.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:300'],
            'code' => ['required', 'string', 'max:100', 'unique:product_categories,code'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        ProductCategory::create($validated);

        return redirect()
            ->route('product-categories.index')
            ->with('success', 'Product category created successfully.');
    }

    public function show(ProductCategory $productCategory)
    {
        $productCategory->load('products');

        return view('product_categories.show', [
            'productCategory' => $productCategory,
        ]);
    }

    public function edit(ProductCategory $productCategory)
    {
        return view('product_categories.edit', [
            'productCategory' => $productCategory,
        ]);
    }

    public function update(Request $request, ProductCategory $productCategory)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:300'],
            'code' => [
                'required',
                'string',
                'max:100',
                'unique:product_categories,code,' . $productCategory->id,
            ],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $productCategory->update($validated);

        return redirect()
            ->route('product-categories.index')
            ->with('success', 'Product category updated successfully.');
    }

    public function destroy(ProductCategory $productCategory)
    {
        $hasProducts = Product::where('product_category_id', $productCategory->id)->exists();

        if ($hasProducts) {
            return redirect()
                ->route('product-categories.index')
                ->with(
                    'error',
                    'This product category cannot be deleted because it still has products.'
                );
        }

        $productCategory->delete();

        return redirect()
            ->route('product-categories.index')
            ->with('success', 'Product category deleted. It can be restored by an admin if needed.');
    }
}