<!doctype html>
<html lang="ro">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name'))</title>
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
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    @stack('head')
</head>
<body data-is-admin="{{ session()->boolean('is_admin') ? 'true' : 'false' }}">
    <div class="top-strip">
        <a class="top-brand-mobile" href="{{ route('home') }}" aria-label="Pagina principala">
            <img src="{{ asset('images/logo/logo.png') }}" alt="ReclamDesign Modern">
        </a>
        <a class="top-link" href="{{ route('about') }}">Despre noi</a>
        <a class="top-link" href="{{ route('contacte') }}">Contacte</a>
        <span class="top-note">ReclamDesign Modern</span>
    </div>

    <header class="site-navbar" id="site-navbar">
        <div class="nav-left">
            <a class="brand" href="{{ route('home') }}" aria-label="Pagina principala">
                <img src="{{ asset('images/logo/logo.png') }}" alt="ReclamDesign Modern">
                <span>ReclamDesign<br><strong>Modern</strong></span>
            </a>

            <div class="catalog-wrap">
                <button class="catalog-toggle" type="button" id="catalog-toggle" aria-expanded="false">
                    <span class="hamburger" aria-hidden="true"><i></i><i></i><i></i></span>
                    <span>Catalog</span>
                    <span class="catalog-arrow" aria-hidden="true">▾</span>
                </button>
                <div class="catalog-menu catalog-menu-rich" id="catalog-menu">
                    @forelse($navCategories as $category)
                        <div class="catalog-row">
                            <button class="catalog-category-link" type="button" data-catalog-category aria-expanded="false">
                                <span>{{ $category->icon }}</span>
                                <strong>{{ $category->name }}</strong>
                                <em aria-hidden="true">›</em>
                            </button>
                            <div class="catalog-submenu">
                                <a class="catalog-subitem all-subitems" href="{{ route('categories.show', $category) }}">Toate din {{ $category->name }}</a>
                                @foreach($category->activeSubcategories as $subcategory)
                                    <a class="catalog-subitem" href="{{ route('categories.show', [$category, $subcategory->slug]) }}">
                                        <span>
                                            @if($subcategory->image)
                                                <img
                                                    class="subcategory-thumb tiny"
                                                    src="{{ \App\Support\StoredImage::url($subcategory->image) }}"
                                                    alt=""
                                                    aria-hidden="true"
                                                >
                                            @else
                                                {{ $subcategory->icon }}
                                            @endif
                                        </span>
                                        <strong>{{ $subcategory->name }}</strong>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @empty
                        <span class="muted small-pad">Ruleaza migrarile si seederele.</span>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="search-box">
            <input id="product-search" type="search" placeholder="Cauta produse instant..." autocomplete="off" aria-label="Cauta produse">
            <div class="search-results" id="search-results" hidden></div>
        </div>

        <div class="nav-actions">
            @admin
                <a class="admin-pill" href="{{ route('admin.dashboard') }}">Admin</a>
            @endadmin

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

            <a class="cart-button" href="{{ route('cart.index') }}" aria-label="Cos produse">
                <span aria-hidden="true">🛒</span>
                <span class="cart-badge" id="cart-badge">0</span>
            </a>

            <button class="theme-toggle" type="button" id="theme-toggle" aria-label="Schimba dark mode si light mode">
                <span class="theme-track">
                    <span class="theme-glass"></span>
                    <span class="theme-icon theme-sun">
                        <img src="{{ asset('assets/Light_Dark_Mode/light.png') }}" alt="" aria-hidden="true" onerror="this.hidden=true; this.nextElementSibling.hidden=false;">
                        <span class="theme-fallback" hidden></span>
                    </span>
                    <span class="theme-icon theme-moon">
                        <img src="{{ asset('assets/Light_Dark_Mode/dark.png') }}" alt="" aria-hidden="true" onerror="this.hidden=true; this.nextElementSibling.hidden=false;">
                        <span class="theme-fallback" hidden></span>
                    </span>
                </span>
            </button>
        </div>
    </header>

    <div class="flash-area">
        @if(session('success'))
            <div class="alert success" data-autodismiss>{{ session('success') }}</div>
        @endif
        @if(session('warning'))
            <div class="alert warning" data-autodismiss>{{ session('warning') }}</div>
        @endif
        @if($errors->any())
            <div class="alert danger" data-autodismiss>
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif
    </div>

    <main>
        @yield('content')
    </main>

    @unless(session()->boolean('is_admin'))
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
    @endunless

    <div class="contact-modal" id="gmail-contact-modal" role="dialog" aria-modal="true" aria-labelledby="gmail-contact-title" data-open-on-load="{{ (($errors->any() && old('contact_form')) || request()->boolean('gmail')) ? 'true' : 'false' }}" hidden>
        <div class="contact-dialog gmail-modal-dialog">
            <button class="modal-close" type="button" data-close-gmail-modal aria-label="Închide">&times;</button>

            <form class="contact-form gmail-contact-form-modal" method="POST" action="{{ route('contacte.store') }}">
                @csrf
                <input type="hidden" name="contact_form" value="1">

                <div class="wizard-head gmail-form-head">
                    <div>
                        <span class="eyebrow">Gmail</span>
                        <h2 id="gmail-contact-title">Formular de contact</h2>
                        <p class="gmail-form-intro">Completează formularul și îți răspundem cât mai curând.</p>
                    </div>
                </div>

                <div class="contact-meta contact-meta-modal" data-gmail-contact-details aria-label="Date de contact Gmail"></div>

                @if($errors->any() && old('contact_form'))
                    <div class="alert warning contact-form-warning">
                        Verifică formularul și completează câmpurile marcate.
                    </div>
                @endif

                <div class="contact-form-grid">
                    <label>Nume
                        <input name="first_name" value="{{ old('first_name') }}" required maxlength="120" autocomplete="family-name">
                        @error('first_name')<span class="field-error">{{ $message }}</span>@enderror
                    </label>
                    <label>Prenume
                        <input name="last_name" value="{{ old('last_name') }}" required maxlength="120" autocomplete="given-name">
                        @error('last_name')<span class="field-error">{{ $message }}</span>@enderror
                    </label>
                    <label class="full-field">Email
                        <input name="email" type="email" value="{{ old('email') }}" required maxlength="160" autocomplete="email">
                        @error('email')<span class="field-error">{{ $message }}</span>@enderror
                    </label>
                    <label class="full-field">Descrierea problemei / necesității
                        <textarea name="description" rows="6" required>{{ old('description') }}</textarea>
                        @error('description')<span class="field-error">{{ $message }}</span>@enderror
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
                <a class="footer-logo-link" href="{{ route('home') }}" aria-label="Pagina principala">
                    <img src="{{ asset('images/logo/logo_404.png') }}" alt="ReclamDesign Modern">
                    <span>ReclamDesign Modern</span>
                </a>
                <p>&copy; ReclamDesign Modern {{ now()->year }}<br>Toate drepturile sunt rezervate.</p>
            </div>

            <nav class="footer-info" aria-label="Informatii">
                <h2>Informatii</h2>
                <a href="{{ route('about') }}">Despre noi</a>
                <a href="{{ route('contacte') }}">Contacte</a>
            </nav>

            <div class="footer-contacts" aria-label="Contacte">
                <h2>Contacte</h2>
                <div class="footer-contact-list">
                    <a href="tel:+37379833325"><span aria-hidden="true">&#9742;</span><strong>+373 79 833 325</strong></a>
                    <span><span aria-hidden="true">&#8982;</span><strong>Cahul, Moldova</strong></span>
                    <a href="mailto:tronciu.adrian@elev.cihcahul.md"><span aria-hidden="true">&#9993;</span><strong>tronciu.adrian@elev.cihcahul.md</strong></a>
                    <span><span aria-hidden="true">&#128336;</span><strong>Luni - Vineri, 09:00 - 18:00<br>Sâmbătă, 09:00 - 14:00</strong></span>
                </div>
            </div>
        </div>
    </footer>

    <script>
        window.ReclamRoutes = {
            search: @json(route('products.search')),
            cart: @json(route('cart.index')),
            promoCheck: @json(route('cart.promocode.check')),
        };
        window.ReclamCsrf = @json(csrf_token());
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
    <script src="{{ asset('js/app.js') }}?v={{ filemtime(public_path('js/app.js')) }}"></script>
    @stack('scripts')
</body>
</html>
