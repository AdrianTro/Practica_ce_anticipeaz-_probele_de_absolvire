<?php $__env->startSection('title', $category->name.' | ReclamDesign Modern'); ?>

<?php $__env->startSection('content'); ?>
<?php
    $activeFilterCount = collect($selectedFilters)
        ->reduce(function ($carry, $value) {
            if (is_array($value)) {
                return $carry + collect($value)->flatten()->count();
            }

            return $carry + (filled($value) ? 1 : 0);
        }, 0);
?>

<section class="section-shell category-hero">
    <div>
        <span class="eyebrow"><?php echo e($category->icon); ?> Categoria</span>
        <h1><?php echo e($category->name); ?></h1>
        <div class="subcategory-pills">
            <a class="subcategory-pill <?php echo e($selectedSubcategory ? '' : 'selected'); ?>" href="<?php echo e(route('categories.show', $category)); ?>">Toate</a>
            <?php $__currentLoopData = $subcategories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $subcategory): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <a class="subcategory-pill <?php echo e($selectedSubcategory?->id === $subcategory->id ? 'selected' : ''); ?>" href="<?php echo e(route('categories.show', [$category, $subcategory->slug])); ?>">
                    <?php if($subcategory->image): ?>
                        <img
                            class="subcategory-thumb"
                            src="<?php echo e(\App\Support\StoredImage::url($subcategory->image)); ?>"
                            alt=""
                            aria-hidden="true"
                        >
                    <?php else: ?>
                        <?php echo e($subcategory->icon); ?>

                    <?php endif; ?>
                    <?php echo e($subcategory->name); ?>

                </a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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
                <?php if($activeFilterCount > 0): ?>
                    <span class="filter-badge"><?php echo e($activeFilterCount); ?> activ<?php echo e($activeFilterCount > 1 ? 'e' : ''); ?></span>
                <?php endif; ?>
            </div>

            <form method="GET" action="<?php echo e(route('categories.show', $selectedSubcategory ? [$category, $selectedSubcategory->slug] : $category)); ?>" class="filter-form">
                <?php if($availableFilters['price']['min'] !== null && $availableFilters['price']['max'] !== null): ?>
                    <details class="filter-group">
                        <summary>Pret</summary>
                        <div class="filter-group-body price-filter-grid">
                            <label>
                                <span>Minim</span>
                                <input type="number" name="min_price" min="0" step="0.01" value="<?php echo e($selectedFilters['min_price']); ?>" placeholder="<?php echo e((int) $availableFilters['price']['min']); ?>">
                            </label>
                            <label>
                                <span>Maxim</span>
                                <input type="number" name="max_price" min="0" step="0.01" value="<?php echo e($selectedFilters['max_price']); ?>" placeholder="<?php echo e((int) $availableFilters['price']['max']); ?>">
                            </label>
                        </div>
                    </details>
                <?php endif; ?>

                <?php if(count($availableFilters['subcategories'])): ?>
                    <details class="filter-group">
                        <summary>Subcategoria</summary>
                        <div class="filter-group-body option-list">
                            <?php $__currentLoopData = $availableFilters['subcategories']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $filterSubcategory): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <label class="option-check">
                                    <input type="checkbox" name="subcategories[]" value="<?php echo e($filterSubcategory->id); ?>" <?php if(in_array($filterSubcategory->id, $selectedFilters['subcategories'], true)): echo 'checked'; endif; ?>>
                                    <span>
                                        <?php if($filterSubcategory->image): ?>
                                            <img
                                                class="subcategory-thumb tiny"
                                                src="<?php echo e(\App\Support\StoredImage::url($filterSubcategory->image)); ?>"
                                                alt=""
                                                aria-hidden="true"
                                            >
                                        <?php else: ?>
                                            <?php echo e($filterSubcategory->icon); ?>

                                        <?php endif; ?>
                                        <?php echo e($filterSubcategory->name); ?>

                                    </span>
                                </label>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </details>
                <?php endif; ?>

                <?php if(count($availableFilters['colors'])): ?>
                    <details class="filter-group">
                        <summary>Culoare</summary>
                        <div class="filter-group-body option-list">
                            <?php $__currentLoopData = $availableFilters['colors']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $color): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <label class="option-check">
                                    <input type="checkbox" name="colors[]" value="<?php echo e($color); ?>" <?php if(in_array($color, $selectedFilters['colors'], true)): echo 'checked'; endif; ?>>
                                    <span><?php echo e($color); ?></span>
                                </label>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </details>
                <?php endif; ?>

                <?php if(count($availableFilters['sizes'])): ?>
                    <details class="filter-group">
                        <summary>Marime</summary>
                        <div class="filter-group-body option-list">
                            <?php $__currentLoopData = $availableFilters['sizes']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $size): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <label class="option-check">
                                    <input type="checkbox" name="sizes[]" value="<?php echo e($size); ?>" <?php if(in_array($size, $selectedFilters['sizes'], true)): echo 'checked'; endif; ?>>
                                    <span><?php echo e($size); ?></span>
                                </label>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </details>
                <?php endif; ?>

                <?php if (\Illuminate\Support\Facades\Blade::check('admin')): ?>
                    <details class="filter-group">
                        <summary>Stoc</summary>
                        <div class="filter-group-body option-list compact-list">
                            <label>
                                <select name="stock">
                                    <option value="">Toate</option>
                                    <?php $__currentLoopData = $availableFilters['stock']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stockValue => $stockLabel): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($stockValue); ?>" <?php if($selectedFilters['stock'] === $stockValue): echo 'selected'; endif; ?>><?php echo e($stockLabel); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </label>
                        </div>
                    </details>
                <?php endif; ?>

                <?php if(count($availableFilters['types'])): ?>
                    <details class="filter-group">
                        <summary>Tip</summary>
                        <div class="filter-group-body option-list">
                            <?php $__currentLoopData = $availableFilters['types']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <label class="option-check">
                                    <input type="checkbox" name="types[]" value="<?php echo e($type); ?>" <?php if(in_array($type, $selectedFilters['types'], true)): echo 'checked'; endif; ?>>
                                    <span><?php echo e($type); ?></span>
                                </label>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </details>
                <?php endif; ?>

                <?php if(count($availableFilters['dimensions'])): ?>
                    <details class="filter-group">
                        <summary>Dimensiuni</summary>
                        <div class="filter-group-body option-list">
                            <?php $__currentLoopData = $availableFilters['dimensions']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dimensions): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <label class="option-check">
                                    <input type="checkbox" name="dimensions[]" value="<?php echo e($dimensions); ?>" <?php if(in_array($dimensions, $selectedFilters['dimensions'], true)): echo 'checked'; endif; ?>>
                                    <span><?php echo e($dimensions); ?></span>
                                </label>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </details>
                <?php endif; ?>

                <?php if(count($availableFilters['volumes'])): ?>
                    <details class="filter-group">
                        <summary>Volum</summary>
                        <div class="filter-group-body option-list">
                            <?php $__currentLoopData = $availableFilters['volumes']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $volume): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <label class="option-check">
                                    <input type="checkbox" name="volumes[]" value="<?php echo e($volume); ?>" <?php if(in_array($volume, $selectedFilters['volumes'], true)): echo 'checked'; endif; ?>>
                                    <span><?php echo e($volume); ?></span>
                                </label>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </details>
                <?php endif; ?>

                <?php $__currentLoopData = $availableFilters['custom']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $featureKey => $filter): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <details class="filter-group">
                        <summary><?php echo e($filter['label']); ?></summary>
                        <div class="filter-group-body option-list">
                            <?php $__empty_1 = true; $__currentLoopData = $filter['values']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <label class="option-check">
                                    <input type="checkbox" name="custom[<?php echo e($featureKey); ?>][]" value="<?php echo e($value); ?>" <?php if(in_array($value, $selectedFilters['custom'][$featureKey] ?? [], true)): echo 'checked'; endif; ?>>
                                    <span><?php echo e($value); ?></span>
                                </label>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <p class="muted">Adauga valori la produse pentru a filtra dupa aceasta caracteristica.</p>
                            <?php endif; ?>
                        </div>
                    </details>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                <div class="filter-actions">
                    <button class="primary-btn wide" type="submit">Aplica filtrele</button>
                    <a class="secondary-btn wide" href="<?php echo e(route('categories.show', $selectedSubcategory ? [$category, $selectedSubcategory->slug] : $category)); ?>">Reseteaza</a>
                </div>
            </form>
        </div>
    </aside>

    <div class="category-results">
        <div class="product-grid">
            <?php $__empty_1 = true; $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php echo $__env->make('partials.product-card', ['product' => $product], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="empty-state">Nu exista produse in aceasta selectie.</div>
            <?php endif; ?>
        </div>

        <div class="pagination-wrap">
            <?php echo e($products->links()); ?>

        </div>
    </div>
</section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\user\Desktop\Optimizat\resources\views/products/category.blade.php ENDPATH**/ ?>