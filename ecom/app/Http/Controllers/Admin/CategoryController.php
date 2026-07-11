<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Category\StoreCategoryRequest;
use App\Http\Requests\Category\UpdateCategoryRequest;
use App\Services\CategoryService;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function __construct(
        protected CategoryService $categoryService,
    ) {}

    public function index(Request $request)
    {
        $filters = $request->only(['search']);
        $categories = $this->categoryService->paginate($filters, 15);

        return view('admin.categories.index', compact('categories'));
    }

    public function create()
    {
        $parentCategories = $this->categoryService->getRootCategories();

        return view('admin.categories.create', compact('parentCategories'));
    }

    public function store(StoreCategoryRequest $request)
    {
        $this->categoryService->create($request->validated());

        return redirect()->route('admin.categories.index')->with('success', 'Category created successfully.');
    }

    public function edit(int $id)
    {
        $category = $this->categoryService->getById($id);
        $parentCategories = $this->categoryService->getRootCategories()->filter(fn ($c) => $c->id !== $id);

        return view('admin.categories.create', compact('category', 'parentCategories'));
    }

    public function update(UpdateCategoryRequest $request, int $id)
    {
        $this->categoryService->update($id, $request->validated());

        return redirect()->route('admin.categories.index')->with('success', 'Category updated successfully.');
    }

    public function destroy(int $id)
    {
        $this->categoryService->delete($id);

        return redirect()->route('admin.categories.index')->with('success', 'Category deleted successfully.');
    }
}
