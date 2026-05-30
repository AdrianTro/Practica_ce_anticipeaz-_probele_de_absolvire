<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'subcategory_id',
        'name',
        'slug',
        'price',
        'stock',
        'size',
        'color',
        'description',
        'type',
        'dimensions',
        'volume',
        'attributes',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'stock' => 'integer',
        'attributes' => 'array',
        'is_active' => 'boolean',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    protected static function booted(): void
    {
        static::creating(function (Product $product): void {
            if (blank($product->slug)) {
                $product->slug = static::makeUniqueSlug($product->name);
            }
        });

        static::updating(function (Product $product): void {
            if ($product->isDirty('name') && blank($product->slug)) {
                $product->slug = static::makeUniqueSlug($product->name, $product->id);
            }
        });
    }

    public static function makeUniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name) ?: 'produs';
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

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function subcategory(): BelongsTo
    {
        return $this->belongsTo(Subcategory::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

 public function isShirtProduct(): bool
{
    $categoryKey = Str::slug((string) ($this->category?->slug ?: $this->category?->name));
    $subcategoryKey = Str::slug((string) ($this->subcategory?->slug ?: $this->subcategory?->name));
    $productKey = Str::slug((string) ($this->slug ?: $this->name));

    return $categoryKey === 'haine'
        && (
            str_contains($subcategoryKey, 'tricou')
            || str_contains($productKey, 'tricou')
        );
}

    public function mainImagePath(): string
    {
        if ($this->isShirtProduct()) {
            return 'assets/IMG_SHIRT/fata.png';
        }

        return $this->images->first()?->path ?? 'images/carousel/RO/RO_Rechi.png';
    }

    public function secondImagePath(): string
    {
        if ($this->isShirtProduct()) {
            return 'assets/IMG_SHIRT/spate.png';
        }

        return $this->images->skip(1)->first()?->path ?? $this->mainImagePath();
    }

    public function isWearableCustomizable(): bool
    {
        return $this->category?->slug === 'haine'
            || (bool) data_get($this->subcategory?->features ?? [], 'front_back_customizer', false);
    }

    public function isMugCustomizable(): bool
    {
        return $this->category?->slug === 'cani'
            || (bool) data_get($this->subcategory?->features ?? [], 'mug_customizer', false);
    }

    public function supportsCustomDesign(): bool
    {
        return $this->isWearableCustomizable()
            || $this->isMugCustomizable()
            || (bool) data_get($this->subcategory?->features ?? [], 'custom_design', false);
    }

    public function localizedCarouselImages(?string $path = null): array
    {
        $path = ltrim((string) ($path ?: $this->mainImagePath()), '/');

        $sets = [
            'rechizite' => [
                'ro' => 'images/carousel/RO/RO_Rechi.png',
                'ru' => 'images/carousel/RU/RU_NEOB.png',
                'en' => 'images/carousel/EN/EN_NECES.png',
            ],
            'tricou' => [
                'ro' => 'images/carousel/RO/RO_Tricou.png',
                'ru' => 'images/carousel/RU/RU_Futb.png',
                'en' => 'images/carousel/EN/EN_TShir.png',
            ],
            'cana' => [
                'ro' => 'images/carousel/RO/RO_Cana.png',
                'ru' => 'images/carousel/RU/RU_Stili.png',
                'en' => 'images/carousel/EN/EN_MUG.png',
            ],
            'banere' => [
                'ro' => 'images/carousel/RO/RO_Banere.png',
                'ru' => 'images/carousel/RU/RU_Zacaz.png',
                'en' => 'images/carousel/EN/EN_Order.png',
            ],
        ];

        $key = $this->carouselImageKey($path);

        return $key && isset($sets[$key])
            ? $sets[$key]
            : ['ro' => $path, 'ru' => $path, 'en' => $path];
    }

    private function carouselImageKey(string $path): ?string
    {
        if (! Str::startsWith($path, 'images/carousel/')) {
            return null;
        }

        $path = Str::lower($path);

        return match (true) {
            str_contains($path, 'rechi'),
            str_contains($path, 'rechiz'),
            str_contains($path, 'neob'),
            str_contains($path, 'neces') => 'rechizite',
            str_contains($path, 'tricou'),
            str_contains($path, 'tshir'),
            str_contains($path, 'futb') => 'tricou',
            str_contains($path, 'cana'),
            str_contains($path, 'mug'),
            str_contains($path, 'stili') => 'cana',
            str_contains($path, 'banere'),
            str_contains($path, 'order'),
            str_contains($path, 'zacaz') => 'banere',
            default => null,
        };
    }

    public function wearablePrintAreas(): array
    {
        $fallback = $this->isShirtProduct()
            ? [
                'front' => ['x' => 26, 'y' => 22, 'width' => 48, 'height' => 66],
                'back' => ['x' => 32, 'y' => 16, 'width' => 36, 'height' => 73],
            ]
            : [
                'front' => ['x' => 14, 'y' => 14, 'width' => 72, 'height' => 76],
                'back' => ['x' => 14, 'y' => 14, 'width' => 72, 'height' => 76],
            ];

        $attributes = $this->getAttribute('attributes');
        $customAreas = is_array($attributes) ? ($attributes['print_areas'] ?? null) : null;

        if (! is_array($customAreas)) {
            return $fallback;
        }

        return [
            'front' => $this->normalizePrintArea($customAreas['front'] ?? null, $fallback['front']),
            'back' => $this->normalizePrintArea($customAreas['back'] ?? null, $fallback['back']),
        ];
    }

    private function normalizePrintArea(mixed $area, array $fallback): array
    {
        if (! is_array($area)) {
            return $fallback;
        }

        $normalized = [];
        foreach (['x', 'y', 'width', 'height'] as $key) {
            $value = $area[$key] ?? null;
            if (! is_numeric($value)) {
                $normalized[$key] = $fallback[$key];
                continue;
            }

            $number = (float) $value;
            $normalized[$key] = $number > 0 && $number <= 1 ? $number * 100 : $number;
        }

        $normalized['x'] = max(0, min(95, $normalized['x']));
        $normalized['y'] = max(0, min(95, $normalized['y']));
        $normalized['width'] = max(5, min(100 - $normalized['x'], $normalized['width']));
        $normalized['height'] = max(5, min(100 - $normalized['y'], $normalized['height']));

        return $normalized;
    }
}
