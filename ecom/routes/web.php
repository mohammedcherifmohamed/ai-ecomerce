<?php

use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\CustomerController as AdminCustomerController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\DocumentController as AdminDocumentController;
use App\Http\Controllers\Admin\EmployeeController as AdminEmployeeController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\AiChatController;
use App\Http\Controllers\AuthWebController;
use App\Http\Controllers\CategoryWebController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\CustomerOrderController;
use App\Http\Controllers\Employee\CustomerController as EmployeeCustomerController;
use App\Http\Controllers\Employee\InventoryController as EmployeeInventoryController;
use App\Http\Controllers\Employee\OrderController as EmployeeOrderController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProductWebController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

Route::get('/products', [ProductWebController::class, 'index'])->name('products.index');
Route::get('/products/{slug}', [ProductWebController::class, 'show'])->name('products.show');

Route::get('/categories', [CategoryWebController::class, 'index'])->name('categories.index');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthWebController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthWebController::class, 'login'])->name('login.post');
    Route::get('/register', [AuthWebController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthWebController::class, 'register'])->name('register.post');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthWebController::class, 'logout'])->name('logout');

    Route::post('/checkout/buy-now', [CheckoutController::class, 'buyNow'])->name('checkout.buy-now');
    Route::get('/checkout/success/{id}', [CheckoutController::class, 'success'])->name('checkout.success');

    Route::prefix('my-orders')->name('customer.orders.')->group(function () {
        Route::get('/', [CustomerOrderController::class, 'index'])->name('index');
        Route::get('/{id}', [CustomerOrderController::class, 'show'])->name('show');
    });

    Route::middleware('role:administrator')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', AdminDashboardController::class)->name('dashboard');
        Route::resource('products', AdminProductController::class)->except(['show']);
        Route::resource('categories', AdminCategoryController::class)->except(['show']);
        Route::resource('employees', AdminEmployeeController::class)->except(['show']);
        Route::get('orders', [AdminOrderController::class, 'index'])->name('orders.index');
        Route::get('orders/{id}', [AdminOrderController::class, 'show'])->name('orders.show');
        Route::put('orders/{id}/status', [AdminOrderController::class, 'updateStatus'])->name('orders.status');
        Route::post('orders/{id}/cancel', [AdminOrderController::class, 'cancel'])->name('orders.cancel');
        Route::post('orders/{id}/refund', [AdminOrderController::class, 'refund'])->name('orders.refund');
        Route::get('customers', [AdminCustomerController::class, 'index'])->name('customers.index');
        Route::get('customers/{id}', [AdminCustomerController::class, 'show'])->name('customers.show');
        Route::delete('customers/{id}', [AdminCustomerController::class, 'destroy'])->name('customers.destroy');
        Route::get('documents', [AdminDocumentController::class, 'index'])->name('documents.index');
        Route::post('documents', [AdminDocumentController::class, 'store'])->name('documents.store');
        Route::get('documents/{id}/download', [AdminDocumentController::class, 'download'])->name('documents.download');
        Route::delete('documents/{id}', [AdminDocumentController::class, 'destroy'])->name('documents.destroy');
    });

    Route::middleware('role:administrator,employee')->prefix('employee')->name('employee.')->group(function () {
        Route::get('orders', [EmployeeOrderController::class, 'index'])->name('orders.index');
        Route::get('orders/{id}', [EmployeeOrderController::class, 'show'])->name('orders.show');
        Route::put('orders/{id}/status', [EmployeeOrderController::class, 'updateStatus'])->name('orders.status');
        Route::post('orders/{id}/cancel', [EmployeeOrderController::class, 'cancel'])->name('orders.cancel');
        Route::post('orders/{id}/refund', [EmployeeOrderController::class, 'refund'])->name('orders.refund');
        Route::get('inventory', [EmployeeInventoryController::class, 'index'])->name('inventory.index');
        Route::put('inventory/{productId}/stock', [EmployeeInventoryController::class, 'update'])->name('inventory.update');
        Route::get('customers', [EmployeeCustomerController::class, 'index'])->name('customers.index');
    });
});

Route::post('/chat', [AiChatController::class, 'ask'])->name('chat.ask');
