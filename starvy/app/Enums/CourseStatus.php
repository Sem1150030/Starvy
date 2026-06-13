<?php

namespace App\Enums;

enum CourseStatus: string
{
    case Public = 'public';
    case Private = 'private';
    case Draft = 'draft';

    /**
     * Human-friendly label for display.
     */
    public function label(): string
    {
        return match ($this) {
            self::Public => 'Public',
            self::Private => 'Private',
            self::Draft => 'Draft',
        };
    }
}
