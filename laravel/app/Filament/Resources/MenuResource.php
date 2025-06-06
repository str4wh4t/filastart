<?php

namespace App\Filament\Resources;

use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Datlechin\FilamentMenuBuilder\Resources\MenuResource as BaseMenuResource;

class MenuResource extends BaseMenuResource implements HasShieldPermissions
{
    protected static ?int $navigationSort = 0;

    protected static ?string $navigationIcon = 'fluentui-navigation-16';

    public static function getPermissionPrefixes(): array
    {
        return [
            'view',
            'view_any',
            'create',
            'update',
            'delete',
            'delete_any',
            'reorder',
        ];
    }

    public static function getNavigationGroup(): ?string
    {
        return __("menu.nav_group.sites");
    }
}
