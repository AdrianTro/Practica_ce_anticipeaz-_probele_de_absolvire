<?php $__env->startSection('title', 'Pretentii | Admin'); ?>

<?php $__env->startSection('content'); ?>
<section class="section-shell admin-page">
    <div class="admin-header">
        <div>
            <span class="eyebrow">Admin</span>
            <h1>Pretentii</h1>
            
        </div>
        <a class="secondary-btn" href="<?php echo e(route('admin.dashboard')); ?>">Inapoi</a>
    </div>

    <div class="claim-grid">
        <?php $__empty_1 = true; $__currentLoopData = $threads; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $thread): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <a class="claim-card <?php echo e($thread->new_messages_count > 0 ? 'has-new-claim' : ''); ?>" href="<?php echo e(route('admin.claims.show', $thread->thread_uuid)); ?>">
                <?php if($thread->new_messages_count > 0): ?>
                    <span class="claim-new-badge" aria-label="Pretentie noua">NEW</span>
                <?php endif; ?>
                <div>
                    <span class="status-pill <?php echo e($thread->status === 'closed' ? 'invalid' : 'valid'); ?>">
                        <?php echo e($thread->status === 'closed' ? 'Încheiat' : 'Activ'); ?>

                    </span>
                    <h2><?php echo e($thread->fullName()); ?></h2>
                    <p><?php echo e($thread->email); ?></p>
                </div>
                <div class="claim-meta">
                    <span>Trimis: <?php echo e($thread->created_at->format('d.m.Y H:i')); ?></span>
                    <span>Mesaje: <?php echo e($thread->messages_count); ?></span>
                    <span>Ultimul mesaj: <?php echo e(optional($thread->last_message_at)->format('d.m.Y H:i') ?: '-'); ?></span>
                </div>
            </a>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div class="empty-state">Nu exista mesaje primite.</div>
        <?php endif; ?>
    </div>

    <div class="pagination-wrap"><?php echo e($threads->links()); ?></div>
</section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\user\Desktop\Optimizat\resources\views/admin/claims/index.blade.php ENDPATH**/ ?>