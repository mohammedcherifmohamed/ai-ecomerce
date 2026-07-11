<?php

namespace App\Http\Controllers;

use App\Services\CategoryService;
use App\Services\ProductService;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function __construct(
        protected ProductService $productService,
        protected CategoryService $categoryService,
    ) {}

    public function __invoke(Request $request)
    {
        $featuredProducts = $this->productService->getFeaturedProducts(8);

        return view('home.index', compact('featuredProducts'));
    }
}
