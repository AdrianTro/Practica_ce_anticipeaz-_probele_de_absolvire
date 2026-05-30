@extends('layouts.app')

@section('title', $category->name.' | ReclamDesign Modern')

@section('content')
@php
    $activeFilterCount = collect($selectedFilters)
        ->reduce(function ($carry, $value) {
            if (is_array($value)) {
                return $carry + collect($value)->flatten()->count();
            }

            return $carry + (filled($value) ? 1 : 0);
        }, 0);
@endphp

<section class="section-shell category-hero">
    <div>
        <span class="eyebrow">{{ $category->icon }} Categoria</span>
        <h1>{{ $category->name }}</h1>
        <div class="subcategory-pills">
            <a class="subcategory-pill {{ $selectedSubcategory ? '' : 'selected' }}" href="{{ route('categories.show', $category) }}">Toate</a>
            @foreach($subcategories as $subcategory)
                <a class="subcategory-pill {{ $selectedSubcategory?->id === $subcategory->id ? 'selected' : '' }}" href="{{ route('categories.show', [$category, $subcategory->slug]) }}">
                    @if($subcategory->image)
                        <img
                            class="subcategory-thumb"
                            src="{{ \App\Support\StoredImage::url($subcategory->image) }}"
                            alt=""
                            aria-hidden="true"
                        >
                    @else
                        {{ $subcategory->icon }}
                    @endif
                    {{ $subcategory->name }}
                </a>
            @endforeach
        </div>
    </div>
</section>

