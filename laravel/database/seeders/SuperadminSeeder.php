<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Faker\Factory as Faker;
use Illuminate\Support\Facades\Artisan;

class SuperadminSeeder extends Seeder
{
    public function run()
    {
        // Superadmin user
        $sid = Str::uuid();
        DB::table('users')->insert([
            'id' => $sid,
            'username' => 'superadmin',
            'firstname' => 'Super',
            'lastname' => 'Admin',
            'email' => 'superadmin@starter-kit.com',
            'email_verified_at' => now(),
            'password' => Hash::make('superadmin'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Bind superadmin user to FilamentShield
        Artisan::call('shield:super-admin', ['--user' => $sid]);
    }
}

