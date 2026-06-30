<?php

namespace Database\Factories;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Role>
 */
class RoleFactory extends Factory
{
    protected $model = Role::class;

    public function definition(): array
    {
        $name = fake()->unique()->jobTitle();

        return [
            'name' => $name,
            'slug' => Str::slug($name.'-'.fake()->unique()->randomNumber(4, true)),
            'description' => fake()->sentence(),
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (Role $role): void {
            if (! in_array($role->slug, ['admin', 'super-admin'], true)) {
                return;
            }

            $permissionIds = collect(Permission::DEFINITIONS)->map(function (string $description, string $slug): int {
                return Permission::query()->firstOrCreate(
                    ['slug' => $slug],
                    ['name' => Str::headline(str_replace('.', ' ', $slug)), 'description' => $description],
                )->id;
            });

            $role->permissions()->syncWithoutDetaching($permissionIds);
        });
    }
}
