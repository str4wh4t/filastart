<?php

namespace App\Filament\Resources\Blog\CategoryResource\Pages;

use App\Filament\Resources\Blog\CategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Livewire\Attributes\Url;

class EditCategory extends EditRecord
{
    protected static string $resource = CategoryResource::class;

    #[Url]
    public ?string $page = null;

    #[Url]
    public ?string $activeTab = null;

    #[Url]
    public ?array $tableFilters = null;

    #[Url]
    public ?string $tableSearch = null;

    // protected function getHeaderActions(): array
    // {
    //     return [
    //         Actions\ViewAction::make(),
    //         Actions\DeleteAction::make(),
    //         Actions\ForceDeleteAction::make(),
    //         Actions\RestoreAction::make(),
    //     ];
    // }

    public function getTitle(): string
    {
        return __('Edit Blog Category');
    }

    public function getFormActions(): array
    {
        return [
            // ...parent::getFormActions(),
            Actions\Action::make('save')
                ->label('Save')
                ->color('success')
                ->icon('heroicon-o-check-circle')
                ->keyBindings(['mod+s'])
                ->submit('save'),
            Actions\Action::make('create')
                ->label('Create Another')
                ->color('primary')
                ->icon('heroicon-o-plus-circle')
                ->url(fn (): string => static::getResource()::getUrl('create')),
            Actions\Action::make('list')
                ->label('Back')
                ->color('gray')
                ->icon('heroicon-o-chevron-left')
                // ->alpineClickHandler('document.referrer ? window.history.back() : (window.location.href = ' . \Illuminate\Support\Js::from($this->previousUrl ?? static::getResource()::getUrl('index')) . ')'),
                ->url(fn (): string => static::getResource()::getUrl('index', ['page' => $this->page, 'activeTab' => $this->activeTab, 'tableFilters' => $this->tableFilters, 'tableSearch' => $this->tableSearch])),
            Actions\DeleteAction::make()->icon('heroicon-o-trash'),
        ];
    }
}
