<?php $__env->startSection('title', 'ReclamDesign Modern'); ?>

<?php $__env->startSection('content'); ?>
<section class="hero section-shell">
    <div class="hero-carousel" id="hero-carousel">
        <div class="slides">
            <?php $__currentLoopData = $carousel; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $slide): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <a class="slide <?php echo e($index === 0 ? 'active' : ''); ?>" href="<?php echo e(route('categories.show', $slide['category'])); ?>" data-slide="<?php echo e($index); ?>">
                    <img
                        src="<?php echo e(\App\Support\StoredImage::url($slide['image'])); ?>"
                        data-carousel-image
                        data-image-ro="<?php echo e(\App\Support\StoredImage::url($slide['images']['ro'] ?? $slide['image'])); ?>"
                        data-image-ru="<?php echo e(\App\Support\StoredImage::url($slide['images']['ru'] ?? $slide['image'])); ?>"
                        data-image-en="<?php echo e(\App\Support\StoredImage::url($slide['images']['en'] ?? $slide['image'])); ?>"
                        alt="<?php echo e($slide['title']); ?>"
                    >
                    <div class="slide-caption slide-caption-<?php echo e($slide['text_position'] ?? 'bottom-left'); ?>">
                        <span><?php echo e($slide['label'] ?? 'ReclamDesign Modern'); ?></span>
                        <h1><?php echo e($slide['title']); ?></h1>
                        <p><?php echo e($slide['text']); ?></p>
                    </div>
                </a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
        <div class="carousel-dots" aria-label="Indicator carusel">
            <?php $__currentLoopData = $carousel; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $slide): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <button type="button" class="dot <?php echo e($index === 0 ? 'active' : ''); ?>" data-dot="<?php echo e($index); ?>" aria-label="Imagine <?php echo e($index + 1); ?>"></button>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>
</section>

<section class="section-shell category-section">
    <div class="section-heading compact-title-heading">
        <h2>Catalog rapid</h2>
    </div>
    <?php ($hasCategoryControls = $categories->count() > 4); ?>
    <div class="category-carousel <?php echo e($hasCategoryControls ? 'has-controls' : ''); ?>" data-category-carousel>
        <?php if($hasCategoryControls): ?>
            <button class="category-arrow category-arrow-prev" type="button" data-category-arrow="prev" aria-label="Categorii precedente">‹</button>
        <?php endif; ?>
        <div class="category-grid <?php echo e($hasCategoryControls ? 'category-rail' : ''); ?>" data-category-rail>
            <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <a class="category-tile" href="<?php echo e(route('categories.show', $category)); ?>">
                    <span class="cat-icon"><?php echo e($category->icon); ?></span>
                    <strong><?php echo e($category->name); ?></strong>
                    <small><?php echo e($category->products_count); ?> produse</small>
                    <?php if($category->activeSubcategories->isNotEmpty()): ?>
                        <em><?php echo e($category->activeSubcategories->pluck('name')->join(' · ')); ?></em>
                    <?php endif; ?>
                </a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
        <?php if($hasCategoryControls): ?>
            <button class="category-arrow category-arrow-next" type="button" data-category-arrow="next" aria-label="Urmatoarele categorii">›</button>
        <?php endif; ?>
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
        <?php $__empty_1 = true; $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <?php echo $__env->make('partials.product-card', ['product' => $product], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div class="empty-state">Nu exista produse. Intra in admin si adauga produse.</div>
        <?php endif; ?>
    </div>
</section>

<div class="catalog-modal" id="catalog-modal" hidden>
    <div class="catalog-modal-card">
        <button class="modal-close" type="button" data-close-catalog-modal aria-label="Inchide">×</button>
        <span class="eyebrow">Catalog</span>
        <h2>Alege categoria</h2>
        <div class="catalog-modal-grid">
            <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <article class="catalog-modal-category">
                    <a class="catalog-modal-main" href="<?php echo e(route('categories.show', $category)); ?>">
                        <span><?php echo e($category->icon); ?></span>
                        <strong><?php echo e($category->name); ?></strong>
                    </a>
                    <div class="catalog-modal-subitems">
                        <?php $__empty_1 = true; $__currentLoopData = $category->activeSubcategories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $subcategory): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <a href="<?php echo e(route('categories.show', [$category, $subcategory->slug])); ?>">
                                <?php echo e($subcategory->icon); ?> <?php echo e($subcategory->name); ?>

                            </a>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <span class="muted">Fara subcategorii.</span>
                        <?php endif; ?>
                    </div>
                </article>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\routu\OneDrive\Рабочий стол\Stable_Optimizate\resources\views/home.blade.php ENDPATH**/ ?>