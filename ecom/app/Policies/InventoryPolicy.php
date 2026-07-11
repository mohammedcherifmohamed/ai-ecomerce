<?php

namespace App\Policies;

use App\Models\User;

class InventoryPolicy
{
    public function manage(User $user): bool
    {
        return $user->isAdmin() || $user->isEmployee();
    }

    public function updateStock(User $user): bool
    {
        return $user->isAdmin() || $user->isEmployee();
    }
}
