<?php

namespace App\Http\Controllers;

use App\Services\CategoryService;

class CategoryWebController extends Controller
{
    public function __construct(
        protected CategoryService $categoryService,
    ) {}

    public function index()
    {
        $categories = $this->categoryService->getRootCategories()->load('children', 'products');

        return view('categories.index', compact('categories'));
    }
}
