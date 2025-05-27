<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('general.brand_name', 'SuperDuper Starter Kit');
        $this->migrator->add('general.brand_logo', 'sites/logo.png');
        $this->migrator->add('general.brand_logoHeight', '100');
        $this->migrator->add('general.site_favicon', 'sites/logo.ico');
        $this->migrator->add('general.search_engine_indexing', false);
        $this->migrator->add('general.site_theme', [
            "primary" => "#134bf5",
            "secondary" => "#00dbd1",
            "gray" => "#363636",
            "success" => "#22b800",
            "danger" => "#ff5467",
            "info" => "#783edb",
            "warning" => "#f0991b",
        ]);
    }
};
