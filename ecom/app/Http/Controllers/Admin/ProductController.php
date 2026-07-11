<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Services\CategoryService;
use App\Services\ProductService;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct(
        protected ProductService $productService,
        protected CategoryService $categoryService,
    ) {}

    public function index(Request $request)
    {
        $filters = $request->only(['search', 'category_id']);
        $products = $this->productService->paginate($filters, 15);

        return view('admin.products.index', compact('products'));
    }

    public function create()
    {
        $categories = $this->categoryService->getActiveCategories();

        return view('admin.products.create', ['categories' => $categories]);
    }

    public function store(StoreProductRequest $request)
    {
        $this->productService->create($request->validated());

        return redirect()->route('admin.products.index')->with('success', 'Product created successfully.');
    }

    public function edit(int $id)
    {
        $product = $this->productService->getById($id);
        $categories = $this->categoryService->getActiveCategories();

        return view('admin.products.create', compact('product', 'categories'));
    }

    public function update(UpdateProductRequest $request, int $id)
    {
        $this->productService->update($id, $request->validated());

        return redirect()->route('admin.products.index')->with('success', 'Product updated successfully.');
    }

    public function destroy(int $id)
    {
        $this->productService->delete($id);

        return redirect()->route('admin.products.index')->with('success', 'Product deleted successfully.');
    }
}
