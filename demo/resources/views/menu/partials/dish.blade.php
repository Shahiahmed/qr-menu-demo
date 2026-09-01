@php
    use App\Support\Money;

    // Searchable text for the client-side menu filter: both locales' name + desc,
    // lowercased, so a guest can type in either language.
    $searchKey = mb_strtolower(trim(implode(' ', array_filter([
        $d->name_ru, $d->name_kk, $d->description_ru, $d->description_kk,
    ]))));
@endphp
{{-- One dish. The same markup renders in all three layouts; the .dish CSS
     rearranges it. `$d` is a Dish model, `$venue` is in scope. --}}
<article
    @class(['dish', 'is-sold-out' => ! $d->is_available])
    data-key="{{ $searchKey }}"
    x-show="matchesKey($el.dataset.key)"
>
    <a href="{{ route('menu.dish', $d->slug) }}" class="dish-photo" aria-label="{{ $d->name_ru }}">
        @if ($d->imageUrl())
            <img src="{{ $d->imageUrl() }}" alt="{{ $d->name_ru }}" loading="lazy">
        @else
            <span class="placeholder-stripes-sm flex h-full w-full items-center justify-center text-accent">
                @include('menu.icons.utensils')
            </span>
        @endif
    </a>

    <div class="dish-body">
        <a href="{{ route('menu.dish', $d->slug) }}" class="dish-name hover:underline">
            <span x-show="locale === 'ru'">{{ $d->name_ru }}</span>
            <span x-show="locale === 'kk'" x-cloak>{{ $d->name_kk ?: $d->name_ru }}</span>
        </a>

        @if ($d->description_ru || $d->description_kk)
            <p class="dish-desc">
                <span x-show="locale === 'ru'">{{ $d->description_ru }}</span>
                <span x-show="locale === 'kk'" x-cloak>{{ $d->description_kk ?: $d->description_ru }}</span>
            </p>
        @endif

        <div class="dish-foot">
            <span class="dish-price">{{ Money::format((int) $d->price, $venue->currency) }}</span>

            @if (! $d->is_available)
                <span class="rounded-full bg-surface-2 px-2.5 py-1 text-xs font-medium text-muted" x-text="t('soldOut')"></span>
            @else
                {{-- Add button when the cart has none of this dish… (ordering on) --}}
                <button
                    type="button"
                    x-show="ordering && qty({{ $d->id }}) === 0"
                    x-on:click="add({{ $d->id }})"
                    class="rounded-full bg-accent-soft px-3.5 py-1.5 text-xs font-semibold text-accent transition hover:bg-accent-tint"
                    x-text="t('add')"
                ></button>

                {{-- …a stepper once it's in the cart. --}}
                <div x-show="ordering && qty({{ $d->id }}) > 0" x-cloak class="flex items-center gap-2 rounded-full bg-accent px-1.5 py-1 text-white">
                    <button type="button" x-on:click="dec({{ $d->id }})" class="flex h-6 w-6 items-center justify-center rounded-full transition hover:bg-white/20" aria-label="−">
                        @include('menu.icons.minus')
                    </button>
                    <span class="min-w-4 text-center text-sm font-bold" x-text="qty({{ $d->id }})"></span>
                    <button type="button" x-on:click="inc({{ $d->id }})" class="flex h-6 w-6 items-center justify-center rounded-full transition hover:bg-white/20" aria-label="+">
                        @include('menu.icons.plus')
                    </button>
                </div>
            @endif
        </div>
    </div>
</article>
