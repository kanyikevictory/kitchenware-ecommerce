<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        foreach (Permission::DEFINITIONS as $slug => $description) {
            Permission::query()->updateOrCreate(
                ['slug' => $slug],
                ['name' => Str::headline(str_replace('.', ' ', $slug)), 'description' => $description],
            );
        }

        $permissions = Permission::query()->pluck('id');
        Role::query()->whereIn('slug', ['admin', 'super-admin'])->get()
            ->each(fn (Role $role) => $role->permissions()->sync($permissions));
    }
}
