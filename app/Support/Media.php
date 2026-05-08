<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class Media
{
    public static function diskName(): string
    {
        return (string) config('media.disk', 'public');
    }

    public static function url(?string $path): ?string
    {
        if (blank($path)) {
            return null;
        }

        if (Str::startsWith($path, ['http://', 'https://', '//'])) {
            return $path;
        }

        return Storage::disk(static::diskName())->url($path);
    }

    public static function storeUploadedImage(UploadedFile $file, string $directory): string
    {
        $extension = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: '');
        $directory = trim($directory, '/');

        return match ($extension) {
            'jpg', 'jpeg', 'png' => static::storeAsWebp($file, $directory),
            'gif', 'webp' => static::storeOriginal($file, $directory, $extension),
            default => throw new RuntimeException("Unsupported image upload extension [{$extension}]."),
        };
    }

    public static function replaceUploadedImage(?UploadedFile $file, ?string $existingPath, string $directory): ?string
    {
        if (! $file) {
            return $existingPath;
        }

        $newPath = static::storeUploadedImage($file, $directory);
        static::delete($existingPath);

        return $newPath;
    }

    public static function delete(?string $path): void
    {
        if (blank($path)) {
            return;
        }

        $disk = Storage::disk(static::diskName());

        if ($disk->exists($path)) {
            $disk->delete($path);
        }
    }

    protected static function storeOriginal(UploadedFile $file, string $directory, string $extension): string
    {
        return $file->storeAs($directory, Str::uuid().'.'.$extension, static::diskName());
    }

    protected static function storeAsWebp(UploadedFile $file, string $directory): string
    {
        if (! extension_loaded('gd') || ! function_exists('imagewebp')) {
            throw new RuntimeException('GD with WebP support is required for image optimization.');
        }

        $image = match ($file->getMimeType()) {
            'image/jpeg' => static::loadJpeg($file),
            'image/png' => static::loadPng($file),
            default => throw new RuntimeException("Unsupported image mime type [{$file->getMimeType()}] for WebP conversion."),
        };

        ob_start();
        imagewebp($image, null, (int) config('media.webp_quality', 82));
        $contents = ob_get_clean();
        imagedestroy($image);

        if ($contents === false) {
            throw new RuntimeException('Unable to encode optimized WebP image.');
        }

        $path = $directory.'/'.Str::uuid().'.webp';

        Storage::disk(static::diskName())->put($path, $contents, ['visibility' => 'public']);

        return $path;
    }

    protected static function loadJpeg(UploadedFile $file)
    {
        $image = imagecreatefromjpeg($file->getRealPath());

        if ($image === false) {
            throw new RuntimeException('Unable to decode JPEG upload.');
        }

        return static::normalizeOrientation($image, $file);
    }

    protected static function loadPng(UploadedFile $file)
    {
        $image = imagecreatefrompng($file->getRealPath());

        if ($image === false) {
            throw new RuntimeException('Unable to decode PNG upload.');
        }

        if (function_exists('imagepalettetotruecolor')) {
            imagepalettetotruecolor($image);
        }

        imagealphablending($image, true);
        imagesavealpha($image, true);

        return $image;
    }

    protected static function normalizeOrientation($image, UploadedFile $file)
    {
        if (! function_exists('exif_read_data')) {
            return $image;
        }

        $metadata = @exif_read_data($file->getRealPath());
        $orientation = (int) ($metadata['Orientation'] ?? 1);

        $rotated = match ($orientation) {
            3 => imagerotate($image, 180, 0),
            6 => imagerotate($image, -90, 0),
            8 => imagerotate($image, 90, 0),
            default => $image,
        };

        return $rotated === false ? $image : $rotated;
    }
}
