<?php

namespace App\Http\Controllers;

use App\Models\ProductCategory;
use Illuminate\Http\Request;

class ProductCategoryController extends Controller
{
    public function index()
    {
        $categories = ProductCategory::latest()->paginate(10);

        return view('product_categories.index', compact('categories'));
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
        $productCategory->delete();

        return redirect()
            ->route('product-categories.index')
            ->with('success', 'Product category deleted successfully.');
    }
}