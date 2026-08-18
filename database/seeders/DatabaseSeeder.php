<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        if (! User::query()->exists()) {
            User::factory()->create([
                'name' => 'Admin',
                'email' => 'admin@automaya.test',
            ]);
        }

        $this->call([
            AutomayaDemoSeeder::class,
        ]);
    }
}
