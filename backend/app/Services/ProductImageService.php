<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ProductImageService
{
    private const MAX_DIMENSION = 1600;

    private const WEBP_QUALITY = 82;

    public function store(UploadedFile $file): string
    {
        $source = @imagecreatefromstring((string) file_get_contents($file->getRealPath()));

        if (! $source) {
            throw ValidationException::withMessages(['images' => ['One of the uploaded images could not be decoded.']]);
        }

        $sourceWidth = imagesx($source);
        $sourceHeight = imagesy($source);
        $scale = min(1, self::MAX_DIMENSION / max($sourceWidth, $sourceHeight));
        $width = max(1, (int) round($sourceWidth * $scale));
        $height = max(1, (int) round($sourceHeight * $scale));
        $image = imagecreatetruecolor($width, $height);

        imagealphablending($image, false);
        imagesavealpha($image, true);
        $transparent = imagecolorallocatealpha($image, 0, 0, 0, 127);
        imagefilledrectangle($image, 0, 0, $width, $height, $transparent);
        imagecopyresampled($image, $source, 0, 0, 0, 0, $width, $height, $sourceWidth, $sourceHeight);

        ob_start();
        $encoded = imagewebp($image, null, self::WEBP_QUALITY);
        $contents = ob_get_clean();
        imagedestroy($source);
        imagedestroy($image);

        if (! $encoded || ! is_string($contents)) {
            throw ValidationException::withMessages(['images' => ['One of the uploaded images could not be converted to WebP.']]);
        }

        $path = 'products/'.Str::uuid().'.webp';

        if (! Storage::disk('public')->put($path, $contents)) {
            throw ValidationException::withMessages(['images' => ['One of the product images could not be stored.']]);
        }

        return $path;
    }
}
