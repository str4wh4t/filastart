<?php

namespace App\Filament\Resources\Banner\CategoryResource\Pages;

use App\Filament\Resources\Banner\CategoryResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCategory extends CreateRecord
{
    protected static string $resource = CategoryResource::class;

    // protected function getRedirectUrl(): string
    // {
    //     return $this->getResource()::getUrl('index');
    // }

    public function getTitle(): string
    {
        return __('Create Banner Category');
    }

    protected function getFormActions(): array
    {
        return [
            // ...parent::getFormActions(),
            parent::getCreateFormAction()
                ->label('Save Record')
                ->color('success')
                ->icon('heroicon-o-check-circle'),
            parent::getCancelFormAction()
                ->label('Back')
                ->icon('heroicon-o-chevron-left'),
        ];
    }
}
