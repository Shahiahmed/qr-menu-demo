{{-- Settings bottom-sheet: the guest picks colour + layout for themselves.
     `$themes` and `$layouts` come from config('menu'). --}}
<div x-show="sheet === 'settings'" x-cloak class="fixed inset-0 z-40" x-on:keydown.escape.window="sheet = null">
    <div class="absolute inset-0 bg-black/40" x-on:click="sheet = null"></div>

    <div class="animate-sheet-up absolute inset-x-0 bottom-0 mx-auto max-h-[85vh] max-w-[680px] overflow-y-auto rounded-t-3xl bg-surface p-4 pb-8 shadow-2xl">
        <div class="mb-1 flex items-center justify-between">
            <h3 class="text-base font-bold text-foreground" x-text="t('settingsTitle')"></h3>
            <button type="button" x-on:click="sheet = null" class="rounded-full p-1.5 text-muted transition hover:bg-surface-2">@include('menu.icons.x')</button>
        </div>
        <p class="mb-4 text-xs text-muted" x-text="t('settingsHint')"></p>

        {{-- Colour --}}
        <p class="mb-2 text-sm font-semibold text-foreground" x-text="t('settingsColor')"></p>
        <div class="mb-6 grid grid-cols-8 gap-2">
            @foreach ($themes as $key => $t)
                <button
                    type="button"
                    x-on:click="setTheme('{{ $key }}')"
                    :class="theme === '{{ $key }}' ? 'ring-2 ring-offset-2 ring-foreground' : 'ring-1 ring-border'"
                    class="aspect-square rounded-full transition"
                    style="background: {{ $t['accent'] }}"
                    title="{{ $t['ru'] }}"
                    aria-label="{{ $t['ru'] }}"
                ></button>
            @endforeach
        </div>

        {{-- Layout --}}
        <p class="mb-2 text-sm font-semibold text-foreground" x-text="t('settingsDesign')"></p>
        <div class="grid grid-cols-3 gap-3">
            @foreach ($layouts as $key => $l)
                <button
                    type="button"
                    x-on:click="setLayout('{{ $key }}')"
                    :class="layout === '{{ $key }}' ? 'ring-2 ring-accent' : 'ring-1 ring-border'"
                    class="rounded-xl bg-white p-2.5 text-left transition hover:ring-border-strong"
                >
                    <div class="mb-2 h-16 overflow-hidden rounded-lg bg-surface-2 p-1.5">
                        @include('menu.partials.layout-preview', ['variant' => $key])
                    </div>
                    <span class="block text-[11px] font-medium leading-tight text-muted">{{ $l['ru'] }}</span>
                </button>
            @endforeach
        </div>
    </div>
</div>
