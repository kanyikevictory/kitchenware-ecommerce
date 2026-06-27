<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class ProductService
{
    public function __construct(private readonly ProductImageService $imageService) {}

    /** @param array<int, UploadedFile> $images */
    public function create(array $attributes, array $images): Product
    {
        $paths = $this->storeImages($images);

        try {
            return DB::transaction(function () use ($attributes, $paths): Product {
                $product = Product::query()->create([
                    ...Arr::except($attributes, ['images']),
                    'slug' => $this->uniqueSlug($attributes['name']),
                ]);

                $this->createImageRecords($product, $paths, $attributes['name']);

                return $product;
            });
        } catch (Throwable $exception) {
            Storage::disk('public')->delete($paths);
            throw $exception;
        }
    }

    /** @param array<int, UploadedFile> $images */
    public function update(Product $product, array $attributes, array $images): Product
    {
        if ($product->images()->count() + count($images) > 8) {
            throw ValidationException::withMessages(['images' => ['A product may have at most 8 images.']]);
        }

        $paths = $this->storeImages($images);

        try {
            DB::transaction(function () use ($product, $attributes, $paths): void {
                $data = Arr::except($attributes, ['images']);

                if (isset($data['name']) && $data['name'] !== $product->name) {
                    $data['slug'] = $this->uniqueSlug($data['name'], $product);
                }

                $product->update($data);
                $this->createImageRecords($product, $paths, $product->name);
            });
        } catch (Throwable $exception) {
            Storage::disk('public')->delete($paths);
            throw $exception;
        }

        return $product->refresh();
    }

    public function delete(Product $product): void
    {
        DB::transaction(function () use ($product): void {
            $product->images()->delete();
            $product->delete();
        });
    }

    public function deleteImage(Product $product, ProductImage $image): void
    {
        abort_unless($image->product_id === $product->id, 404);

        DB::transaction(function () use ($product, $image): void {
            $wasPrimary = $image->is_primary;
            $path = $image->path;
            $image->delete();

            if ($wasPrimary) {
                $nextImage = ProductImage::query()
                    ->where('product_id', $product->id)
                    ->orderBy('sort_order')
                    ->first();

                $nextImage?->update(['is_primary' => true]);
            }

            Storage::disk('public')->delete($path);
        });
    }

    public function setPrimaryImage(Product $product, ProductImage $image): void
    {
        abort_unless($image->product_id === $product->id, 404);

        DB::transaction(function () use ($product, $image): void {
            ProductImage::query()
                ->where('product_id', $product->id)
                ->update(['is_primary' => false]);

            $image->update(['is_primary' => true]);
        });
    }

    /** @param array<int, UploadedFile> $images
     * @return array<int, string>
     */
    private function storeImages(array $images): array
    {
        $paths = [];

        try {
            foreach ($images as $image) {
                $paths[] = $this->imageService->store($image);
            }
        } catch (Throwable $exception) {
            Storage::disk('public')->delete($paths);
            throw $exception;
        }

        return $paths;
    }

    /** @param array<int, string> $paths */
    private function createImageRecords(Product $product, array $paths, string $altText): void
    {
        $start = (int) $product->images()->max('sort_order') + 1;
        $hasPrimary = $product->images()->where('is_primary', true)->exists();

        foreach ($paths as $index => $path) {
            $product->images()->create([
                'path' => $path,
                'alt_text' => $altText,
                'sort_order' => $start + $index,
                'is_primary' => ! $hasPrimary && $index === 0,
            ]);
        }
    }

    private function uniqueSlug(string $name, ?Product $ignore = null): string
    {
        $base = Str::slug($name) ?: 'product';
        $slug = $base;
        $suffix = 2;

        while (Product::withTrashed()->when($ignore, fn ($query) => $query->whereKeyNot($ignore->id))->where('slug', $slug)->exists()) {
            $slug = "{$base}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }
}
