<?php

/**
 * Menu look-and-feel, ported verbatim from the main project
 * (front/src/content/themes.ts + layouts.ts). Keys must stay in sync with the
 * values stored in `venue_settings.theme` / `.layout` and with the guest-side
 * Alpine switcher.
 *
 * A theme only recolours the accent family — background and text never change,
 * so the owner (or guest) can never make the menu unreadable.
 */
return [
    'default_theme' => 'classic',
    'default_layout' => 'classic',

    // key => [label ru/kk, accent, accentHover, accentSoft, accentTint]
    'themes' => [
        'classic'  => ['ru' => 'Коралловый', 'kk' => 'Коралл',    'accent' => '#ff6a4d', 'hover' => '#ee5233', 'soft' => '#fff1ec', 'tint' => '#ffe3d9'],
        'graphite' => ['ru' => 'Графит',      'kk' => 'Графит',    'accent' => '#3f4753', 'hover' => '#2b313a', 'soft' => '#eef0f3', 'tint' => '#dfe3e9'],
        'forest'   => ['ru' => 'Лесной',      'kk' => 'Орман',     'accent' => '#1f9d57', 'hover' => '#178048', 'soft' => '#e8f6ee', 'tint' => '#cdebd9'],
        'ocean'    => ['ru' => 'Океан',       'kk' => 'Мұхит',     'accent' => '#2b8fd6', 'hover' => '#1f76b8', 'soft' => '#e7f2fb', 'tint' => '#cbe4f6'],
        'berry'    => ['ru' => 'Ягодный',     'kk' => 'Жидек',     'accent' => '#c2417f', 'hover' => '#a52f68', 'soft' => '#fbe9f2', 'tint' => '#f4cfe0'],
        'sand'     => ['ru' => 'Песочный',    'kk' => 'Құм',       'accent' => '#a9761f', 'hover' => '#8c6015', 'soft' => '#f7efdd', 'tint' => '#ecdcb9'],
        'rose'     => ['ru' => 'Розовый',     'kk' => 'Раушан',    'accent' => '#e05a72', 'hover' => '#c8465e', 'soft' => '#fdeaee', 'tint' => '#f8ccd5'],
        'midnight' => ['ru' => 'Полночь',     'kk' => 'Түнгі',     'accent' => '#5b60d6', 'hover' => '#474cc0', 'soft' => '#ecedfb', 'tint' => '#d5d7f5'],
    ],

    // key => label ru/kk. The schematic preview is drawn client-side.
    'layouts' => [
        'classic' => ['ru' => 'Карточки с фото слева', 'kk' => 'Сол жақта фотосы бар карточкалар'],
        'grid'    => ['ru' => 'Две колонки, фото сверху', 'kk' => 'Екі баған, фотосы жоғарыда'],
        'compact' => ['ru' => 'Плотный список',           'kk' => 'Ықшам тізім'],
    ],
];
