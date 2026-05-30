<?php $__env->startSection('title', 'Conversatie contact | ReclamDesign Modern'); ?>

<?php $__env->startSection('content'); ?>
<section class="section-shell contact-thread-page">
    <div class="admin-header">
        <div>
            <span class="eyebrow">Contactare</span>
            <h1>Conversatia ta</h1>
            <p><?php echo e($contactThread->fullName()); ?> · <?php echo e($contactThread->email); ?></p>
        </div>
        <a class="secondary-btn" href="<?php echo e(route('contacte')); ?>">Contacte</a>
    </div>

    <div class="thread-layout">
        <aside class="thread-sidebar">
            <span class="eyebrow">Solicitare</span>
            <h2><?php echo e($contactThread->thread_uuid); ?></h2>
            <div class="thread-meta">
                <span class="status-pill <?php echo e($contactThread->status === 'closed' ? 'invalid' : 'valid'); ?>">
                    <?php echo e($contactThread->status === 'closed' ? 'Încheiat' : 'Activ'); ?>

                </span>
                <span>Creat: <?php echo e($contactThread->created_at->format('d.m.Y H:i')); ?></span>
                <span>Ultimul mesaj: <?php echo e(optional($contactThread->last_message_at)->format('d.m.Y H:i') ?: '-'); ?></span>
            </div>
        </aside>

        <div class="thread-panel">
            <div class="message-list">
                <?php $__currentLoopData = $contactThread->messages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $message): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <article class="chat-row <?php echo e($message->sender === \App\Models\ContactMessage::SENDER_COMPANY ? 'from-company' : 'from-customer'); ?>">
                        <div class="chat-bubble">
                            <strong><?php echo e($message->sender === \App\Models\ContactMessage::SENDER_COMPANY ? 'ReclamDesign Modern' : $contactThread->fullName()); ?></strong>
                            <p><?php echo nl2br(e($message->body)); ?></p>
                            <span><?php echo e($message->created_at->format('d.m.Y H:i')); ?></span>
                        </div>
                    </article>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>

            <?php if($contactThread->status === 'closed'): ?>
                <div class="thread-closed-note">Pretenția este încheiată. Conversația rămâne vizibilă, dar nu mai pot fi trimise mesaje.</div>
            <?php else: ?>
                <form class="thread-reply-form" method="POST" action="<?php echo e(route('contacte.thread.message', $contactThread->public_token)); ?>">
                    <?php echo csrf_field(); ?>
                    <label>Continua conversatia
                        <textarea name="description" rows="4" required placeholder="Scrie un mesaj nou..."><?php echo e(old('description')); ?></textarea>
                    </label>
                    <button class="primary-btn" type="submit">Trimite mesaj</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\routu\OneDrive\Рабочий стол\Stable_Optimizate\resources\views/contact-thread.blade.php ENDPATH**/ ?>