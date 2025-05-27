<?php

namespace App\Filament\Resources\Blog\PostResource\Pages;

use App\Filament\Resources\Blog\PostResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePost extends CreateRecord
{
    protected static string $resource = PostResource::class;

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
                ->url(fn (): string => static::getResource()::getUrl('index')),
        ];
    }
}
