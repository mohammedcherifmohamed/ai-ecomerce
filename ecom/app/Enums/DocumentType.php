<?php

namespace App\Enums;

enum DocumentType: string
{
    case Policy = 'policy';
    case Manual = 'manual';
    case Guide = 'guide';
    case Report = 'report';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Policy => 'Policy',
            self::Manual => 'Manual',
            self::Guide => 'Guide',
            self::Report => 'Report',
            self::Other => 'Other',
        };
    }
}
