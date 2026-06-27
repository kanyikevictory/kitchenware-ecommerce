<?php

namespace App\Services;

use App\Models\Category;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class CategoryService
{
    public function create(array $attributes, ?UploadedFile $image): Category
    {
        $imagePath = $image?->store('categories', 'public');

        try {
            return DB::transaction(fn (): Category => Category::query()->create([
                ...Arr::except($attributes, ['image']),
                'slug' => $this->uniqueSlug($attributes['name']),
                'image_path' => $imagePath,
            ]));
        } catch (Throwable $exception) {
            if ($imagePath) {
                Storage::disk('public')->delete($imagePath);
            }

            throw $exception;
        }
    }

    public function update(Category $category, array $attributes, ?UploadedFile $image): Category
    {
        $oldImagePath = $category->image_path;
        $newImagePath = $image?->store('categories', 'public');

        try {
            DB::transaction(function () use ($category, $attributes, $newImagePath): void {
                $data = Arr::except($attributes, ['image']);

                if (isset($data['name']) && $data['name'] !== $category->name) {
                    $data['slug'] = $this->uniqueSlug($data['name'], $category);
                }

                if ($newImagePath) {
                    $data['image_path'] = $newImagePath;
                }

                $category->update($data);
            });
        } catch (Throwable $exception) {
            if ($newImagePath) {
                Storage::disk('public')->delete($newImagePath);
            }

            throw $exception;
        }

        if ($newImagePath && $oldImagePath) {
            Storage::disk('public')->delete($oldImagePath);
        }

        return $category->refresh();
    }

    public function delete(Category $category): void
    {
        if ($category->children()->exists() || $category->products()->exists()) {
            throw ValidationException::withMessages([
                'category' => ['Move or remove this category’s children and products before deleting it.'],
            ]);
        }

        $category->delete();
    }

    private function uniqueSlug(string $name, ?Category $ignore = null): string
    {
        $base = Str::slug($name) ?: 'category';
        $slug = $base;
        $suffix = 2;

        while (Category::withTrashed()
            ->when($ignore, fn ($query) => $query->whereKeyNot($ignore->id))
            ->where('slug', $slug)
            ->exists()) {
            $slug = "{$base}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }
}
