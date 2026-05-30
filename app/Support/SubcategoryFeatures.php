<?php

namespace App\Support;

use App\Models\Category;
use Illuminate\Support\Str;

class SubcategoryFeatures
{
    public const BUILT_INS = [
        'size' => 'Marime',
        'color' => 'Culoare',
        'type' => 'Tip',
        'dimensions' => 'Dimensiuni',
        'volume' => 'Volum',
        'custom_design' => 'Design',
        'front_back_customizer' => 'Fata/Spate',
        'mug_customizer' => 'Cana 3D',
    ];

    public const BY_CATEGORY = [
        'cancelarie' => ['size', 'color', 'type', 'custom_design'],
        'haine' => ['size', 'color', 'front_back_customizer', 'custom_design'],
        'banere' => ['dimensions', 'custom_design'],
        'cani' => ['type', 'volume', 'color', 'mug_customizer', 'custom_design'],
    ];

    public static function builtIns(): array
    {
        return self::BUILT_INS;
    }

    public static function builtInKeys(): array
    {
        return array_keys(self::BUILT_INS);
    }

    public static function categoryRules(): array
    {
        return self::BY_CATEGORY;
    }

    public static function allowedForCategory(?string $slug): array
    {
        return self::BY_CATEGORY[$slug ?? ''] ?? self::builtInKeys();
    }

    public static function defaultsForCategory(?string $slug): array
    {
        return collect(self::allowedForCategory($slug))
            ->mapWithKeys(fn (string $feature) => [$feature => true])
            ->all();
    }

    public static function customFeaturesForCategory(Category $category): array
    {
        $subcategories = $category->relationLoaded('activeSubcategories')
            ? $category->activeSubcategories
            : $category->subcategories;

        return $subcategories
            ->flatMap(fn ($subcategory) => self::customFeaturesFromMap($subcategory->features ?? []))
            ->reduce(function (array $carry, array $definition): array {
                $key = $definition['key'];
                if (! isset($carry[$key])) {
                    unset($definition['key']);
                    $carry[$key] = $definition;
                }

                return $carry;
            }, []);
    }

    public static function customFeaturesFromMap(array $features): array
    {
        $custom = [];

        foreach ($features as $key => $value) {
            if (! is_string($key) || in_array($key, self::builtInKeys(), true)) {
                continue;
            }

            $definition = is_array($value) ? $value : [];
            $label = trim((string) ($definition['label'] ?? Str::headline(Str::after($key, 'custom_'))));

            if ($label === '') {
                continue;
            }

            $custom[] = [
                'key' => $key,
                'label' => $label,
                'type' => $definition['type'] ?? 'text',
                'custom' => true,
            ];
        }

        return $custom;
    }

    public static function normalizeCustomDefinition(string $label): array
    {
        return [
            'label' => trim($label),
            'type' => 'text',
            'custom' => true,
        ];
    }

    public static function makeCustomKey(string $label, array $takenKeys = []): string
    {
        $base = 'custom_'.(Str::slug($label, '_') ?: 'caracteristica');
        $key = $base;
        $counter = 2;

        while (array_key_exists($key, $takenKeys)) {
            $key = $base.'_'.$counter;
            $counter++;
        }

        return $key;
    }

    public static function labelFor(string $key, mixed $definition = null): string
    {
        if (isset(self::BUILT_INS[$key])) {
            return self::BUILT_INS[$key];
        }

        if (is_array($definition) && filled($definition['label'] ?? null)) {
            return (string) $definition['label'];
        }

        return Str::headline(Str::after($key, 'custom_'));
    }
}
