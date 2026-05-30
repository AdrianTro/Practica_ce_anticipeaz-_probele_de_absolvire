<?php $__env->startSection('title', 'Panou Admin | ReclamDesign Modern'); ?>

<?php $__env->startSection('content'); ?>
    <section class="section-shell admin-page">
        <div class="admin-header">
            <div>
                <span class="eyebrow">Admin</span>
                <h1>Panou de administrare</h1>
                
            </div>
            <form method="POST" action="<?php echo e(route('admin.logout')); ?>">
                <?php echo csrf_field(); ?>
                <button class="secondary-btn" type="submit">Logout</button>
            </form>
        </div>

        <div class="stats-grid">
            <div class="stat-card"><span>Comenzi</span><strong><?php echo e($stats['orders']); ?></strong></div>
            <div class="stat-card"><span>Produse</span><strong><?php echo e($stats['products']); ?></strong></div>
            <div class="stat-card"><span>Categorii</span><strong><?php echo e($stats['categories']); ?></strong></div>
            <div class="stat-card"><span>Subcategorii</span><strong><?php echo e($stats['subcategories']); ?></strong></div>
            <div class="stat-card"><span>Total vanzari</span><strong><?php echo e(number_format((float) $stats['revenue'], 2)); ?>

                    MDL</strong></div>
        </div>


            <div class="category-wizard-modal" id="category-wizard-modal" hidden>
                <div class="subcategory-wizard-dialog category-wizard-dialog" role="dialog" aria-modal="true" aria-labelledby="category-wizard-title">
                    <form class="subcategory-wizard-form category-wizard-form" method="POST" action="<?php echo e(route('admin.categories.store')); ?>" enctype="multipart/form-data" data-admin-category-form>
                        <?php echo csrf_field(); ?>
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
                <form class="order-search-form" method="GET" action="<?php echo e(route('admin.dashboard')); ?>" data-live-order-search>
                    <input name="order" value="<?php echo e($orderSearch); ?>" placeholder="Ex: 12345" autocomplete="off" data-live-order-search-input>
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
                        <?php $__empty_1 = true; $__currentLoopData = $orders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td><strong><?php echo e($order->order_uuid); ?></strong></td>
                                <td><?php echo e($order->customer_name); ?></td>
                                <td><?php echo e($order->customer_email); ?></td>
                                <td><?php echo e(number_format((float) $order->total, 2)); ?> MDL</td>
                                <td><?php echo e($order->promocode_code ?: '—'); ?></td>
                                <td><?php echo e($order->items_count); ?></td>
                                <td><?php echo e($order->status); ?></td>
                                <td><a class="icon-btn" href="<?php echo e(route('admin.orders.show', $order)); ?>">👁️</a></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="8">Nu exista comenzi.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="pagination-wrap order-pagination-wrap" data-order-pagination>
                <?php echo e($orders->onEachSide(1)->links()); ?>

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
                <form class="mini-admin-form" method="POST" action="<?php echo e(route('admin.promocodes.store')); ?>">
                    <?php echo csrf_field(); ?>
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
                            <?php $__empty_1 = true; $__currentLoopData = $promocodes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $promocode): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td><strong><?php echo e($promocode->code); ?></strong></td>
                                    <td><?php echo e(number_format((float) $promocode->discount_percent, 2)); ?>%</td>
                                    <td>
                                        <span class="status-pill <?php echo e($promocode->is_valid ? 'valid' : 'invalid'); ?>">
                                            <?php echo e($promocode->is_valid ? 'Valid' : 'Invalid'); ?>

                                        </span>
                                    </td>
                                    <td>
                                        <form method="POST" action="<?php echo e(route('admin.promocodes.toggle', $promocode)); ?>">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('PATCH'); ?>
                                            <button class="secondary-btn tiny-btn"
                                                type="submit"><?php echo e($promocode->is_active ? 'Dezactivează' : 'Activează'); ?></button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="4">Nu exista promocoduri.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <div class="claims-under-promocodes">
                    <a class="primary-btn claims-dashboard-btn" href="<?php echo e(route('admin.claims.index')); ?>">
                        <span>Pretenții</span>
                        <?php if($newClaimsCount > 0): ?>
                            <span class="admin-claim-count" aria-label="<?php echo e($newClaimsCount); ?> pretenții noi"><?php echo e($newClaimsCount); ?></span>
                        <?php endif; ?>
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
                    <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="category-admin-row <?php echo e($category->is_active ? '' : 'inactive'); ?>">
                            <span>
                                <strong><?php echo e($category->icon ?: '📦'); ?> <?php echo e($category->name); ?></strong>
                                <small><?php echo e($category->products_count); ?> produse · <?php echo e($category->subcategories_count); ?> subcategorii · <?php echo e($category->show_in_carousel ? 'carusel activ' : 'fara carusel'); ?></small>
                            </span>
                            <div class="subcategory-admin-actions">
                                <form method="POST" action="<?php echo e(route('admin.categories.toggle', $category)); ?>">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('PATCH'); ?>
                                    <button class="tiny-btn <?php echo e($category->is_active ? 'secondary-btn' : 'primary-btn'); ?>" type="submit">
                                        <?php echo e($category->is_active ? 'Dezactivează' : 'Activează'); ?>

                                    </button>
                                </form>
                                <button class="tiny-btn secondary-btn" type="button"
                                    data-edit-category
                                    data-update-url="<?php echo e(route('admin.categories.update', $category)); ?>"
                                    data-name="<?php echo e($category->name); ?>"
                                    data-icon="<?php echo e($category->icon); ?>"
                                    data-description="<?php echo e($category->description); ?>"
                                    data-carousel-image-url-ro="<?php echo e(\Illuminate\Support\Str::startsWith((string) ($category->carousel_image_ro ?: $category->carousel_image), ['http://', 'https://']) ? ($category->carousel_image_ro ?: $category->carousel_image) : ''); ?>"
                                    data-carousel-image-url-ru="<?php echo e(\Illuminate\Support\Str::startsWith((string) $category->carousel_image_ru, ['http://', 'https://']) ? $category->carousel_image_ru : ''); ?>"
                                    data-carousel-image-url-en="<?php echo e(\Illuminate\Support\Str::startsWith((string) $category->carousel_image_en, ['http://', 'https://']) ? $category->carousel_image_en : ''); ?>"
                                    data-has-carousel-image="<?php echo e($category->hasCarouselImages() ? '1' : '0'); ?>"
                                    data-carousel-title="<?php echo e($category->carousel_title); ?>"
                                    data-carousel-label="<?php echo e($category->carousel_label); ?>"
                                    data-carousel-text="<?php echo e($category->carousel_text); ?>"
                                    data-carousel-position="<?php echo e($category->carouselPosition()); ?>">
                                    Modifică
                                </button>
                                <form method="POST" action="<?php echo e(route('admin.categories.destroy', $category)); ?>"
                                    data-confirm="Stergi categoria <?php echo e($category->name); ?>? Se vor sterge si subcategoriile, produsele si imaginile legate de ea.">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('DELETE'); ?>
                                    <button class="tiny-btn danger-btn" type="submit">Șterge</button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>

                <div class="admin-block-head compact-head subcategory-admin-head">
                    <div>
                        <h2>Subcategorii</h2>
                        
                    </div>
                    <button class="primary-btn" type="button" data-open-subcategory-modal>+ Adauga subcategorie</button>
                </div>

                <div class="subcategory-wizard-modal" id="subcategory-wizard-modal" hidden>
                    <div class="subcategory-wizard-dialog" role="dialog" aria-modal="true" aria-labelledby="subcategory-wizard-title">
                        <form class="subcategory-wizard-form" method="POST" action="<?php echo e(route('admin.subcategories.store')); ?>" enctype="multipart/form-data" data-admin-subcategory-form>
                            <?php echo csrf_field(); ?>
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
                                        <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($category->id); ?>">
                                                <?php echo e($category->icon); ?> <?php echo e($category->name); ?>

                                            </option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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
                                    <?php $__currentLoopData = $subcategoryFeatureLabels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $featureKey => $featureLabel): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <label data-subcategory-feature="<?php echo e($featureKey); ?>">
                                            <input type="checkbox" name="features[]" value="<?php echo e($featureKey); ?>">
                                            <?php echo e($featureLabel); ?>

                                        </label>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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
                    <?php ($allSubcategories = $categories->pluck('subcategories')->flatten()); ?>
                    <?php if($allSubcategories->isEmpty()): ?>
                        <div class="subcategory-empty-state muted">Nu exista subcategorii.</div>
                    <?php else: ?>
                        <?php $__currentLoopData = $allSubcategories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $subcategory): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="subcategory-admin-row flat-subcategory-admin-row">
                                <span>
                                    <?php if($subcategory->image): ?>
                                        <img
                                            class="subcategory-admin-thumb"
                                            src="<?php echo e(\App\Support\StoredImage::url($subcategory->image)); ?>"
                                            alt=""
                                            aria-hidden="true"
                                        >
                                    <?php else: ?>
                                        <?php echo e($subcategory->icon); ?>

                                    <?php endif; ?>
                                    <?php echo e($subcategory->name); ?>

                                    <small>(<?php echo e($subcategory->products_count); ?> produse)</small>
                                </span>

                                <div class="subcategory-admin-actions">
                                    <form method="POST" action="<?php echo e(route('admin.subcategories.toggle', $subcategory)); ?>">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('PATCH'); ?>
                                        <button class="tiny-btn <?php echo e($subcategory->is_active ? 'secondary-btn' : 'primary-btn'); ?>"
                                            type="submit">
                                            <?php echo e($subcategory->is_active ? 'Dezactivează' : 'Activează'); ?>

                                        </button>
                                    </form>
                                    <button class="tiny-btn secondary-btn" type="button"
                                        data-edit-subcategory
                                        data-update-url="<?php echo e(route('admin.subcategories.update', $subcategory)); ?>"
                                        data-category-id="<?php echo e($subcategory->category_id); ?>"
                                        data-name="<?php echo e(e($subcategory->name)); ?>"
                                        data-icon="<?php echo e(e($subcategory->icon)); ?>"
                                        data-image-url="<?php echo e(\Illuminate\Support\Str::startsWith((string) $subcategory->image, ['http://', 'https://']) ? e($subcategory->image) : ''); ?>"
                                        data-description="<?php echo e(e($subcategory->description)); ?>"
                                        data-features="<?php echo e(e(base64_encode(json_encode($subcategory->features ?? [], JSON_UNESCAPED_UNICODE)))); ?>">
                                        Modifică
                                    </button>
                                    <form method="POST" action="<?php echo e(route('admin.subcategories.destroy', $subcategory)); ?>"
                                        data-confirm="Stergi subcategoria <?php echo e($subcategory->name); ?>? Produsele vor ramane fara aceasta subcategorie.">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('DELETE'); ?>
                                        <button class="tiny-btn danger-btn" type="submit">Șterge</button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="admin-block admin-products-block" id="admin-products">
            <div class="admin-block-head admin-products-head">
                <div class="admin-products-title-row">
                    <h2>Produse</h2>
                    <form class="admin-product-search-form" method="GET" action="<?php echo e(route('admin.dashboard')); ?>#admin-products" data-live-product-search>
                        <?php if($orderSearch !== ''): ?>
                            <input type="hidden" name="order" value="<?php echo e($orderSearch); ?>">
                        <?php endif; ?>
                        <input
                            id="admin-product-search"
                            name="product"
                            type="search"
                            value="<?php echo e($productSearch); ?>"
                            placeholder="Cauta produs dupa nume..."
                            aria-label="Cauta produs dupa nume"
                            autocomplete="off"
                            data-live-product-search-input
                        >
                        <button class="secondary-btn" type="submit">Caută</button>
                        <?php if($productSearch !== ''): ?>
                            <a class="secondary-btn product-search-reset" href="<?php echo e(route('admin.dashboard')); ?>#admin-products">Reset</a>
                        <?php endif; ?>
                    </form>
                </div>
                <a class="primary-btn add-product-btn" href="<?php echo e(route('admin.products.create')); ?>">+ Adauga produs</a>
            </div>
            <div class="admin-product-results" data-product-results>
            <div class="admin-product-grid">
                <?php $adminProducts = $products ?? collect(); ?>
                <?php if ($adminProducts->count() === 0) { ?>
                    <div class="empty-state"><?php echo e($productSearch !== '' ? 'Nu exista produse cu acest nume.' : 'Nu exista produse.'); ?></div>
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
                                src="<?php echo e(\App\Support\StoredImage::url($image)); ?>"
                                <?php if ($hasLocalizedCarouselImage) { ?>
                                    data-carousel-image
                                    data-image-ro="<?php echo e(\App\Support\StoredImage::url($carouselImages['ro'])); ?>"
                                    data-image-ru="<?php echo e(\App\Support\StoredImage::url($carouselImages['ru'])); ?>"
                                    data-image-en="<?php echo e(\App\Support\StoredImage::url($carouselImages['en'])); ?>"
                                <?php } ?>
                                alt="<?php echo e($product->name); ?>"
                            >
                            <div>
                                <span><?php echo e($categoryPath); ?></span>
                                <h3><?php echo e($product->name); ?></h3>
                                <p><?php echo e(number_format((float) $product->price, 2)); ?> MDL · Stoc <?php echo e($product->stock); ?></p>
                            </div>
                            <div class="admin-card-actions">
                                <a class="icon-btn" title="Modifica" aria-label="Modifica produsul" href="<?php echo e(route('admin.products.edit', $product)); ?>">✏️</a>
                                <form method="POST" action="<?php echo e(route('admin.products.destroy', $product)); ?>"
                                    data-confirm="Stergi produsul <?php echo e($product->name); ?>?">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('DELETE'); ?>
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
                            <a class="page-control" href="<?php echo e($products->previousPageUrl()); ?>#admin-products">&lsaquo;</a>
                        <?php } ?>

                        <?php
                            $startPage = max(1, $products->currentPage() - 1);
                            $endPage = min($products->lastPage(), $products->currentPage() + 1);
                        ?>

                        <?php if ($startPage > 1) { ?>
                            <a class="page-number" href="<?php echo e($products->url(1)); ?>#admin-products">1</a>
                            <?php if ($startPage > 2) { ?><span class="page-ellipsis">...</span><?php } ?>
                        <?php } ?>

                        <?php for ($page = $startPage; $page <= $endPage; $page++) { ?>
                            <?php if ($page === $products->currentPage()) { ?>
                                <span class="page-number active" aria-current="page"><?php echo e($page); ?></span>
                            <?php } else { ?>
                                <a class="page-number" href="<?php echo e($products->url($page)); ?>#admin-products"><?php echo e($page); ?></a>
                            <?php } ?>
                        <?php } ?>

                        <?php if ($endPage < $products->lastPage()) { ?>
                            <?php if ($endPage < $products->lastPage() - 1) { ?><span class="page-ellipsis">...</span><?php } ?>
                            <a class="page-number" href="<?php echo e($products->url($products->lastPage())); ?>#admin-products"><?php echo e($products->lastPage()); ?></a>
                        <?php } ?>

                        <?php if ($products->hasMorePages()) { ?>
                            <a class="page-control" href="<?php echo e($products->nextPageUrl()); ?>#admin-products">&rsaquo;</a>
                        <?php } else { ?>
                            <span class="page-control disabled" aria-disabled="true">&rsaquo;</span>
                        <?php } ?>
                    </div>
                </nav>
            <?php } ?>
            </div>
        </div>
    </section>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
    window.ReclamSubcategoryWizard = {
        categories: <?php echo json_encode($subcategoryWizardCatalog, JSON_UNESCAPED_UNICODE, 512) ?>,
    };
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\user\Desktop\Optimizat\resources\views/admin/dashboard.blade.php ENDPATH**/ ?>