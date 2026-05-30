<?php
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
?>
<article class="product-card" data-product="<?php echo e(e(json_encode($payload, JSON_UNESCAPED_UNICODE))); ?>" data-product-url="<?php echo e(route('products.show', $product)); ?>" role="link" tabindex="0">
    <?php if (\Illuminate\Support\Facades\Blade::check('admin')): ?>
        <div class="card-admin-actions">
            <a class="card-admin-icon edit" title="Modifica" href="<?php echo e(route('admin.products.edit', $product)); ?>">✏️</a>
            <form method="POST" action="<?php echo e(route('admin.products.destroy', $product)); ?>" data-confirm="Stergi produsul <?php echo e($product->name); ?>?">
                <?php echo csrf_field(); ?>
                <?php echo method_field('DELETE'); ?>
                <button class="card-admin-icon delete" title="Sterge" type="submit">🗑️</button>
            </form>
        </div>
    <?php endif; ?>
    <div class="product-image">
        <img
            src="<?php echo e(\App\Support\StoredImage::url($imagePath)); ?>"
            <?php if($hasLocalizedCarouselImage): ?>
                data-carousel-image
                data-image-ro="<?php echo e(\App\Support\StoredImage::url($carouselImages['ro'])); ?>"
                data-image-ru="<?php echo e(\App\Support\StoredImage::url($carouselImages['ru'])); ?>"
                data-image-en="<?php echo e(\App\Support\StoredImage::url($carouselImages['en'])); ?>"
            <?php endif; ?>
            alt="<?php echo e($product->name); ?>"
        >
        <?php if (\Illuminate\Support\Facades\Blade::check('admin')): ?>
            <span class="stock-pill">Stoc <?php echo e($product->stock); ?></span>
        <?php endif; ?>
    </div>
    <div class="product-info">
        <span class="product-category">
            <?php echo e($product->category?->name); ?><?php if($product->subcategory): ?> / <?php echo e($product->subcategory->name); ?><?php endif; ?>
        </span>
        <h3><?php echo e($product->name); ?></h3>
        <?php if($cardFeatures->isNotEmpty()): ?>
            <div class="product-feature-pills" aria-label="Caracteristici produs">
                <?php $__currentLoopData = $cardFeatures; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $feature): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <span><?php echo e($feature); ?></span>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        <?php endif; ?>
        <div class="product-bottom">
            <strong><?php echo e(number_format((float) $product->price, 2)); ?> MDL</strong>
            <button class="add-cart" type="button" data-add-cart aria-label="Adauga in cos">🛒 +</button>
        </div>
    </div>
</article>
<?php /**PATH C:\Users\routu\OneDrive\Рабочий стол\Stable_Optimizate\resources\views/partials/product-card.blade.php ENDPATH**/ ?>