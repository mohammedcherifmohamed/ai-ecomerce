<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Document;
use App\Models\Employee;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Policies\CategoryPolicy;
use App\Policies\CustomerPolicy;
use App\Policies\DocumentPolicy;
use App\Policies\EmployeePolicy;
use App\Policies\OrderPolicy;
use App\Policies\ProductPolicy;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        User::class => UserPolicy::class,
        Product::class => ProductPolicy::class,
        Category::class => CategoryPolicy::class,
        Customer::class => CustomerPolicy::class,
        Employee::class => EmployeePolicy::class,
        Order::class => OrderPolicy::class,
        Document::class => DocumentPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
    }
}
