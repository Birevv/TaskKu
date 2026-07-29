<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum DisplayDensity: string implements HasLabel
{
    case Comfortable = 'comfortable';
    case Compact = 'compact';

    public function getLabel(): string
    {
        return match ($this) {
            self::Comfortable => 'Comfortable',
            self::Compact => 'Compact',
        };
    }
}
