(() => {
    const cartStorageKey = 'rdm_cart_v2';
    const oldCartCookie = 'rdm_cart';
    const promoStorageKey = 'rdm_promocode';
    const wearableDraftStorageKey = 'rdm_wearable_draft_v2';
    const languageStorageKey = 'rdm_language';
    const carouselStateStorageKey = 'rdm_carousel_active_index';
    const galleryStateStorageKey = 'rdm_gallery_active_index';
    const html = document.documentElement;
    const sourceLanguage = 'ro';
    const languageLabels = { ro: 'RO', ru: 'RU', en: 'EN' };

    const escapeHtml = (value) => String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');

    const money = (value) => `${Number(value || 0).toFixed(2)} MDL`;
    const cssEscape = (value) => window.CSS?.escape ? window.CSS.escape(String(value)) : String(value);

    const makeUid = () => {
        try {
            if (window.crypto && typeof window.crypto.randomUUID === "function") {
                return window.crypto.randomUUID();
            }
        } catch (error) {
            // Fallback below.
        }
        return `${Date.now()}-${Math.random().toString(16).slice(2)}`;
    };

    const getCookie = (name) => {
        const row = document.cookie.split('; ').find((part) => part.startsWith(`${name}=`));
        return row ? decodeURIComponent(row.split('=').slice(1).join('=')) : null;
    };

    const setCookie = (name, value, days = 365) => {
        const maxAge = days * 24 * 60 * 60;
        document.cookie = `${name}=${encodeURIComponent(value)}; max-age=${maxAge}; path=/; SameSite=Lax`;
    };

    const translateCookieDomains = () => {
        const host = window.location.hostname;
        const domains = [''];

        if (host && host !== 'localhost' && !/^\d+\.\d+\.\d+\.\d+$/.test(host) && host.includes('.')) {
            domains.push(host);
            const parts = host.split('.');
            if (parts.length > 1) domains.push(`.${parts.slice(-2).join('.')}`);
        }

        return [...new Set(domains)];
    };

    const writeRawCookie = (name, value, maxAge, domain = '') => {
        document.cookie = `${name}=${value}; max-age=${maxAge}; path=/${domain ? `; domain=${domain}` : ''}; SameSite=Lax`;
    };

    const saveTranslateCookie = (language) => {
        const safeLanguage = languageLabels[language] ? language : sourceLanguage;

        translateCookieDomains().forEach((domain) => {
            if (safeLanguage === sourceLanguage) {
                writeRawCookie('googtrans', '', 0, domain);
            } else {
                writeRawCookie('googtrans', `/${sourceLanguage}/${safeLanguage}`, 365 * 24 * 60 * 60, domain);
            }
        });
    };

    const languageFromTranslateCookie = () => {
        const cookie = getCookie('googtrans') || '';
        const match = cookie.match(/^\/[^/]+\/([a-z-]+)$/i);
        const language = match ? match[1].toLowerCase() : '';
        return languageLabels[language] ? language : null;
    };

    const loadJson = (key, fallback) => {
        try {
            const value = localStorage.getItem(key);
            return value ? JSON.parse(value) : fallback;
        } catch (error) {
            return fallback;
        }
    };

    const saveJson = (key, value) => {
        localStorage.setItem(key, JSON.stringify(value));
    };

    const getCart = () => {
        const stored = loadJson(cartStorageKey, null);
        if (Array.isArray(stored)) return stored;

        try {
            const legacy = JSON.parse(getCookie(oldCartCookie) || '[]');
            if (Array.isArray(legacy)) {
                const migrated = legacy.map((item) => ({ ...item, line_uid: item.line_uid || makeUid() }));
                saveJson(cartStorageKey, migrated);
                return migrated;
            }
        } catch (error) {
            return [];
        }

        return [];
    };

    const lightweightCart = (cart) => cart.map((item) => {
        const options = { ...(item.options || {}) };

        if (Array.isArray(options.design_items)) {
            options.design_items = options.design_items.map((design) => ({
                ...design,
                image: String(design.image || '').startsWith('data:') ? '' : design.image,
            }));
        }

        if (options.design_previews) {
            options.design_previews = {};
        }

        return { ...item, image: item.image, options };
    });

    const saveCart = (cart) => {
        let saved = false;
        const normalized = cart.map((item) => ({
            ...item,
            line_uid: item.line_uid || makeUid(),
            id: Number(item.id),
            price: Number(item.price || 0),
            basePrice: Number(item.basePrice || item.price || 0),
            qty: Math.max(1, Math.min(100, Number(item.qty || 1))),
            options: item.options || {},
        }));

        try {
            saveJson(cartStorageKey, normalized);
            saved = true;
        } catch (error) {
            try {
                saveJson(cartStorageKey, lightweightCart(normalized));
                saved = true;
                showToast('Coșul a fost salvat, dar imaginile foarte mari au fost simplificate.', 'warning');
            } catch (secondError) {
                showToast('Coșul nu a putut fi salvat. Șterge câteva produse sau imagini prea mari.', 'danger');
            }
        }

        try {
            setCookie(oldCartCookie, JSON.stringify(normalized.map((item) => ({ id: item.id, qty: item.qty }))));
        } catch (error) {
            // Cookie-ul este doar fallback; localStorage rămâne sursa principală.
        }

        updateCartBadge();
        renderCartPage();
        return saved;
    };

    const getPromo = () => loadJson(promoStorageKey, null);

    const savePromo = (promo) => {
        if (promo) saveJson(promoStorageKey, promo);
        else localStorage.removeItem(promoStorageKey);
        renderCartPage();
    };

    const updateCartBadge = () => {
        const badge = document.getElementById('cart-badge');
        if (!badge) return;
        const count = getCart().reduce((sum, item) => sum + Number(item.qty || 0), 0);
        badge.textContent = count;
    };

    const itemHasDesign = (item) => Boolean(item?.options?.design_items?.length || item?.options?.design_previews);

    const simpleItemKey = (item) => [
        Number(item.id),
        item.options?.selected_size || '',
        item.options?.selected_color || '',
    ].join('|');

    const addToCart = (product, replaceLineUid = null) => {
        const cart = getCart();

        if (replaceLineUid) {
            const existingLine = cart.find((item) => item.line_uid === replaceLineUid);
            if (existingLine) {
                existingLine.id = Number(product.id);
                existingLine.name = product.name;
                existingLine.price = Number(product.price || 0);
                existingLine.basePrice = Number(product.basePrice || product.price || 0);
                existingLine.category = product.category;
                existingLine.image = product.image;
                existingLine.url = product.url;
                existingLine.options = product.options || {};
                existingLine.qty = Math.max(1, Number(existingLine.qty || 1));
                saveCart(cart);
                showToast(`${product.name} a fost actualizat în coș.`);
                return;
            }
        }

        const hasDesign = itemHasDesign(product);
        const key = simpleItemKey(product);
        const existing = hasDesign ? null : cart.find((item) => !itemHasDesign(item) && simpleItemKey(item) === key);

        if (existing) {
            existing.qty = Number(existing.qty || 1) + 1;
        } else {
            cart.push({
                line_uid: makeUid(),
                id: Number(product.id),
                name: product.name,
                price: Number(product.price || 0),
                basePrice: Number(product.basePrice || product.price || 0),
                category: product.category,
                image: product.image,
                url: product.url,
                qty: 1,
                options: product.options || {},
            });
        }

        saveCart(cart);
        showToast(`${product.name} a fost adăugat în coș.`);
    };

    const showToast = (message, type = 'success') => {
        const area = document.querySelector('.flash-area');
        if (!area) return;
        const toast = document.createElement('div');
        toast.className = `alert ${type}`;
        toast.setAttribute('data-autodismiss', 'true');
        toast.textContent = message;
        area.appendChild(toast);
        setTimeout(() => toast.remove(), 5000);
    };

    const cartPayload = () => getCart().map((item) => ({
        id: Number(item.id),
        qty: Number(item.qty || 1),
        options: item.options || {},
    }));

    const optionLines = (options = {}) => {
        const lines = [];
        if (options.selected_size) lines.push(`Mărime: ${options.selected_size}`);
        if (options.selected_color) lines.push(`Culoare: ${options.selected_color}`);
        if (options.modification_label) lines.push(options.modification_label);
        if (Number(options.custom_design_fee || 0) > 0) lines.push(`Personalizare: +${money(options.custom_design_fee)}`);
        return lines;
    };

    const preferredPreview = (item) => {
        const previews = item.options?.design_previews || {};
        return previews.front || previews.back || previews.mug || item.image;
    };

    const renderCartPage = () => {
        const page = document.getElementById('cart-page');
        if (!page) return;

        const itemsHolder = document.getElementById('cart-items');
        const subtotalHolder = document.getElementById('cart-subtotal');
        const discountRow = document.getElementById('cart-discount-row');
        const discountHolder = document.getElementById('cart-discount');
        const totalHolder = document.getElementById('cart-total');
        const payloadInput = document.getElementById('cart-payload');
        const checkoutButton = document.getElementById('checkout-button');
        const promoInput = document.getElementById('promocode-input');
        const promoHidden = document.getElementById('promocode-hidden');
        const clearPromo = document.getElementById('clear-promocode');
        const promoMessage = document.getElementById('promo-message');
        const cart = getCart();
        const promo = getPromo();

        if (!itemsHolder || !subtotalHolder || !totalHolder || !payloadInput || !checkoutButton) return;

        if (cart.length === 0) {
            itemsHolder.innerHTML = '<div class="empty-state">Coșul este gol.</div>';
            subtotalHolder.textContent = money(0);
            if (discountRow) discountRow.hidden = true;
            if (discountHolder) discountHolder.textContent = `-${money(0)}`;
            totalHolder.textContent = money(0);
            payloadInput.value = '[]';
            checkoutButton.disabled = true;
            return;
        }

        itemsHolder.innerHTML = cart.map((item) => {
            const previews = item.options?.design_previews || {};
            const lines = optionLines(item.options).map((line) => `<span>${escapeHtml(line)}</span>`).join('');
            const previewBadges = Object.keys(previews).map((side) => `<span class="cart-preview-badge">${side === 'front' ? 'Față' : side === 'back' ? 'Spate' : 'Cană'}</span>`).join('');
            return `
                <article class="cart-item" data-cart-line="${escapeHtml(item.line_uid)}" data-cart-url="${escapeHtml(item.url || '')}" role="link" tabindex="0">
                    <img src="${escapeHtml(preferredPreview(item))}" alt="${escapeHtml(item.name)}">
                    <div>
                        <h3>${escapeHtml(item.name)}</h3>
                        <p>${escapeHtml(item.category)} · ${money(item.price)}</p>
                        ${lines ? `<div class="cart-option-lines">${lines}</div>` : ''}
                        ${previewBadges ? `<div class="cart-preview-badges">${previewBadges}</div>` : ''}
                    </div>
                    <div class="qty-control">
                        <button type="button" data-cart-action="minus" aria-label="Scade cantitatea">−</button>
                        <strong>${Number(item.qty || 1)}</strong>
                        <button type="button" data-cart-action="plus" aria-label="Creste cantitatea">+</button>
                        <button class="remove-cart" type="button" data-cart-action="remove" aria-label="Elimina">×</button>
                    </div>
                </article>
            `;
        }).join('');

        const subtotal = cart.reduce((sum, item) => sum + Number(item.price || 0) * Number(item.qty || 1), 0);
        const discountPercent = Number(promo?.discount_percent || 0);
        const discount = discountPercent > 0 ? subtotal * discountPercent / 100 : 0;
        const total = Math.max(0, subtotal - discount);

        subtotalHolder.textContent = money(subtotal);
        if (discountRow && discountHolder) {
            discountRow.hidden = discount <= 0;
            discountHolder.textContent = `-${money(discount)}`;
        }
        totalHolder.textContent = money(total);
        payloadInput.value = JSON.stringify(cartPayload());
        checkoutButton.disabled = false;

        if (promoInput && promo) promoInput.value = promo.code;
        if (promoHidden) promoHidden.value = promo?.code || '';
        if (clearPromo) clearPromo.hidden = !promo;
        if (promoMessage && promo) {
            promoMessage.textContent = `Promocod aplicat: ${promo.code} (-${Number(promo.discount_percent).toFixed(0)}%).`;
            promoMessage.classList.add('valid');
        }
    };

    const readTheme = () => {
        try {
            const stored = localStorage.getItem('rdm_theme');
            if (stored === 'dark' || stored === 'light') return stored;
        } catch (error) {
            // Cookie fallback below.
        }

        const cookieTheme = getCookie('rdm_theme');
        return cookieTheme === 'dark' || cookieTheme === 'light' ? cookieTheme : 'light';
    };

    const applyTheme = (theme) => {
        const safeTheme = theme === 'dark' ? 'dark' : 'light';
        html.dataset.theme = safeTheme;
        html.style.colorScheme = safeTheme;

        const toggle = document.getElementById('theme-toggle');
        if (toggle) {
            toggle.setAttribute('aria-pressed', safeTheme === 'dark' ? 'true' : 'false');
            toggle.setAttribute('title', safeTheme === 'dark' ? 'Comuta pe light mode' : 'Comuta pe dark mode');
        }
    };

    const saveTheme = (theme) => {
        const safeTheme = theme === 'dark' ? 'dark' : 'light';
        try {
            localStorage.setItem('rdm_theme', safeTheme);
        } catch (error) {
            // Cookie fallback below.
        }
        setCookie('rdm_theme', safeTheme);
    };

    const setupTheme = () => {
        applyTheme(readTheme());

        document.getElementById('theme-toggle')?.addEventListener('click', () => {
            const next = html.dataset.theme === 'dark' ? 'light' : 'dark';
            applyTheme(next);
            saveTheme(next);
        });
    };

    const readLanguage = () => {
        try {
            const stored = localStorage.getItem(languageStorageKey);
            if (languageLabels[stored]) return stored;
        } catch (error) {
            // Cookie fallback below.
        }

        const savedCookie = getCookie(languageStorageKey);
        if (languageLabels[savedCookie]) return savedCookie;

        return languageFromTranslateCookie() || sourceLanguage;
    };

    const pageStateKey = (prefix) => `${prefix}_${window.location.pathname}`;

    const rememberSessionValue = (key, value) => {
        try {
            sessionStorage.setItem(key, String(value));
        } catch (error) {
            // Browserul poate bloca sessionStorage; interfata functioneaza si fara memorare.
        }
    };

    const readSessionValue = (key) => {
        try {
            return sessionStorage.getItem(key);
        } catch (error) {
            return null;
        }
    };

    const carouselImageDatasetKey = (language) => ({ ro: 'imageRo', ru: 'imageRu', en: 'imageEn' }[language] || 'imageRo');

    const applyCarouselLanguage = (language) => {
        const safeLanguage = languageLabels[language] ? language : sourceLanguage;
        const imageKey = carouselImageDatasetKey(safeLanguage);
        document.querySelectorAll('[data-carousel-image]').forEach((image) => {
            const nextSrc = image.dataset[imageKey] || image.dataset.imageRo || image.src;
            if (nextSrc && image.src !== nextSrc) image.src = nextSrc;
        });
    };

    const applyLanguageUi = (language) => {
        const safeLanguage = languageLabels[language] ? language : sourceLanguage;
        html.dataset.lang = safeLanguage;
        applyCarouselLanguage(safeLanguage);

        document.querySelectorAll('[data-current-language]').forEach((label) => {
            label.textContent = languageLabels[safeLanguage];
        });

        document.querySelectorAll('[data-language-option]').forEach((button) => {
            const active = button.dataset.languageOption === safeLanguage;
            button.classList.toggle('active', active);
            button.setAttribute('aria-pressed', active ? 'true' : 'false');
        });
    };

    const saveLanguage = (language) => {
        const safeLanguage = languageLabels[language] ? language : sourceLanguage;
        try {
            localStorage.setItem(languageStorageKey, safeLanguage);
        } catch (error) {
            // Cookie fallback below.
        }
        setCookie(languageStorageKey, safeLanguage);
        saveTranslateCookie(safeLanguage);
        applyLanguageUi(safeLanguage);
        return safeLanguage;
    };

    const setupLanguageSwitcher = () => {
        const switcher = document.querySelector('[data-language-switcher]');
        if (!switcher) return;

        const toggle = switcher.querySelector('[data-language-toggle]');
        const menu = switcher.querySelector('[data-language-menu]');
        if (!toggle || !menu) return;

        const close = () => {
            menu.classList.remove('open');
            toggle.setAttribute('aria-expanded', 'false');
        };

        applyLanguageUi(readLanguage());

        toggle.addEventListener('click', (event) => {
            event.stopPropagation();
            const isOpen = menu.classList.toggle('open');
            toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        });

        menu.querySelectorAll('[data-language-option]').forEach((button) => {
            button.addEventListener('click', (event) => {
                event.preventDefault();
                event.stopPropagation();
                const currentLanguage = readLanguage();
                const nextLanguage = button.dataset.languageOption;
                const savedLanguage = saveLanguage(nextLanguage);
                close();

                if (savedLanguage !== currentLanguage) {
                    window.setTimeout(() => window.location.reload(), 80);
                }
            });
        });

        document.addEventListener('click', (event) => {
            if (!switcher.contains(event.target)) close();
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') close();
        });
    };

    const setupNavbar = () => {
        const navbar = document.getElementById('site-navbar');
        if (!navbar) return;
        let lastY = window.scrollY;
        let ticking = false;

        window.addEventListener('scroll', () => {
            if (ticking) return;
            window.requestAnimationFrame(() => {
                const currentY = window.scrollY;
                if (currentY > lastY && currentY > 130) navbar.classList.add('nav-hidden');
                else navbar.classList.remove('nav-hidden');
                lastY = currentY;
                ticking = false;
            });
            ticking = true;
        }, { passive: true });
    };

    const setupCatalog = () => {
        const toggle = document.getElementById('catalog-toggle');
        const menu = document.getElementById('catalog-menu');
        if (!toggle || !menu) return;

        const closeRows = () => {
            menu.querySelectorAll('.catalog-row.is-open').forEach((row) => {
                row.classList.remove('is-open');
                row.querySelector('[data-catalog-category]')?.setAttribute('aria-expanded', 'false');
            });
        };

        const close = () => {
            toggle.classList.remove('open');
            menu.classList.remove('open');
            toggle.setAttribute('aria-expanded', 'false');
            closeRows();
        };

        menu.querySelectorAll('[data-catalog-category]').forEach((button) => {
            button.addEventListener('click', (event) => {
                event.preventDefault();
                event.stopPropagation();
                const row = button.closest('.catalog-row');
                if (!row) return;
                const isOpen = row.classList.contains('is-open');
                closeRows();
                row.classList.toggle('is-open', !isOpen);
                button.setAttribute('aria-expanded', String(!isOpen));
            });
        });

        menu.querySelectorAll('.catalog-subitem').forEach((link) => {
            link.addEventListener('click', () => close());
        });

        toggle.addEventListener('click', (event) => {
            event.stopPropagation();
            const isOpen = menu.classList.toggle('open');
            toggle.classList.toggle('open', isOpen);
            toggle.setAttribute('aria-expanded', String(isOpen));
        });

        document.addEventListener('click', (event) => {
            if (!menu.contains(event.target) && !toggle.contains(event.target)) close();
        });
    };

    const setupCatalogModal = () => {
        const modal = document.getElementById('catalog-modal');
        const openButton = document.querySelector('[data-open-catalog-modal]');
        const closeButton = document.querySelector('[data-close-catalog-modal]');
        if (!modal || !openButton) return;

        const open = () => {
            modal.hidden = false;
            document.body.classList.add('modal-open');
        };
        const close = () => {
            modal.hidden = true;
            document.body.classList.remove('modal-open');
        };

        openButton.addEventListener('click', open);
        closeButton?.addEventListener('click', close);
        modal.addEventListener('click', (event) => { if (event.target === modal) close(); });
        document.addEventListener('keydown', (event) => { if (event.key === 'Escape') close(); });
    };

    const setupSearch = () => {
        const input = document.getElementById('product-search');
        const results = document.getElementById('search-results');
        if (!input || !results || !window.ReclamRoutes?.search) return;

        let controller = null;
        let timer = null;

        const render = (items) => {
            if (!items.length) {
                results.innerHTML = '<div class="search-result-item"><span>Niciun rezultat gasit.</span></div>';
            } else {
                results.innerHTML = items.map((item) => `
                    <a class="search-result-item" href="${escapeHtml(item.url)}">
                        <strong>${escapeHtml(item.name)}</strong>
                        <span>${escapeHtml(item.category)}</span>
                    </a>
                `).join('');
            }
            results.hidden = false;
        };

        const searchNow = async () => {
            const q = input.value.trim();
            if (q.length < 1) {
                results.hidden = true;
                results.innerHTML = '';
                return;
            }

            controller?.abort();
            controller = new AbortController();
            try {
                const response = await fetch(`${window.ReclamRoutes.search}?q=${encodeURIComponent(q)}`, {
                    headers: { Accept: 'application/json' },
                    signal: controller.signal,
                });
                render(await response.json());
            } catch (error) {
                if (error.name !== 'AbortError') results.hidden = true;
            }
        };

        input.addEventListener('input', () => {
            clearTimeout(timer);
            timer = setTimeout(searchNow, 70);
        });

        document.addEventListener('click', (event) => {
            if (!results.contains(event.target) && event.target !== input) results.hidden = true;
        });
    };

    const setupCarousel = () => {
        const carousel = document.getElementById('hero-carousel');
        if (!carousel) return;
        const slides = [...carousel.querySelectorAll('.slide')];
        const dots = [...carousel.querySelectorAll('.dot')];
        if (!slides.length) return;
        applyCarouselLanguage(readLanguage());
        const savedIndex = Number(readSessionValue(pageStateKey(carouselStateStorageKey)));
        let index = Number.isInteger(savedIndex) ? savedIndex : 0;
        let interval = null;

        const show = (next) => {
            index = (next + slides.length) % slides.length;
            rememberSessionValue(pageStateKey(carouselStateStorageKey), index);
            slides.forEach((slide, i) => slide.classList.toggle('active', i === index));
            dots.forEach((dot, i) => dot.classList.toggle('active', i === index));
        };

        const start = () => {
            clearInterval(interval);
            interval = setInterval(() => show(index + 1), 3200);
        };

        dots.forEach((dot, i) => {
            dot.addEventListener('click', (event) => {
                event.preventDefault();
                show(i);
                start();
            });
        });

        carousel.addEventListener('mouseenter', () => clearInterval(interval));
        carousel.addEventListener('mouseleave', start);
        show(index);
        start();
    };


    const setupCategoryRail = () => {
        document.querySelectorAll('[data-category-carousel]').forEach((carousel) => {
            const rail = carousel.querySelector('[data-category-rail]');
            const prev = carousel.querySelector('[data-category-arrow="prev"]');
            const next = carousel.querySelector('[data-category-arrow="next"]');
            const tiles = rail ? [...rail.querySelectorAll('.category-tile')] : [];
            if (!rail || !tiles.length || (!prev && !next)) return;

            let activeIndex = 0;
            let teaseTimer = null;
            const mobileQuery = window.matchMedia('(max-width: 620px)');

            const tileLeft = (tile) => tile.offsetLeft - rail.offsetLeft;
            const visibleCount = () => {
                const styles = window.getComputedStyle(rail);
                const gap = Number.parseFloat(styles.columnGap || styles.gap || '20') || 20;
                const tileWidth = tiles[0]?.getBoundingClientRect().width || rail.clientWidth;
                return Math.max(1, Math.floor((rail.clientWidth + gap) / Math.max(1, tileWidth + gap)));
            };

            const maxScrollLeft = () => Math.max(0, rail.scrollWidth - rail.clientWidth);
            const isAtStart = () => rail.scrollLeft <= 2;
            const isAtEnd = () => rail.scrollLeft >= maxScrollLeft() - 2;

            const targetLeft = (index) => {
                const tile = tiles[index];
                if (!tile) return 0;
                const start = tileLeft(tile);

                if (!mobileQuery.matches) return start;

                const tileWidth = tile.getBoundingClientRect().width || tile.offsetWidth || rail.clientWidth;
                const centered = start - ((rail.clientWidth - tileWidth) / 2);
                return Math.max(0, Math.min(maxScrollLeft(), centered));
            };

            const syncIndexFromScroll = () => {
                const current = rail.scrollLeft;
                let bestIndex = 0;
                let bestDistance = Number.POSITIVE_INFINITY;
                tiles.forEach((tile, index) => {
                    const distance = Math.abs(targetLeft(index) - current);
                    if (distance < bestDistance) {
                        bestDistance = distance;
                        bestIndex = index;
                    }
                });
                activeIndex = bestIndex;
            };

            const goTo = (index, behavior = 'smooth') => {
                activeIndex = (index + tiles.length) % tiles.length;
                rail.classList.remove('mobile-tease');
                rail.scrollTo({ left: targetLeft(activeIndex), behavior });
                restartTease();
            };

            const go = (direction) => {
                syncIndexFromScroll();
                const count = visibleCount();
                const lastUsefulIndex = Math.max(0, tiles.length - count);

                if (direction > 0) {
                    goTo(activeIndex >= lastUsefulIndex || isAtEnd() ? 0 : activeIndex + 1);
                    return;
                }

                if (activeIndex <= 0 || isAtStart()) {
                    goTo(lastUsefulIndex);
                    return;
                }

                goTo(activeIndex - 1);
            };

            const playTease = () => {
                if (!mobileQuery.matches || tiles.length < 2 || carousel.matches(':hover')) return;
                rail.classList.remove('mobile-tease');
                window.requestAnimationFrame(() => rail.classList.add('mobile-tease'));
            };

            function restartTease() {
                window.clearInterval(teaseTimer);
                if (!mobileQuery.matches) return;
                teaseTimer = window.setInterval(playTease, 5200);
            }

            prev?.addEventListener('click', () => go(-1));
            next?.addEventListener('click', () => go(1));
            rail.addEventListener('scroll', () => window.requestAnimationFrame(syncIndexFromScroll), { passive: true });
            rail.addEventListener('animationend', () => rail.classList.remove('mobile-tease'));
            mobileQuery.addEventListener?.('change', () => {
                rail.classList.remove('mobile-tease');
                goTo(0, 'auto');
                restartTease();
            });

            goTo(0, 'auto');
            restartTease();
        });
    };

    const fileToCompressedDataUrl = (file, maxSize = 900) => new Promise((resolve, reject) => {
        const reader = new FileReader();
        reader.onload = () => {
            const img = new Image();
            img.onload = () => {
                const ratio = Math.min(1, maxSize / Math.max(img.width, img.height));
                const canvas = document.createElement('canvas');
                canvas.width = Math.max(1, Math.round(img.width * ratio));
                canvas.height = Math.max(1, Math.round(img.height * ratio));
                const ctx = canvas.getContext('2d');
                ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
                resolve(canvas.toDataURL('image/png'));
            };
            img.onerror = reject;
            img.src = reader.result;
        };
        reader.onerror = reject;
        reader.readAsDataURL(file);
    });

    const loadImage = (src) => new Promise((resolve, reject) => {
        const img = new Image();
        img.onload = () => resolve(img);
        img.onerror = reject;
        img.src = src;
    });

    const drawContain = (ctx, img, width, height) => {
        const ratio = Math.min(width / img.width, height / img.height);
        const drawWidth = img.width * ratio;
        const drawHeight = img.height * ratio;
        const x = (width - drawWidth) / 2;
        const y = (height - drawHeight) / 2;
        ctx.drawImage(img, x, y, drawWidth, drawHeight);
    };

    const wearableState = {
        items: [],
        selectedId: null,
        side: 'front',
        productId: null,
        restoredFromCartLine: null,
        ready: false,
    };

    const currentDesignFee = () => wearableState.items.length * 15 + (window.ReclamMugState?.texture ? 15 : 0);

    const modificationLabel = () => {
        const front = wearableState.items.some((item) => item.side === 'front');
        const back = wearableState.items.some((item) => item.side === 'back');
        if (front && back) return 'Modificat Față - Spate';
        if (front) return 'Modificat numai Față';
        if (back) return 'Modificat numai Spate';
        if (window.ReclamMugState?.texture) return 'Modificat cană';
        return '';
    };

    const updateCustomizationSummary = () => {
        const priceHolder = document.getElementById('detail-price');
        const note = document.getElementById('design-price-note');
        const spec = document.getElementById('modification-spec');
        const counter = document.getElementById('design-counter');
        const base = Number(priceHolder?.dataset.basePrice || 0);
        const fee = currentDesignFee();
        const label = modificationLabel();

        if (priceHolder) priceHolder.textContent = money(base + fee);
        if (note) {
            note.hidden = fee <= 0;
            note.textContent = `Personalizare: +${money(fee)}`;
        }
        if (spec) {
            spec.hidden = !label;
            const strong = spec.querySelector('strong');
            if (strong) strong.textContent = label.replace('Modificat ', '');
        }
        if (counter) counter.textContent = `${wearableState.items.length} / 4 imagini`;
    };

    const wearableDraftKey = (productId) => `${wearableDraftStorageKey}_${productId || 'product'}`;

    const normalizeSide = (side) => side === 'back' || side === 'spate' ? 'back' : 'front';

    const normalizeDesignItems = (items = []) => Array.isArray(items) ? items
        .filter((item) => item && item.image)
        .slice(0, 4)
        .map((item) => ({
            id: item.id || makeUid(),
            side: normalizeSide(item.side),
            image: item.image,
            x: Math.min(100, Math.max(0, Number(item.x ?? 50))),
            y: Math.min(100, Math.max(0, Number(item.y ?? 42))),
            width: Math.min(360, Math.max(60, Number(item.width ?? 150))),
            rotation: Math.min(180, Math.max(-180, Number(item.rotation ?? 0))),
            aspectRatio: Number(item.aspectRatio || item.ratio || 1),
        })) : [];

    const normalizeWearableOptions = (options = {}) => ({
        selected_size: options.selected_size || '',
        selected_color: options.selected_color || '',
        design_items: normalizeDesignItems(options.design_items),
    });

    const saveWearableDraft = () => {
        if (!wearableState.ready || !wearableState.productId) return;
        const optionFields = {};
        document.querySelectorAll('[data-product-option]').forEach((field) => {
            if (field.value) optionFields[field.dataset.productOption] = field.value;
        });

        const payload = {
            product_id: Number(wearableState.productId),
            side: wearableState.side,
            selectedId: wearableState.selectedId,
            options: optionFields,
            design_items: wearableState.items.map((item) => ({
                id: item.id,
                side: item.side,
                image: item.image,
                x: item.x,
                y: item.y,
                width: item.width,
                rotation: item.rotation || 0,
                aspectRatio: item.aspectRatio || 1,
            })),
        };

        try {
            saveJson(wearableDraftKey(wearableState.productId), payload);
        } catch (error) {
            // localStorage poate fi plin din cauza imaginilor; coșul rămâne sursa principală după adăugare.
        }
    };

    const readWearableDraft = (productId) => loadJson(wearableDraftKey(productId), null);

    const clearWearableDraft = (productId) => {
        if (!productId) return;
        try {
            localStorage.removeItem(wearableDraftKey(productId));
        } catch (error) {
            // Draftul este doar confort local; comanda rămâne salvata in baza de date.
        }
    };

    const clearAllWearableDrafts = () => {
        try {
            Object.keys(localStorage)
                .filter((key) => key.startsWith(`${wearableDraftStorageKey}_`))
                .forEach((key) => localStorage.removeItem(key));
        } catch (error) {
            // Ignoram browserele care restrictioneaza accesul la localStorage.
        }
    };

    const applyProductOptionFields = (options = {}) => {
        document.querySelectorAll('[data-product-option]').forEach((field) => {
            const value = options[field.dataset.productOption];
            if (!value) return;
            const hasOption = field.tagName === 'SELECT'
                ? [...field.options].some((option) => option.value === value)
                : true;
            if (hasOption) field.value = value;
        });
    };

    const restoreWearableDesign = (source = {}) => {
        const sourceOptions = source.options || source;
        const normalized = normalizeWearableOptions({
            ...sourceOptions,
            design_items: source.design_items || sourceOptions.design_items || [],
        });
        wearableState.items = normalized.design_items;
        wearableState.side = normalizeSide(source.side || wearableState.items[0]?.side || 'front');
        wearableState.selectedId = source.selectedId && wearableState.items.some((item) => item.id === source.selectedId)
            ? source.selectedId
            : (wearableState.items.find((item) => item.side === wearableState.side)?.id || wearableState.items[0]?.id || null);
        applyProductOptionFields(sourceOptions);
    };

    const getCartLineFromUrl = () => {
        try {
            return new URLSearchParams(window.location.search).get('cart_line') || '';
        } catch (error) {
            return '';
        }
    };

    const findCartLine = (lineUid) => {
        if (!lineUid) return null;
        return getCart().find((item) => item.line_uid === lineUid) || null;
    };

    const imageAspectRatio = async (src) => {
        try {
            const img = await loadImage(src);
            return img.width && img.height ? img.width / img.height : 1;
        } catch (error) {
            return 1;
        }
    };

    const getBaseDisplayRect = (stage, base) => {
        const stageRect = stage.getBoundingClientRect();
        const naturalWidth = Number(base?.naturalWidth || 0);
        const naturalHeight = Number(base?.naturalHeight || 0);

        if (!stageRect.width || !stageRect.height || !naturalWidth || !naturalHeight) {
            return { left: 0, top: 0, width: stageRect.width || 1, height: stageRect.height || 1 };
        }

        const ratio = Math.min(stageRect.width / naturalWidth, stageRect.height / naturalHeight);
        const width = naturalWidth * ratio;
        const height = naturalHeight * ratio;

        return {
            left: (stageRect.width - width) / 2,
            top: (stageRect.height - height) / 2,
            width,
            height,
        };
    };

    const clampNumber = (value, min, max) => Math.min(max, Math.max(min, Number(value)));

    const normalizePrintArea = (area = {}) => {
        const toPercent = (value, fallback) => {
            const number = Number(value);
            if (!Number.isFinite(number)) return fallback;
            return number > 0 && number <= 1 ? number * 100 : number;
        };

        const x = clampNumber(toPercent(area.x, 0), 0, 95);
        const y = clampNumber(toPercent(area.y, 0), 0, 95);
        const width = clampNumber(toPercent(area.width, 100), 5, 100 - x);
        const height = clampNumber(toPercent(area.height, 100), 5, 100 - y);

        return { x, y, width, height };
    };

    const readWearablePrintAreas = (customizer) => {
        if (customizer._reclamPrintAreas) return customizer._reclamPrintAreas;

        let parsed = {};
        try {
            parsed = JSON.parse(customizer.dataset.printAreas || '{}');
        } catch (error) {
            parsed = {};
        }

        customizer._reclamPrintAreas = {
            front: normalizePrintArea(parsed.front || {}),
            back: normalizePrintArea(parsed.back || parsed.front || {}),
        };

        return customizer._reclamPrintAreas;
    };

    const getPrintableRect = (customizer, baseRect, side = 'front') => {
        const areas = readWearablePrintAreas(customizer);
        const area = areas[side] || areas.front || normalizePrintArea();

        return {
            left: baseRect.left + (area.x / 100) * baseRect.width,
            top: baseRect.top + (area.y / 100) * baseRect.height,
            width: (area.width / 100) * baseRect.width,
            height: (area.height / 100) * baseRect.height,
        };
    };

    const normalizeRotation = (value) => {
        let angle = Number(value || 0);
        while (angle > 180) angle -= 360;
        while (angle < -180) angle += 360;
        return Math.round(angle * 10) / 10;
    };

    const rotatedItemBounds = (item, widthOverride = null) => {
        const widthPx = Number(item.width || 150);
        const width = Number(widthOverride ?? widthPx);
        const height = width / Math.max(0.1, Number(item.aspectRatio || 1));
        const radians = (Number(item.rotation || 0) * Math.PI) / 180;
        const cos = Math.abs(Math.cos(radians));
        const sin = Math.abs(Math.sin(radians));

        return {
            width: width * cos + height * sin,
            height: width * sin + height * cos,
        };
    };

    const fitItemToPrintArea = (item, printArea) => {
        let nextWidth = Math.max(1, Number(item.width || 150));

        for (let i = 0; i < 4; i += 1) {
            const bounds = rotatedItemBounds(item, nextWidth);
            const scale = Math.min(
                1,
                printArea.width / Math.max(1, bounds.width),
                printArea.height / Math.max(1, bounds.height)
            );

            if (scale >= 0.999) break;
            nextWidth = Math.max(1, nextWidth * scale);
        }

        item.width = Math.round(nextWidth * 10) / 10;
        return item;
    };

    const constrainItemToBase = (item, baseRect, printArea = baseRect) => {
        fitItemToPrintArea(item, printArea);

        const bounds = rotatedItemBounds(item);
        const minX = ((printArea.left - baseRect.left) + bounds.width / 2) / Math.max(1, baseRect.width) * 100;
        const maxX = ((printArea.left - baseRect.left + printArea.width) - bounds.width / 2) / Math.max(1, baseRect.width) * 100;
        const minY = ((printArea.top - baseRect.top) + bounds.height / 2) / Math.max(1, baseRect.height) * 100;
        const maxY = ((printArea.top - baseRect.top + printArea.height) - bounds.height / 2) / Math.max(1, baseRect.height) * 100;
        const centerX = ((printArea.left - baseRect.left) + printArea.width / 2) / Math.max(1, baseRect.width) * 100;
        const centerY = ((printArea.top - baseRect.top) + printArea.height / 2) / Math.max(1, baseRect.height) * 100;

        item.x = minX > maxX ? centerX : Math.min(maxX, Math.max(minX, Number(item.x || centerX)));
        item.y = minY > maxY ? centerY : Math.min(maxY, Math.max(minY, Number(item.y || centerY)));
        item.rotation = normalizeRotation(item.rotation || 0);

        return item;
    };

    const drawContainedImage = (ctx, img, width, height) => {
        const ratio = Math.min(width / img.width, height / img.height);
        const drawWidth = img.width * ratio;
        const drawHeight = img.height * ratio;
        const x = (width - drawWidth) / 2;
        const y = (height - drawHeight) / 2;
        ctx.drawImage(img, x, y, drawWidth, drawHeight);
        return { x, y, width: drawWidth, height: drawHeight };
    };

    const setupWearableCustomizer = () => {
        const customizer = document.querySelector('[data-wearable-customizer]');
        if (!customizer) return;

        const stage = document.getElementById('wearable-stage');
        const base = document.getElementById('wearable-base');
        const overlays = document.getElementById('wearable-overlays');
        const upload = document.getElementById('wearable-upload');
        const add = document.querySelector('[data-design-add]');
        const cartButton = document.querySelector('[data-add-cart]');
        const center = document.querySelector('[data-design-center]');
        const remove = document.querySelector('[data-design-remove]');
        const toggle = document.querySelector('[data-side-toggle]');
        const range = document.getElementById('wearable-size');
        const rotationRange = document.getElementById('wearable-rotation');
        const sideLabel = document.getElementById('stage-side-label');
        const productHolder = document.querySelector('.product-side[data-product]');
        const product = readProductPayload(productHolder);
        if (!stage || !base || !overlays || !upload) return;

        wearableState.productId = product?.id || productHolder?.dataset?.productId || null;

        const cartLineUid = getCartLineFromUrl();
        const cartLine = findCartLine(cartLineUid);
        if (cartLine && Number(cartLine.id) === Number(wearableState.productId) && cartLine.options?.design_items?.length) {
            window.ReclamEditingCartLine = cartLineUid;
            restoreWearableDesign({ ...cartLine.options, options: cartLine.options });
            wearableState.restoredFromCartLine = cartLineUid;
            if (cartButton) cartButton.textContent = 'Actualizează în coș 🛒';
        } else {
            const draft = readWearableDraft(wearableState.productId);
            if (draft?.design_items?.length || draft?.options) restoreWearableDesign(draft);
        }

        const render = () => {
            base.src = wearableState.side === 'front' ? customizer.dataset.frontImage : customizer.dataset.backImage;
            stage.dataset.currentSide = wearableState.side;
            if (toggle) toggle.textContent = wearableState.side === 'front' ? 'Spate' : 'Față';
            if (sideLabel) sideLabel.textContent = wearableState.side === 'front' ? 'Față' : 'Spate';

            const baseRect = getBaseDisplayRect(stage, base);
            const printArea = getPrintableRect(customizer, baseRect, wearableState.side);
            wearableState.items
                .filter((item) => item.side === wearableState.side)
                .forEach((item) => constrainItemToBase(item, baseRect, printArea));

            overlays.innerHTML = wearableState.items
                .filter((item) => item.side === wearableState.side)
                .map((item) => {
                    const left = baseRect.left + (item.x / 100) * baseRect.width;
                    const top = baseRect.top + (item.y / 100) * baseRect.height;
                    const selected = item.id === wearableState.selectedId;
                    return `
                        <div class="design-layer ${selected ? 'selected' : ''}"
                             data-layer-id="${escapeHtml(item.id)}"
                             style="left:${left}px; top:${top}px; width:${item.width}px; transform:translate(-50%, -50%) rotate(${Number(item.rotation || 0)}deg)">
                            <img class="design-layer-image"
                                 draggable="false"
                                 src="${escapeHtml(item.image)}"
                                 alt="Imagine design">
                            <button class="design-rotate-handle design-rotate-handle-tl" type="button" data-rotate-handle aria-label="Roteste imaginea din colt"></button>
                            <button class="design-rotate-handle design-rotate-handle-tr" type="button" data-rotate-handle aria-label="Roteste imaginea din colt"></button>
                            <button class="design-rotate-handle design-rotate-handle-br" type="button" data-rotate-handle aria-label="Roteste imaginea din colt"></button>
                            <button class="design-rotate-handle design-rotate-handle-bl" type="button" data-rotate-handle aria-label="Roteste imaginea din colt"></button>
                            <button class="design-resize-handle design-resize-handle-nw" type="button" data-resize-handle="nw" aria-label="Redimensioneaza imaginea"></button>
                            <button class="design-resize-handle design-resize-handle-ne" type="button" data-resize-handle="ne" aria-label="Redimensioneaza imaginea"></button>
                            <button class="design-resize-handle design-resize-handle-se" type="button" data-resize-handle="se" aria-label="Redimensioneaza imaginea"></button>
                            <button class="design-resize-handle design-resize-handle-sw" type="button" data-resize-handle="sw" aria-label="Redimensioneaza imaginea"></button>
                            <button class="design-resize-handle design-resize-handle-n" type="button" data-resize-handle="n" aria-label="Mareste imaginea pe verticala"></button>
                            <button class="design-resize-handle design-resize-handle-e" type="button" data-resize-handle="e" aria-label="Lateste imaginea"></button>
                            <button class="design-resize-handle design-resize-handle-s" type="button" data-resize-handle="s" aria-label="Mareste imaginea pe verticala"></button>
                            <button class="design-resize-handle design-resize-handle-w" type="button" data-resize-handle="w" aria-label="Lateste imaginea"></button>
                        </div>
                    `;
                }).join('');

            const selected = wearableState.items.find((item) => item.id === wearableState.selectedId);
            if (selected && range) range.value = selected.width;
            if (selected && rotationRange) rotationRange.value = selected.rotation || 0;
            updateCustomizationSummary();
            saveWearableDraft();
        };

        const rerenderWhenBaseLoads = () => {
            if (base.complete) return;
            base.addEventListener('load', render, { once: true });
        };

        const selectItem = (id) => {
            wearableState.selectedId = id;
            const selected = wearableState.items.find((item) => item.id === id);
            if (selected && range) range.value = selected.width;
            if (selected && rotationRange) rotationRange.value = selected.rotation || 0;
            render();
        };

        add?.addEventListener('click', () => upload.click());

        upload.addEventListener('change', async (event) => {
            const files = [...(event.target.files || [])];
            if (!files.length) return;

            for (const file of files) {
                if (wearableState.items.length >= 4) {
                    showToast('Poți adăuga maximum 4 imagini.', 'warning');
                    break;
                }

                const image = await fileToCompressedDataUrl(file);
                const id = makeUid();
                wearableState.items.push({
                    id,
                    side: wearableState.side,
                    image,
                    x: 50,
                    y: 42,
                    width: Number(range?.value || 150),
                    rotation: Number(rotationRange?.value || 0),
                    aspectRatio: await imageAspectRatio(image),
                });
                wearableState.selectedId = id;
            }

            upload.value = '';
            render();
        });

        toggle?.addEventListener('click', () => {
            wearableState.side = wearableState.side === 'front' ? 'back' : 'front';
            const firstOnSide = wearableState.items.find((item) => item.side === wearableState.side);
            wearableState.selectedId = firstOnSide?.id || null;
            render();
            rerenderWhenBaseLoads();
        });

        center?.addEventListener('click', () => {
            const item = wearableState.items.find((entry) => entry.id === wearableState.selectedId);
            if (!item) return;
            item.x = 50;
            item.y = 42;
            render();
        });

        remove?.addEventListener('click', () => {
            const index = wearableState.items.findIndex((entry) => entry.id === wearableState.selectedId);
            if (index < 0) return;
            wearableState.items.splice(index, 1);
            wearableState.selectedId = wearableState.items.find((item) => item.side === wearableState.side)?.id || null;
            render();
        });

        range?.addEventListener('input', () => {
            const item = wearableState.items.find((entry) => entry.id === wearableState.selectedId);
            if (!item) return;
            item.width = Number(range.value);
            const baseRect = getBaseDisplayRect(stage, base);
            constrainItemToBase(item, baseRect, getPrintableRect(customizer, baseRect, wearableState.side));
            render();
        });

        rotationRange?.addEventListener('input', () => {
            const item = wearableState.items.find((entry) => entry.id === wearableState.selectedId);
            if (!item) return;
            item.rotation = normalizeRotation(rotationRange.value);
            const baseRect = getBaseDisplayRect(stage, base);
            constrainItemToBase(item, baseRect, getPrintableRect(customizer, baseRect, wearableState.side));
            render();
        });

        document.querySelectorAll('[data-product-option]').forEach((field) => {
            field.addEventListener('change', saveWearableDraft);
        });

        let dragging = null;

        const updateLayerPosition = (layer, item, baseRect) => {
            if (!layer) return;
            const left = baseRect.left + (item.x / 100) * baseRect.width;
            const top = baseRect.top + (item.y / 100) * baseRect.height;
            layer.style.left = `${left}px`;
            layer.style.top = `${top}px`;
            layer.style.width = `${item.width}px`;
            layer.style.transform = `translate(-50%, -50%) rotate(${Number(item.rotation || 0)}deg)`;
        };

        const itemClientCenter = (item, baseRect) => {
            const stageRect = stage.getBoundingClientRect();
            return {
                x: stageRect.left + baseRect.left + (item.x / 100) * baseRect.width,
                y: stageRect.top + baseRect.top + (item.y / 100) * baseRect.height,
            };
        };

        const pointerAngleForItem = (event, item, baseRect) => {
            const centerPoint = itemClientCenter(item, baseRect);
            return Math.atan2(event.clientY - centerPoint.y, event.clientX - centerPoint.x) * 180 / Math.PI;
        };

        const localPointerDelta = (event, drag) => {
            const dx = event.clientX - drag.startX;
            const dy = event.clientY - drag.startY;
            const radians = -(Number(drag.item.rotation || 0) * Math.PI) / 180;
            const cos = Math.cos(radians);
            const sin = Math.sin(radians);

            return {
                x: dx * cos - dy * sin,
                y: dx * sin + dy * cos,
            };
        };

        const resizeWidthFromHandle = (event, drag) => {
            const delta = localPointerDelta(event, drag);
            const handle = drag.handle;
            const aspectRatio = Math.max(0.1, Number(drag.item.aspectRatio || 1));
            const changes = [];

            if (handle.includes('e')) changes.push(delta.x * 2);
            if (handle.includes('w')) changes.push(-delta.x * 2);
            if (handle.includes('s')) changes.push(delta.y * 2 * aspectRatio);
            if (handle.includes('n')) changes.push(-delta.y * 2 * aspectRatio);

            const dominantChange = changes.reduce((best, value) => (
                Math.abs(value) > Math.abs(best) ? value : best
            ), 0);

            return Math.round(clampNumber(drag.startWidth + dominantChange, 60, 360) * 10) / 10;
        };

        overlays.addEventListener('pointerdown', (event) => {
            const layer = event.target.closest('.design-layer');
            if (!layer) return;
            const item = wearableState.items.find((entry) => entry.id === layer.dataset.layerId);
            if (!item) return;
            const rotateHandle = event.target.closest('[data-rotate-handle]');
            const resizeHandle = event.target.closest('[data-resize-handle]');
            const baseRect = getBaseDisplayRect(stage, base);
            wearableState.selectedId = item.id;
            if (range) range.value = item.width;
            if (rotationRange) rotationRange.value = item.rotation || 0;
            overlays.querySelectorAll('.design-layer').forEach((node) => {
                node.classList.toggle('selected', node.dataset.layerId === item.id);
            });
            dragging = resizeHandle
                ? {
                    mode: 'resize',
                    item,
                    capture: resizeHandle,
                    handle: resizeHandle.dataset.resizeHandle,
                    startX: event.clientX,
                    startY: event.clientY,
                    startWidth: Number(item.width || 150),
                }
                : rotateHandle
                ? {
                    mode: 'rotate',
                    item,
                    capture: rotateHandle,
                    startRotation: Number(item.rotation || 0),
                    startPointerAngle: pointerAngleForItem(event, item, baseRect),
                }
                : {
                    mode: 'move',
                    item,
                    capture: layer,
                    startX: event.clientX,
                    startY: event.clientY,
                    startItemX: Number(item.x || 50),
                    startItemY: Number(item.y || 42),
                };
            dragging.capture.setPointerCapture(event.pointerId);
            updateCustomizationSummary();
            saveWearableDraft();
            event.preventDefault();
        });

        overlays.addEventListener('pointermove', (event) => {
            if (!dragging) return;
            const baseRect = getBaseDisplayRect(stage, base);
            const printArea = getPrintableRect(customizer, baseRect, dragging.item.side);

            if (dragging.mode === 'rotate') {
                const pointerAngle = pointerAngleForItem(event, dragging.item, baseRect);
                dragging.item.rotation = normalizeRotation(dragging.startRotation + pointerAngle - dragging.startPointerAngle);
                if (rotationRange) rotationRange.value = dragging.item.rotation;
            } else if (dragging.mode === 'resize') {
                dragging.item.width = resizeWidthFromHandle(event, dragging);
            } else {
                dragging.item.x = dragging.startItemX + ((event.clientX - dragging.startX) / Math.max(1, baseRect.width)) * 100;
                dragging.item.y = dragging.startItemY + ((event.clientY - dragging.startY) / Math.max(1, baseRect.height)) * 100;
            }

            constrainItemToBase(dragging.item, baseRect, printArea);
            const layer = overlays.querySelector(`[data-layer-id="${cssEscape(dragging.item.id)}"]`);
            updateLayerPosition(layer, dragging.item, baseRect);
            if (range) range.value = dragging.item.width;
        });

        const stopDragging = (event) => {
            if (!dragging) return;
            try { dragging.capture?.releasePointerCapture(event.pointerId); } catch (error) { /* ignore */ }
            dragging = null;
            render();
        };
        overlays.addEventListener('pointerup', stopDragging);
        overlays.addEventListener('pointercancel', stopDragging);

        window.addEventListener('resize', render);
        window.addEventListener('pageshow', (event) => {
            if (!event.persisted || getCartLineFromUrl()) return;
            const draft = readWearableDraft(wearableState.productId);
            if (draft?.design_items?.length || !wearableState.items.length) return;
            wearableState.items = [];
            wearableState.selectedId = null;
            render();
        });
        wearableState.ready = true;
        render();
        rerenderWhenBaseLoads();
    };

    const captureWearableSide = async (side) => {
        const customizer = document.querySelector('[data-wearable-customizer]');
        const stage = document.getElementById('wearable-stage');
        if (!customizer || !stage) return null;

        const sideItems = wearableState.items.filter((item) => item.side === side);
        if (!sideItems.length) return null;

        const canvas = document.createElement('canvas');
        canvas.width = 900;
        canvas.height = 900;
        const ctx = canvas.getContext('2d');
        ctx.fillStyle = '#ffffff';
        ctx.fillRect(0, 0, canvas.width, canvas.height);

        const baseSrc = side === 'front' ? customizer.dataset.frontImage : customizer.dataset.backImage;
        let baseDraw = { x: 0, y: 0, width: canvas.width, height: canvas.height };
        try {
            const baseImage = await loadImage(baseSrc);
            baseDraw = drawContainedImage(ctx, baseImage, canvas.width, canvas.height);
        } catch (error) {
            // preview fără baza produsului este mai bun decât pierderea designului
        }

        const stageRect = stage.getBoundingClientRect();
        const stageWidth = Math.max(1, stageRect.width || canvas.width);
        const baseDisplay = (() => {
            const baseElement = document.getElementById('wearable-base');
            return baseElement ? getBaseDisplayRect(stage, baseElement) : { left: 0, top: 0, width: stageWidth, height: stageWidth };
        })();
        const printArea = getPrintableRect(customizer, baseDisplay, side);
        const scale = baseDraw.width / Math.max(1, baseDisplay.width || stageWidth);

        for (const item of sideItems) {
            try {
                constrainItemToBase(item, baseDisplay, printArea);
                const img = await loadImage(item.image);
                const drawWidth = item.width * scale;
                const drawHeight = drawWidth * (img.height / img.width);
                const x = baseDraw.x + (item.x / 100) * baseDraw.width;
                const y = baseDraw.y + (item.y / 100) * baseDraw.height;
                ctx.save();
                ctx.translate(x, y);
                ctx.rotate((Number(item.rotation || 0) * Math.PI) / 180);
                ctx.drawImage(img, -drawWidth / 2, -drawHeight / 2, drawWidth, drawHeight);
                ctx.restore();
            } catch (error) {
                // skip invalid image
            }
        }

        return canvas.toDataURL('image/jpeg', 0.84);
    };

    const collectProductOptions = async (includeDesign) => {
        const options = {};
        document.querySelectorAll('[data-product-option]').forEach((field) => {
            if (field.value) options[field.dataset.productOption] = field.value;
        });

        if (!includeDesign) return { options, fee: 0 };

        const label = modificationLabel();
        if (label) options.modification_label = label;

        if (wearableState.items.length) {
            options.design_items = wearableState.items.map((item) => ({
                id: item.id,
                side: item.side,
                x: item.x,
                y: item.y,
                width: item.width,
                rotation: item.rotation || 0,
                aspectRatio: item.aspectRatio || 1,
                image: item.image,
            }));
            options.design_previews = {};
            const front = await captureWearableSide('front');
            const back = await captureWearableSide('back');
            if (front) options.design_previews.front = front;
            if (back) options.design_previews.back = back;
        }

        if (window.ReclamMugState?.texture) {
            options.modification_label = 'Modificat cană';
            options.design_items = [{ side: 'mug', x: 50, y: 50, width: 150, rotation: 0, image: window.ReclamMugState.texture }];
            const canvas = window.ReclamMugState.canvas;
            let preview = null;
            try { preview = canvas?.toDataURL('image/png'); } catch (error) { preview = null; }
            options.design_previews = { mug: preview || window.ReclamMugState.texture };
        }

        options.custom_design_fee = currentDesignFee();
        return { options, fee: currentDesignFee() };
    };

    const setupMugEvents = () => {
        document.addEventListener('reclam:mug-texture-added', updateCustomizationSummary);
    };

    const readProductPayload = (holder) => {
        if (!holder?.dataset?.product) return null;
        try {
            return JSON.parse(holder.dataset.product);
        } catch (error) {
            try {
                const textarea = document.createElement('textarea');
                textarea.innerHTML = holder.dataset.product;
                return JSON.parse(textarea.value);
            } catch (secondError) {
                return null;
            }
        }
    };

    const isInteractiveElement = (target) => Boolean(target.closest('a, button, input, select, textarea, label, form, [data-add-cart], [data-cart-action], .card-admin-actions'));

    const handleAddToCartClick = async (event) => {
        const addButton = event.target.closest('[data-add-cart]');
        if (!addButton) return false;

        event.preventDefault();
        event.stopPropagation();
        event.stopImmediatePropagation?.();

        if (addButton.disabled) return true;
        addButton.disabled = true;

        const holder = addButton.closest('[data-product]');
        const product = readProductPayload(holder);
        if (!product) {
            showToast('Produsul nu a putut fi citit. Reîncarcă pagina și încearcă din nou.', 'danger');
            addButton.disabled = false;
            return true;
        }

        try {
            const visibleProductImage = addButton.closest('.product-card')?.querySelector('img') || document.getElementById('main-product-image');
            if (visibleProductImage?.src) product.image = visibleProductImage.src;
            const includeDesign = holder.classList.contains('product-side');
            const { options, fee } = await collectProductOptions(includeDesign);
            product.basePrice = Number(product.basePrice || product.price || 0);
            product.price = product.basePrice + fee;
            product.options = options;
            addToCart(product, window.ReclamEditingCartLine || null);
        } catch (error) {
            console.error('Produs invalid', error);
            showToast('Produsul nu a putut fi adăugat în coș.', 'danger');
        } finally {
            addButton.disabled = false;
        }

        return true;
    };

    const setupProductCardNavigation = () => {
        document.addEventListener('click', (event) => {
            const card = event.target.closest('.product-card[data-product-url]');
            if (!card || isInteractiveElement(event.target)) return;
            window.location.href = card.dataset.productUrl;
        });

        document.addEventListener('keydown', (event) => {
            if (event.key !== 'Enter' && event.key !== ' ') return;
            const card = event.target.closest?.('.product-card[data-product-url]');
            if (!card || isInteractiveElement(event.target)) return;
            event.preventDefault();
            window.location.href = card.dataset.productUrl;
        });
    };

    const setupCartEvents = () => {
        document.addEventListener('click', async (event) => {
            if (await handleAddToCartClick(event)) return;

            const actionButton = event.target.closest('[data-cart-action]');
            if (!actionButton) return;

            event.preventDefault();
            event.stopPropagation();
            const row = actionButton.closest('[data-cart-line]');
            if (!row) return;
            const line = row.dataset.cartLine;
            const cart = getCart();
            const item = cart.find((entry) => entry.line_uid === line);
            if (!item) return;

            const action = actionButton.dataset.cartAction;
            if (action === 'plus') item.qty = Math.min(100, Number(item.qty || 1) + 1);
            if (action === 'minus') item.qty = Math.max(1, Number(item.qty || 1) - 1);
            if (action === 'remove') cart.splice(cart.indexOf(item), 1);
            saveCart(cart);
        }, true);

        document.addEventListener('click', (event) => {
            const row = event.target.closest('#cart-items .cart-item[data-cart-line]');
            if (!row || isInteractiveElement(event.target)) return;
            const item = getCart().find((entry) => entry.line_uid === row.dataset.cartLine);
            if (!item?.url) return;
            const separator = item.url.includes('?') ? '&' : '?';
            window.location.href = `${item.url}${separator}cart_line=${encodeURIComponent(item.line_uid)}`;
        });

        document.addEventListener('keydown', (event) => {
            if (event.key !== 'Enter' && event.key !== ' ') return;
            const row = event.target.closest?.('#cart-items .cart-item[data-cart-line]');
            if (!row) return;
            event.preventDefault();
            const item = getCart().find((entry) => entry.line_uid === row.dataset.cartLine);
            if (!item?.url) return;
            const separator = item.url.includes('?') ? '&' : '?';
            window.location.href = `${item.url}${separator}cart_line=${encodeURIComponent(item.line_uid)}`;
        });
    };


    const setupCheckout = () => {
        const form = document.getElementById('checkout-form');
        if (!form) return;

        const validateFields = () => {
            const messages = [];
            form.querySelectorAll('input[required]').forEach((input) => {
                const valid = input.type === 'email' ? input.value.trim() && input.checkValidity() : input.value.trim().length > 0;
                input.classList.toggle('field-invalid', !valid);
                input.classList.toggle('field-valid', Boolean(valid));
                if (!valid) messages.push(input.dataset.requiredMessage || 'Completați câmpul obligatoriu.');
            });
            const box = document.getElementById('checkout-validation');
            if (box) {
                box.hidden = messages.length === 0;
                box.innerHTML = messages.map((message) => `<div>${escapeHtml(message)}</div>`).join('');
            }
            return messages.length === 0;
        };

        form.querySelectorAll('input[required]').forEach((input) => {
            input.addEventListener('input', validateFields);
        });

        form.addEventListener('submit', (event) => {
            const payloadInput = document.getElementById('cart-payload');
            const promoHidden = document.getElementById('promocode-hidden');
            if (payloadInput) payloadInput.value = JSON.stringify(cartPayload());
            if (promoHidden) promoHidden.value = getPromo()?.code || '';

            if (!validateFields()) {
                event.preventDefault();
                showToast('Introduceți Nume, Telefon, Email, și apoi veți putea comanda.', 'danger');
            }
        });
    };

    const setupPromocode = () => {
        const apply = document.getElementById('apply-promocode');
        const input = document.getElementById('promocode-input');
        const clear = document.getElementById('clear-promocode');
        const message = document.getElementById('promo-message');
        if (!apply || !input || !window.ReclamRoutes?.promoCheck) return;

        apply.addEventListener('click', async () => {
            const code = input.value.trim();
            if (!code) {
                message.textContent = 'Introduce codul promocodului.';
                message.classList.remove('valid');
                message.classList.add('invalid');
                return;
            }

            try {
                const response = await fetch(window.ReclamRoutes.promoCheck, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': window.ReclamCsrf,
                    },
                    body: JSON.stringify({ code }),
                });
                const data = await response.json();
                if (!response.ok || !data.valid) throw new Error(data.message || 'Promocod invalid.');
                savePromo({ code: data.code, discount_percent: Number(data.discount_percent || 0) });
                message.textContent = data.message;
                message.classList.add('valid');
                message.classList.remove('invalid');
                showToast(data.message);
            } catch (error) {
                savePromo(null);
                message.textContent = error.message || 'Promocod invalid sau dezactivat.';
                message.classList.add('invalid');
                message.classList.remove('valid');
            }
        });

        clear?.addEventListener('click', () => {
            savePromo(null);
            input.value = '';
            message.textContent = '';
            message.classList.remove('valid', 'invalid');
        });
    };

    const setupGallery = () => {
        const main = document.getElementById('main-product-image');
        if (!main) return;
        const buttons = [...document.querySelectorAll('.thumb-button')];
        const stateKey = pageStateKey(galleryStateStorageKey);

        const showThumb = (button, index) => {
            const imageKey = carouselImageDatasetKey(readLanguage());
            ['imageRo', 'imageRu', 'imageEn'].forEach((key) => {
                if (button.dataset[key]) main.dataset[key] = button.dataset[key];
                else delete main.dataset[key];
            });
            if (button.dataset.imageRo || button.dataset.imageRu || button.dataset.imageEn) {
                main.setAttribute('data-carousel-image', '');
            } else {
                main.removeAttribute('data-carousel-image');
            }
            main.src = button.dataset[imageKey] || button.dataset.image;
            buttons.forEach((entry) => entry.classList.toggle('active', entry === button));
            rememberSessionValue(stateKey, index);
        };

        buttons.forEach((button, index) => {
            button.addEventListener('click', () => showThumb(button, index));
        });

        const savedIndex = Number(readSessionValue(stateKey));
        if (Number.isInteger(savedIndex) && buttons[savedIndex]) showThumb(buttons[savedIndex], savedIndex);
    };


    const setupAdminCategoryForm = () => {
        const form = document.querySelector('[data-admin-category-form]');
        const modal = document.getElementById('category-wizard-modal');
        if (!form || !modal) return;

        const createAction = form.getAttribute('action');
        const openButtons = [...document.querySelectorAll('[data-open-category-modal]')];
        const closeButton = form.querySelector('[data-close-category-modal]');
        const title = document.getElementById('category-wizard-title');
        const eyebrow = form.querySelector('.wizard-head .eyebrow');
        const prevButton = form.querySelector('[data-category-prev]');
        const nextButton = form.querySelector('[data-category-next]');
        const finishButton = form.querySelector('[data-category-finish]');
        const nameInput = form.querySelector('[data-category-name]');
        const iconInput = form.querySelector('[data-category-icon]');
        const descriptionInput = form.querySelector('[data-category-description]');
        const carouselLanguages = ['ro', 'ru', 'en'];
        const imageUrlInputs = Object.fromEntries(carouselLanguages.map((language) => [
            language,
            form.querySelector(`[data-category-carousel-image-url-${language}]`),
        ]));
        const imageUploadInputs = Object.fromEntries(carouselLanguages.map((language) => [
            language,
            form.querySelector(`[data-category-carousel-image-upload-${language}]`),
        ]));
        const clearImageWrap = form.querySelector('[data-clear-category-carousel-image-wrap]');
        const clearImageInput = form.querySelector('[data-clear-category-carousel-image]');
        const carouselTitleInput = form.querySelector('[data-category-carousel-title]');
        const carouselLabelInput = form.querySelector('[data-category-carousel-label]');
        const carouselTextInput = form.querySelector('[data-category-carousel-text]');
        const carouselPositionSelect = form.querySelector('[data-category-carousel-position]');
        const generateDescription = form.querySelector('[data-generate-category-description]');

        let currentStep = 1;

        const ensureMethodInput = () => {
            let methodInput = form.querySelector('input[name="_method"][data-category-edit-method]');
            if (!methodInput) {
                methodInput = document.createElement('input');
                methodInput.type = 'hidden';
                methodInput.name = '_method';
                methodInput.dataset.categoryEditMethod = 'true';
                form.appendChild(methodInput);
            }
            return methodInput;
        };

        const removeMethodInput = () => {
            form.querySelector('input[name="_method"][data-category-edit-method]')?.remove();
        };

        const setStep = (step) => {
            currentStep = step;
            form.querySelectorAll('[data-category-step]').forEach((stepElement) => {
                const active = Number(stepElement.dataset.categoryStep) === currentStep;
                stepElement.hidden = !active;
                stepElement.classList.toggle('active', active);
            });
            form.querySelectorAll('[data-category-step-indicator]').forEach((indicator) => {
                indicator.classList.toggle('active', Number(indicator.dataset.categoryStepIndicator) === currentStep);
            });
            if (prevButton) prevButton.hidden = currentStep === 1;
            if (nextButton) nextButton.hidden = currentStep !== 1;
            if (finishButton) finishButton.hidden = currentStep !== 2;
        };

        const showModal = () => {
            modal.hidden = false;
            document.body.classList.add('modal-open');
            setStep(1);
            nameInput?.focus();
        };

        const closeModal = () => {
            modal.hidden = true;
            document.body.classList.remove('modal-open');
        };

        const resetToCreate = () => {
            form.setAttribute('action', createAction);
            removeMethodInput();
            form.reset();
            if (title) title.textContent = 'Adauga categorie';
            if (eyebrow) eyebrow.textContent = 'Categorie noua';
            if (finishButton) finishButton.textContent = 'Finalizeaza';
            if (clearImageWrap) clearImageWrap.hidden = true;
            if (clearImageInput) clearImageInput.checked = false;
            setStep(1);
        };

        const fillField = (field, value) => {
            if (field) field.value = value || '';
        };

        openButtons.forEach((openButton) => {
            openButton.addEventListener('click', () => {
                resetToCreate();
                showModal();
            });
        });

        closeButton?.addEventListener('click', closeModal);
        modal.addEventListener('click', (event) => {
            if (event.target === modal) closeModal();
        });
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && !modal.hidden) closeModal();
        });

        nextButton?.addEventListener('click', () => {
            const stepOne = form.querySelector('[data-category-step="1"]');
            if (!customValidateForm(form, { scope: stepOne })) return;
            setStep(2);
        });
        prevButton?.addEventListener('click', () => setStep(1));

        generateDescription?.addEventListener('click', () => {
            if (!descriptionInput) return;
            const name = nameInput?.value.trim() || 'Aceasta categorie';
            descriptionInput.value = `${name} include produse personalizabile, pregatite pentru catalog, filtrare si cautare rapida.`;
            descriptionInput.focus();
        });

        document.querySelectorAll('[data-edit-category]').forEach((button) => {
            button.addEventListener('click', () => {
                form.setAttribute('action', button.dataset.updateUrl || createAction);
                ensureMethodInput().value = 'PUT';
                if (title) title.textContent = 'Modifica categorie';
                if (eyebrow) eyebrow.textContent = 'Editare categorie';
                if (finishButton) finishButton.textContent = 'Salveaza modificarile';

                form.reset();
                fillField(nameInput, button.dataset.name);
                fillField(iconInput, button.dataset.icon);
                fillField(descriptionInput, button.dataset.description);
                carouselLanguages.forEach((language) => {
                    fillField(imageUrlInputs[language], button.dataset[`carouselImageUrl${language.charAt(0).toUpperCase()}${language.slice(1)}`]);
                    if (imageUploadInputs[language]) imageUploadInputs[language].value = '';
                });
                fillField(carouselTitleInput, button.dataset.carouselTitle);
                fillField(carouselLabelInput, button.dataset.carouselLabel);
                fillField(carouselTextInput, button.dataset.carouselText);
                if (carouselPositionSelect) carouselPositionSelect.value = button.dataset.carouselPosition || 'bottom-left';
                if (clearImageWrap) clearImageWrap.hidden = button.dataset.hasCarouselImage !== '1';
                if (clearImageInput) clearImageInput.checked = false;

                showModal();
            });
        });
    };

    const setupAdminSubcategoryForm = () => {
        const form = document.querySelector('[data-admin-subcategory-form]');
        if (!form) return;

        const modal = document.getElementById('subcategory-wizard-modal');
        const openButton = document.querySelector('[data-open-subcategory-modal]');
        const closeButton = form.querySelector('[data-close-subcategory-modal]');
        const categorySelect = form.querySelector('[data-subcategory-category-select]');
        const featureLabels = [...form.querySelectorAll('[data-subcategory-feature]')];
        const existingCustomList = form.querySelector('[data-existing-custom-feature-list]');
        const customFeatureList = form.querySelector('[data-new-custom-feature-list]');
        const addCustomFeature = form.querySelector('[data-add-custom-feature]');
        const generateDescription = form.querySelector('[data-generate-subcategory-description]');
        const description = form.querySelector('[data-subcategory-description]');
        const nameInput = form.querySelector('[data-subcategory-name]');
        const prevButton = form.querySelector('[data-subcategory-prev]');
        const nextButton = form.querySelector('[data-subcategory-next]');
        const finishButton = form.querySelector('[data-subcategory-finish]');
        const wizardCatalog = Array.isArray(window.ReclamSubcategoryWizard?.categories)
            ? window.ReclamSubcategoryWizard.categories
            : [];
        if (!categorySelect || !featureLabels.length) return;

        let currentStep = 1;

        const categoryById = (id) => wizardCatalog.find((category) => Number(category.id) === Number(id));
        const currentCategory = () => categoryById(categorySelect.value);

        const renderExistingCustomFeatures = () => {
            if (!existingCustomList) return;
            const customFeatures = currentCategory()?.customFeatures || {};
            const entries = Object.entries(customFeatures);

            if (!entries.length) {
                existingCustomList.innerHTML = '<p class="muted">Nu exista caracteristici custom pentru categoria aleasa.</p>';
                return;
            }

            existingCustomList.innerHTML = entries.map(([key, definition]) => `
                <label>
                    <input type="checkbox" name="features[]" value="${escapeHtml(key)}">
                    ${escapeHtml(definition?.label || key)}
                </label>
            `).join('');
        };

        const refreshFeatures = () => {
            const allowedFeatures = new Set(currentCategory()?.allowedFeatures || []);

            featureLabels.forEach((label) => {
                const input = label.querySelector('input[type="checkbox"]');
                const isAllowed = allowedFeatures.has(label.dataset.subcategoryFeature);

                label.hidden = !isAllowed;
                label.classList.toggle('field-hidden', !isAllowed);

                if (!isAllowed && input) {
                    input.checked = false;
                    input.disabled = true;
                } else if (input) {
                    input.disabled = false;
                }
            });
            renderExistingCustomFeatures();
        };

        const setStep = (step) => {
            currentStep = step;
            form.querySelectorAll('[data-subcategory-step]').forEach((stepElement) => {
                const active = Number(stepElement.dataset.subcategoryStep) === currentStep;
                stepElement.hidden = !active;
                stepElement.classList.toggle('active', active);
            });
            form.querySelectorAll('[data-step-indicator]').forEach((indicator) => {
                indicator.classList.toggle('active', Number(indicator.dataset.stepIndicator) === currentStep);
            });

            if (prevButton) prevButton.hidden = currentStep === 1;
            if (nextButton) nextButton.hidden = currentStep !== 1;
            if (finishButton) finishButton.hidden = currentStep !== 2;
        };

        const openModal = () => {
            if (!modal) return;
            modal.hidden = false;
            document.body.classList.add('modal-open');
            setStep(1);
            refreshFeatures();
            categorySelect.focus();
        };

        const closeModal = () => {
            if (!modal) return;
            modal.hidden = true;
            document.body.classList.remove('modal-open');
        };

        const addCustomFeatureInput = () => {
            if (!customFeatureList) return;
            const label = document.createElement('label');
            label.innerHTML = `
                Nume caracteristica
                <span class="custom-feature-row">
                    <input name="custom_features[]" placeholder="Ex: Material" maxlength="80">
                    <button class="tiny-btn secondary-btn" type="button" data-remove-custom-feature>Elimina</button>
                </span>
            `;
            customFeatureList.appendChild(label);
            label.querySelector('input')?.focus();
        };

        categorySelect.addEventListener('change', refreshFeatures);
        openButton?.addEventListener('click', openModal);
        closeButton?.addEventListener('click', closeModal);
        modal?.addEventListener('click', (event) => {
            if (event.target === modal) closeModal();
        });
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && modal && !modal.hidden) closeModal();
        });
        nextButton?.addEventListener('click', () => {
            const stepOne = form.querySelector('[data-subcategory-step="1"]');
            if (!customValidateForm(form, { scope: stepOne })) return;
            setStep(2);
        });
        prevButton?.addEventListener('click', () => setStep(1));
        addCustomFeature?.addEventListener('click', addCustomFeatureInput);
        customFeatureList?.addEventListener('click', (event) => {
            const removeButton = event.target.closest('[data-remove-custom-feature]');
            if (!removeButton) return;
            removeButton.closest('label')?.remove();
        });
        generateDescription?.addEventListener('click', () => {
            if (!description) return;
            const categoryName = currentCategory()?.name || 'categoria aleasa';
            const subcategoryName = nameInput?.value.trim() || 'aceasta subcategorie';
            description.value = `${subcategoryName} include produse din categoria ${categoryName}, pregatite pentru personalizare si filtrare rapida in catalog.`;
            description.focus();
        });
        refreshFeatures();
        setStep(1);
    };

    const setupAdminProductForm = () => {
        const form = document.querySelector('[data-admin-product-form]');
        if (!form || !Array.isArray(window.ReclamAdminCatalog)) return;
        const categorySelect = document.getElementById('admin-category-select');
        const subcategorySelect = document.getElementById('admin-subcategory-select');
        const fields = [...form.querySelectorAll('[data-product-field]')];
        const customFields = [...form.querySelectorAll('[data-product-custom-feature]')];
        if (!categorySelect || !subcategorySelect) return;

        const categoryById = (id) => window.ReclamAdminCatalog.find((category) => Number(category.id) === Number(id));
        const subcategoryById = (id) => window.ReclamAdminCatalog.flatMap((category) => category.subcategories || []).find((subcategory) => Number(subcategory.id) === Number(id));

        const refresh = (clearHidden = false) => {
            const category = categoryById(categorySelect.value);
            [...subcategorySelect.options].forEach((option) => {
                if (!option.value) {
                    option.hidden = false;
                    return;
                }
                option.hidden = Number(option.dataset.categoryId) !== Number(categorySelect.value);
            });

            const currentOption = subcategorySelect.selectedOptions[0];
            if (currentOption?.hidden) subcategorySelect.value = '';

            const subcategory = subcategoryById(subcategorySelect.value);
            const features = subcategory?.features && Object.keys(subcategory.features).length ? subcategory.features : (category?.features || {});

            fields.forEach((field) => {
                const key = field.dataset.productField;
                const visible = Boolean(features[key]);
                field.hidden = !visible;
                field.classList.toggle('field-hidden', !visible);
                if (!visible && clearHidden) {
                    const input = field.querySelector('input, textarea, select');
                    if (input) input.value = '';
                }
            });

            customFields.forEach((field) => {
                const key = field.dataset.productCustomFeature;
                const visible = Boolean(features[key]);
                field.hidden = !visible;
                field.classList.toggle('field-hidden', !visible);
                if (!visible && clearHidden) {
                    const input = field.querySelector('input, textarea, select');
                    if (input) input.value = '';
                }
            });
        };

        categorySelect.addEventListener('change', () => refresh(true));
        subcategorySelect.addEventListener('change', () => refresh(true));
        form.closest('form')?.addEventListener('submit', () => refresh(true));
        refresh(false);
    };



    const setupClientStockFilterCleanup = () => {
        if (document.body?.dataset.isAdmin === 'true') return;
        const filterForm = document.querySelector('.filter-form');
        if (!filterForm) return;

        filterForm.querySelectorAll('.filter-group').forEach((group) => {
            const summary = group.querySelector('summary');
            if (summary && summary.textContent.trim().toLowerCase() === 'stoc') {
                group.hidden = true;
            }
        });

        const stockSelect = filterForm.querySelector('select[name="stock"]');
        if (stockSelect) stockSelect.value = '';
    };

    const setupAdminSubcategoryEditButtons = () => {
        const form = document.querySelector('[data-admin-subcategory-form]');
        const modal = document.getElementById('subcategory-wizard-modal');
        if (!form || !modal) return;

        const createAction = form.getAttribute('action');
        const title = document.getElementById('subcategory-wizard-title');
        const eyebrow = form.querySelector('.wizard-head .eyebrow');
        const finishButton = form.querySelector('[data-subcategory-finish]');
        const categorySelect = form.querySelector('[name="category_id"]');
        const nameInput = form.querySelector('[name="name"]');
        const iconInput = form.querySelector('[name="icon"]');
        const imageUrlInput = form.querySelector('[name="image_url"]');
        const imageUploadInput = form.querySelector('[name="image_upload"]');
        const descriptionInput = form.querySelector('[name="description"]');

        const ensureMethodInput = () => {
            let methodInput = form.querySelector('input[name="_method"][data-subcategory-edit-method]');
            if (!methodInput) {
                methodInput = document.createElement('input');
                methodInput.type = 'hidden';
                methodInput.name = '_method';
                methodInput.dataset.subcategoryEditMethod = 'true';
                form.appendChild(methodInput);
            }
            return methodInput;
        };

        const removeMethodInput = () => {
            form.querySelector('input[name="_method"][data-subcategory-edit-method]')?.remove();
        };

        const setStepOne = () => {
            form.querySelectorAll('[data-subcategory-step]').forEach((step) => {
                const active = step.dataset.subcategoryStep === '1';
                step.hidden = !active;
                step.classList.toggle('active', active);
            });
            form.querySelectorAll('[data-step-indicator]').forEach((indicator) => {
                indicator.classList.toggle('active', indicator.dataset.stepIndicator === '1');
            });
            const prevButton = form.querySelector('[data-subcategory-prev]');
            const nextButton = form.querySelector('[data-subcategory-next]');
            if (prevButton) prevButton.hidden = true;
            if (nextButton) nextButton.hidden = false;
            if (finishButton) finishButton.hidden = true;
        };

        const resetToCreate = () => {
            form.setAttribute('action', createAction);
            removeMethodInput();
            form.reset();
            if (title) title.textContent = 'Adauga subcategorie';
            if (eyebrow) eyebrow.textContent = 'Subcategorie noua';
            if (finishButton) finishButton.textContent = 'Finalizeaza';
            setStepOne();
            categorySelect?.dispatchEvent(new Event('change', { bubbles: true }));
        };

        const decodeFeaturePayload = (payload) => {
            try {
                return JSON.parse(atob(payload || 'e30='));
            } catch (error) {
                return {};
            }
        };

        const markFeatures = (features) => {
            const keys = Object.keys(features || {});
            form.querySelectorAll('input[name="features[]"]').forEach((input) => {
                input.checked = keys.includes(input.value);
            });
        };

        document.querySelector('[data-open-subcategory-modal]')?.addEventListener('click', () => {
            window.setTimeout(resetToCreate, 0);
        });

        document.querySelectorAll('[data-edit-subcategory]').forEach((button) => {
            button.addEventListener('click', () => {
                form.setAttribute('action', button.dataset.updateUrl || createAction);
                ensureMethodInput().value = 'PUT';
                if (title) title.textContent = 'Modifica subcategorie';
                if (eyebrow) eyebrow.textContent = 'Editare subcategorie';
                if (finishButton) finishButton.textContent = 'Salveaza modificarile';

                if (categorySelect) categorySelect.value = button.dataset.categoryId || categorySelect.value;
                if (nameInput) nameInput.value = button.dataset.name || '';
                if (iconInput) iconInput.value = button.dataset.icon || '';
                if (imageUrlInput) imageUrlInput.value = button.dataset.imageUrl || '';
                if (imageUploadInput) imageUploadInput.value = '';
                if (descriptionInput) descriptionInput.value = button.dataset.description || '';

                modal.hidden = false;
                document.body.classList.add('modal-open');
                setStepOne();
                categorySelect?.dispatchEvent(new Event('change', { bubbles: true }));
                window.setTimeout(() => markFeatures(decodeFeaturePayload(button.dataset.features)), 0);
                nameInput?.focus();
            });
        });
    };
    const setupConfirmForms = () => {
        document.querySelectorAll('form[data-confirm]').forEach((form) => {
            form.addEventListener('submit', (event) => {
                if (!confirm(form.dataset.confirm || 'Confirmi actiunea?')) {
                    event.preventDefault();
                }
            });
        });
    };

    const setupLiveAdminOrderSearch = () => {
        const form = document.querySelector('[data-live-order-search]');
        const input = form?.querySelector('[data-live-order-search-input]');
        const results = document.querySelector('[data-order-results]');
        if (!form || !input || !results) return;

        let timer = null;
        let activeRequest = null;

        const makeSearchUrl = (pageUrl = null) => {
            const base = pageUrl ? new URL(pageUrl, window.location.origin) : new URL(form.action, window.location.origin);
            const params = new URLSearchParams(pageUrl ? base.search : window.location.search);
            const query = input.value.trim();

            if (query) {
                params.set('order', query);
            } else {
                params.delete('order');
            }

            if (!pageUrl) params.delete('page');
            base.search = params.toString();
            return base;
        };

        const replaceResultsFrom = async (url, updateHistory = true) => {
            let requestController = null;
            try {
                if (activeRequest) activeRequest.abort();
                requestController = new AbortController();
                activeRequest = requestController;
                results.setAttribute('aria-busy', 'true');

                const response = await fetch(url.toString(), {
                    headers: {
                        'Accept': 'text/html',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    signal: requestController.signal,
                });

                if (!response.ok) throw new Error('Căutarea nu a reușit.');

                const html = await response.text();
                const documentCopy = new DOMParser().parseFromString(html, 'text/html');
                const freshResults = documentCopy.querySelector('[data-order-results]');
                if (!freshResults) throw new Error('Rezultatele nu au putut fi citite.');

                results.innerHTML = freshResults.innerHTML;
                if (updateHistory) {
                    window.history.replaceState({}, '', `${url.pathname}${url.search}`);
                }
            } catch (error) {
                if (error.name !== 'AbortError') {
                    // Dacă AJAX nu poate actualiza lista, formularul rămâne functional prin butonul Caută.
                    console.warn(error);
                }
            } finally {
                if (activeRequest === requestController) {
                    results.removeAttribute('aria-busy');
                    activeRequest = null;
                }
            }
        };

        const runLiveSearch = () => {
            window.clearTimeout(timer);
            timer = window.setTimeout(() => {
                replaceResultsFrom(makeSearchUrl(), true);
            }, 250);
        };

        input.addEventListener('input', runLiveSearch);

        form.addEventListener('submit', (event) => {
            event.preventDefault();
            window.clearTimeout(timer);
            replaceResultsFrom(makeSearchUrl(), true);
        });

        results.addEventListener('click', (event) => {
            const link = event.target.closest('.pagination-wrap a');
            if (!link) return;
            event.preventDefault();
            replaceResultsFrom(makeSearchUrl(link.href), true);
        });
    };


    const setupLiveAdminProductSearch = () => {
        const form = document.querySelector('[data-live-product-search]');
        const input = form?.querySelector('[data-live-product-search-input]');
        const results = document.querySelector('[data-product-results]');
        if (!form || !input || !results) return;

        let timer = null;
        let activeRequest = null;

        const makeSearchUrl = (pageUrl = null) => {
            const base = pageUrl ? new URL(pageUrl, window.location.origin) : new URL(form.action, window.location.origin);
            const params = new URLSearchParams(pageUrl ? base.search : window.location.search);
            const query = input.value.trim();

            if (query) {
                params.set('product', query);
            } else {
                params.delete('product');
            }

            if (!pageUrl) params.delete('products_page');
            base.search = params.toString();
            base.hash = 'admin-products';
            return base;
        };

        const replaceResultsFrom = async (url, updateHistory = true) => {
            let requestController = null;
            try {
                if (activeRequest) activeRequest.abort();
                requestController = new AbortController();
                activeRequest = requestController;
                results.setAttribute('aria-busy', 'true');

                const response = await fetch(url.toString(), {
                    headers: {
                        'Accept': 'text/html',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    signal: requestController.signal,
                });

                if (!response.ok) throw new Error('Căutarea produselor nu a reușit.');

                const html = await response.text();
                const documentCopy = new DOMParser().parseFromString(html, 'text/html');
                const freshResults = documentCopy.querySelector('[data-product-results]');
                if (!freshResults) throw new Error('Rezultatele produselor nu au putut fi citite.');

                results.innerHTML = freshResults.innerHTML;
                if (updateHistory) {
                    window.history.replaceState({}, '', `${url.pathname}${url.search}#admin-products`);
                }
            } catch (error) {
                if (error.name !== 'AbortError') {
                    console.warn(error);
                }
            } finally {
                if (activeRequest === requestController) {
                    results.removeAttribute('aria-busy');
                    activeRequest = null;
                }
            }
        };

        const runLiveSearch = () => {
            window.clearTimeout(timer);
            timer = window.setTimeout(() => {
                replaceResultsFrom(makeSearchUrl(), true);
            }, 250);
        };

        input.addEventListener('input', runLiveSearch);

        form.addEventListener('submit', (event) => {
            event.preventDefault();
            window.clearTimeout(timer);
            replaceResultsFrom(makeSearchUrl(), true);
        });

        results.addEventListener('click', (event) => {
            const link = event.target.closest('.product-pagination-wrap a');
            if (!link) return;
            event.preventDefault();
            replaceResultsFrom(makeSearchUrl(link.href), true);
        });
    };

    const setupMessagingContact = () => {
        const cards = [...document.querySelectorAll('[data-contact-card]')];
        const hasGmailModal = Boolean(document.getElementById('gmail-contact-modal'));
        const hasGmailOpenButtons = Boolean(document.querySelector('[data-open-gmail-modal]'));
        if (!cards.length && !hasGmailModal && !hasGmailOpenButtons) return;

        const phoneDisplay = '+373 79 833 325';
        const phoneCompact = '+37379833325';
        const encodedPhone = encodeURIComponent(phoneCompact);
        const gmailAddress = 'tronciu.adrian@elev.cihcahul.md';

        const contactMethods = {
            viber: {
                name: 'Viber',
                description: 'Design și imprimare',
                href: `viber://chat?number=${encodedPhone}`,
                details: [
                    { label: 'Telefon', value: phoneDisplay, href: `tel:${phoneCompact}` },
                ],
                icon: `<svg viewBox="0 0 48 48" role="img" aria-label="Viber"><path fill="currentColor" d="M14.6 9.2c6.1-4.2 18.7-3.7 23.8 1.3 4.7 4.8 5.6 16.8 1.1 23.5-3.7 5.4-10.4 7.2-16.2 6.4l-7.5 4.6a1.4 1.4 0 0 1-2.1-1.2l.1-6c-4.7-3-7-8-6.6-13.8.4-6.2 2.9-11.6 7.4-14.8Zm6.8 8.2c-.8-.9-1.7-.8-2.5-.5-.8.3-1.7 1-2.1 1.8-.8 1.6-.2 4.4 2.4 8.1 2.6 3.7 6.4 6.7 10.1 7.6 1.8.4 3.4.1 4.5-.8.7-.6 1.4-1.8 1.4-2.8 0-.8-.4-1.4-1.2-1.9l-3.4-2c-.9-.5-1.8-.4-2.5.4l-1 1.2c-.4.5-1 .5-1.6.2-1.6-.9-2.9-2.2-4-3.8-.4-.6-.4-1.2.1-1.7l1.1-1.1c.6-.7.7-1.5.1-2.3l-1.4-2.4Zm5.5-3.1a1.3 1.3 0 0 0-.2 2.6c2.3.3 4 1.1 5.2 2.4 1.2 1.3 1.9 3.1 2.1 5.5a1.3 1.3 0 0 0 2.6-.2c-.2-3-1.2-5.4-2.8-7.2-1.7-1.8-3.9-2.8-6.9-3.1Zm-.2 4.3a1.2 1.2 0 1 0-.3 2.4c1.1.2 1.9.5 2.4 1 .5.5.8 1.3 1 2.5a1.2 1.2 0 0 0 2.4-.3c-.2-1.7-.8-3-1.7-3.9-1-.9-2.2-1.5-3.8-1.7Z"/></svg>`,
            },
            whatsApp: {
                name: 'WhatsApp',
                description: 'Design și imprimare',
                href: `https://api.whatsapp.com/send?phone=${encodedPhone}&text=${encodeURIComponent('Salut')}`,
                details: [
                    { label: 'Telefon', value: phoneDisplay, href: `tel:${phoneCompact}` },
                ],
                icon: `<svg viewBox="0 0 48 48" role="img" aria-label="WhatsApp"><path fill="currentColor" d="M24 5.5A18.2 18.2 0 0 0 8.5 33.3L6 42.5l9.4-2.5A18.2 18.2 0 1 0 24 5.5Zm0 4A14.2 14.2 0 0 1 36.1 31c-3.7 5.8-11.1 8.1-17.4 5.5l-1.1-.5-5 1.3 1.4-4.8-.6-1.1A14.2 14.2 0 0 1 24 9.5Zm-6.5 7.9c-.5 0-1.1.2-1.4.6-.5.5-1.8 1.8-1.8 4.3 0 2.5 1.8 5 2.1 5.3.3.3 3.6 5.6 8.8 7.5 4.3 1.7 5.2 1.3 6.1 1.2 1-.1 3.1-1.3 3.5-2.5.4-1.2.4-2.3.3-2.5-.2-.2-.5-.4-1.1-.7l-3.3-1.6c-.5-.2-1-.3-1.4.3-.4.6-1.5 1.8-1.9 2.2-.3.4-.7.4-1.3.1-.6-.3-2.4-.9-4.5-2.8-1.7-1.5-2.8-3.4-3.1-4-.3-.6 0-.9.2-1.2l.8-.9c.3-.3.4-.6.6-1 .2-.4.1-.8-.1-1.1l-1.5-3.6c-.4-1-.9-1-1.3-1Z"/></svg>`,
            },
            telegram: {
                name: 'Telegram',
                description: 'Design și imprimare',
                href: 'https://t.me/reclamd',
                details: [
                    { label: 'Telefon', value: phoneDisplay, href: `tel:${phoneCompact}` },
                    { label: 'Telegram', value: '@reclamd', href: 'https://t.me/reclamd' },
                ],
                icon: `<svg viewBox="0 0 48 48" role="img" aria-label="Telegram"><path fill="currentColor" d="M42.4 8.1c.8-.3 1.6.4 1.3 1.3l-6 28.5c-.4 1.9-1.8 2.4-3.4 1.5l-9.2-6.8-4.4 4.3c-.5.5-.9.9-1.9.9l.7-9.5L36.9 12.6c.8-.7-.2-1.1-1.2-.4L14.1 25.8 4.8 22.9c-2-.6-2-2 .4-3L42.4 8.1Z"/></svg>`,
            },
            gmail: {
                name: 'Gmail',
                description: 'Suport clienți',
                href: `mailto:${gmailAddress}`,
                details: [
                    { label: 'Email', value: gmailAddress, href: `mailto:${gmailAddress}` },
                ],
                icon: `<svg viewBox="0 0 48 48" role="img" aria-label="Gmail"><path fill="currentColor" d="M8.8 12h30.4A3.8 3.8 0 0 1 43 15.8v16.4a3.8 3.8 0 0 1-3.8 3.8H8.8A3.8 3.8 0 0 1 5 32.2V15.8A3.8 3.8 0 0 1 8.8 12Zm0 4v16h30.4V16l-15.2 11L8.8 16Zm2.6-.7L24 24.4l12.6-9.1H11.4Z"/></svg>`,
            },
        };

        window.ReclamContactMethods = contactMethods;

        const renderDetails = (container, details) => {
            if (!container) return;
            container.replaceChildren();
            details.forEach((detail) => {
                const link = document.createElement('a');
                link.href = detail.href;
                link.className = 'contact-meta-link';
                if (/^https?:/i.test(detail.href)) {
                    link.target = '_blank';
                    link.rel = 'noopener noreferrer';
                }

                const label = document.createElement('span');
                label.textContent = detail.label;
                const value = document.createElement('strong');
                value.textContent = detail.value;
                link.append(label, value);
                container.append(link);
            });
        };

        cards.forEach((card) => {
            const method = contactMethods[card.dataset.contactCard];
            if (!method) return;

            const name = card.querySelector('[data-contact-name]');
            const description = card.querySelector('[data-contact-description]');
            const icon = card.querySelector('[data-contact-icon]');
            const details = card.querySelector('[data-contact-details]');

            if (name) name.textContent = method.name;
            if (description) description.textContent = method.description;
            if (icon) icon.innerHTML = method.icon;
            renderDetails(details, method.details);
        });

        renderDetails(document.querySelector('[data-gmail-contact-details]'), contactMethods.gmail.details);

        const confirmModal = document.getElementById('messaging-confirm-modal');
        const confirmMessage = confirmModal?.querySelector('[data-messaging-confirm-message]');
        const redirectButton = confirmModal?.querySelector('[data-messaging-redirect]');
        const confirmCloseButton = confirmModal?.querySelector('[data-close-messaging-confirm]');
        const gmailModal = document.getElementById('gmail-contact-modal');
        const gmailCloseButtons = [...document.querySelectorAll('[data-close-gmail-modal]')];
        const gmailFirstField = gmailModal?.querySelector('input[name="first_name"]');
        const shouldOpenGmailOnLoad = gmailModal?.dataset.openOnLoad === 'true';
        let selectedPlatform = null;

        const syncBodyModalState = () => {
            const hasOpenModal = Boolean((confirmModal && !confirmModal.hidden) || (gmailModal && !gmailModal.hidden));
            document.body.classList.toggle('modal-open', hasOpenModal);
        };

        const closeConfirm = () => {
            if (!confirmModal) return;
            confirmModal.hidden = true;
            syncBodyModalState();
        };

        const closeGmailModal = () => {
            if (!gmailModal) return;
            gmailModal.hidden = true;
            syncBodyModalState();
        };

        const openGmailModal = () => {
            if (!gmailModal) return;
            gmailModal.hidden = false;
            syncBodyModalState();
            window.setTimeout(() => gmailFirstField?.focus(), 20);
        };

        const openConfirm = (platform) => {
            const method = contactMethods[platform];
            if (!method || platform === 'gmail') return;
            selectedPlatform = platform;
            if (confirmMessage) {
                confirmMessage.textContent = `Doriți să fiți direcționat către conversație prin ${method.name}?`;
            }
            if (confirmModal) {
                confirmModal.hidden = false;
                syncBodyModalState();
                redirectButton?.focus();
            } else {
                window.location.href = method.href;
            }
        };

        document.querySelectorAll('[data-contact-open]').forEach((button) => {
            button.addEventListener('click', () => {
                const platform = button.dataset.contactOpen;
                if (platform === 'gmail') {
                    openGmailModal();
                    return;
                }
                openConfirm(platform);
            });
        });

        document.querySelectorAll('[data-open-gmail-modal]').forEach((button) => {
            button.addEventListener('click', (event) => {
                event.preventDefault();
                openGmailModal();
            });
        });

        redirectButton?.addEventListener('click', () => {
            const method = contactMethods[selectedPlatform];
            if (!method) return;
            window.location.href = method.href;
        });

        confirmCloseButton?.addEventListener('click', closeConfirm);
        confirmModal?.addEventListener('click', (event) => {
            if (event.target === confirmModal) closeConfirm();
        });

        gmailCloseButtons.forEach((button) => button.addEventListener('click', closeGmailModal));
        gmailModal?.addEventListener('click', (event) => {
            if (event.target === gmailModal) closeGmailModal();
        });

        document.addEventListener('keydown', (event) => {
            if (event.key !== 'Escape') return;
            if (confirmModal && !confirmModal.hidden) closeConfirm();
            if (gmailModal && !gmailModal.hidden) closeGmailModal();
        });

        if (shouldOpenGmailOnLoad) {
            openGmailModal();
        }
    };

    const fieldLabel = (field) => {
        const label = field.closest('label');
        if (!label) return field.getAttribute('aria-label') || field.name || 'campul';
        return (label.childNodes[0]?.textContent || label.textContent || field.name || 'campul').trim().replace(/\s+/g, ' ');
    };

    const validationMessageFor = (field) => {
        const name = fieldLabel(field);
        if (field.validity.valueMissing) return `Completeaza ${name}.`;
        if (field.validity.typeMismatch && field.type === 'email') return `Introdu un email valid pentru ${name}.`;
        if (field.validity.typeMismatch && field.type === 'url') return `Introdu un link valid pentru ${name}.`;
        if (field.validity.tooShort) return `${name} trebuie completat cu mai multe caractere.`;
        if (field.validity.rangeUnderflow) return `${name} este prea mic.`;
        if (field.validity.rangeOverflow) return `${name} este prea mare.`;
        if (field.validity.stepMismatch) return `${name} are o valoare invalida.`;
        if (field.validity.badInput) return `${name} nu este completat corect.`;
        return `Verifica ${name}.`;
    };

    const customValidateForm = (form, options = {}) => {
        if (!form) return true;
        const scope = options.scope || form;
        const fields = [...scope.querySelectorAll('input, select, textarea')]
            .filter((field) => !field.disabled && field.type !== 'hidden' && !field.closest('[hidden]') && (field.offsetWidth > 0 || field.offsetHeight > 0));
        const invalidFields = [];
        const messages = [];

        fields.forEach((field) => {
            field.classList.remove('field-invalid', 'field-valid');

            if ((field.type === 'checkbox' || field.type === 'radio') && !field.required) return;
            if (field.type === 'file' && !field.required) return;
            if (!field.required && !field.value.trim()) return;

            const isValid = field.checkValidity();
            field.classList.toggle('field-invalid', !isValid);
            field.classList.toggle('field-valid', isValid && Boolean(field.value.trim()));

            if (!isValid) {
                invalidFields.push(field);
                messages.push(field.dataset.requiredMessage || validationMessageFor(field));
            }
        });

        if (invalidFields.length) {
            const uniqueMessages = [...new Set(messages)];
            showToast(uniqueMessages[0] || 'Completeaza campurile marcate.', 'danger');
            invalidFields[0].focus({ preventScroll: true });
            invalidFields[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
            return false;
        }

        return true;
    };

    const setupCustomFormValidation = () => {
        document.querySelectorAll('form').forEach((form) => {
            form.setAttribute('novalidate', 'novalidate');
            form.addEventListener('submit', (event) => {
                if (!customValidateForm(form)) {
                    event.preventDefault();
                    event.stopImmediatePropagation();
                }
            }, true);
        });

        document.addEventListener('invalid', (event) => {
            event.preventDefault();
            const field = event.target;
            if (field instanceof HTMLElement) {
                field.classList.add('field-invalid');
            }
        }, true);
    };

    const setupFloatingContact = () => {
        const widget = document.querySelector('[data-floating-contact]');
        const toggle = widget?.querySelector('[data-floating-contact-toggle]');
        if (!widget || !toggle) return;

        const setOpen = (open) => {
            widget.classList.toggle('is-open', open);
            toggle.setAttribute('aria-expanded', String(open));
        };

        toggle.addEventListener('click', (event) => {
            event.stopPropagation();
            setOpen(!widget.classList.contains('is-open'));
        });

        widget.querySelectorAll('.floating-contact-link').forEach((link) => {
            link.addEventListener('click', () => setOpen(false));
        });

        document.addEventListener('click', (event) => {
            if (!widget.contains(event.target)) setOpen(false);
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') setOpen(false);
        });
    };

    const setupAutoDismissAlerts = () => {
        document.querySelectorAll('[data-autodismiss]').forEach((alert) => {
            setTimeout(() => alert.remove(), 5000);
        });
    };

    const setupAdminLock = () => {
        const modal = document.getElementById('admin-lock-modal');
        if (!modal) return;
        const lockUntil = Number(modal.dataset.lockUntil || 0) * 1000;
        if (!lockUntil || lockUntil <= Date.now()) return;
        const countdown = document.getElementById('admin-lock-countdown');
        const form = document.querySelector('[data-admin-login-form]');

        const tick = () => {
            const remaining = Math.max(0, Math.ceil((lockUntil - Date.now()) / 1000));
            if (countdown) countdown.textContent = remaining;
            if (remaining <= 0) {
                modal.hidden = true;
                form?.querySelectorAll('input, button').forEach((field) => { field.disabled = false; });
                clearInterval(interval);
            }
        };

        modal.hidden = false;
        const interval = setInterval(tick, 250);
        tick();
    };

    if (window.ReclamCartClear) {
        saveCart([]);
        savePromo(null);
        clearAllWearableDrafts();
    }

    document.addEventListener('DOMContentLoaded', () => {
        if (window.ReclamCartClear) {
            saveCart([]);
            savePromo(null);
            clearAllWearableDrafts();
        }
        setupTheme();
        setupCustomFormValidation();
        setupLanguageSwitcher();
        setupNavbar();
        setupCatalog();
        setupCatalogModal();
        setupSearch();
        setupCarousel();
        setupCategoryRail();
        setupProductCardNavigation();
        setupCartEvents();
        setupCheckout();
        setupPromocode();
        setupGallery();
        setupWearableCustomizer();
        setupMugEvents();
        setupAdminCategoryForm();
        setupAdminSubcategoryForm();
        setupAdminSubcategoryEditButtons();
        setupClientStockFilterCleanup();
        setupAdminProductForm();
        setupConfirmForms();
        setupLiveAdminOrderSearch();
        setupLiveAdminProductSearch();
        setupMessagingContact();
        setupFloatingContact();
        setupAutoDismissAlerts();
        setupAdminLock();
        updateCartBadge();
        renderCartPage();
        updateCustomizationSummary();
    });
})();
