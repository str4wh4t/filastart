<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('general.brand_name', 'Laravel Starter Kit');
        $this->migrator->add('general.brand_logo', null);
        $this->migrator->add('general.brand_logoHeight', '50');
        $this->migrator->add('general.site_favicon', null);
        $this->migrator->add('general.login_cover_image', null);
        $this->migrator->add('general.search_engine_indexing', false);
        $this->migrator->add('general.site_theme', [
            "primary" => "#134bf5",
            "secondary" => "#00dbd1",
            "gray" => "#636363",
            "success" => "#22b800",
            "danger" => "#ff5467",
            "info" => "#bb42d9",
            "warning" => "#f0991b",
        ]);
    }
};
