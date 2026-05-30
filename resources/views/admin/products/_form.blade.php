@php
    $categoryDefaults = $categories->mapWithKeys(fn ($category) => [
        $category->slug => array_merge(
            \App\Support\SubcategoryFeatures::defaultsForCategory($category->slug),
            \App\Support\SubcategoryFeatures::customFeaturesForCategory($category)
        ),
    ])->all();
    $customFeatureDefinitions = $categories->reduce(function (array $carry, $category) {
        foreach (\App\Support\SubcategoryFeatures::customFeaturesForCategory($category) as $key => $definition) {
            $carry[$key] ??= $definition;
        }

        return $carry;
    }, []);
    $catalogPayload = $categories->map(fn ($category) => [
        'id' => $category->id,
        'slug' => $category->slug,
        'features' => $categoryDefaults[$category->slug] ?? [],
        'subcategories' => $category->subcategories->map(fn ($subcategory) => [
            'id' => $subcategory->id,
            'category_id' => $subcategory->category_id,
            'name' => $subcategory->name,
            'features' => $subcategory->features ?? [],
            'is_active' => (bool) $subcategory->is_active,
        ])->values(),
    ])->values();
    $selectedCategoryId = (int) old('category_id', $product->category_id ?: ($categories->first()?->id));
    $selectedSubcategoryId = (int) old('subcategory_id', $product->subcategory_id);
    $savedCustomFeatures = old('custom_features', $product->attributes['custom_features'] ?? []);
@endphp

<div class="form-grid" data-admin-product-form>
    <label>Categorie
        <select name="category_id" id="admin-category-select" required>
            @foreach($categories as $category)
                <option value="{{ $category->id }}" data-slug="{{ $category->slug }}" @selected($selectedCategoryId === $category->id)>
                    {{ $category->icon }} {{ $category->name }}
                </option>
            @endforeach
        </select>
    </label>

    <label>Subcategorie
        <select name="subcategory_id" id="admin-subcategory-select">
            <option value="">Fara subcategorie</option>
            @foreach($categories as $category)
                @foreach($category->subcategories as $subcategory)
                    <option value="{{ $subcategory->id }}" data-category-id="{{ $category->id }}" @selected($selectedSubcategoryId === $subcategory->id)>
                        {{ $subcategory->icon }} {{ $subcategory->name }}
                    </option>
                @endforeach
            @endforeach
        </select>
        <small>Se afiseaza doar subcategoriile categoriei alese.</small>
    </label>

    <label>Nume
        <input name="name" value="{{ old('name', $product->name) }}" required maxlength="160">
    </label>

    <label>Pret MDL
        <input name="price" type="number" step="0.01" min="0" value="{{ old('price', $product->price ?? 0) }}" required>
    </label>

    <label>Stoc
        <input name="stock" type="number" min="0" value="{{ old('stock', $product->stock ?? 100) }}">
    </label>

    <label data-product-field="size">Marime
        <input name="size" value="{{ old('size', $product->size) }}" placeholder="XS, S, M, L, XL, XXL / A5 / universal">
    </label>

    <label data-product-field="color">Culoare
        <input name="color" value="{{ old('color', $product->color) }}" placeholder="alb, negru, albastru">
    </label>

    <label data-product-field="type">Tip produs
        <input name="type" value="{{ old('type', $product->type) }}" placeholder="simpla / termo / pix / caiet">
    </label>

    <label data-product-field="dimensions">Dimensiuni
        <input name="dimensions" value="{{ old('dimensions', $product->dimensions) }}" placeholder="X x Y / A5 / 85 x 200 cm">
    </label>

    <label data-product-field="volume">Volum cana
        <input name="volume" value="{{ old('volume', $product->volume ?? '250ML') }}" placeholder="250ML">
    </label>

    <label class="full-field">Descriere
        <textarea name="description" rows="5">{{ old('description', $product->description) }}</textarea>
    </label>

    @foreach($customFeatureDefinitions as $featureKey => $featureDefinition)
        <label data-product-custom-feature="{{ $featureKey }}">
            {{ $featureDefinition['label'] ?? \Illuminate\Support\Str::headline(\Illuminate\Support\Str::after($featureKey, 'custom_')) }}
            <input
                name="custom_features[{{ $featureKey }}]"
                value="{{ $savedCustomFeatures[$featureKey] ?? '' }}"
                placeholder="Valoare pentru filtrare"
                maxlength="160"
            >
        </label>
    @endforeach

    <label class="full-field">Imagini produs (1-4)
        <input name="images[]" type="file" accept="image/*" multiple>
        <small>Se accepta jpg, jpeg, png, webp. Maxim 4 imagini, 20MB fiecare. A doua imagine poate fi folosita ca spate pentru haine.</small>
    </label>

    @if($product->exists && $product->images->isNotEmpty())
        <div class="full-field current-images">
            <strong>Imagini actuale</strong>
            <div>
                @foreach($product->images as $image)
                    <img src="{{ \App\Support\StoredImage::url($image->path) }}" alt="Imagine produs">
                @endforeach
            </div>
            <label class="inline-check">
                <input name="replace_images" value="1" type="checkbox">
                Sterge imaginile vechi cand incarc imagini noi
            </label>
        </div>
    @endif

    <label class="inline-check full-field">
        <input name="is_active" value="1" type="checkbox" @checked((bool) old('is_active', $product->is_active ?? true))>
        Produs activ in catalog
    </label>
</div>

@push('scripts')
<script>
    window.ReclamAdminCatalog = @json($catalogPayload, JSON_UNESCAPED_UNICODE);
</script>
@endpush
