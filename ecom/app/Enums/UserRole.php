<?php

namespace App\Enums;

enum UserRole: string
{
    case Customer = 'customer';
    case Employee = 'employee';
    case Administrator = 'administrator';

    public function label(): string
    {
        return match ($this) {
            self::Customer => 'Customer',
            self::Employee => 'Employee',
            self::Administrator => 'Administrator',
        };
    }
}
