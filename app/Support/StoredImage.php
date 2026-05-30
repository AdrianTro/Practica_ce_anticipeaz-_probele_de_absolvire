<?php

namespace App\Support;

use Illuminate\Support\Str;

class StoredImage
{
    public static function url(?string $path, string $fallback = 'images/carousel/RO/RO_Rechi.png'): string
    {
        $path = trim((string) $path);

        if ($path === '') {
            $path = $fallback;
        }

        if (Str::startsWith($path, ['http://', 'https://', 'data:image/'])) {
            return $path;
        }

        return asset(ltrim($path, '/'));
    }

    public static function isRemote(?string $path): bool
    {
        return Str::startsWith(trim((string) $path), ['http://', 'https://']);
    }
}
