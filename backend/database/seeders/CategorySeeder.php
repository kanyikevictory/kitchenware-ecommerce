<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Cookware',
            'Bakeware',
            'Knives',
            'Utensils',
            'Storage',
            'Small Appliances',
        ];

        foreach ($categories as $index => $name) {
            Category::query()->updateOrCreate(
                ['slug' => Str::slug($name)],
                [
                    'name' => $name,
                    'description' => $name.' for the kitchen store catalog.',
                    'sort_order' => $index + 1,
                    'is_active' => true,
                ]
            );
        }
    }
}
