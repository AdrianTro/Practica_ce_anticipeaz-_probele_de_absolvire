@extends('layouts.app')

@section('title', 'ReclamDesign Modern')

@section('content')
<section class="hero section-shell">
    <div class="hero-carousel" id="hero-carousel">
        <div class="slides">
            @foreach($carousel as $index => $slide)
                <a class="slide {{ $index === 0 ? 'active' : '' }}" href="{{ route('categories.show', $slide['category']) }}" data-slide="{{ $index }}">
                    <img
                        src="{{ \App\Support\StoredImage::url($slide['image']) }}"
                        data-carousel-image
                        data-image-ro="{{ \App\Support\StoredImage::url($slide['images']['ro'] ?? $slide['image']) }}"
                        data-image-ru="{{ \App\Support\StoredImage::url($slide['images']['ru'] ?? $slide['image']) }}"
                        data-image-en="{{ \App\Support\StoredImage::url($slide['images']['en'] ?? $slide['image']) }}"
                        alt="{{ $slide['title'] }}"
                    >
                    <div class="slide-caption slide-caption-{{ $slide['text_position'] ?? 'bottom-left' }}">
                        <span>{{ $slide['label'] ?? 'ReclamDesign Modern' }}</span>
                        <h1>{{ $slide['title'] }}</h1>
                        <p>{{ $slide['text'] }}</p>
                    </div>
                </a>
            @endforeach
        </div>
        <div class="carousel-dots" aria-label="Indicator carusel">
            @foreach($carousel as $index => $slide)
                <button type="button" class="dot {{ $index === 0 ? 'active' : '' }}" data-dot="{{ $index }}" aria-label="Imagine {{ $index + 1 }}"></button>
            @endforeach
        </div>
    </div>
</section>

<section class="section-shell category-section">
    <div class="section-heading compact-title-heading">
        <h2>Catalog rapid</h2>
    </div>
    @php($hasCategoryControls = $categories->count() > 4)
    <div class="category-carousel {{ $hasCategoryControls ? 'has-controls' : '' }}" data-category-carousel>
        @if($hasCategoryControls)
            <button class="category-arrow category-arrow-prev" type="button" data-category-arrow="prev" aria-label="Categorii precedente">‹</button>
        @endif
        <div class="category-grid {{ $hasCategoryControls ? 'category-rail' : '' }}" data-category-rail>
            @foreach($categories as $category)
                <a class="category-tile" href="{{ route('categories.show', $category) }}">
                    <span class="cat-icon">{{ $category->icon }}</span>
                    <strong>{{ $category->name }}</strong>
                    <small>{{ $category->products_count }} produse</small>
                    @if($category->activeSubcategories->isNotEmpty())
                        <em>{{ $category->activeSubcategories->pluck('name')->join(' · ') }}</em>
                    @endif
                </a>
            @endforeach
        </div>
        @if($hasCategoryControls)
            <button class="category-arrow category-arrow-next" type="button" data-category-arrow="next" aria-label="Urmatoarele categorii">›</button>
        @endif
    </div>
</section>

<section class="section-shell products-section">
    <div class="section-heading row-heading products-row-heading">
        <div>
            <h2>Produse recente</h2>
        </div>
        <button class="ghost-link ghost-button" type="button" data-open-catalog-modal>Vezi catalog</button>
    </div>
    <div class="product-grid">
        @forelse($products as $product)
            @include('partials.product-card', ['product' => $product])
        @empty
            <div class="empty-state">Nu exista produse. Intra in admin si adauga produse.</div>
        @endforelse
    </div>
</section>

<div class="catalog-modal" id="catalog-modal" hidden>
    <div class="catalog-modal-card">
        <button class="modal-close" type="button" data-close-catalog-modal aria-label="Inchide">×</button>
        <span class="eyebrow">Catalog</span>
        <h2>Alege categoria</h2>
        <div class="catalog-modal-grid">
            @foreach($categories as $category)
                <article class="catalog-modal-category">
                    <a class="catalog-modal-main" href="{{ route('categories.show', $category) }}">
                        <span>{{ $category->icon }}</span>
                        <strong>{{ $category->name }}</strong>
                    </a>
                    <div class="catalog-modal-subitems">
                        @forelse($category->activeSubcategories as $subcategory)
                            <a href="{{ route('categories.show', [$category, $subcategory->slug]) }}">
                                {{ $subcategory->icon }} {{ $subcategory->name }}
                            </a>
                        @empty
                            <span class="muted">Fara subcategorii.</span>
                        @endforelse
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</div>
@endsection
