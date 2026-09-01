<!DOCTYPE html>
<html lang="{{ $venue->default_locale === 'kk' ? 'kk' : 'ru' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- SEO defaults come from the venue's panel-editable fields (resolved for
         the default locale). Per-page views override them with @section. --}}
    <title>@yield('title', $venue->seoTitle())</title>
    <meta name="description" content="@yield('description', $venue->seoDescription())">
    @hasSection('keywords')
        <meta name="keywords" content="@yield('keywords')">
    @elseif ($venue->seoKeywords())
        <meta name="keywords" content="{{ $venue->seoKeywords() }}">
    @endif

    {{-- Open Graph / social preview. og:image always resolves (owner's OG image
         → cover → default photo), so a shared link never shows a blank card. --}}
    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:site_name" content="{{ $venue->name }}">
    <meta property="og:title" content="@yield('title', $venue->seoTitle())">
    <meta property="og:description" content="@yield('description', $venue->seoDescription())">
    <meta property="og:locale" content="{{ $venue->default_locale === 'kk' ? 'kk_KZ' : 'ru_RU' }}">
    <meta property="og:image" content="@yield('og_image', $venue->ogImageUrl())">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="@yield('title', $venue->seoTitle())">
    <meta name="twitter:description" content="@yield('description', $venue->seoDescription())">
    <meta name="twitter:image" content="@yield('og_image', $venue->ogImageUrl())">

    @yield('head')

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>[x-cloak]{display:none !important;}</style>
</head>
<body>
    @yield('body')
</body>
</html>
