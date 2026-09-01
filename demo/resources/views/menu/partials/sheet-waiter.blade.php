{{-- Call-the-waiter bottom-sheet. Local only (a demo affordance) — nothing is
     sent to a server; it just acknowledges on screen. --}}
<div x-show="sheet === 'waiter'" x-cloak class="fixed inset-0 z-40" x-on:keydown.escape.window="sheet = null">
    <div class="absolute inset-0 bg-black/40" x-on:click="sheet = null"></div>

    <div class="animate-sheet-up absolute inset-x-0 bottom-0 mx-auto max-w-[680px] rounded-t-3xl bg-surface p-4 pb-8 shadow-2xl">
        <div class="mb-1 flex items-center justify-between">
            <h3 class="text-base font-bold text-foreground" x-text="t('waiterTitle')"></h3>
            <button type="button" x-on:click="sheet = null" class="rounded-full p-1.5 text-muted transition hover:bg-surface-2">@include('menu.icons.x')</button>
        </div>
        <p class="mb-5 text-sm text-muted" x-text="t('waiterHint')"></p>

        <template x-if="!waiterCalled">
            <button type="button" x-on:click="callWaiter()" class="flex w-full items-center justify-center gap-2 rounded-2xl bg-accent py-3.5 font-semibold text-white transition hover:bg-accent-hover">
                @include('menu.icons.bell')
                <span x-text="t('waiterCall')"></span>
            </button>
        </template>
        <template x-if="waiterCalled">
            <div class="flex items-center justify-center gap-2 rounded-2xl bg-accent-soft py-3.5 font-semibold text-accent">
                @include('menu.icons.check')
                <span x-text="t('waiterCalled')"></span>
            </div>
        </template>
    </div>
</div>
