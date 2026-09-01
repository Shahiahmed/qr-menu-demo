{{-- Cart bottom-sheet: order review, or the local "show the waiter" ticket. --}}
<div x-show="sheet === 'cart'" x-cloak class="fixed inset-0 z-40" x-on:keydown.escape.window="sheet = null">
    <div class="absolute inset-0 bg-black/40" x-on:click="sheet = null"></div>

    <div class="animate-sheet-up absolute inset-x-0 bottom-0 mx-auto max-h-[85vh] max-w-[680px] overflow-y-auto rounded-t-3xl bg-surface p-4 pb-8 shadow-2xl">
        <div class="mb-3 flex items-center justify-between">
            <h3 class="text-base font-bold text-foreground" x-text="placed ? t('placedTitle') : t('cartTitle')"></h3>
            <button type="button" x-on:click="sheet = null" class="rounded-full p-1.5 text-muted transition hover:bg-surface-2">@include('menu.icons.x')</button>
        </div>

        {{-- Placed ticket --}}
        <template x-if="placed">
            <div>
                <div class="mb-3 flex items-center justify-between gap-3 rounded-xl bg-accent-soft p-3">
                    <div>
                        <p class="text-sm font-semibold text-accent"><span x-text="t('placedNumber')"></span><span x-text="orderNumber"></span></p>
                        <p class="mt-0.5 text-xs text-accent/80" x-text="t('placedHint')"></p>
                    </div>
                    <template x-if="placedTable">
                        <span class="shrink-0 rounded-full bg-accent px-3 py-1 text-xs font-bold text-white"><span x-text="t('placedTable')"></span> <span x-text="placedTable"></span></span>
                    </template>
                </div>
                <ul class="divide-y divide-border rounded-2xl bg-white ring-1 ring-border">
                    <template x-for="line in cartLines" :key="line.id">
                        <li class="flex items-center justify-between gap-3 p-3 text-sm">
                            <span class="flex-1 truncate text-foreground"><span x-text="line.name"></span> <span class="text-muted" x-text="'× ' + line.n"></span></span>
                            <span class="font-semibold text-foreground" x-text="formatPrice(line.price * line.n)"></span>
                        </li>
                    </template>
                </ul>
                <div class="mt-3 flex items-center justify-between px-1">
                    <span class="text-sm font-medium text-muted" x-text="t('cartTotal')"></span>
                    <span class="text-lg font-bold text-foreground" x-text="formatPrice(totalMinor)"></span>
                </div>
                <button type="button" x-on:click="newOrder()" class="mt-4 w-full rounded-2xl bg-foreground py-3 font-semibold text-white transition hover:opacity-90" x-text="t('placedNew')"></button>
            </div>
        </template>

        {{-- Active cart --}}
        <template x-if="!placed">
            <div>
                <template x-if="totalCount === 0">
                    <p class="py-10 text-center text-muted" x-text="t('cartEmpty')"></p>
                </template>

                <template x-if="totalCount > 0">
                    <div>
                        <ul class="divide-y divide-border rounded-2xl bg-white ring-1 ring-border">
                            <template x-for="line in cartLines" :key="line.id">
                                <li class="flex items-center justify-between gap-3 p-3">
                                    <span class="flex-1 truncate text-sm text-foreground" x-text="line.name"></span>
                                    <div class="flex items-center gap-2 rounded-full bg-accent px-1.5 py-1 text-white">
                                        <button type="button" x-on:click="dec(line.id)" class="flex h-6 w-6 items-center justify-center rounded-full transition hover:bg-white/20">@include('menu.icons.minus')</button>
                                        <span class="min-w-4 text-center text-sm font-bold" x-text="line.n"></span>
                                        <button type="button" x-on:click="inc(line.id)" class="flex h-6 w-6 items-center justify-center rounded-full transition hover:bg-white/20">@include('menu.icons.plus')</button>
                                    </div>
                                    <span class="w-20 shrink-0 text-right text-sm font-semibold text-foreground" x-text="formatPrice(line.price * line.n)"></span>
                                </li>
                            </template>
                        </ul>

                        {{-- Table picker: a 1..N dropdown when the venue set a table
                             count, else a free numeric entry. Prefilled from ?table. --}}
                        <div class="mt-4">
                            <label class="mb-1 block text-xs font-medium text-muted" x-text="t('cartTable')"></label>
                            <template x-if="tablesCount">
                                <select x-model="tableNumber" class="w-full rounded-xl border border-border bg-white px-3 py-2.5 text-sm text-foreground focus:border-accent focus:outline-none">
                                    <option value="" x-text="t('cartTablePick')"></option>
                                    <template x-for="n in tablesCount" :key="n">
                                        <option :value="String(n)" x-text="n"></option>
                                    </template>
                                </select>
                            </template>
                            <template x-if="!tablesCount">
                                <input type="number" inputmode="numeric" min="1" max="9999" x-model="tableNumber" :placeholder="t('cartTablePick')" class="w-full rounded-xl border border-border bg-white px-3 py-2.5 text-sm text-foreground focus:border-accent focus:outline-none">
                            </template>
                        </div>

                        {{-- Optional note for the kitchen. --}}
                        <div class="mt-3">
                            <label class="mb-1 block text-xs font-medium text-muted" x-text="t('cartComment')"></label>
                            <textarea x-model="comment" rows="2" maxlength="500" :placeholder="t('cartCommentHint')" class="w-full resize-none rounded-xl border border-border bg-white px-3 py-2.5 text-sm text-foreground focus:border-accent focus:outline-none"></textarea>
                        </div>

                        <div class="mt-3 flex items-center justify-between px-1">
                            <span class="text-sm font-medium text-muted" x-text="t('cartTotal')"></span>
                            <span class="text-lg font-bold text-foreground" x-text="formatPrice(totalMinor)"></span>
                        </div>

                        <template x-if="orderError">
                            <p class="mt-3 rounded-xl bg-red-50 p-3 text-sm text-red-600" x-text="orderError"></p>
                        </template>

                        <button type="button" x-on:click="submitOrder()" :disabled="submitting" class="mt-4 w-full rounded-2xl bg-accent py-3 font-semibold text-white transition hover:bg-accent-hover disabled:opacity-60" x-text="submitting ? t('cartSending') : t('cartCheckout')"></button>
                        <button type="button" x-on:click="clearCart()" class="mt-2 w-full rounded-2xl py-2 text-sm font-medium text-muted transition hover:text-foreground" x-text="t('cartClear')"></button>
                    </div>
                </template>
            </div>
        </template>
    </div>
</div>
