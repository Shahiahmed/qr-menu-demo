import Alpine from 'alpinejs';

const CURRENCY_SYMBOLS = { KZT: '₸', USD: '$', RUB: '₽' };

/**
 * The guest menu's whole interactive layer, ported from the React GuestMenu:
 * language toggle, per-device theme/layout override, cart with a local "show the
 * waiter" ticket, and the bottom-sheet UI. Guest choices persist in
 * localStorage, keyed to this single venue — they never touch the server data.
 */
function menu(boot) {
    const prefsKey = 'qmenu.guest-prefs.v1';
    const cartKey = 'qmenu.guest-cart.v1';

    const readJson = (key, fallback) => {
        try {
            const raw = localStorage.getItem(key);
            return raw ? JSON.parse(raw) : fallback;
        } catch {
            return fallback;
        }
    };
    const writeJson = (key, value) => {
        try {
            localStorage.setItem(key, JSON.stringify(value));
        } catch {
            /* private mode / quota — ignore, the menu still works */
        }
    };

    const prefs = readJson(prefsKey, {});

    return {
        // --- config from the server ---
        currency: boot.currency,
        themes: boot.themes,
        dishes: boot.dishes, // { [id]: { ru, kk, price } }
        ordering: boot.ordering, // master switch
        tablesCount: boot.tablesCount, // N → 1..N dropdown; null → free numeric
        orderUrl: boot.orderUrl,
        // NB: named `strings`, not `copy` — a `copy(text)` clipboard method lives
        // below, and a duplicate object key would shadow this data (breaking t()).
        strings: boot.copy, // { ru: {...}, kk: {...} }

        // --- reactive state (guest overrides the owner's default) ---
        locale: prefs.locale || boot.defaultLocale,
        theme: prefs.theme || boot.defaultTheme,
        layout: prefs.layout || boot.defaultLayout,
        dark: prefs.dark || false,
        query: '',
        cart: readJson(cartKey, {}),
        sheet: null, // 'cart' | 'settings' | 'waiter' | null
        placed: false,
        waiterCalled: false,
        // --- ordering state ---
        tableNumber: '', // prefilled from ?table=N when a per-table QR is scanned
        comment: '',
        submitting: false,
        orderError: '',
        orderNumber: null,
        placedTable: null,
        activeTop: 0, // 0 = "Все"; otherwise a top-level category id (a filter)
        activeSub: null, // scroll-spied subcategory id, highlights its chip
        copied: false,

        init() {
            // A per-table QR carries ?table=N — prefill the table so the guest
            // never types it. Kept as a string to match the <select>/<input>.
            try {
                const t = new URLSearchParams(window.location.search).get('table');
                if (t && /^\d+$/.test(t)) this.tableNumber = String(parseInt(t, 10));
            } catch {
                /* no URL / SSR — ignore */
            }

            this.applyTheme();
            this.applyDark();
            this.$watch('dark', () => {
                this.applyDark();
                this.persistPrefs();
            });
            // Reflect the language on <html lang> for a11y / SEO parity.
            this.$watch('locale', (v) => {
                document.documentElement.lang = v === 'kk' ? 'kk' : 'ru';
                this.persistPrefs();
            });
            this.$watch('theme', () => {
                this.applyTheme();
                this.persistPrefs();
            });
            this.$watch('layout', () => this.persistPrefs());
            this.$watch('cart', (v) => writeJson(cartKey, v));

            // Lock body scroll while a bottom sheet is open.
            this.$watch('sheet', (v) => {
                document.body.style.overflow = v ? 'hidden' : '';
            });

            this.setupScrollSpy();
        },

        // --- preferences ---
        persistPrefs() {
            writeJson(prefsKey, { locale: this.locale, theme: this.theme, layout: this.layout, dark: this.dark });
        },
        applyTheme() {
            const t = this.themes[this.theme] || this.themes[boot.defaultTheme];
            if (!t) return;
            const root = this.$root;
            root.style.setProperty('--accent', t.accent);
            root.style.setProperty('--accent-hover', t.hover);
            root.style.setProperty('--accent-soft', t.soft);
            root.style.setProperty('--accent-tint', t.tint);
        },
        // Dark mode flips the surface/foreground tokens for the whole document
        // (set on <html> so the body backdrop and every sheet follow). The accent
        // family is independent — it stays whatever theme the guest picked.
        applyDark() {
            const el = document.documentElement;
            if (this.dark) el.setAttribute('data-mode', 'dark');
            else el.removeAttribute('data-mode');
        },
        toggleDark() {
            this.dark = !this.dark;
        },
        setLocale(v) {
            this.locale = v;
        },
        setTheme(key) {
            this.theme = key;
        },
        setLayout(key) {
            this.layout = key;
        },

        // --- search (client-side filter over the dishes already in the DOM) ---
        get q() {
            return this.query.trim().toLowerCase();
        },
        get searching() {
            return this.q.length > 0;
        },
        // A dish node carries its searchable text in data-key (ru+kk name+desc).
        matchesKey(key) {
            return !this.q || (key || '').includes(this.q);
        },
        // A category section shows during search only if some dish under it matches.
        sectionHasMatch(el) {
            if (!this.q) return true;
            return Array.from(el.querySelectorAll('[data-key]')).some((n) => n.dataset.key.includes(this.q));
        },
        get noResults() {
            if (!this.q) return false;
            return !Array.from(document.querySelectorAll('[data-key]')).some((n) => n.dataset.key.includes(this.q));
        },
        clearSearch() {
            this.query = '';
        },

        // --- cart ---
        qty(id) {
            return this.cart[id] || 0;
        },
        add(id) {
            this.cart = { ...this.cart, [id]: (this.cart[id] || 0) + 1 };
        },
        inc(id) {
            this.add(id);
        },
        dec(id) {
            const next = { ...this.cart };
            const n = (next[id] || 0) - 1;
            if (n <= 0) delete next[id];
            else next[id] = n;
            this.cart = next;
        },
        clearCart() {
            this.cart = {};
        },
        get totalCount() {
            return Object.values(this.cart).reduce((a, b) => a + b, 0);
        },
        get totalMinor() {
            return Object.entries(this.cart).reduce((sum, [id, n]) => {
                const d = this.dishes[id];
                return d ? sum + d.price * n : sum;
            }, 0);
        },
        get cartLines() {
            return Object.entries(this.cart)
                .map(([id, n]) => {
                    const d = this.dishes[id];
                    if (!d) return null;
                    return { id, n, name: d[this.locale] || d.ru, price: d.price };
                })
                .filter(Boolean);
        },

        // True when the guest still needs to pick a table before ordering.
        get tableRequired() {
            return !this.tableNumber;
        },
        // Send the order to the server, which re-prices it from live dish data
        // (the client total is never trusted). On success we show the ticket.
        async submitOrder() {
            if (this.totalCount === 0 || this.submitting) return;
            if (this.tableRequired) {
                this.orderError = this.t('cartTableRequired');
                return;
            }

            this.submitting = true;
            this.orderError = '';

            const token = document.querySelector('meta[name="csrf-token"]')?.content || '';
            const payload = {
                items: this.cartLines.map((l) => ({ id: Number(l.id), qty: l.n })),
                table_number: this.tableNumber ? Number(this.tableNumber) : null,
                comment: this.comment || null,
                locale: this.locale,
            };

            try {
                const res = await fetch(this.orderUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': token,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify(payload),
                });

                if (!res.ok) {
                    this.orderError = this.t('orderError');
                    return;
                }

                const data = await res.json();
                this.orderNumber = data.number ?? data.id ?? null;
                this.placedTable = this.tableNumber || null;
                this.placed = true;
            } catch {
                this.orderError = this.t('orderError');
            } finally {
                this.submitting = false;
            }
        },
        newOrder() {
            this.placed = false;
            this.orderNumber = null;
            this.orderError = '';
            this.comment = '';
            this.clearCart();
            this.sheet = null;
        },
        showTicket() {
            this.sheet = 'cart';
        },
        callWaiter() {
            this.waiterCalled = true;
        },

        // --- formatting ---
        formatPrice(minor) {
            const major = Math.trunc(minor / 100);
            const grouped = new Intl.NumberFormat('ru-RU').format(major);
            const symbol = CURRENCY_SYMBOLS[this.currency] || this.currency;
            return `${grouped} ${symbol}`;
        },
        loc(ru, kk) {
            return this.locale === 'kk' ? kk || ru : ru;
        },
        t(key) {
            const table = this.strings[this.locale] || this.strings.ru;
            return table[key] ?? key;
        },

        async copy(text) {
            try {
                await navigator.clipboard.writeText(text);
                this.copied = true;
                setTimeout(() => (this.copied = false), 1600);
            } catch {
                /* clipboard blocked — ignore */
            }
        },

        // --- two-level category navigation / scroll-spy ---
        scrollLock: false,
        headerOffset() {
            const header = document.getElementById('menu-header');
            return header ? header.offsetHeight : 0;
        },
        // Height of the fixed bottom dock (nav + a little breathing room) we must
        // keep clear when bottom-aligning a short group.
        dockOffset: 72,
        // Pick a top-level category (or 0 = "Все"). Picking a category also hides
        // the browse blocks (about/promos/collections) above the list, so the only
        // thing above the sticky header is the cover hero.
        //
        // A tall group is pinned: its heading sits just under the sticky header.
        // A SHORT group is bottom-aligned instead — pinning it would strand the few
        // dishes above a large empty band over the nav; bottom-aligning drops that
        // band and lets the cover hero fill the top, so the screen reads as a clean
        // short page rather than a void. min() picks whichever scrolls less.
        selectTop(id) {
            this.activeTop = id;
            this.activeSub = null;
            this.scrollLock = true;
            // $nextTick so the x-show filter (and the now-hidden browse blocks) are
            // applied before we measure — getBoundingClientRect must see final layout.
            this.$nextTick(() => {
                const el =
                    id === 0
                        ? document.getElementById('menu-top')
                        : document.querySelector(`[data-group="${id}"]`);
                if (!el) {
                    this.scrollLock = false;
                    return;
                }
                const rect = el.getBoundingClientRect();
                const top = rect.top + window.scrollY;
                const bottom = rect.bottom + window.scrollY;
                const pinTop = top - this.headerOffset() - 8;
                const bottomAlign = bottom + this.dockOffset - window.innerHeight;
                const target = Math.max(0, Math.min(pinTop, bottomAlign));
                window.scrollTo({ top: target, behavior: 'smooth' });
                setTimeout(() => (this.scrollLock = false), 650);
            });
        },
        // Jump to a subcategory section within the selected group.
        goToSub(id) {
            const el = document.getElementById('sub-' + id);
            if (!el) return;
            const top = el.getBoundingClientRect().top + window.scrollY - this.headerOffset() - 8;
            this.scrollLock = true;
            this.activeSub = id;
            window.scrollTo({ top, behavior: 'smooth' });
            setTimeout(() => (this.scrollLock = false), 650);
        },
        setupScrollSpy() {
            const onScroll = () => {
                if (this.scrollLock) return;
                const offset = this.headerOffset() + 16;
                // Only sections currently on-screen (a filtered-out group has
                // offsetParent === null) count toward the active subcategory.
                const sections = Array.from(document.querySelectorAll('[data-sub]')).filter(
                    (s) => s.offsetParent !== null,
                );
                let current = sections.length ? sections[0].dataset.sub : null;
                for (const s of sections) {
                    if (s.getBoundingClientRect().top - offset <= 0) current = s.dataset.sub;
                }
                // Near the bottom the last section may never clear the offset (its
                // top stays below the header), so pin the last tab once we've
                // scrolled to the end. Lets us drop the tall spacer that used to
                // force that room — no more blank gap under the last dish.
                const atBottom =
                    window.innerHeight + window.scrollY >=
                    document.documentElement.scrollHeight - 4;
                if (atBottom && sections.length) {
                    current = sections[sections.length - 1].dataset.sub;
                }
                this.activeSub = current === null ? null : Number(current);
            };
            window.addEventListener('scroll', onScroll, { passive: true });
            onScroll();
        },
    };
}

