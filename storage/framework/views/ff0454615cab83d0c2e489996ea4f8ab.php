<!doctype html>
<html lang="ro">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title><?php echo $__env->yieldContent('title', config('app.name')); ?></title>
    <script>
        (function () {
            var theme = 'light';
            try {
                var stored = localStorage.getItem('rdm_theme');
                var cookie = document.cookie.match(/(?:^|; )rdm_theme=([^;]+)/);
                theme = (stored || (cookie ? decodeURIComponent(cookie[1]) : '') || 'light');
            } catch (error) {
                var fallback = document.cookie.match(/(?:^|; )rdm_theme=([^;]+)/);
                theme = fallback ? decodeURIComponent(fallback[1]) : 'light';
            }
            if (theme !== 'dark' && theme !== 'light') theme = 'light';
            document.documentElement.dataset.theme = theme;
            document.documentElement.style.colorScheme = theme;
        })();
    </script>
    <script>
        (function () {
            var allowed = { ro: true, ru: true, en: true };
            var sourceLanguage = 'ro';

            function readCookie(name) {
                var row = document.cookie.split('; ').find(function (part) {
                    return part.indexOf(name + '=') === 0;
                });
                return row ? decodeURIComponent(row.split('=').slice(1).join('=')) : '';
            }

            function cookieDomains() {
                var host = window.location.hostname;
                var domains = [''];

                if (host && host !== 'localhost' && !/^\d+\.\d+\.\d+\.\d+$/.test(host)) {
                    domains.push(host);
                    var parts = host.split('.');
                    if (parts.length > 1) domains.push('.' + parts.slice(-2).join('.'));
                }

                return domains;
            }

            function writeCookie(name, value, maxAge, domain) {
                document.cookie = name + '=' + value + '; max-age=' + maxAge + '; path=/' + (domain ? '; domain=' + domain : '') + '; SameSite=Lax';
            }

            function applyTranslateCookie(language) {
                cookieDomains().forEach(function (domain) {
                    if (language === sourceLanguage) {
                        writeCookie('googtrans', '', 0, domain);
                    } else {
                        writeCookie('googtrans', '/' + sourceLanguage + '/' + language, 31536000, domain);
                    }
                });
            }

            var language = sourceLanguage;
            try {
                language = localStorage.getItem('rdm_language') || readCookie('rdm_language') || sourceLanguage;
            } catch (error) {
                language = readCookie('rdm_language') || sourceLanguage;
            }

            if (!allowed[language]) language = sourceLanguage;
            document.documentElement.dataset.lang = language;
            applyTranslateCookie(language);
        })();
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo e(asset('css/app.css')); ?>">
    <?php echo $__env->yieldPushContent('head'); ?>
