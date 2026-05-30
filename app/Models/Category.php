<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Category extends Model
{
    use HasFactory;

    public const CAROUSEL_POSITIONS = [
        'bottom-left',
        'top-left',
        'bottom-right',
        'top-right',
        'top-center',
        'bottom-center',
        'middle-right',
        'middle-left',
        'center',
    ];

    public const CAROUSEL_LANGUAGES = ['ro', 'ru', 'en'];

    protected $fillable = [
        'name',
        'slug',
        'icon',
        'description',
        'is_active',
        'show_in_carousel',
        'carousel_image',
        'carousel_image_ro',
        'carousel_image_ru',
        'carousel_image_en',
        'carousel_title',
        'carousel_label',
        'carousel_text',
        'carousel_text_position',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'show_in_carousel' => 'boolean',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public static function makeUniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name) ?: 'categorie';
        $slug = $base;
        $counter = 2;

        while (static::query()
            ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
            ->where('slug', $slug)
            ->exists()) {
            $slug = $base.'-'.$counter;
            $counter++;
        }

        return $slug;
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function activeProducts(): HasMany
    {
        return $this->products()->where('is_active', true);
    }

    public function subcategories(): HasMany
    {
        return $this->hasMany(Subcategory::class)->orderBy('name');
    }

    public function activeSubcategories(): HasMany
    {
        return $this->subcategories()->where('is_active', true);
    }

    public function carouselImagePath(string $language = 'ro'): string
    {
        $images = $this->localizedCarouselImages();
        $language = in_array($language, self::CAROUSEL_LANGUAGES, true) ? $language : 'ro';

        return $images[$language] ?? $images['ro'];
    }

    public function localizedCarouselImages(): array
    {
        $legacy = $this->cleanCarouselPath($this->carousel_image);
        $ro = $this->cleanCarouselPath($this->carousel_image_ro) ?: $legacy;
        $ru = $this->cleanCarouselPath($this->carousel_image_ru) ?: $ro ?: $legacy;
        $en = $this->cleanCarouselPath($this->carousel_image_en) ?: $ro ?: $legacy;
        $fallback = 'images/carousel/RO/RO_Rechi.png';

        return [
            'ro' => $ro ?: $fallback,
            'ru' => $ru ?: $ro ?: $fallback,
            'en' => $en ?: $ro ?: $fallback,
        ];
    }

    public function hasCarouselImages(): bool
    {
        foreach (['carousel_image', 'carousel_image_ro', 'carousel_image_ru', 'carousel_image_en'] as $field) {
            if (filled($this->{$field})) {
                return true;
            }
        }

        return false;
    }

    public function carouselTitle(): string
    {
        return filled($this->carousel_title) ? (string) $this->carousel_title : (string) $this->name;
    }

    public function carouselLabel(): string
    {
        return filled($this->carousel_label) ? (string) $this->carousel_label : 'ReclamDesign Modern';
    }

    public function carouselText(): string
    {
        return filled($this->carousel_text) ? (string) $this->carousel_text : (string) ($this->description ?? '');
    }

    public function carouselPosition(): string
    {
        $position = (string) ($this->carousel_text_position ?: 'bottom-left');

        return in_array($position, self::CAROUSEL_POSITIONS, true) ? $position : 'bottom-left';
    }

    private function cleanCarouselPath(?string $path): ?string
    {
        $path = trim((string) $path);

        return $path === '' ? null : ltrim($path, '/');
    }
}