/**
 * `x-drag-scroll` — makes a horizontal overflow strip draggable with a mouse and
 * scrollable with the wheel. Touch/trackpad already pan these natively (and the
 * scrollbar is hidden by `.no-scrollbar`), so on a laptop the rails felt stuck;
 * this restores grab-drag + wheel→horizontal for a plain mouse.
 *
 * Mouse only: touch/pen keep native scrolling (we'd fight it otherwise). A drag
 * past a small threshold swallows the trailing click so rail cards (which are
 * <a> links) don't navigate when the guest was only dragging.
 */
Alpine.directive('drag-scroll', (el) => {
    let startX = 0;
    let startLeft = 0;
    let dragging = false;
    let moved = false;
    let pointerId = null;

    el.style.cursor = 'grab';

    el.addEventListener('pointerdown', (e) => {
        if (e.pointerType !== 'mouse' || e.button !== 0) return;
        dragging = true;
        moved = false;
        startX = e.clientX;
        startLeft = el.scrollLeft;
        pointerId = e.pointerId;
    });

    el.addEventListener('pointermove', (e) => {
        if (!dragging) return;
        const dx = e.clientX - startX;
        // Only claim the gesture once it's clearly a drag — keeps taps clickable.
        if (!moved && Math.abs(dx) > 4) {
            moved = true;
            el.style.cursor = 'grabbing';
            try {
                el.setPointerCapture(pointerId);
            } catch {
                /* capture unsupported — drag still works while inside el */
            }
        }
        if (moved) el.scrollLeft = startLeft - dx;
    });

    const end = () => {
        if (!dragging) return;
        dragging = false;
        el.style.cursor = 'grab';
        if (pointerId !== null) {
            try {
                el.releasePointerCapture(pointerId);
            } catch {
                /* nothing captured */
            }
            pointerId = null;
        }
    };
    el.addEventListener('pointerup', end);
    el.addEventListener('pointercancel', end);

    // Capture phase so we cancel the click before it reaches the <a> card.
    el.addEventListener(
        'click',
        (e) => {
            if (moved) {
                e.preventDefault();
                e.stopPropagation();
            }
            moved = false;
        },
        true,
    );

    // Vertical wheel over the strip scrolls it sideways (a mouse wheel has no
    // horizontal axis). Only when there's actually somewhere to scroll.
    el.addEventListener(
        'wheel',
        (e) => {
            if (el.scrollWidth <= el.clientWidth + 1) return;
            const delta = Math.abs(e.deltaX) > Math.abs(e.deltaY) ? e.deltaX : e.deltaY;
            if (delta === 0) return;
            e.preventDefault();
            el.scrollLeft += delta;
        },
        { passive: false },
    );
});

window.Alpine = Alpine;
Alpine.data('menu', menu);
Alpine.start();
