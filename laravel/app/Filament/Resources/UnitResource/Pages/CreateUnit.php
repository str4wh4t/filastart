<?php

namespace App\Filament\Resources\UnitResource\Pages;

use App\Filament\Resources\UnitResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateUnit extends CreateRecord
{
    protected static string $resource = UnitResource::class;

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

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // dd($data);
        $data['code'] = $data['code_temp'];
        if (!empty($data['parent_id'])) {
            $parent = \App\Models\Unit::find($data['parent_id']);
            $data['code'] = $parent->code . $data['code_temp'];
        }

        return $data;
    }
}
