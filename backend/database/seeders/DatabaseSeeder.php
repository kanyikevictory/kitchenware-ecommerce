<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            CategorySeeder::class,
        ]);

        $superAdminRole = \App\Models\Role::query()->where('slug', 'super-admin')->firstOrFail();
        $customerRole = \App\Models\Role::query()->where('slug', 'customer')->firstOrFail();

        User::factory()->create([
            'role_id' => $superAdminRole->id,
            'name' => 'Super Admin',
            'email' => 'admin@example.com',
            'password' => 'password',
            'status' => 'active',
        ]);

        User::factory()->create([
            'role_id' => $customerRole->id,
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'status' => 'active',
        ]);
    }
}
