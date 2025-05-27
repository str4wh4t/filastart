<?php

namespace App\Filament\Resources\Blog\CategoryResource\Pages;

use App\Filament\Resources\Blog\CategoryResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Actions;

class CreateCategory extends CreateRecord
{
    protected static string $resource = CategoryResource::class;

    // protected function getRedirectUrl(): string
    // {
    //     return $this->getResource()::getUrl('index');
    // }

    public function getTitle(): string
    {
        return __('Create Blog Category');
    }

    protected function getFormActions(): array
    {
        return [
            // ...parent::getFormActions(),
            Actions\Action::make('create')
                ->label('Create')
                ->color('success')
                ->icon('heroicon-o-check-circle')
                ->submit('create'),
            Actions\Action::make('list')
                ->label('Back')
                ->color('gray')
                ->icon('heroicon-o-chevron-left')
                ->url(fn (): string => CategoryResource::getUrl('index')),
        ];
    }
}
