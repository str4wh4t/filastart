<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\PendaftaranChartWidget;
use App\Models\Sekolah;
use Filament\Pages\Page;

class Dashboard extends Page
{
    protected static ?string $navigationIcon = 'fluentui-home-more-20-o';

    protected static string $view = 'filament.pages.dashboard';

    protected function getHeaderWidgets(): array
    {
        return Sekolah::orderBy('sort')->pluck('nama_sekolah')->map(function ($nama_sekolah) {
            return PendaftaranChartWidget::make([
                'nama_sekolah' => $nama_sekolah
            ]);
        })->all();
    }

}
