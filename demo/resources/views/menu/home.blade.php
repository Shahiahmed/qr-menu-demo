@php
    use App\Support\Money;

    $themes = config('menu.themes');
    $layouts = config('menu.layouts');
    $guestCopy = config('guest');

    $defaultLocale = $venue->default_locale === 'kk' ? 'kk' : 'ru';
    $initTheme = $themes[$venue->theme] ?? $themes[config('menu.default_theme')];

    // Alpine payload.
    $themesJs = collect($themes)->map(fn ($t) => [
        'accent' => $t['accent'], 'hover' => $t['hover'], 'soft' => $t['soft'], 'tint' => $t['tint'],
    ]);

    // Flatten every dish (direct on a top category + inside subcategories) into
    // the cart lookup Alpine needs.
    $dishesJs = [];
    $collectDishes = function ($dishes) use (&$dishesJs) {
        foreach ($dishes as $d) {
            $dishesJs[$d->id] = ['ru' => $d->name_ru, 'kk' => $d->name_kk ?: $d->name_ru, 'price' => (int) $d->price];
        }
    };
    foreach ($groups as $g) {
        $collectDishes($g->dishes);
        foreach ($g->children as $sub) {
            $collectDishes($sub->dishes);
        }
    }

    $boot = [
        'currency' => $venue->currency,
        'defaultLocale' => $defaultLocale,
        'defaultTheme' => $venue->theme,
        'defaultLayout' => $venue->layout,
        'themes' => $themesJs,
        'dishes' => $dishesJs,
        'copy' => $guestCopy,
        // Ordering: master switch + how many tables (null → free numeric entry).
        'ordering' => (bool) $venue->ordering_enabled,
        'tablesCount' => $venue->tables_count,
        'orderUrl' => route('orders.store'),
    ];

    $hasContacts = $venue->address || $venue->phone || $venue->wifi_ssid || $venue->instagram_url || $venue->facebook_url || $venue->tiktok_url;
@endphp

@extends('layouts.guest')

{{-- Home uses the layout's SEO defaults (venue->seoTitle/seoDescription/
     seoKeywords/ogImageUrl), all editable in the panel. No overrides needed. --}}

