<!doctype html>
<html lang="ro">
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; background: #f4f7fb; color: #172033; margin: 0; padding: 24px; }
        .card { max-width: 720px; margin: auto; background: #ffffff; border-radius: 18px; overflow: hidden; box-shadow: 0 18px 45px rgba(28, 44, 74, .12); }
        .header { background: linear-gradient(135deg, #0969ff, #ffd21e); color: #ffffff; padding: 28px; }
        .content { padding: 28px; }
        .details { margin: 18px 0; padding: 16px; background: #f8fafc; border-radius: 14px; }
        .message { white-space: pre-line; line-height: 1.6; padding: 18px; border-left: 5px solid #0969ff; background: #f8fafc; border-radius: 12px; }
        .button { display: inline-block; margin-top: 20px; padding: 13px 18px; border-radius: 12px; background: #0969ff; color: #ffffff !important; text-decoration: none; font-weight: 800; }
        .muted { color: #6b7280; font-size: 12px; line-height: 1.5; }
    </style>
</head>
<body>
    <div class="card">
        <div class="header">
            <h1>Mesaj nou de contact</h1>
            <p>Fir: <strong><?php echo e($thread->thread_uuid); ?></strong></p>
        </div>
        <div class="content">
            <div class="details">
                <p><strong>Nume:</strong> <?php echo e($thread->fullName()); ?></p>
                <p><strong>Email:</strong> <?php echo e($thread->email); ?></p>
                <p><strong>Data:</strong> <?php echo e($contactMessage->created_at->format('d.m.Y H:i')); ?></p>
            </div>

            <h2>Mesaj</h2>
            <div class="message"><?php echo e($contactMessage->body); ?></div>

            <a class="button" href="<?php echo e($adminUrl); ?>">Răspunde</a>
            <p class="muted">Butonul deschide conversația în panoul de administrare, unde răspunsul va fi salvat și trimis clientului.</p>
        </div>
    </div>
</body>
</html>
<?php /**PATH C:\Users\user\Desktop\Optimizat\resources\views/emails/contact_thread_notification.blade.php ENDPATH**/ ?>