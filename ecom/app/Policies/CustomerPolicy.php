<?php

namespace App\Policies;

use App\Models\Customer;
use App\Models\User;

class CustomerPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isEmployee();
    }

    public function view(User $user, Customer $customer): bool
    {
        if ($user->isAdmin() || $user->isEmployee()) {
            return true;
        }

        return $user->id === $customer->user_id;
    }

    public function delete(User $user, Customer $customer): bool
    {
        return $user->isAdmin();
    }

    public function viewPurchaseHistory(User $user, Customer $customer): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->id === $customer->user_id;
    }
}
