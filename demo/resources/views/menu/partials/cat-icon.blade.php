{{--
    Category icon for subcategory chips. Renders an inline SVG for the given
    $icon key (from config('menu.category_icons')). Unknown/empty key => nothing,
    so a stale key never forces a wrong glyph. Keys must match config('menu.category_icons').

    @param string|null $icon  category icon key
--}}
@php($icon = $icon ?? null)
@switch($icon)
    @case('utensils')
        <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M3 2v7c0 1.1.9 2 2 2h4a2 2 0 0 0 2-2V2"/><path d="M7 2v20"/><path d="M21 15V2a5 5 0 0 0-5 5v6c0 1.1.9 2 2 2h3Zm0 0v7"/></svg>
        @break
    @case('salad')
        <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M3 11h18a9 9 0 0 1-9 9 9 9 0 0 1-9-9Z"/><path d="M7 21h10"/><path d="M12 11c.2-2.6 2.2-4.6 5-5-.4 2.8-2.4 4.8-5 5Z"/><path d="M12 11c-.2-2.2-1.9-3.7-4.3-4C7.9 9 9.6 10.6 12 11Z"/></svg>
        @break
    @case('soup')
        <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M4 12h16a8 8 0 0 1-8 8 8 8 0 0 1-8-8Z"/><path d="M6 20h12"/><path d="M9 8c0-1 .8-1.5.8-2.5S9 3.5 9 3"/><path d="M13 8c0-1 .8-1.5.8-2.5S13 3.5 13 3"/></svg>
        @break
    @case('meat')
        <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M4 13c0-4 3.6-7 8-7s8 2.9 8 6c0 2-2 3-4 3-1 0-2 1-3.5 1S10 15 8 15s-4-1-4-2Z"/><path d="M8.5 10.5l3 2"/></svg>
        @break
    @case('fish')
        <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12c2.5-3.5 6-5 9-5 4 0 6 2.5 7 5-1 2.5-3 5-7 5-3 0-6.5-1.5-9-5Z"/><path d="M19 12l3-2v4l-3-2Z"/><circle cx="8" cy="11" r=".9" fill="currentColor" stroke="none"/></svg>
        @break
    @case('pizza')
        <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3 20 19a1 1 0 0 1-1.2 1.4L12 18.5 5.2 20.4A1 1 0 0 1 4 19Z"/><circle cx="10.5" cy="12" r="1" fill="currentColor" stroke="none"/><circle cx="13.5" cy="14.5" r="1" fill="currentColor" stroke="none"/></svg>
        @break
    @case('bread')
        <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M3 13a5 5 0 0 1 5-5h8a5 5 0 0 1 5 5 2 2 0 0 1-2 2H5a2 2 0 0 1-2-2Z"/><path d="M5 15v2a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-2"/><path d="M8 11.5h.01M12 11h.01M16 11.5h.01"/></svg>
        @break
    @case('grain')
        <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3v18"/><path d="M12 9c-2 0-3.2-1.1-3.2-3.2C10.8 5.8 12 6.9 12 9Z"/><path d="M12 9c2 0 3.2-1.1 3.2-3.2C13.2 5.8 12 6.9 12 9Z"/><path d="M12 14c-2 0-3.2-1.1-3.2-3.2C10.8 10.8 12 11.9 12 14Z"/><path d="M12 14c2 0 3.2-1.1 3.2-3.2C13.2 10.8 12 11.9 12 14Z"/><path d="M12 19c-2 0-3.2-1.1-3.2-3.2C10.8 15.8 12 16.9 12 19Z"/><path d="M12 19c2 0 3.2-1.1 3.2-3.2C13.2 15.8 12 16.9 12 19Z"/></svg>
        @break
    @case('cheese')
        <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M4 16 15 7l5 3-1 7a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1Z"/><circle cx="9" cy="14" r="1" fill="currentColor" stroke="none"/><circle cx="14" cy="15" r=".9" fill="currentColor" stroke="none"/></svg>
        @break
    @case('cake')
        <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M4 20h16v-8a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4Z"/><path d="M4 14c1.5 1.2 3-1 4.5 0s3 1.2 4.5 0 3-1 4.5 0"/><path d="M12 8V4.5"/><path d="M12 4.5c.7-.5.7-1.3 0-2-.7.7-.7 1.5 0 2Z"/></svg>
        @break
    @case('icecream')
        <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M17 7A5 5 0 0 0 7 7"/><path d="M17 7a2 2 0 0 1 0 4H7a2 2 0 0 1 0-4"/><path d="m7 11 4.08 10.35a1 1 0 0 0 1.84 0L17 11"/></svg>
        @break
    @case('fruit')
        <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M12 7c-1.5-1.5-4-1.7-5.6-.3C4.6 8.2 4.5 11 6 14c1 2 2.2 4 3.6 4 .9 0 1.4-.5 2.4-.5s1.5.5 2.4.5c1.4 0 2.6-2 3.6-4 1.5-3 1.4-5.8-.4-7.3C15.9 5.3 13.5 5.5 12 7Z"/><path d="M12 7c0-1.5.5-3 2-4"/></svg>
        @break
    @case('sauce')
        <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22a7 7 0 0 0 7-7c0-2-1-3.9-3-5.5s-3.5-4-4-6.5c-.5 2.5-2 4.9-4 6.5C6 11.1 5 13 5 15a7 7 0 0 0 7 7Z"/></svg>
        @break
    @case('flame')
        <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.4-.5-2-1-3-1.1-2.1-.2-4 2-6 .5 2.5 2 4.9 4 6.5s3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.2.4-2.3 1-3a2.5 2.5 0 0 0 2.5 2.5Z"/></svg>
        @break
    @case('leaf')
        <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M11 20A7 7 0 0 1 9.8 6.1C15.5 5 17 4.5 19 2c1 2 2 4.2 2 8 0 5.5-4.8 10-10 10Z"/><path d="M2 21c0-3 1.9-5.4 5.1-6C9.5 14.5 12 13 13 12"/></svg>
        @break
    @case('coffee')
        <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M10 2v2"/><path d="M14 2v2"/><path d="M6 2v2"/><path d="M16 8a1 1 0 0 1 1 1v8a4 4 0 0 1-4 4H7a4 4 0 0 1-4-4V9a1 1 0 0 1 1-1h14a4 4 0 1 1 0 8h-1"/></svg>
        @break
    @case('tea')
        <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M4 9h13a1 1 0 0 1 1 1 4 4 0 0 1-4 4H7a4 4 0 0 1-4-4 1 1 0 0 1 1-1Z"/><path d="M18 10h1a2 2 0 0 1 0 4h-1"/><path d="M4 18h14"/><path d="M8 6c0-1 .7-1.4.7-2.4S8 2.2 8 2"/><path d="M12 6c0-1 .7-1.4.7-2.4S12 2.2 12 2"/></svg>
        @break
    @case('water')
        <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M5 3h14l-1.2 17.1a2 2 0 0 1-2 1.9H8.2a2 2 0 0 1-2-1.9L5 3Z"/><path d="M6.2 9a5 5 0 0 1 3.3.4 5 5 0 0 0 5 0"/></svg>
        @break
    @case('juice')
        <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M6 7h12l-1.3 13.1a2 2 0 0 1-2 1.9H9.3a2 2 0 0 1-2-1.9Z"/><path d="M5 7h14"/><path d="M14 7 15 2"/></svg>
        @break
    @case('wine')
        <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M8 22h8"/><path d="M12 15v7"/><path d="M7 10h10"/><path d="M12 15a5 5 0 0 0 5-5c0-2-.5-4-2-8H9c-1.5 4-2 6-2 8a5 5 0 0 0 5 5Z"/></svg>
        @break
    @case('beer')
        <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M17 11h1a3 3 0 0 1 0 6h-1"/><path d="M9 12v6"/><path d="M13 12v6"/><path d="M5 8v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V8"/><path d="M14 7.5c-1 0-1.4.5-3 .5s-2-.5-3-.5-1.7.5-2.5.5a2.5 2.5 0 0 1 0-5c.8 0 1.6.5 2.5.5S9.4 3 11 3s2 .5 3 .5 1.7-.5 2.5-.5a2.5 2.5 0 0 1 0 5c-.8 0-1.5-.5-2.5-.5Z"/></svg>
        @break
    @case('cocktail')
        <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M8 22h8"/><path d="M12 11v11"/><path d="m19 3-7 8-7-8Z"/><path d="m14.5 6-3.5 4"/></svg>
        @break
    @case('bottle')
        <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M10 2h4"/><path d="M10 2v3.5c0 .6-.3 1.1-.7 1.5L8 8.5A3 3 0 0 0 7 11v8a3 3 0 0 0 3 3h4a3 3 0 0 0 3-3v-8a3 3 0 0 0-1-2.5l-1.3-1.5c-.4-.4-.7-.9-.7-1.5V2"/><path d="M7 13h10"/></svg>
        @break
    @case('milk')
        <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M6 8 12 3l6 5v11a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2Z"/><path d="M6 8h12"/><path d="M12 3v5"/></svg>
        @break
    @case('cigarette')
        <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M2 13h15v3H2Z"/><path d="M18 13h4v3h-4Z"/><path d="M16 9c0-2-2-2-2-4"/><path d="M20 9c0-2-2-2-2-4"/></svg>
        @break
@endswitch
