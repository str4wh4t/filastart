<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Models\User;
use Filament\Actions;
use Filament\Pages\Concerns\ExposesTableToWidgets;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ListUsers extends ListRecords
{
    use ExposesTableToWidgets;
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        // return [
        //     Actions\CreateAction::make(),
        // ];

        return [
            Actions\CreateAction::make()
                ->label('Create Record')
                ->color('primary')
                ->icon('heroicon-o-plus-circle'),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return static::$resource::getWidgets();
    }

    public function getTabs(): array
    {
        /** @var \App\Models\User $user */
        // $user = Auth::user();
        $tabs = [
            null => Tab::make('All'),
            'admin' => Tab::make()->query(fn ($query) => $query->with('roles')->whereRelation('roles', 'name', '=', 'admin')),
            'author' => Tab::make()->query(fn ($query) => $query->with('roles')->whereRelation('roles', 'name', '=', 'author')),
        ];

        // if ($user->isSuperAdmin()) {
        //     $tabs['superadmin'] = Tab::make()->query(fn ($query) => $query->with('roles')->whereRelation('roles', 'name', '=', config('filament-shield.super_admin.name')));
        // }

        return $tabs;
    }

    protected function getTableQuery(): Builder
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $model = (new (static::$resource::getModel()))->with('roles')->where('id', '!=', $user->id);

        // if (!$user->isSuperAdmin()) {
            $model = $model->whereDoesntHave('roles', function ($query) {
                $query->where('name', '=', config('filament-shield.super_admin.name'));
            });
        // }

        return $model;
    }
}
