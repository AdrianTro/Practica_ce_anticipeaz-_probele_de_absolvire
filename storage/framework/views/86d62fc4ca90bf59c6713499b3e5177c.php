<?php $__env->startSection('title', 'Contacte | ReclamDesign Modern'); ?>

<?php $__env->startSection('content'); ?>
<section class="section-shell contact-page">
    <div class="section-heading contact-heading">
        <span>Contacte</span>
        <h1>Alege aplicația prin care vrei să ne contactezi</h1>
        
    </div>

    <div class="contact-grid contact-app-grid" data-contact-apps>
        <article class="contact-card contact-app-card" data-contact-card="viber">
            <button class="contact-app-open" type="button" data-contact-open="viber" aria-label="Contactează prin Viber">
                <span class="contact-app-icon" data-contact-icon aria-hidden="true"></span>
                <span class="contact-app-name" data-contact-name>Viber</span>
            </button>
            <p class="contact-app-description" data-contact-description>Design și imprimare</p>
            <div class="contact-meta" data-contact-details aria-label="Date de contact Viber"></div>
        </article>

        <article class="contact-card contact-app-card" data-contact-card="whatsApp">
            <button class="contact-app-open" type="button" data-contact-open="whatsApp" aria-label="Contactează prin WhatsApp">
                <span class="contact-app-icon" data-contact-icon aria-hidden="true"></span>
                <span class="contact-app-name" data-contact-name>WhatsApp</span>
            </button>
            <p class="contact-app-description" data-contact-description>Design și imprimare</p>
            <div class="contact-meta" data-contact-details aria-label="Date de contact WhatsApp"></div>
        </article>

        <article class="contact-card contact-app-card" data-contact-card="telegram">
            <button class="contact-app-open" type="button" data-contact-open="telegram" aria-label="Contactează prin Telegram">
                <span class="contact-app-icon" data-contact-icon aria-hidden="true"></span>
                <span class="contact-app-name" data-contact-name>Telegram</span>
            </button>
            <p class="contact-app-description" data-contact-description>Design și imprimare</p>
            <div class="contact-meta" data-contact-details aria-label="Date de contact Telegram"></div>
        </article>

        <article class="contact-card contact-app-card" data-contact-card="gmail">
            <button class="contact-app-open" type="button" data-contact-open="gmail" aria-label="Deschide formularul Gmail">
                <span class="contact-app-icon" data-contact-icon aria-hidden="true"></span>
                <span class="contact-app-name" data-contact-name>Gmail</span>
            </button>
            <p class="contact-app-description" data-contact-description>Suport clienți</p>
            <div class="contact-meta" data-contact-details aria-label="Date de contact Gmail"></div>
        </article>
    </div>

    <div class="messaging-confirm-modal" id="messaging-confirm-modal" role="dialog" aria-modal="true" aria-labelledby="messaging-confirm-title" hidden>
        <div class="messaging-confirm-dialog">
            <button class="modal-close" type="button" data-close-messaging-confirm aria-label="Închide">&times;</button>
            <span class="eyebrow">Redirecționare</span>
            <h2 id="messaging-confirm-title">Deschide conversația</h2>
            <p data-messaging-confirm-message>Direcționare către conversație?</p>
            <p class="muted">Dacă nu sunteți autentificat pe platforma aleasă, aplicația vă poate cere autentificarea înainte de conectare.</p>
            <div class="contact-form-actions">
                <button class="primary-btn" type="button" data-messaging-redirect>Direcționează</button>
            </div>
        </div>
    </div>
</section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\user\Desktop\Optimizat\resources\views/contacte.blade.php ENDPATH**/ ?>