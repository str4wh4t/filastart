<?php

namespace App\Filament\Resources\UserResource\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class UserInfo extends Widget
{
    protected static string $view = 'filament.resources.user-resource.widgets.user-info';
    protected static bool $isLazy = false;

    protected function getViewData(): array
    {
        return [
            'user' => Auth::user(),
        ];
    }
}
