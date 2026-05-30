<?php
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
?>

<div class="form-grid" data-admin-product-form>
    <label>Categorie
        <select name="category_id" id="admin-category-select" required>
            <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($category->id); ?>" data-slug="<?php echo e($category->slug); ?>" <?php if($selectedCategoryId === $category->id): echo 'selected'; endif; ?>>
                    <?php echo e($category->icon); ?> <?php echo e($category->name); ?>

                </option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
    </label>

    <label>Subcategorie
        <select name="subcategory_id" id="admin-subcategory-select">
            <option value="">Fara subcategorie</option>
            <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php $__currentLoopData = $category->subcategories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $subcategory): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($subcategory->id); ?>" data-category-id="<?php echo e($category->id); ?>" <?php if($selectedSubcategoryId === $subcategory->id): echo 'selected'; endif; ?>>
                        <?php echo e($subcategory->icon); ?> <?php echo e($subcategory->name); ?>

                    </option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
        <small>Se afiseaza doar subcategoriile categoriei alese.</small>
    </label>

    <label>Nume
        <input name="name" value="<?php echo e(old('name', $product->name)); ?>" required maxlength="160">
    </label>

    <label>Pret MDL
        <input name="price" type="number" step="0.01" min="0" value="<?php echo e(old('price', $product->price ?? 0)); ?>" required>
    </label>

    <label>Stoc
        <input name="stock" type="number" min="0" value="<?php echo e(old('stock', $product->stock ?? 100)); ?>">
    </label>

    <label data-product-field="size">Marime
        <input name="size" value="<?php echo e(old('size', $product->size)); ?>" placeholder="XS, S, M, L, XL, XXL / A5 / universal">
    </label>

    <label data-product-field="color">Culoare
        <input name="color" value="<?php echo e(old('color', $product->color)); ?>" placeholder="alb, negru, albastru">
    </label>

    <label data-product-field="type">Tip produs
        <input name="type" value="<?php echo e(old('type', $product->type)); ?>" placeholder="simpla / termo / pix / caiet">
    </label>

    <label data-product-field="dimensions">Dimensiuni
        <input name="dimensions" value="<?php echo e(old('dimensions', $product->dimensions)); ?>" placeholder="X x Y / A5 / 85 x 200 cm">
    </label>

    <label data-product-field="volume">Volum cana
        <input name="volume" value="<?php echo e(old('volume', $product->volume ?? '250ML')); ?>" placeholder="250ML">
    </label>

    <label class="full-field">Descriere
        <textarea name="description" rows="5"><?php echo e(old('description', $product->description)); ?></textarea>
    </label>

    <?php $__currentLoopData = $customFeatureDefinitions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $featureKey => $featureDefinition): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <label data-product-custom-feature="<?php echo e($featureKey); ?>">
            <?php echo e($featureDefinition['label'] ?? \Illuminate\Support\Str::headline(\Illuminate\Support\Str::after($featureKey, 'custom_'))); ?>

            <input
                name="custom_features[<?php echo e($featureKey); ?>]"
                value="<?php echo e($savedCustomFeatures[$featureKey] ?? ''); ?>"
                placeholder="Valoare pentru filtrare"
                maxlength="160"
            >
        </label>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

    <label class="full-field">Imagini produs (1-4)
        <input name="images[]" type="file" accept="image/*" multiple>
        <small>Se accepta jpg, jpeg, png, webp. Maxim 4 imagini, 20MB fiecare. A doua imagine poate fi folosita ca spate pentru haine.</small>
    </label>

    <?php if($product->exists && $product->images->isNotEmpty()): ?>
        <div class="full-field current-images">
            <strong>Imagini actuale</strong>
            <div>
                <?php $__currentLoopData = $product->images; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $image): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <img src="<?php echo e(\App\Support\StoredImage::url($image->path)); ?>" alt="Imagine produs">
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
            <label class="inline-check">
                <input name="replace_images" value="1" type="checkbox">
                Sterge imaginile vechi cand incarc imagini noi
            </label>
        </div>
    <?php endif; ?>

    <label class="inline-check full-field">
        <input name="is_active" value="1" type="checkbox" <?php if((bool) old('is_active', $product->is_active ?? true)): echo 'checked'; endif; ?>>
        Produs activ in catalog
    </label>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
    window.ReclamAdminCatalog = <?php echo json_encode($catalogPayload, JSON_UNESCAPED_UNICODE, 512) ?>;
</script>
<?php $__env->stopPush(); ?>
<?php /**PATH C:\Users\user\Desktop\Optimizat\resources\views/admin/products/_form.blade.php ENDPATH**/ ?>