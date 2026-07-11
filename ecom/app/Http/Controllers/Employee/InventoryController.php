<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Http\Requests\Inventory\UpdateStockRequest;
use App\Services\ProductService;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function __construct(
        protected ProductService $productService,
    ) {}

    public function index(Request $request)
    {
        $products = $this->productService->paginate([], 20);
        $lowStockProducts = $this->productService->getLowStockProducts();

        return view('employee.inventory.index', compact('products', 'lowStockProducts'));
    }

    public function update(UpdateStockRequest $request, int $productId)
    {
        $this->productService->updateStock($productId, $request->stock_quantity);

        return redirect()->route('employee.inventory.index')->with('success', 'Stock updated successfully.');
    }
}