<section class="section-shell category-layout">
    <aside class="filter-sidebar">
        <div class="filter-card">
            <div class="filter-card-head">
                <div>
                    <span class="eyebrow">Filtre produse</span>
                    <h2>Filtreaza produsele</h2>
                </div>
                @if($activeFilterCount > 0)
                    <span class="filter-badge">{{ $activeFilterCount }} activ{{ $activeFilterCount > 1 ? 'e' : '' }}</span>
                @endif
            </div>

            <form method="GET" action="{{ route('categories.show', $selectedSubcategory ? [$category, $selectedSubcategory->slug] : $category) }}" class="filter-form">
                @if($availableFilters['price']['min'] !== null && $availableFilters['price']['max'] !== null)
                    <details class="filter-group">
                        <summary>Pret</summary>
                        <div class="filter-group-body price-filter-grid">
                            <label>
                                <span>Minim</span>
                                <input type="number" name="min_price" min="0" step="0.01" value="{{ $selectedFilters['min_price'] }}" placeholder="{{ (int) $availableFilters['price']['min'] }}">
                            </label>
                            <label>
                                <span>Maxim</span>
                                <input type="number" name="max_price" min="0" step="0.01" value="{{ $selectedFilters['max_price'] }}" placeholder="{{ (int) $availableFilters['price']['max'] }}">
                            </label>
                        </div>
                    </details>
                @endif

                @if(count($availableFilters['subcategories']))
                    <details class="filter-group">
                        <summary>Subcategoria</summary>
                        <div class="filter-group-body option-list">
                            @foreach($availableFilters['subcategories'] as $filterSubcategory)
                                <label class="option-check">
                                    <input type="checkbox" name="subcategories[]" value="{{ $filterSubcategory->id }}" @checked(in_array($filterSubcategory->id, $selectedFilters['subcategories'], true))>
                                    <span>
                                        @if($filterSubcategory->image)
                                            <img
                                                class="subcategory-thumb tiny"
                                                src="{{ \App\Support\StoredImage::url($filterSubcategory->image) }}"
                                                alt=""
                                                aria-hidden="true"
                                            >
                                        @else
                                            {{ $filterSubcategory->icon }}
                                        @endif
                                        {{ $filterSubcategory->name }}
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </details>
                @endif

                @if(count($availableFilters['colors']))
                    <details class="filter-group">
                        <summary>Culoare</summary>
                        <div class="filter-group-body option-list">
                            @foreach($availableFilters['colors'] as $color)
                                <label class="option-check">
                                    <input type="checkbox" name="colors[]" value="{{ $color }}" @checked(in_array($color, $selectedFilters['colors'], true))>
                                    <span>{{ $color }}</span>
                                </label>
                            @endforeach
                        </div>
                    </details>
                @endif

                @if(count($availableFilters['sizes']))
                    <details class="filter-group">
                        <summary>Marime</summary>
                        <div class="filter-group-body option-list">
                            @foreach($availableFilters['sizes'] as $size)
                                <label class="option-check">
                                    <input type="checkbox" name="sizes[]" value="{{ $size }}" @checked(in_array($size, $selectedFilters['sizes'], true))>
                                    <span>{{ $size }}</span>
                                </label>
                            @endforeach
                        </div>
                    </details>
                @endif

                @admin
                    <details class="filter-group">
                        <summary>Stoc</summary>
                        <div class="filter-group-body option-list compact-list">
                            <label>
                                <select name="stock">
                                    <option value="">Toate</option>
                                    @foreach($availableFilters['stock'] as $stockValue => $stockLabel)
                                        <option value="{{ $stockValue }}" @selected($selectedFilters['stock'] === $stockValue)>{{ $stockLabel }}</option>
                                    @endforeach
                                </select>
                            </label>
                        </div>
                    </details>
                @endadmin

                @if(count($availableFilters['types']))
                    <details class="filter-group">
                        <summary>Tip</summary>
                        <div class="filter-group-body option-list">
                            @foreach($availableFilters['types'] as $type)
                                <label class="option-check">
                                    <input type="checkbox" name="types[]" value="{{ $type }}" @checked(in_array($type, $selectedFilters['types'], true))>
                                    <span>{{ $type }}</span>
                                </label>
                            @endforeach
                        </div>
                    </details>
                @endif

                @if(count($availableFilters['dimensions']))
                    <details class="filter-group">
                        <summary>Dimensiuni</summary>
                        <div class="filter-group-body option-list">
                            @foreach($availableFilters['dimensions'] as $dimensions)
                                <label class="option-check">
                                    <input type="checkbox" name="dimensions[]" value="{{ $dimensions }}" @checked(in_array($dimensions, $selectedFilters['dimensions'], true))>
                                    <span>{{ $dimensions }}</span>
                                </label>
                            @endforeach
                        </div>
                    </details>
                @endif

                @if(count($availableFilters['volumes']))
                    <details class="filter-group">
                        <summary>Volum</summary>
                        <div class="filter-group-body option-list">
                            @foreach($availableFilters['volumes'] as $volume)
                                <label class="option-check">
                                    <input type="checkbox" name="volumes[]" value="{{ $volume }}" @checked(in_array($volume, $selectedFilters['volumes'], true))>
                                    <span>{{ $volume }}</span>
                                </label>
                            @endforeach
                        </div>
                    </details>
                @endif

                @foreach($availableFilters['custom'] as $featureKey => $filter)
                    <details class="filter-group">
                        <summary>{{ $filter['label'] }}</summary>
                        <div class="filter-group-body option-list">
                            @forelse($filter['values'] as $value)
                                <label class="option-check">
                                    <input type="checkbox" name="custom[{{ $featureKey }}][]" value="{{ $value }}" @checked(in_array($value, $selectedFilters['custom'][$featureKey] ?? [], true))>
                                    <span>{{ $value }}</span>
                                </label>
                            @empty
                                <p class="muted">Adauga valori la produse pentru a filtra dupa aceasta caracteristica.</p>
                            @endforelse
                        </div>
                    </details>
                @endforeach

                <div class="filter-actions">
                    <button class="primary-btn wide" type="submit">Aplica filtrele</button>
                    <a class="secondary-btn wide" href="{{ route('categories.show', $selectedSubcategory ? [$category, $selectedSubcategory->slug] : $category) }}">Reseteaza</a>
                </div>
            </form>
        </div>
    </aside>

    <div class="category-results">
        <div class="product-grid">
            @forelse($products as $product)
                @include('partials.product-card', ['product' => $product])
            @empty
                <div class="empty-state">Nu exista produse in aceasta selectie.</div>
            @endforelse
        </div>

        <div class="pagination-wrap">
            {{ $products->links() }}
        </div>
    </div>
</section>
@endsection
