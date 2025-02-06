<?php

namespace App\Filament\Resources;

use Filament\Resources\Resource;

abstract class AdminResource extends Resource
{
    public static function shouldRegisterNavigation(): bool
    {
        // Return the value from the config (defaulting to false if not set)
        return config('filament.navigation_enabled', false);
    }
}
