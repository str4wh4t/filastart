<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Faker\Factory as Faker;
use Illuminate\Support\Facades\Artisan;

class FakeUsersTableSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create();
        $roles = DB::table('roles')->whereNot('name', 'super_admin')->get();
        foreach ($roles as $role) {
            for ($i = 0; $i < 10; $i++) {
                $userId = Str::uuid();
                DB::table('users')->insert([
                    'id' => $userId,
                    'username' => $faker->unique()->userName,
                    'firstname' => $faker->firstName,
                    'lastname' => $faker->lastName,
                    'email' => $faker->unique()->safeEmail,
                    'email_verified_at' => now(),
                    'password' => Hash::make('password'),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                DB::table('model_has_roles')->insert([
                    'role_id' => $role->id,
                    'model_type' => 'App\Models\User',
                    'model_id' => $userId,
                ]);
            }
        }
    }
}

