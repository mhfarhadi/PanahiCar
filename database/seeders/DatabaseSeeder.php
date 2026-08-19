<?php

namespace Database\Seeders;

use App\Models\User;
use App\Support\UserRoles;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        if (! User::query()->exists()) {
            User::factory()->create([
                'name' => 'Maya',
                'email' => 'maya@mhfarhadi.com',
                'role' => UserRoles::SUPER_ADMIN,
                'is_active' => true,
                'location_id' => null,
            ]);
        } else {
            User::query()->orderBy('id')->limit(1)->update([
                'name' => 'Maya',
                'role' => UserRoles::SUPER_ADMIN,
                'location_id' => null,
                'is_active' => true,
            ]);
        }

        $this->call([
            AutomayaDemoSeeder::class,
        ]);
    }
}
