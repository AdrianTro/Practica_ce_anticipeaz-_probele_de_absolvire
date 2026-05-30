<?php $__env->startSection('title', 'Pretentie '.$contactThread->thread_uuid.' | Admin'); ?>

<?php $__env->startSection('content'); ?>
<section class="section-shell admin-order-page">
    <div class="admin-header">
        <div>
            <span class="eyebrow">Pretentie</span>
            <h1><?php echo e($contactThread->fullName()); ?></h1>
            <p><?php echo e($contactThread->email); ?> · <?php echo e($contactThread->created_at->format('d.m.Y H:i')); ?></p>
        </div>
        <a class="secondary-btn" href="<?php echo e(route('admin.claims.index')); ?>">Inapoi</a>
    </div>

    <div class="thread-layout admin-thread-layout">
        <aside class="thread-sidebar">
            <span class="eyebrow">Fir</span>
            <h2><?php echo e($contactThread->thread_uuid); ?></h2>
            
            <div class="thread-meta">
                <span class="status-pill <?php echo e($contactThread->status === 'closed' ? 'invalid' : 'valid'); ?>">
                    <?php echo e($contactThread->status === 'closed' ? 'Încheiat' : 'Activ'); ?>

                </span>
                <span>Ultimul mesaj: <?php echo e(optional($contactThread->last_message_at)->format('d.m.Y H:i') ?: '-'); ?></span>
            </div>
        </aside>

        <div class="thread-panel">
            <div class="message-list">
                <?php $__currentLoopData = $contactThread->messages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $message): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <article class="chat-row <?php echo e($message->sender === \App\Models\ContactMessage::SENDER_COMPANY ? 'from-company' : 'from-customer'); ?>">
                        <div class="chat-bubble">
                            <strong><?php echo e($message->sender === \App\Models\ContactMessage::SENDER_COMPANY ? 'Compania' : $contactThread->fullName()); ?></strong>
                            <p><?php echo nl2br(e($message->body)); ?></p>
                            <span><?php echo e($message->created_at->format('d.m.Y H:i')); ?></span>
                        </div>
                    </article>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>

            <?php if($contactThread->status === 'closed'): ?>
                <div class="thread-closed-note">Pretenția este încheiată. Nu se mai pot trimite mesaje.</div>
            <?php else: ?>
                <form id="admin-thread-reply-form" class="thread-reply-form" method="POST" action="<?php echo e(route('admin.claims.reply', $contactThread->thread_uuid)); ?>">
                    <?php echo csrf_field(); ?>
                    <label>Raspuns catre client
                        <textarea name="message" rows="5" required placeholder="Scrie raspunsul companiei..."><?php echo e(old('message')); ?></textarea>
                    </label>
                </form>
                <div class="thread-action-row">
                    <form method="POST" action="<?php echo e(route('admin.claims.close', $contactThread->thread_uuid)); ?>" data-confirm="Închei pretenția? După încheiere nu se mai pot trimite mesaje.">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('PATCH'); ?>
                        <button class="secondary-btn" type="submit">Încheie Pretenția</button>
                    </form>
                    <button class="primary-btn" type="submit" form="admin-thread-reply-form">Trimite raspuns</button>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\routu\OneDrive\Рабочий стол\Stable_Optimizate\resources\views/admin/claims/show.blade.php ENDPATH**/ ?>