<?php

namespace Database\Seeders;

use App\Filament\Resources\Shield\RoleResource;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = ["dekan", "wd1", "wd2", "manager", "supervisor"];

        foreach ($roles as $key => $role) {
            $roleCreated = (new (RoleResource::getModel()))->create(
                [
                    'name' => $role,
                    'guard_name' => 'web',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
