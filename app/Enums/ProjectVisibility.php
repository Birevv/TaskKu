<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum ProjectVisibility: string implements HasLabel
{
    case Workspace = 'workspace';
    case Private = 'private';

    public function getLabel() : string
    {
        return match ($this) {
            self::Workspace => 'Workspace',
            self::Private => 'Private',
        };
    }
}
