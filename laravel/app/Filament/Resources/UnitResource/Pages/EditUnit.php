<?php

namespace App\Filament\Resources\UnitResource\Pages;

use App\Filament\Resources\UnitResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Livewire\Attributes\Url;

class EditUnit extends EditRecord
{
    protected static string $resource = UnitResource::class;

    #[Url]
    public ?string $page = null;

    #[Url]
    public ?string $activeTab = null;

    #[Url]
    public ?array $tableFilters = null;

    #[Url]
    public ?string $tableSearch = null;

    public function getFormActions(): array
    {
        return [
            parent::getSaveFormAction()
                ->label('Save Changes')
                ->color('success')
                ->icon('heroicon-o-check-circle'),
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
            Actions\DeleteAction::make()->label('Trash')->icon('heroicon-o-trash'),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // dd($data);
        $data['code_temp'] = $data['code'];
        if (!empty($data['parent_id'])) {
            $parent = \App\Models\Unit::find($data['parent_id']);
            $data['code_temp'] = substr($data['code'], strlen($parent->code), strlen($data['code']) - strlen($parent->code));
        }
        // dd($data);
        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // dd($data);
        $data['code'] = $data['code_temp'];
        if (!empty($data['parent_id'])) {
            $parent = \App\Models\Unit::find($data['parent_id']);
            $data['code'] = $parent->code . $data['code_temp'];
        }
        // dd($data);
        return $data;
    }
}
