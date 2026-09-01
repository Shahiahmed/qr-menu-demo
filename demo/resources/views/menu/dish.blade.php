@php
    use App\Support\Money;

    $title = "{$dish->name_ru} — {$venue->name}";
    $desc = \Illuminate\Support\Str::limit(strip_tags($dish->description_ru ?: $venue->name . ', ' . ($venue->address ?? '')), 160);
    $priceMajor = number_format(intdiv((int) $dish->price, 100), 0, '.', '');

    // schema.org — a menu item priced offer, so search engines can show it richly.
    $jsonLd = [
        '@context' => 'https://schema.org',
        '@type' => 'MenuItem',
        'name' => $dish->name_ru,
        'description' => $dish->description_ru ?: null,
        'image' => $dish->imageUrl(),
        'offers' => [
            '@type' => 'Offer',
            'price' => $priceMajor,
            'priceCurrency' => $venue->currency,
            'availability' => $dish->is_available
                ? 'https://schema.org/InStock'
                : 'https://schema.org/OutOfStock',
        ],
    ];
@endphp

@extends('layouts.guest')

@section('title', $title)
@section('description', $desc)
@section('og_type', 'product')
@if ($dish->imageUrl())
    @section('og_image', $dish->imageUrl())
@endif

@section('head')
    <link rel="canonical" href="{{ route('menu.dish', $dish->slug) }}">
    <script type="application/ld+json">{!! json_encode(array_filter($jsonLd), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
@endsection

@section('body')
    <div class="min-h-screen bg-surface pb-16">
        <div class="mx-auto max-w-[680px] px-4">
            <div class="py-4">
                <a href="{{ route('menu.home') }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-muted transition hover:text-accent">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                    {{ $venue->name }}
                </a>
            </div>

            <article class="overflow-hidden rounded-3xl bg-white shadow-sm ring-1 ring-border">
                <div class="aspect-[4/3] w-full overflow-hidden bg-surface-2">
                    @if ($dish->imageUrl())
                        <img src="{{ $dish->imageUrl() }}" alt="{{ $dish->name_ru }}" class="h-full w-full object-cover">
                    @else
                        <div class="placeholder-stripes flex h-full w-full items-center justify-center text-accent">
                            <span class="text-5xl font-bold opacity-40">{{ mb_substr($dish->name_ru, 0, 1) }}</span>
                        </div>
                    @endif
                </div>

                <div class="p-5">
                    @if ($dish->category)
                        <p class="text-xs font-medium uppercase tracking-wide text-accent">{{ $dish->category->name_ru }}</p>
                    @endif
                    <h1 class="mt-1 text-2xl font-bold text-foreground">{{ $dish->name_ru }}</h1>
                    @if ($dish->name_kk && $dish->name_kk !== $dish->name_ru)
                        <p class="text-base text-muted">{{ $dish->name_kk }}</p>
                    @endif

                    @if ($dish->description_ru)
                        <p class="mt-3 leading-relaxed text-muted">{{ $dish->description_ru }}</p>
                    @endif
                    @if ($dish->description_kk && $dish->description_kk !== $dish->description_ru)
                        <p class="mt-1.5 text-sm leading-relaxed text-muted-soft">{{ $dish->description_kk }}</p>
                    @endif

                    <div class="mt-5 flex items-center justify-between">
                        <span class="text-2xl font-bold text-foreground">{{ Money::format((int) $dish->price, $venue->currency) }}</span>
                        @unless ($dish->is_available)
                            <span class="rounded-full bg-surface-2 px-3 py-1.5 text-sm font-medium text-muted">Закончилось</span>
                        @endunless
                    </div>

                    <a href="{{ route('menu.home') }}#cat-{{ $dish->menu_category_id }}" class="mt-5 block w-full rounded-2xl bg-accent py-3 text-center font-semibold text-white transition hover:bg-accent-hover">
                        Открыть меню
                    </a>
                </div>
            </article>

            @if ($related->isNotEmpty())
                <h2 class="mb-3 mt-8 text-lg font-bold text-foreground">Ещё из раздела</h2>
                <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
                    @foreach ($related as $r)
                        <a href="{{ route('menu.dish', $r->slug) }}" class="overflow-hidden rounded-2xl bg-white ring-1 ring-border transition hover:ring-border-strong">
                            <div class="aspect-square w-full overflow-hidden bg-surface-2">
                                @if ($r->imageUrl())
                                    <img src="{{ $r->imageUrl() }}" alt="{{ $r->name_ru }}" loading="lazy" class="h-full w-full object-cover">
                                @else
                                    <div class="placeholder-stripes-sm h-full w-full"></div>
                                @endif
                            </div>
                            <div class="p-2.5">
                                <p class="truncate text-sm font-medium text-foreground">{{ $r->name_ru }}</p>
                                <p class="mt-0.5 text-sm font-semibold text-accent">{{ Money::format((int) $r->price, $venue->currency) }}</p>
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
@endsection