</head>
<body data-is-admin="<?php echo e(session()->boolean('is_admin') ? 'true' : 'false'); ?>">
    <div class="top-strip">
        <a class="top-brand-mobile" href="<?php echo e(route('home')); ?>" aria-label="Pagina principala">
            <img src="<?php echo e(asset('images/logo/logo.png')); ?>" alt="ReclamDesign Modern">
        </a>
        <a class="top-link" href="<?php echo e(route('about')); ?>">Despre noi</a>
        <a class="top-link" href="<?php echo e(route('contacte')); ?>">Contacte</a>
        <span class="top-note">ReclamDesign Modern</span>
    </div>

    <header class="site-navbar" id="site-navbar">
        <div class="nav-left">
            <a class="brand" href="<?php echo e(route('home')); ?>" aria-label="Pagina principala">
                <img src="<?php echo e(asset('images/logo/logo.png')); ?>" alt="ReclamDesign Modern">
                <span>ReclamDesign<br><strong>Modern</strong></span>
            </a>

            <div class="catalog-wrap">
                <button class="catalog-toggle" type="button" id="catalog-toggle" aria-expanded="false">
                    <span class="hamburger" aria-hidden="true"><i></i><i></i><i></i></span>
                    <span>Catalog</span>
                    <span class="catalog-arrow" aria-hidden="true">▾</span>
                </button>
                <div class="catalog-menu catalog-menu-rich" id="catalog-menu">
                    <?php $__empty_1 = true; $__currentLoopData = $navCategories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <div class="catalog-row">
                            <button class="catalog-category-link" type="button" data-catalog-category aria-expanded="false">
                                <span><?php echo e($category->icon); ?></span>
                                <strong><?php echo e($category->name); ?></strong>
                                <em aria-hidden="true">›</em>
                            </button>
                            <div class="catalog-submenu">
                                <a class="catalog-subitem all-subitems" href="<?php echo e(route('categories.show', $category)); ?>">Toate din <?php echo e($category->name); ?></a>
                                <?php $__currentLoopData = $category->activeSubcategories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $subcategory): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <a class="catalog-subitem" href="<?php echo e(route('categories.show', [$category, $subcategory->slug])); ?>">
                                        <span>
                                            <?php if($subcategory->image): ?>
                                                <img
                                                    class="subcategory-thumb tiny"
                                                    src="<?php echo e(\App\Support\StoredImage::url($subcategory->image)); ?>"
                                                    alt=""
                                                    aria-hidden="true"
                                                >
                                            <?php else: ?>
                                                <?php echo e($subcategory->icon); ?>

                                            <?php endif; ?>
                                        </span>
                                        <strong><?php echo e($subcategory->name); ?></strong>
                                    </a>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <span class="muted small-pad">Ruleaza migrarile si seederele.</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="search-box">
            <input id="product-search" type="search" placeholder="Cauta produse instant..." autocomplete="off" aria-label="Cauta produse">
            <div class="search-results" id="search-results" hidden></div>
        </div>

        <div class="nav-actions">
            <?php if (\Illuminate\Support\Facades\Blade::check('admin')): ?>
                <a class="admin-pill" href="<?php echo e(route('admin.dashboard')); ?>">Admin</a>
            <?php endif; ?>

            <div class="language-switcher notranslate" data-language-switcher translate="no">
                <button class="language-toggle" type="button" data-language-toggle aria-expanded="false" aria-label="Schimba limba">
                    <span data-current-language>RO</span>
                    <span class="language-arrow" aria-hidden="true">▾</span>
                </button>
                <div class="language-menu" data-language-menu>
                    <button type="button" data-language-option="ro" aria-pressed="true">RO</button>
                    <button type="button" data-language-option="ru" aria-pressed="false">RU</button>
                    <button type="button" data-language-option="en" aria-pressed="false">EN</button>
                </div>
            </div>

            <a class="cart-button" href="<?php echo e(route('cart.index')); ?>" aria-label="Cos produse">
                <span aria-hidden="true">🛒</span>
                <span class="cart-badge" id="cart-badge">0</span>
            </a>

            <button class="theme-toggle" type="button" id="theme-toggle" aria-label="Schimba dark mode si light mode">
                <span class="theme-track">
                    <span class="theme-glass"></span>
                    <span class="theme-icon theme-sun">
                        <img src="<?php echo e(asset('assets/Light_Dark_Mode/light.png')); ?>" alt="" aria-hidden="true" onerror="this.hidden=true; this.nextElementSibling.hidden=false;">
                        <span class="theme-fallback" hidden></span>
                    </span>
                    <span class="theme-icon theme-moon">
                        <img src="<?php echo e(asset('assets/Light_Dark_Mode/dark.png')); ?>" alt="" aria-hidden="true" onerror="this.hidden=true; this.nextElementSibling.hidden=false;">
                        <span class="theme-fallback" hidden></span>
                    </span>
                </span>
            </button>
        </div>
    </header>

    <div class="flash-area">
        <?php if(session('success')): ?>
            <div class="alert success" data-autodismiss><?php echo e(session('success')); ?></div>
        <?php endif; ?>
        <?php if(session('warning')): ?>
            <div class="alert warning" data-autodismiss><?php echo e(session('warning')); ?></div>
        <?php endif; ?>
        <?php if($errors->any()): ?>
            <div class="alert danger" data-autodismiss>
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div><?php echo e($error); ?></div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        <?php endif; ?>
    </div>

    <main>
        <?php echo $__env->yieldContent('content'); ?>
    </main>

    <?php if (! (session()->boolean('is_admin'))): ?>
        <div class="floating-contact" data-floating-contact>
            <button class="floating-contact-toggle" type="button" data-floating-contact-toggle aria-expanded="false" aria-label="Deschide contacte rapide">
                <span aria-hidden="true">💬</span>
            </button>
            <div class="floating-contact-menu" data-floating-contact-menu aria-label="Contacte rapide">
                <a class="floating-contact-link viber" href="viber://chat?number=%2B37379833325" aria-label="Viber">
                    <span aria-hidden="true">☎</span><strong>Viber</strong>
                </a>
                <a class="floating-contact-link whatsapp" href="https://api.whatsapp.com/send?phone=37379833325&text=Salut" target="_blank" rel="noopener noreferrer" aria-label="WhatsApp">
                    <span aria-hidden="true">☘</span><strong>WhatsApp</strong>
                </a>
                <a class="floating-contact-link telegram" href="https://t.me/reclamd" target="_blank" rel="noopener noreferrer" aria-label="Telegram">
                    <span aria-hidden="true">✈</span><strong>Telegram</strong>
                </a>
                <button class="floating-contact-link gmail" type="button" data-open-gmail-modal aria-label="Gmail">
                    <span aria-hidden="true">✉</span><strong>Gmail</strong>
                </button>
            </div>
        </div>
    <?php endif; ?>

    <div class="contact-modal" id="gmail-contact-modal" role="dialog" aria-modal="true" aria-labelledby="gmail-contact-title" data-open-on-load="<?php echo e((($errors->any() && old('contact_form')) || request()->boolean('gmail')) ? 'true' : 'false'); ?>" hidden>
        <div class="contact-dialog gmail-modal-dialog">
            <button class="modal-close" type="button" data-close-gmail-modal aria-label="Închide">&times;</button>

            <form class="contact-form gmail-contact-form-modal" method="POST" action="<?php echo e(route('contacte.store')); ?>">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="contact_form" value="1">

                <div class="wizard-head gmail-form-head">
                    <div>
                        <span class="eyebrow">Gmail</span>
                        <h2 id="gmail-contact-title">Formular de contact</h2>
                        <p class="gmail-form-intro">Completează formularul și îți răspundem cât mai curând.</p>
                    </div>
                </div>

                <div class="contact-meta contact-meta-modal" data-gmail-contact-details aria-label="Date de contact Gmail"></div>

                <?php if($errors->any() && old('contact_form')): ?>
                    <div class="alert warning contact-form-warning">
                        Verifică formularul și completează câmpurile marcate.
                    </div>
                <?php endif; ?>

                <div class="contact-form-grid">
                    <label>Nume
                        <input name="first_name" value="<?php echo e(old('first_name')); ?>" required maxlength="120" autocomplete="family-name">
                        <?php $__errorArgs = ['first_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><span class="field-error"><?php echo e($message); ?></span><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </label>
                    <label>Prenume
                        <input name="last_name" value="<?php echo e(old('last_name')); ?>" required maxlength="120" autocomplete="given-name">
                        <?php $__errorArgs = ['last_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><span class="field-error"><?php echo e($message); ?></span><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </label>
                    <label class="full-field">Email
                        <input name="email" type="email" value="<?php echo e(old('email')); ?>" required maxlength="160" autocomplete="email">
                        <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><span class="field-error"><?php echo e($message); ?></span><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </label>
                    <label class="full-field">Descrierea problemei / necesității
                        <textarea name="description" rows="6" required><?php echo e(old('description')); ?></textarea>
                        <?php $__errorArgs = ['description'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><span class="field-error"><?php echo e($message); ?></span><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </label>
                </div>

                <div class="contact-form-actions">
                    <button class="primary-btn" type="submit">Trimite prin Gmail</button>
                </div>
            </form>
        </div>
    </div>

    <footer class="site-footer compact-footer">
        <div class="footer-inner">
            <div class="footer-brand-block">
                <a class="footer-logo-link" href="<?php echo e(route('home')); ?>" aria-label="Pagina principala">
                    <img src="<?php echo e(asset('images/logo/logo_404.png')); ?>" alt="ReclamDesign Modern">
                    <span>ReclamDesign Modern</span>
                </a>
                <p>&copy; ReclamDesign Modern <?php echo e(now()->year); ?><br>Toate drepturile sunt rezervate.</p>
            </div>

            <nav class="footer-info" aria-label="Informatii">
                <h2>Informatii</h2>
                <a href="<?php echo e(route('about')); ?>">Despre noi</a>
                <a href="<?php echo e(route('contacte')); ?>">Contacte</a>
            </nav>

            <div class="footer-contacts" aria-label="Contacte">
                <h2>Contacte</h2>
                <div class="footer-contact-list">
                    <a href="tel:+37379833325"><span aria-hidden="true">&#9742;</span><strong>+373 79 833 325</strong></a>
                    <span><span aria-hidden="true">&#8982;</span><strong>Cahul, Moldova</strong></span>
                    <a href="mailto:tronciu.adrian@elev.cihcahul.md"><span aria-hidden="true">&#9993;</span><strong>tronciu.adrian@elev.cihcahul.md</strong></a>
                    <span><span aria-hidden="true">&#128336;</span><strong>Luni - Vineri, 09:00 - 18:00</strong></span>
                </div>
            </div>
        </div>
    </footer>

    <script>
        window.ReclamRoutes = {
            search: <?php echo json_encode(route('products.search'), 15, 512) ?>,
            cart: <?php echo json_encode(route('cart.index'), 15, 512) ?>,
            promoCheck: <?php echo json_encode(route('cart.promocode.check'), 15, 512) ?>,
        };
        window.ReclamCsrf = <?php echo json_encode(csrf_token(), 15, 512) ?>;
        window.googleTranslateElementInit = function () {
            if (!window.google || !window.google.translate) return;
            new window.google.translate.TranslateElement({
                pageLanguage: 'ro',
                includedLanguages: 'ro,ru,en',
                autoDisplay: false
            }, 'google_translate_element');
        };
    </script>
    <div id="google_translate_element" class="google-translate-host notranslate" translate="no"></div>
    <script src="https://translate.google.com/translate_a/element.js?cb=googleTranslateElementInit" defer></script>
    <script src="<?php echo e(asset('js/app.js')); ?>?v=<?php echo e(filemtime(public_path('js/app.js'))); ?>"></script>
    <?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html>
<?php /**PATH C:\Users\routu\OneDrive\Рабочий стол\Stable_Optimizate\resources\views/layouts/app.blade.php ENDPATH**/ ?>