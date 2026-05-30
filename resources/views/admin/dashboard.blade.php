@extends('layouts.app')

@section('title', 'Panou Admin | ReclamDesign Modern')

@section('content')
    <section class="section-shell admin-page">
        <div class="admin-header">
            <div>
                <span class="eyebrow">Admin</span>
                <h1>Panou de administrare</h1>
                
            </div>
            <form method="POST" action="{{ route('admin.logout') }}">
                @csrf
                <button class="secondary-btn" type="submit">Logout</button>
            </form>
        </div>

        <div class="stats-grid">
            <div class="stat-card"><span>Comenzi</span><strong>{{ $stats['orders'] }}</strong></div>
            <div class="stat-card"><span>Produse</span><strong>{{ $stats['products'] }}</strong></div>
            <div class="stat-card"><span>Categorii</span><strong>{{ $stats['categories'] }}</strong></div>
            <div class="stat-card"><span>Subcategorii</span><strong>{{ $stats['subcategories'] }}</strong></div>
            <div class="stat-card"><span>Total vanzari</span><strong>{{ number_format((float) $stats['revenue'], 2) }}
                    MDL</strong></div>
        </div>


            <div class="category-wizard-modal" id="category-wizard-modal" hidden>
                <div class="subcategory-wizard-dialog category-wizard-dialog" role="dialog" aria-modal="true" aria-labelledby="category-wizard-title">
                    <form class="subcategory-wizard-form category-wizard-form" method="POST" action="{{ route('admin.categories.store') }}" enctype="multipart/form-data" data-admin-category-form>
                        @csrf
                        <div class="wizard-head">
                            <div>
                                <span class="eyebrow">Categorie noua</span>
                                <h3 id="category-wizard-title">Adauga categorie</h3>
                            </div>
                            <button class="modal-close" type="button" data-close-category-modal aria-label="Inchide">&times;</button>
                        </div>

                        <div class="wizard-progress" aria-label="Progres formular categorie">
                            <span class="active" data-category-step-indicator="1">1. Obligatoriu</span>
                            <span data-category-step-indicator="2">2. Carusel optional</span>
                        </div>

                        <div class="wizard-step active" data-category-step="1">
                            <div class="wizard-media-grid">
                                <label>Icon
                                    <input name="icon" placeholder="Ex: 📦 sau C" maxlength="80" data-category-icon>
                                </label>
                                <label>Nume categorie
                                    <input name="name" placeholder="Ex: Cadouri" required maxlength="120" data-category-name>
                                </label>
                            </div>
                            <label class="full-field">Descriere categorie
                                <textarea name="description" rows="4" placeholder="Descriere obligatorie pentru card, catalog si cautare" required data-category-description></textarea>
                            </label>
                            <button class="secondary-btn generate-description-btn" type="button" data-generate-category-description>Genereaza descriere</button>
                        </div>

                        <div class="wizard-step" data-category-step="2" hidden>
                            <div class="wizard-section-title">
                                <h4>Date pentru carusel</h4>
                                <p>Pas optional: daca incarci imagine, titlu sau text, categoria apare automat in caruselul de pe home.</p>
                            </div>
                            <div class="localized-carousel-grid">
                                <fieldset class="localized-carousel-fieldset">
                                    <legend>Imagine RO</legend>
                                    <label>Prin link
                                        <input name="carousel_image_url_ro" type="url" placeholder="https://exemplu.md/carousel-ro.png" data-category-carousel-image-url-ro>
                                    </label>
                                    <label>Din calculator
                                        <input name="carousel_image_upload_ro" type="file" accept="image/*" data-category-carousel-image-upload-ro>
                                    </label>
                                </fieldset>
                                <fieldset class="localized-carousel-fieldset">
                                    <legend>Imagine RU</legend>
                                    <label>Prin link
                                        <input name="carousel_image_url_ru" type="url" placeholder="https://exemplu.md/carousel-ru.png" data-category-carousel-image-url-ru>
                                    </label>
                                    <label>Din calculator
                                        <input name="carousel_image_upload_ru" type="file" accept="image/*" data-category-carousel-image-upload-ru>
                                    </label>
                                </fieldset>
                                <fieldset class="localized-carousel-fieldset">
                                    <legend>Imagine EN</legend>
                                    <label>Prin link
                                        <input name="carousel_image_url_en" type="url" placeholder="https://exemplu.md/carousel-en.png" data-category-carousel-image-url-en>
                                    </label>
                                    <label>Din calculator
                                        <input name="carousel_image_upload_en" type="file" accept="image/*" data-category-carousel-image-upload-en>
                                    </label>
                                </fieldset>
                            </div>
                            <label class="inline-check full-field" data-clear-category-carousel-image-wrap hidden>
                                <input name="clear_carousel_image" value="1" type="checkbox" data-clear-category-carousel-image>
                                Sterge imaginile actuale din carusel
                            </label>
                            <div class="wizard-media-grid">
                                <label>Titlu in carusel
                                    <input name="carousel_title" placeholder="Ex: Cadouri personalizate" maxlength="160" data-category-carousel-title>
                                </label>
                                <label>Eticheta mica
                                    <input name="carousel_label" placeholder="Ex: Noutate" maxlength="80" data-category-carousel-label>
                                </label>
                            </div>
                            <label class="full-field">Descriere in carusel
                                <textarea name="carousel_text" rows="3" placeholder="Text scurt pentru carusel" data-category-carousel-text></textarea>
                            </label>
                            <label>Pozitie text in carusel
                                <select name="carousel_text_position" data-category-carousel-position>
                                    <option value="bottom-left">Stanga jos</option>
                                    <option value="top-left">Stanga sus</option>
                                    <option value="bottom-right">Dreapta jos</option>
                                    <option value="top-right">Dreapta sus</option>
                                    <option value="top-center">Mijloc sus</option>
                                    <option value="bottom-center">Mijloc jos</option>
                                    <option value="middle-right">Mijloc dreapta</option>
                                    <option value="middle-left">Mijloc stanga</option>
                                    <option value="center">Mijloc</option>
                                </select>
                            </label>
                        </div>

                        <div class="wizard-footer">
                            <button class="secondary-btn" type="button" data-category-prev hidden>Inapoi</button>
                            <button class="primary-btn" type="button" data-category-next>Urmatorul pas</button>
                            <button class="primary-btn" type="submit" data-category-finish hidden>Finalizeaza</button>
                        </div>
                    </form>
                </div>
            </div>


        <div class="admin-block">
            <div class="admin-block-head">
                <div>
                    <h2>Comenzi dupa ID</h2>
                    
                </div>
                <form class="order-search-form" method="GET" action="{{ route('admin.dashboard') }}" data-live-order-search>
                    <input name="order" value="{{ $orderSearch }}" placeholder="Ex: 12345" autocomplete="off" data-live-order-search-input>
                    <button class="secondary-btn" type="submit">Caută</button>
                </form>
            </div>
            <div class="admin-order-results" data-order-results>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Client</th>
                            <th>Email</th>
                            <th>Total</th>
                            <th>Promocod</th>
                            <th>Produse</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody data-order-results-body>
                        @forelse($orders as $order)
                            <tr>
                                <td><strong>{{ $order->order_uuid }}</strong></td>
                                <td>{{ $order->customer_name }}</td>
                                <td>{{ $order->customer_email }}</td>
                                <td>{{ number_format((float) $order->total, 2) }} MDL</td>
                                <td>{{ $order->promocode_code ?: '—' }}</td>
                                <td>{{ $order->items_count }}</td>
                                <td>{{ $order->status }}</td>
                                <td><a class="icon-btn" href="{{ route('admin.orders.show', $order) }}">👁️</a></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8">Nu exista comenzi.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="pagination-wrap order-pagination-wrap" data-order-pagination>
                {{ $orders->onEachSide(1)->links() }}
            </div>
            </div>
        </div>

        <div class="admin-block admin-two-columns">
            <div class="admin-promo-column">
                <div class="admin-block-head compact-head">
                    <div>
                        <h2>Promocoduri</h2>
                        
                    </div>
                </div>
                <form class="mini-admin-form" method="POST" action="{{ route('admin.promocodes.store') }}">
                    @csrf
                    <input name="code" placeholder="Nume promocod" required maxlength="60">
                    <input name="discount_percent" type="number" placeholder="% reducere" required min="0.01" max="100"
                        step="0.01">
                    <button class="primary-btn" type="submit">Adauga</button>
                </form>
                <div class="table-wrap small-table">
                    <table>
                        <thead>
                            <tr>
                                <th>Cod</th>
                                <th>%</th>
                                <th>Stare</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($promocodes as $promocode)
                                <tr>
                                    <td><strong>{{ $promocode->code }}</strong></td>
                                    <td>{{ number_format((float) $promocode->discount_percent, 2) }}%</td>
                                    <td>
                                        <span class="status-pill {{ $promocode->is_valid ? 'valid' : 'invalid' }}">
                                            {{ $promocode->is_valid ? 'Valid' : 'Invalid' }}
                                        </span>
                                    </td>
                                    <td>
                                        <form method="POST" action="{{ route('admin.promocodes.toggle', $promocode) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button class="secondary-btn tiny-btn"
                                                type="submit">{{ $promocode->is_active ? 'Dezactivează' : 'Activează' }}</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4">Nu exista promocoduri.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="claims-under-promocodes">
                    <a class="primary-btn claims-dashboard-btn" href="{{ route('admin.claims.index') }}">
                        <span>Pretenții</span>
                        @if($newClaimsCount > 0)
                            <span class="admin-claim-count" aria-label="{{ $newClaimsCount }} pretenții noi">{{ $newClaimsCount }}</span>
                        @endif
                    </a>
                </div>
            </div>

            <div>
                <div class="admin-block-head compact-head admin-category-shortcut-head">
                    <div>
                        <h2>Categorii</h2>
                        
                    </div>
                    <button class="primary-btn" type="button" data-open-category-modal>+ Adauga categorie</button>
                </div>

                <div class="category-list-admin category-list-admin-compact">
                    @foreach($categories as $category)
                        <div class="category-admin-row {{ $category->is_active ? '' : 'inactive' }}">
                            <span>
                                <strong>{{ $category->icon ?: '📦' }} {{ $category->name }}</strong>
                                <small>{{ $category->products_count }} produse · {{ $category->subcategories_count }} subcategorii · {{ $category->show_in_carousel ? 'carusel activ' : 'fara carusel' }}</small>
                            </span>
                            <div class="subcategory-admin-actions">
                                <form method="POST" action="{{ route('admin.categories.toggle', $category) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button class="tiny-btn {{ $category->is_active ? 'secondary-btn' : 'primary-btn' }}" type="submit">
                                        {{ $category->is_active ? 'Dezactivează' : 'Activează' }}
                                    </button>
                                </form>
                                <button class="tiny-btn secondary-btn" type="button"
                                    data-edit-category
                                    data-update-url="{{ route('admin.categories.update', $category) }}"
                                    data-name="{{ $category->name }}"
                                    data-icon="{{ $category->icon }}"
                                    data-description="{{ $category->description }}"
                                    data-carousel-image-url-ro="{{ \Illuminate\Support\Str::startsWith((string) ($category->carousel_image_ro ?: $category->carousel_image), ['http://', 'https://']) ? ($category->carousel_image_ro ?: $category->carousel_image) : '' }}"
                                    data-carousel-image-url-ru="{{ \Illuminate\Support\Str::startsWith((string) $category->carousel_image_ru, ['http://', 'https://']) ? $category->carousel_image_ru : '' }}"
                                    data-carousel-image-url-en="{{ \Illuminate\Support\Str::startsWith((string) $category->carousel_image_en, ['http://', 'https://']) ? $category->carousel_image_en : '' }}"
                                    data-has-carousel-image="{{ $category->hasCarouselImages() ? '1' : '0' }}"
                                    data-carousel-title="{{ $category->carousel_title }}"
                                    data-carousel-label="{{ $category->carousel_label }}"
                                    data-carousel-text="{{ $category->carousel_text }}"
                                    data-carousel-position="{{ $category->carouselPosition() }}">
                                    Modifică
                                </button>
                                <form method="POST" action="{{ route('admin.categories.destroy', $category) }}"
                                    data-confirm="Stergi categoria {{ $category->name }}? Se vor sterge si subcategoriile, produsele si imaginile legate de ea.">
                                    @csrf
                                    @method('DELETE')
                                    <button class="tiny-btn danger-btn" type="submit">Șterge</button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="admin-block-head compact-head subcategory-admin-head">
                    <div>
                        <h2>Subcategorii</h2>
                        
                    </div>
                    <button class="primary-btn" type="button" data-open-subcategory-modal>+ Adauga subcategorie</button>
                </div>

                <div class="subcategory-wizard-modal" id="subcategory-wizard-modal" hidden>
                    <div class="subcategory-wizard-dialog" role="dialog" aria-modal="true" aria-labelledby="subcategory-wizard-title">
                        <form class="subcategory-wizard-form" method="POST" action="{{ route('admin.subcategories.store') }}" enctype="multipart/form-data" data-admin-subcategory-form>
                            @csrf
                            <div class="wizard-head">
                                <div>
                                    <span class="eyebrow">Subcategorie noua</span>
                                    <h3 id="subcategory-wizard-title">Adauga subcategorie</h3>
                                </div>
                                <button class="modal-close" type="button" data-close-subcategory-modal aria-label="Inchide">&times;</button>
                            </div>

                            <div class="wizard-progress" aria-label="Progres formular">
                                <span class="active" data-step-indicator="1">1. Date</span>
                                <span data-step-indicator="2">2. Caracteristici</span>
                            </div>

                            <div class="wizard-step active" data-subcategory-step="1">
                                <label>Categorie
                                    <select name="category_id" required data-subcategory-category-select>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}">
                                                {{ $category->icon }} {{ $category->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </label>

                                <label>Nume subcategorie
                                    <input name="name" placeholder="Ex: Hudi" required maxlength="120" data-subcategory-name>
                                </label>

                                <div class="wizard-media-grid">
                                    <label>Icon
                                        <input name="icon" placeholder="Ex: 👕 sau H" maxlength="80">
                                    </label>
                                    <label>Imagine prin link
                                        <input name="image_url" type="url" placeholder="https://exemplu.md/imagine.png">
                                    </label>
                                    <label class="full-field">Imagine din calculator
                                        <input name="image_upload" type="file" accept="image/*">
                                    </label>
                                </div>

                                <label class="full-field">Descriere
                                    <textarea name="description" rows="4" placeholder="Descriere optionala" data-subcategory-description></textarea>
                                </label>
                                <button class="secondary-btn generate-description-btn" type="button" data-generate-subcategory-description>Genereaza</button>
                            </div>

                            <div class="wizard-step" data-subcategory-step="2" hidden>
                                <div class="wizard-section-title">
                                    <h4>Caracteristici existente</h4>
                                    <p>Selecteaza ce campuri vor fi disponibile pentru aceasta subcategorie.</p>
                                </div>
                                <div class="feature-checks wizard-feature-checks">
                                    @foreach($subcategoryFeatureLabels as $featureKey => $featureLabel)
                                        <label data-subcategory-feature="{{ $featureKey }}">
                                            <input type="checkbox" name="features[]" value="{{ $featureKey }}">
                                            {{ $featureLabel }}
                                        </label>
                                    @endforeach
                                </div>

                                <div class="wizard-section-title">
                                    <h4>Caracteristici deja create pentru categorie</h4>
                                    <p>Acestea provin din alte subcategorii ale categoriei selectate.</p>
                                </div>
                                <div class="feature-checks custom-feature-options" data-existing-custom-feature-list></div>

                                <div class="wizard-section-title">
                                    <h4>Caracteristici noi</h4>
                                    <p>Exemple: Material, Grosime, Model, Suprafata.</p>
                                </div>
                                <div class="custom-feature-builder" data-new-custom-feature-list>
                                    <label>
                                        Nume caracteristica
                                        <input name="custom_features[]" placeholder="Ex: Material" maxlength="80">
                                    </label>
                                </div>
                                <button class="secondary-btn" type="button" data-add-custom-feature>+ Caracteristica noua</button>
                            </div>

                            <div class="wizard-footer">
                                <button class="secondary-btn" type="button" data-subcategory-prev hidden>Inapoi</button>
                                <button class="primary-btn" type="button" data-subcategory-next>Urmatorul pas</button>
                                <button class="primary-btn" type="submit" data-subcategory-finish hidden>Finalizeaza</button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="subcategory-list-admin flat-subcategory-list-admin">
                    @php($allSubcategories = $categories->pluck('subcategories')->flatten())
                    @if($allSubcategories->isEmpty())
                        <div class="subcategory-empty-state muted">Nu exista subcategorii.</div>
                    @else
                        @foreach($allSubcategories as $subcategory)
                            <div class="subcategory-admin-row flat-subcategory-admin-row">
                                <span>
                                    @if($subcategory->image)
                                        <img
                                            class="subcategory-admin-thumb"
                                            src="{{ \App\Support\StoredImage::url($subcategory->image) }}"
                                            alt=""
                                            aria-hidden="true"
                                        >
                                    @else
                                        {{ $subcategory->icon }}
                                    @endif
                                    {{ $subcategory->name }}
                                    <small>({{ $subcategory->products_count }} produse)</small>
                                </span>

                                <div class="subcategory-admin-actions">
                                    <form method="POST" action="{{ route('admin.subcategories.toggle', $subcategory) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button class="tiny-btn {{ $subcategory->is_active ? 'secondary-btn' : 'primary-btn' }}"
                                            type="submit">
                                            {{ $subcategory->is_active ? 'Dezactivează' : 'Activează' }}
                                        </button>
                                    </form>
                                    <button class="tiny-btn secondary-btn" type="button"
                                        data-edit-subcategory
                                        data-update-url="{{ route('admin.subcategories.update', $subcategory) }}"
                                        data-category-id="{{ $subcategory->category_id }}"
                                        data-name="{{ e($subcategory->name) }}"
                                        data-icon="{{ e($subcategory->icon) }}"
                                        data-image-url="{{ \Illuminate\Support\Str::startsWith((string) $subcategory->image, ['http://', 'https://']) ? e($subcategory->image) : '' }}"
                                        data-description="{{ e($subcategory->description) }}"
                                        data-features="{{ e(base64_encode(json_encode($subcategory->features ?? [], JSON_UNESCAPED_UNICODE))) }}">
                                        Modifică
                                    </button>
                                    <form method="POST" action="{{ route('admin.subcategories.destroy', $subcategory) }}"
                                        data-confirm="Stergi subcategoria {{ $subcategory->name }}? Produsele vor ramane fara aceasta subcategorie.">
                                        @csrf
                                        @method('DELETE')
                                        <button class="tiny-btn danger-btn" type="submit">Șterge</button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>

        <div class="admin-block admin-products-block" id="admin-products">
            <div class="admin-block-head admin-products-head">
                <div class="admin-products-title-row">
                    <h2>Produse</h2>
                    <form class="admin-product-search-form" method="GET" action="{{ route('admin.dashboard') }}#admin-products" data-live-product-search>
                        @if($orderSearch !== '')
                            <input type="hidden" name="order" value="{{ $orderSearch }}">
                        @endif
                        <input
                            id="admin-product-search"
                            name="product"
                            type="search"
                            value="{{ $productSearch }}"
                            placeholder="Cauta produs dupa nume..."
                            aria-label="Cauta produs dupa nume"
                            autocomplete="off"
                            data-live-product-search-input
                        >
                        <button class="secondary-btn" type="submit">Caută</button>
                        @if($productSearch !== '')
                            <a class="secondary-btn product-search-reset" href="{{ route('admin.dashboard') }}#admin-products">Reset</a>
                        @endif
                    </form>
                </div>
                <a class="primary-btn add-product-btn" href="{{ route('admin.products.create') }}">+ Adauga produs</a>
            </div>
            <div class="admin-product-results" data-product-results>
            <div class="admin-product-grid">
                <?php $adminProducts = $products ?? collect(); ?>
                <?php if ($adminProducts->count() === 0) { ?>
                    <div class="empty-state">{{ $productSearch !== '' ? 'Nu exista produse cu acest nume.' : 'Nu exista produse.' }}</div>
                <?php } else { ?>
                    <?php foreach ($adminProducts as $product) { ?>
                        <?php
                            $image = $product->images->first()?->path ?? 'images/carousel/RO/RO_Rechi.png';
                            $carouselImages = $product->localizedCarouselImages($image);
                            $hasLocalizedCarouselImage = count(array_unique($carouselImages)) > 1;
                            $categoryPath = trim(($product->category?->name ?? 'Fara categorie') . ($product->subcategory ? ' / ' . $product->subcategory->name : ''));
                        ?>
                        <article class="admin-product-card">
                            <img
                                src="{{ \App\Support\StoredImage::url($image) }}"
                                <?php if ($hasLocalizedCarouselImage) { ?>
                                    data-carousel-image
                                    data-image-ro="{{ \App\Support\StoredImage::url($carouselImages['ro']) }}"
                                    data-image-ru="{{ \App\Support\StoredImage::url($carouselImages['ru']) }}"
                                    data-image-en="{{ \App\Support\StoredImage::url($carouselImages['en']) }}"
                                <?php } ?>
                                alt="{{ $product->name }}"
                            >
                            <div>
                                <span>{{ $categoryPath }}</span>
                                <h3>{{ $product->name }}</h3>
                                <p>{{ number_format((float) $product->price, 2) }} MDL · Stoc {{ $product->stock }}</p>
                            </div>
                            <div class="admin-card-actions">
                                <a class="icon-btn" title="Modifica" aria-label="Modifica produsul" href="{{ route('admin.products.edit', $product) }}">✏️</a>
                                <form method="POST" action="{{ route('admin.products.destroy', $product) }}"
                                    data-confirm="Stergi produsul {{ $product->name }}?">
                                    @csrf
                                    @method('DELETE')
                                    <button class="icon-btn danger-icon" title="Sterge" aria-label="Sterge produsul" type="submit">🗑️</button>
                                </form>
                            </div>
                        </article>
                    <?php } ?>
                <?php } ?>
            </div>
            <?php if ($products->hasPages()) { ?>
                <nav class="pagination-wrap product-pagination-wrap" aria-label="Paginare produse">
                    <div class="product-pagination">
                        <?php if ($products->onFirstPage()) { ?>
                            <span class="page-control disabled" aria-disabled="true">&lsaquo;</span>
                        <?php } else { ?>
                            <a class="page-control" href="{{ $products->previousPageUrl() }}#admin-products">&lsaquo;</a>
                        <?php } ?>

                        <?php
                            $startPage = max(1, $products->currentPage() - 1);
                            $endPage = min($products->lastPage(), $products->currentPage() + 1);
                        ?>

                        <?php if ($startPage > 1) { ?>
                            <a class="page-number" href="{{ $products->url(1) }}#admin-products">1</a>
                            <?php if ($startPage > 2) { ?><span class="page-ellipsis">...</span><?php } ?>
                        <?php } ?>

                        <?php for ($page = $startPage; $page <= $endPage; $page++) { ?>
                            <?php if ($page === $products->currentPage()) { ?>
                                <span class="page-number active" aria-current="page">{{ $page }}</span>
                            <?php } else { ?>
                                <a class="page-number" href="{{ $products->url($page) }}#admin-products">{{ $page }}</a>
                            <?php } ?>
                        <?php } ?>

                        <?php if ($endPage < $products->lastPage()) { ?>
                            <?php if ($endPage < $products->lastPage() - 1) { ?><span class="page-ellipsis">...</span><?php } ?>
                            <a class="page-number" href="{{ $products->url($products->lastPage()) }}#admin-products">{{ $products->lastPage() }}</a>
                        <?php } ?>

                        <?php if ($products->hasMorePages()) { ?>
                            <a class="page-control" href="{{ $products->nextPageUrl() }}#admin-products">&rsaquo;</a>
                        <?php } else { ?>
                            <span class="page-control disabled" aria-disabled="true">&rsaquo;</span>
                        <?php } ?>
                    </div>
                </nav>
            <?php } ?>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
<script>
    window.ReclamSubcategoryWizard = {
        categories: @json($subcategoryWizardCatalog, JSON_UNESCAPED_UNICODE),
    };
</script>
@endpush
