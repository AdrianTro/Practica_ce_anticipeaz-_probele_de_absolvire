<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Subcategory;
use App\Support\SubcategoryFeatures;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function category(Request $request, Category $category, ?string $subcategory = null): View
    {
        abort_unless($category->is_active || session()->boolean('is_admin'), 404);

        $category->load(['activeSubcategories' => fn ($query) => $query->withCount('products')->orderBy('name')]);

        $selectedSubcategory = null;
        if ($subcategory) {
            $selectedSubcategory = Subcategory::query()
                ->where('category_id', $category->id)
                ->where('slug', $subcategory)
                ->where('is_active', true)
                ->firstOrFail();
        }

        $baseQuery = Product::query()
            ->with(['category', 'subcategory', 'images'])
            ->where('category_id', $category->id)
            ->when($selectedSubcategory, fn ($query) => $query->where('subcategory_id', $selectedSubcategory->id))
            ->where('is_active', true)
            ->when(! session()->boolean('is_admin'), fn ($query) => $query->whereHas('category', fn ($categoryQuery) => $categoryQuery->where('is_active', true)));

        $isAdmin = session()->boolean('is_admin');
        $filterProducts = (clone $baseQuery)->get();
        $subcategories = $category->activeSubcategories
            ->filter(fn ($item) => $filterProducts->where('subcategory_id', $item->id)->isNotEmpty())
            ->values();
        $customFeatureDefinitions = SubcategoryFeatures::customFeaturesForCategory($category);

        $selectedFilters = [
            'min_price' => $request->filled('min_price') ? (float) $request->query('min_price') : null,
            'max_price' => $request->filled('max_price') ? (float) $request->query('max_price') : null,
            'subcategories' => collect((array) $request->query('subcategories', []))
                ->map(fn ($value) => (int) $value)
                ->filter()
                ->values()
                ->all(),
            'colors' => collect((array) $request->query('colors', []))
                ->map(fn ($value) => trim((string) $value))
                ->filter()
                ->values()
                ->all(),
            'sizes' => collect((array) $request->query('sizes', []))
                ->map(fn ($value) => trim((string) $value))
                ->filter()
                ->values()
                ->all(),
            'types' => collect((array) $request->query('types', []))
                ->map(fn ($value) => trim((string) $value))
                ->filter()
                ->values()
                ->all(),
            'dimensions' => collect((array) $request->query('dimensions', []))
                ->map(fn ($value) => trim((string) $value))
                ->filter()
                ->values()
                ->all(),
            'volumes' => collect((array) $request->query('volumes', []))
                ->map(fn ($value) => trim((string) $value))
                ->filter()
                ->values()
                ->all(),
            'custom' => collect((array) $request->query('custom', []))
                ->map(fn ($values) => collect((array) $values)
                    ->map(fn ($value) => trim((string) $value))
                    ->filter()
                    ->values()
                    ->all())
                ->filter(fn ($values, $key) => preg_match('/^[A-Za-z0-9_]+$/', (string) $key) && count($values))
                ->all(),
            'stock' => $isAdmin ? trim((string) $request->query('stock', '')) : '',
        ];

        $availableFilters = [
            'price' => [
                'min' => $filterProducts->isNotEmpty() ? (float) floor((float) $filterProducts->min('price')) : null,
                'max' => $filterProducts->isNotEmpty() ? (float) ceil((float) $filterProducts->max('price')) : null,
            ],
            'subcategories' => $subcategories,
            'colors' => $this->extractDistinctValues($filterProducts, 'color', true),
            'sizes' => $this->extractDistinctValues($filterProducts, 'size', true),
            'types' => $this->extractDistinctValues($filterProducts, 'type'),
            'dimensions' => $this->extractDistinctValues($filterProducts, 'dimensions'),
            'volumes' => $this->extractDistinctValues($filterProducts, 'volume'),
            'custom' => $this->extractCustomFilters($filterProducts, $customFeatureDefinitions),
            'stock' => [
                'in_stock' => 'In stoc',
                'low_stock' => 'Stoc redus (1-10)',
                'large_stock' => 'Stoc mare (peste 10)',
                'out_of_stock' => 'Fara stoc',
            ],
        ];

        $products = (clone $baseQuery)
            ->when($selectedFilters['min_price'] !== null, fn ($query) => $query->where('price', '>=', $selectedFilters['min_price']))
            ->when($selectedFilters['max_price'] !== null, fn ($query) => $query->where('price', '<=', $selectedFilters['max_price']))
            ->when($selectedFilters['subcategories'], fn ($query) => $query->whereIn('subcategory_id', $selectedFilters['subcategories']))
            ->when($selectedFilters['colors'], fn ($query) => $this->applyLikeFilter($query, 'color', $selectedFilters['colors']))
            ->when($selectedFilters['sizes'], fn ($query) => $this->applyLikeFilter($query, 'size', $selectedFilters['sizes']))
            ->when($selectedFilters['types'], fn ($query) => $query->whereIn('type', $selectedFilters['types']))
            ->when($selectedFilters['dimensions'], fn ($query) => $query->whereIn('dimensions', $selectedFilters['dimensions']))
            ->when($selectedFilters['volumes'], fn ($query) => $query->whereIn('volume', $selectedFilters['volumes']))
            ->when($selectedFilters['custom'], fn ($query) => $this->applyCustomFilters($query, $selectedFilters['custom']))
            ->when($selectedFilters['stock'] === 'in_stock', fn ($query) => $query->where('stock', '>', 0))
            ->when($selectedFilters['stock'] === 'low_stock', fn ($query) => $query->whereBetween('stock', [1, 10]))
            ->when($selectedFilters['stock'] === 'large_stock', fn ($query) => $query->where('stock', '>', 10))
            ->when($selectedFilters['stock'] === 'out_of_stock', fn ($query) => $query->where('stock', '<=', 0))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('products.category', compact(
            'category',
            'products',
            'subcategories',
            'selectedSubcategory',
            'selectedFilters',
            'availableFilters'
        ));
    }

    public function show(Product $product): View
    {
        $product->load(['category', 'subcategory', 'images']);

        abort_unless(($product->is_active && $product->category?->is_active) || session()->boolean('is_admin'), 404);

        $related = Product::query()
            ->with(['category', 'subcategory', 'images'])
            ->where('is_active', true)
            ->whereHas('category', fn ($query) => $query->where('is_active', true))
            ->where('category_id', $product->category_id)
            ->when($product->subcategory_id, fn ($query) => $query->where('subcategory_id', $product->subcategory_id))
            ->whereKeyNot($product->id)
            ->take(4)
            ->get();

        return view('products.show', compact('product', 'related'));
    }

    public function search(Request $request): JsonResponse
    {
        $q = trim((string) $request->query('q', ''));

        if (mb_strlen($q) < 1) {
            return response()->json([]);
        }

        $categoryResults = Category::query()
            ->where('is_active', true)
            ->where(function ($query) use ($q): void {
                $query->where('name', 'like', "%{$q}%")
                    ->orWhere('description', 'like', "%{$q}%");
            })
            ->limit(4)
            ->get()
            ->map(fn (Category $category) => [
                'name' => $category->name,
                'category' => 'Categorie',
                'url' => route('categories.show', $category),
            ]);

        $subcategoryResults = Subcategory::query()
            ->with('category')
            ->where('is_active', true)
            ->whereHas('category', fn ($categoryQuery) => $categoryQuery->where('is_active', true))
            ->where(function ($query) use ($q): void {
                $query->where('name', 'like', "%{$q}%")
                    ->orWhere('description', 'like', "%{$q}%");
            })
            ->limit(4)
            ->get()
            ->map(fn (Subcategory $subcategory) => [
                'name' => $subcategory->name,
                'category' => 'Subcategorie / '.($subcategory->category?->name ?? 'Catalog'),
                'url' => route('categories.show', [$subcategory->category, $subcategory->slug]),
            ]);

        $productResults = Product::query()
            ->with(['category', 'subcategory'])
            ->where('is_active', true)
            ->whereHas('category', fn ($categoryQuery) => $categoryQuery->where('is_active', true))
            ->where(function ($query) use ($q): void {
                $query->where('name', 'like', "%{$q}%")
                    ->orWhereHas('category', fn ($categoryQuery) => $categoryQuery->where('name', 'like', "%{$q}%"))
                    ->orWhereHas('subcategory', fn ($subcategoryQuery) => $subcategoryQuery->where('name', 'like', "%{$q}%"));
            })
            ->limit(10)
            ->get()
            ->map(fn (Product $product) => [
                'name' => $product->name,
                'category' => trim(($product->category?->name ?? 'Fara categorie').' / '.($product->subcategory?->name ?? ''), ' /'),
                'url' => route('products.show', $product),
            ]);

        $results = $categoryResults
            ->merge($subcategoryResults)
            ->merge($productResults)
            ->unique('url')
            ->take(10)
            ->values();

        return response()->json($results);
    }

    private function extractDistinctValues(Collection $products, string $field, bool $split = false): array
    {
        $values = $products
            ->pluck($field)
            ->filter()
            ->flatMap(function ($value) use ($split) {
                if (! $split) {
                    return [trim((string) $value)];
                }

                return collect(preg_split('/[,\/]+/', (string) $value) ?: [])
                    ->map(fn ($item) => trim((string) $item))
                    ->filter();
            })
            ->filter()
            ->unique(fn ($value) => mb_strtolower((string) $value))
            ->sortBy(fn ($value) => mb_strtolower((string) $value), SORT_NATURAL)
            ->values()
            ->all();

        return $values;
    }

    private function applyLikeFilter($query, string $column, array $values)
    {
        return $query->where(function ($innerQuery) use ($column, $values): void {
            foreach ($values as $value) {
                $innerQuery->orWhere($column, 'like', '%'.$value.'%');
            }
        });
    }

    private function extractCustomFilters(Collection $products, array $definitions): array
    {
        return collect($definitions)
            ->map(function (array $definition, string $key) use ($products): array {
                $values = $products
                    ->map(fn (Product $product) => data_get($product->attributes ?? [], "custom_features.{$key}"))
                    ->filter()
                    ->map(fn ($value) => trim((string) $value))
                    ->filter()
                    ->unique(fn ($value) => mb_strtolower((string) $value))
                    ->sortBy(fn ($value) => mb_strtolower((string) $value), SORT_NATURAL)
                    ->values()
                    ->all();

                return [
                    'label' => SubcategoryFeatures::labelFor($key, $definition),
                    'values' => $values,
                ];
            })
            ->all();
    }

    private function applyCustomFilters($query, array $filters)
    {
        foreach ($filters as $key => $values) {
            if (! preg_match('/^[A-Za-z0-9_]+$/', (string) $key)) {
                continue;
            }

            $query->where(function ($innerQuery) use ($key, $values): void {
                foreach ($values as $value) {
                    $innerQuery->orWhere("attributes->custom_features->{$key}", $value);
                }
            });
        }

        return $query;
    }
}
