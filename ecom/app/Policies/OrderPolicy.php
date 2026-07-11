<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isEmployee();
    }

    public function view(User $user, Order $order): bool
    {
        if ($user->isAdmin() || $user->isEmployee()) {
            return true;
        }

        return $user->id === $order->customer->user_id;
    }

    public function create(User $user): bool
    {
        return $user->isCustomer();
    }

    public function updateStatus(User $user): bool
    {
        return $user->isAdmin() || $user->isEmployee();
    }

    public function cancel(User $user, Order $order): bool
    {
        if ($user->isAdmin() || $user->isEmployee()) {
            return true;
        }

        return $user->id === $order->customer->user_id
            && $order->status->value === 'pending';
    }

    public function refund(User $user): bool
    {
        return $user->isAdmin() || $user->isEmployee();
    }
}
