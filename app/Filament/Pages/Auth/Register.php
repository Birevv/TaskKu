<?php

namespace App\Filament\Pages\Auth;

use Filament\Auth\Pages\Register as BaseRegister;
use Illuminate\Contracts\Support\Htmlable;

class Register extends BaseRegister
{
    public function getTitle(): string|Htmlable
    {
        return 'Create your Taskku account';
    }

    public function getHeading(): string|Htmlable|null
    {
        return 'Start planning clearly';
    }
}
