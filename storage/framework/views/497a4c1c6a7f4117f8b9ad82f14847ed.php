<?php $__env->startSection('title', 'Adauga produs | Admin'); ?>

<?php $__env->startSection('content'); ?>
<section class="section-shell admin-form-page">
    <div class="admin-header">
        <div>
            <span class="eyebrow">Admin</span>
            <h1>Adauga produs</h1>
        </div>
        <a class="secondary-btn" href="<?php echo e(route('admin.dashboard')); ?>">Inapoi</a>
    </div>
    <form class="admin-form" method="POST" action="<?php echo e(route('admin.products.store')); ?>" enctype="multipart/form-data">
        <?php echo csrf_field(); ?>
        <?php echo $__env->make('admin.products._form', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <button class="primary-btn wide" type="submit">Salveaza produs</button>
    </form>
</section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\routu\OneDrive\Рабочий стол\Stable_Optimizate\resources\views/admin/products/create.blade.php ENDPATH**/ ?>