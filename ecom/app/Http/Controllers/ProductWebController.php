<?php

namespace App\Http\Controllers;

use App\Services\CategoryService;
use App\Services\ProductService;
use Illuminate\Http\Request;

class ProductWebController extends Controller
{
    public function __construct(
        protected ProductService $productService,
        protected CategoryService $categoryService,
    ) {}

    public function index(Request $request)
    {
        $filters = $request->only(['category_id', 'search', 'min_price', 'max_price']);
        $products = $this->productService->getActiveProducts($filters, 12);
        $categories = $this->categoryService->getActiveCategories();

        return view('products.index', compact('products', 'categories'));
    }

    public function show(string $slug)
    {
        $product = $this->productService->getBySlug($slug);

        return view('products.show', compact('product'));
    }
}
