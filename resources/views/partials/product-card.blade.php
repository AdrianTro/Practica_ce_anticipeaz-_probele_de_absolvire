@php
    $imagePath = $product->mainImagePath();
    $carouselImages = $product->localizedCarouselImages($imagePath);
    $hasLocalizedCarouselImage = count(array_unique($carouselImages)) > 1;
    $payload = [
        'id' => $product->id,
        'name' => $product->name,
        'price' => (float) $product->price,
        'category' => trim(($product->category?->name ?? 'Fara categorie').' / '.($product->subcategory?->name ?? ''), ' /'),
        'image' => \App\Support\StoredImage::url($imagePath),
        'url' => route('products.show', $product),
    ];

    $productFeatureText = mb_strtolower(trim(($product->category?->name ?? '').' '.($product->subcategory?->name ?? '').' '.$product->name));
    $cardFeatures = collect([
        $product->color ? 'Culoare: '.$product->color : null,
        $product->size ? 'Marime: '.$product->size : null,
        $product->dimensions ? 'Dimensiuni: '.$product->dimensions : null,
        $product->volume ? 'Volum: '.$product->volume : null,
        $product->type ? 'Tip: '.$product->type : null,
    ])->filter()->values();

    if (\Illuminate\Support\Str::contains($productFeatureText, ['tricou', 'tricouri', 'hudi', 'haine'])) {
        $cardFeatures->prepend('Accepta imagine sau logo');
    } elseif (\Illuminate\Support\Str::contains($productFeatureText, ['cana', 'cani', 'cană', 'căni'])) {
        $cardFeatures->prepend('Poti pune imagini pe cana');
    } elseif (\Illuminate\Support\Str::contains($productFeatureText, ['baner', 'banner', 'roll-up'])) {
        $cardFeatures->prepend('Print personalizat');
    }

    $cardFeatures = $cardFeatures->unique()->take(3);
@endphp
<article class="product-card" data-product="{{ e(json_encode($payload, JSON_UNESCAPED_UNICODE)) }}" data-product-url="{{ route('products.show', $product) }}" role="link" tabindex="0">
    @admin
        <div class="card-admin-actions">
            <a class="card-admin-icon edit" title="Modifica" href="{{ route('admin.products.edit', $product) }}">✏️</a>
            <form method="POST" action="{{ route('admin.products.destroy', $product) }}" data-confirm="Stergi produsul {{ $product->name }}?">
                @csrf
                @method('DELETE')
                <button class="card-admin-icon delete" title="Sterge" type="submit">🗑️</button>
            </form>
        </div>
    @endadmin
    <div class="product-image">
        <img
            src="{{ \App\Support\StoredImage::url($imagePath) }}"
            @if($hasLocalizedCarouselImage)
                data-carousel-image
                data-image-ro="{{ \App\Support\StoredImage::url($carouselImages['ro']) }}"
                data-image-ru="{{ \App\Support\StoredImage::url($carouselImages['ru']) }}"
                data-image-en="{{ \App\Support\StoredImage::url($carouselImages['en']) }}"
            @endif
            alt="{{ $product->name }}"
        >
        @admin
            <span class="stock-pill">Stoc {{ $product->stock }}</span>
        @endadmin
    </div>
    <div class="product-info">
        <span class="product-category">
            {{ $product->category?->name }}@if($product->subcategory) / {{ $product->subcategory->name }}@endif
        </span>
        <h3>{{ $product->name }}</h3>
        @if($cardFeatures->isNotEmpty())
            <div class="product-feature-pills" aria-label="Caracteristici produs">
                @foreach($cardFeatures as $feature)
                    <span>{{ $feature }}</span>
                @endforeach
            </div>
        @endif
        <div class="product-bottom">
            <strong>{{ number_format((float) $product->price, 2) }} MDL</strong>
            <button class="add-cart" type="button" data-add-cart aria-label="Adauga in cos">🛒 +</button>
        </div>
    </div>
</article>
