<?php $__env->startSection('title', 'Admin Login | ReclamDesign Modern'); ?>

<?php $__env->startSection('content'); ?>
<?php
    $activeLockUntil = (int) (session('lock_until') ?? ($lockUntil ?? 0));
?>
<section class="section-shell auth-page">
    <form class="auth-card" method="POST" action="<?php echo e(route('admin.login.submit')); ?>" data-admin-login-form>
        <?php echo csrf_field(); ?>
        <h1>Autentificare Admin</h1>
        <label>Nume admin
            <input name="name" value="<?php echo e(old('name')); ?>" required autofocus <?php if($activeLockUntil > now()->timestamp): echo 'disabled'; endif; ?>>
        </label>
        <label>Parola
            <input name="password" type="password" required <?php if($activeLockUntil > now()->timestamp): echo 'disabled'; endif; ?>>
        </label>
        <button class="primary-btn wide" type="submit" <?php if($activeLockUntil > now()->timestamp): echo 'disabled'; endif; ?>>Intra in admin</button>
    </form>
</section>

<div class="admin-lock-modal" id="admin-lock-modal" data-lock-until="<?php echo e($activeLockUntil); ?>" <?php if($activeLockUntil <= now()->timestamp): ?> hidden <?php endif; ?>>
    <div class="admin-lock-card">
        <img src="<?php echo e(asset('assets/fără_success/unsuccess.gif')); ?>" alt="Acces blocat">
        <h2>Parola gresita de 5 ori</h2>
        <p>Acces blocat temporar. Incearca din nou peste <strong id="admin-lock-countdown">10</strong> secunde.</p>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\routu\OneDrive\Рабочий стол\Stable_Optimizate\resources\views/admin/login.blade.php ENDPATH**/ ?>