@section('body')
    <div
        x-data="menu(@js($boot))"
        x-cloak
        :data-layout="layout"
        class="min-h-screen bg-surface"
        style="--accent: {{ $initTheme['accent'] }}; --accent-hover: {{ $initTheme['hover'] }}; --accent-soft: {{ $initTheme['soft'] }}; --accent-tint: {{ $initTheme['tint'] }};"
    >
        {{-- ========== Cover: logo + name centred on the photo, contacts on the
             bottom gradient — the same hero the main project's guest menu uses. --}}
        <header class="relative aspect-[2/1] max-h-[300px] w-full overflow-hidden bg-surface-2 sm:aspect-[21/9] sm:max-h-[340px]">
            @if ($venue->coverUrl())
                <img src="{{ $venue->coverUrl() }}" alt="{{ $venue->name }}" class="h-full w-full object-cover">
            @else
                <div class="placeholder-stripes h-full w-full"></div>
            @endif
            {{-- Dark gradient so a white name and white pills stay legible. --}}
            <div class="pointer-events-none absolute inset-0 bg-gradient-to-t from-black/70 via-black/30 to-black/15"></div>

            {{-- Logo + name, vertically centred in the space above the chip row. --}}
            <div class="absolute inset-x-0 top-0 {{ $hasContacts ? 'bottom-[60px]' : 'bottom-0' }} flex flex-col items-center justify-center gap-3 px-4 text-center">
                @if ($venue->show_logo)
                    <div class="flex h-[72px] w-[72px] items-center justify-center overflow-hidden rounded-full border-2 border-white bg-white shadow-[0_6px_20px_-6px_rgba(0,0,0,0.55)] sm:h-[84px] sm:w-[84px]">
                        @if ($venue->logoUrl())
                            <img src="{{ $venue->logoUrl() }}" alt="{{ $venue->name }}" class="h-full w-full object-cover">
                        @else
                            <span class="text-2xl font-bold text-accent sm:text-3xl">{{ mb_substr($venue->name, 0, 1) }}</span>
                        @endif
                    </div>
                @endif
                <h1 class="text-2xl font-bold text-white drop-shadow-[0_2px_10px_rgba(0,0,0,0.7)] sm:text-3xl">{{ $venue->name }}</h1>
            </div>

            {{-- Contact chips overlaid on the gradient. --}}
            @if ($hasContacts)
                <div class="absolute inset-x-0 bottom-0 px-3 pb-3 sm:px-4">
                    <div class="mx-auto flex max-w-[680px] flex-wrap items-center justify-center gap-1.5 text-[12px]">
                        @if ($venue->address)
                            <span class="inline-flex max-w-full items-center gap-1.5 rounded-full border border-white/50 bg-white/95 px-2.5 py-1.5 text-foreground shadow-sm backdrop-blur-sm">
                                <span class="shrink-0 text-accent">@include('menu.icons.pin')</span>
                                <span class="truncate">{{ $venue->address }}</span>
                            </span>
                        @endif
                        @if ($venue->wifi_ssid)
                            <span class="inline-flex items-center gap-1.5 rounded-full border border-white/50 bg-white/95 px-2.5 py-1.5 text-foreground shadow-sm backdrop-blur-sm">
                                <span class="shrink-0 text-accent">@include('menu.icons.wifi')</span>
                                <span class="font-semibold">{{ $venue->wifi_ssid }}</span>
                            </span>
                        @endif
                        @if ($venue->wifi_password)
                            <button
                                type="button"
                                x-on:click="copy(@js($venue->wifi_password))"
                                class="inline-flex items-center gap-1.5 rounded-full border border-white/50 bg-white/95 px-2.5 py-1.5 text-foreground shadow-sm backdrop-blur-sm transition hover:border-accent"
                            >
                                <span class="shrink-0 text-accent">@include('menu.icons.key')</span>
                                <span x-show="!copied" class="font-semibold tabular-nums">{{ $venue->wifi_password }}</span>
                                <span x-show="copied" x-cloak class="font-semibold text-accent" x-text="t('copyDone')"></span>
                            </button>
                        @endif
                        @if ($venue->phone)
                            <a href="tel:{{ preg_replace('/[^0-9+]/', '', $venue->phone) }}" class="inline-flex h-8 w-8 items-center justify-center rounded-full border border-white/50 bg-white/95 text-foreground shadow-sm backdrop-blur-sm transition hover:border-accent hover:text-accent">
                                @include('menu.icons.phone')
                            </a>
                        @endif
                        @foreach (['instagram_url' => 'instagram', 'facebook_url' => 'facebook', 'tiktok_url' => 'tiktok'] as $field => $icon)
                            @if ($venue->$field)
                                <a href="{{ $venue->$field }}" target="_blank" rel="noopener" class="inline-flex h-8 w-8 items-center justify-center rounded-full border border-white/50 bg-white/95 text-foreground shadow-sm backdrop-blur-sm transition hover:border-accent hover:text-accent">
                                    @include('menu.icons.' . $icon)
                                </a>
                            @endif
                        @endforeach
                    </div>
                </div>
            @endif
        </header>

        {{-- About --}}
        @if ($venue->description_ru || $venue->description_kk)
            <div class="mx-auto mt-5 max-w-[680px] px-4">
                <div class="rounded-2xl bg-white p-4 text-sm leading-relaxed text-muted shadow-sm ring-1 ring-border">
                    <p x-show="locale === 'ru'">{{ $venue->description_ru }}</p>
                    <p x-show="locale === 'kk'" x-cloak>{{ $venue->description_kk ?: $venue->description_ru }}</p>
                </div>
            </div>
        @endif

        {{-- ========== Promo banners (сторис) — hidden while searching ========== --}}
        @if ($promotions->isNotEmpty())
            <div x-show="!searching" class="mx-auto mt-5 max-w-[680px]">
                <div class="promo-scroll no-scrollbar px-4">
                    @foreach ($promotions as $p)
                        <div class="promo-card">
                            @if ($p->imageUrl())
                                <img src="{{ $p->imageUrl() }}" alt="{{ $p->title_ru }}" loading="lazy">
                            @else
                                <div class="promo-fallback"></div>
                            @endif
                            <div class="promo-overlay">
                                <div class="promo-title">
                                    <span x-show="locale === 'ru'">{{ $p->title_ru }}</span>
                                    <span x-show="locale === 'kk'" x-cloak>{{ $p->title_kk ?: $p->title_ru }}</span>
                                </div>
                                @if ($p->subtitle_ru || $p->subtitle_kk)
                                    <div class="promo-sub">
                                        <span x-show="locale === 'ru'">{{ $p->subtitle_ru }}</span>
                                        <span x-show="locale === 'kk'" x-cloak>{{ $p->subtitle_kk ?: $p->subtitle_ru }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- ========== Collections (подборки) — horizontal rails, hidden while searching ========== --}}
        @if ($collections->isNotEmpty())
            <div x-show="!searching" class="mx-auto mt-6 max-w-[680px] space-y-6">
                @foreach ($collections as $col)
                    <section>
                        <h2 class="mb-3 px-4 text-lg font-bold text-foreground">
                            <span x-show="locale === 'ru'">{{ $col->name_ru }}</span>
                            <span x-show="locale === 'kk'" x-cloak>{{ $col->name_kk ?: $col->name_ru }}</span>
                        </h2>
                        <div class="rail no-scrollbar px-4">
                            @foreach ($col->dishes as $d)
                                <a href="{{ route('menu.dish', $d->slug) }}" class="rail-card">
                                    <div class="rail-photo">
                                        @if ($d->imageUrl())
                                            <img src="{{ $d->imageUrl() }}" alt="{{ $d->name_ru }}" loading="lazy">
                                        @else
                                            <span class="placeholder-stripes-sm flex h-full w-full items-center justify-center text-accent">
                                                @include('menu.icons.utensils')
                                            </span>
                                        @endif
                                    </div>
                                    <div class="rail-name">
                                        <span x-show="locale === 'ru'">{{ $d->name_ru }}</span>
                                        <span x-show="locale === 'kk'" x-cloak>{{ $d->name_kk ?: $d->name_ru }}</span>
                                    </div>
                                    <div class="rail-price">{{ Money::format((int) $d->price, $venue->currency) }}</div>
                                </a>
                            @endforeach
                        </div>
                    </section>
                @endforeach
            </div>
        @endif

        {{-- ========== Sticky header: title + controls + search + category tabs ========== --}}
        <div id="menu-header" class="sticky top-0 z-20 mt-5 border-b border-border bg-surface/95 backdrop-blur">
            <div class="mx-auto flex max-w-[680px] items-center justify-between gap-3 px-4 py-2.5">
                <span class="truncate text-sm font-semibold text-foreground" x-text="t('menuTitle')"></span>
                <div class="flex shrink-0 items-center gap-2">
                    {{-- Dark-mode toggle --}}
                    <button
                        type="button"
                        x-on:click="toggleDark()"
                        :aria-label="t('darkMode')"
                        :title="t('darkMode')"
                        class="flex h-8 w-8 items-center justify-center rounded-full bg-surface-2 text-muted ring-1 ring-border transition hover:text-accent"
                    >
                        <span x-show="!dark">@include('menu.icons.moon')</span>
                        <span x-show="dark" x-cloak>@include('menu.icons.sun')</span>
                    </button>
                    {{-- RU / KZ language toggle --}}
                    <div class="flex items-center rounded-full bg-surface-2 p-0.5 text-xs font-semibold ring-1 ring-border">
                        <button type="button" x-on:click="setLocale('ru')" :class="locale === 'ru' ? 'bg-white text-foreground shadow-sm' : 'text-muted'" class="rounded-full px-2.5 py-1 transition">RU</button>
                        <button type="button" x-on:click="setLocale('kk')" :class="locale === 'kk' ? 'bg-white text-foreground shadow-sm' : 'text-muted'" class="rounded-full px-2.5 py-1 transition">KZ</button>
                    </div>
                </div>
            </div>

            {{-- Search --}}
            <div class="mx-auto max-w-[680px] px-4 pb-2">
                <div class="relative">
                    <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-muted-soft">@include('menu.icons.search')</span>
                    <input
                        type="search"
                        x-model="query"
                        :placeholder="t('search')"
                        class="w-full rounded-full border border-border bg-white py-2 pl-10 pr-9 text-sm text-foreground placeholder:text-muted-soft focus:border-accent focus:outline-none focus:ring-2 focus:ring-accent-soft"
                    >
                    <button type="button" x-show="searching" x-cloak x-on:click="clearSearch()" :aria-label="t('cartClear')" class="absolute right-2 top-1/2 -translate-y-1/2 rounded-full p-1 text-muted transition hover:text-accent">@include('menu.icons.x')</button>
                </div>
            </div>

            @if ($groups->isNotEmpty())
                {{-- Top-level category tabs. "Все" shows every group; picking one
                     filters the menu to that group and reveals its subcategory chips. --}}
                <div x-show="!searching" class="no-scrollbar mx-auto max-w-[680px] overflow-x-auto px-4 pb-2">
                    <div class="flex gap-2">
                        <button
                            type="button"
                            x-on:click="selectTop(0)"
                            :class="activeTop === 0 ? 'bg-accent text-white' : 'bg-white text-muted ring-1 ring-border'"
                            class="whitespace-nowrap rounded-full px-3.5 py-1.5 text-[13px] font-medium transition"
                            x-text="t('menuAll')"
                        ></button>
                        @foreach ($groups as $g)
                            <button
                                type="button"
                                x-on:click="selectTop({{ $g->id }})"
                                :class="activeTop === {{ $g->id }} ? 'bg-accent text-white' : 'bg-white text-muted ring-1 ring-border'"
                                class="whitespace-nowrap rounded-full px-3.5 py-1.5 text-[13px] font-medium transition"
                            >
                                <span x-show="locale === 'ru'">{{ $g->name_ru }}</span>
                                <span x-show="locale === 'kk'" x-cloak>{{ $g->name_kk ?: $g->name_ru }}</span>
                            </button>
                        @endforeach
                    </div>
                </div>

                {{-- Subcategory chips — one row per group, only the selected group's
                     row is shown. Groups without subcategories emit nothing. --}}
                @foreach ($groups as $g)
                    @if ($g->children->isNotEmpty())
                        <div x-show="!searching && activeTop === {{ $g->id }}" x-cloak class="no-scrollbar mx-auto max-w-[680px] overflow-x-auto px-4 pb-2">
                            <div class="flex gap-2">
                                @foreach ($g->children as $sub)
                                    <button
                                        type="button"
                                        x-on:click="goToSub({{ $sub->id }})"
                                        :class="activeSub === {{ $sub->id }} ? 'bg-accent-soft text-accent ring-1 ring-accent' : 'bg-white text-muted ring-1 ring-border'"
                                        class="whitespace-nowrap rounded-full px-3 py-1 text-xs font-medium transition"
                                    >
                                        <span x-show="locale === 'ru'">{{ $sub->name_ru }}</span>
                                        <span x-show="locale === 'kk'" x-cloak>{{ $sub->name_kk ?: $sub->name_ru }}</span>
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endforeach
            @endif
        </div>

        {{-- ========== Menu ========== --}}
        <main id="menu-top" class="mx-auto max-w-[680px] px-4 pb-44 pt-5">
            @forelse ($groups as $g)
                {{-- A whole top group is hidden unless it's the selected tab (or "Все",
                     or an active search — where matches decide visibility). --}}
                <div x-show="searching || activeTop === 0 || activeTop === {{ $g->id }}" class="mb-2 last:min-h-[70vh]">
                    @if ($g->children->isNotEmpty())
                        {{-- Group super-heading — the subcategories carry the dishes. --}}
                        <h2 class="mb-4 mt-2 text-xl font-bold text-foreground">
                            <span x-show="locale === 'ru'">{{ $g->name_ru }}</span>
                            <span x-show="locale === 'kk'" x-cloak>{{ $g->name_kk ?: $g->name_ru }}</span>
                        </h2>
                    @endif

                    {{-- Dishes attached directly to the top category (flat groups, and
                         the flexible case of direct dishes alongside subcategories). --}}
                    @if ($g->dishes->isNotEmpty())
                        <section id="sub-g{{ $g->id }}" data-top="{{ $g->id }}" data-sub="{{ $g->id }}" x-show="sectionHasMatch($el)" class="mb-8 scroll-mt-[150px]">
                            @if ($g->children->isEmpty())
                                {{-- Flat group: the section heading is the group name. --}}
                                <h2 class="mb-3 text-lg font-bold text-foreground">
                                    <span x-show="locale === 'ru'">{{ $g->name_ru }}</span>
                                    <span x-show="locale === 'kk'" x-cloak>{{ $g->name_kk ?: $g->name_ru }}</span>
                                </h2>
                            @endif
                            <div class="dish-list">
                                @foreach ($g->dishes as $d)
                                    @include('menu.partials.dish', ['d' => $d])
                                @endforeach
                            </div>
                        </section>
                    @endif

                    {{-- Subcategory sections. --}}
                    @foreach ($g->children as $sub)
                        <section id="sub-{{ $sub->id }}" data-top="{{ $g->id }}" data-sub="{{ $sub->id }}" x-show="sectionHasMatch($el)" class="mb-8 scroll-mt-[150px]">
                            <h3 class="mb-3 text-base font-bold text-foreground">
                                <span x-show="locale === 'ru'">{{ $sub->name_ru }}</span>
                                <span x-show="locale === 'kk'" x-cloak>{{ $sub->name_kk ?: $sub->name_ru }}</span>
                            </h3>
                            <div class="dish-list">
                                @foreach ($sub->dishes as $d)
                                    @include('menu.partials.dish', ['d' => $d])
                                @endforeach
                            </div>
                        </section>
                    @endforeach
                </div>
            @empty
                <p class="py-16 text-center text-muted" x-text="t('empty')"></p>
            @endforelse

            {{-- No search results --}}
            <p x-show="noResults" x-cloak class="py-16 text-center text-muted" x-text="t('searchEmpty')"></p>
        </main>

        {{-- ========== Bottom dock ========== --}}
        {{-- Order CTA bar (only when the cart has items and no ticket is shown) --}}
        <div x-show="ordering && totalCount > 0 && !placed" x-cloak class="fixed inset-x-0 bottom-[60px] z-30 px-4 pb-2">
            <div class="mx-auto max-w-[680px]">
                <button type="button" x-on:click="showTicket()" class="flex w-full items-center justify-between rounded-2xl bg-accent px-5 py-3.5 text-white shadow-lg transition hover:bg-accent-hover">
                    <span class="flex items-center gap-2 font-semibold">
                        @include('menu.icons.cart')
                        <span x-text="t('navCart')"></span>
                        <span class="rounded-full bg-white/25 px-2 py-0.5 text-xs" x-text="totalCount"></span>
                    </span>
                    <span class="font-bold" x-text="formatPrice(totalMinor)"></span>
                </button>
            </div>
        </div>

        {{-- Persistent nav bar --}}
        <nav class="fixed inset-x-0 bottom-0 z-30 border-t border-border bg-white/95 backdrop-blur">
            <div class="mx-auto flex max-w-[680px] items-stretch justify-center px-4">
                <button type="button" x-show="ordering" x-on:click="sheet = 'cart'" class="flex flex-1 flex-col items-center gap-0.5 py-2.5 text-[11px] font-medium text-muted transition hover:text-accent">
                    <span class="relative">
                        @include('menu.icons.cart')
                        <span x-show="totalCount > 0" x-cloak class="absolute -right-2 -top-1.5 flex h-4 min-w-4 items-center justify-center rounded-full bg-accent px-1 text-[10px] font-bold text-white" x-text="totalCount"></span>
                    </span>
                    <span x-text="t('navCart')"></span>
                </button>
                <button type="button" x-on:click="sheet = 'waiter'" class="flex flex-1 flex-col items-center gap-0.5 py-2.5 text-[11px] font-medium text-muted transition hover:text-accent">
                    @include('menu.icons.bell')
                    <span x-text="t('navWaiter')"></span>
                </button>
                <button type="button" x-on:click="sheet = 'settings'" class="flex flex-1 flex-col items-center gap-0.5 py-2.5 text-[11px] font-medium text-muted transition hover:text-accent">
                    @include('menu.icons.settings')
                    <span x-text="t('navSettings')"></span>
                </button>
            </div>
        </nav>

        {{-- ========== Bottom sheets ========== --}}
        @include('menu.partials.sheet-cart')
        @include('menu.partials.sheet-settings', ['themes' => $themes, 'layouts' => $layouts])
        @include('menu.partials.sheet-waiter')

        {{-- Footer credit --}}
        <p class="pb-2 pt-6 text-center text-xs text-muted-soft" x-text="t('poweredBy')"></p>
    </div>
@endsection
