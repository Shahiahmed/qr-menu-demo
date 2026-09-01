<!DOCTYPE html>
<html lang="{{ $venue->default_locale === 'kk' ? 'kk' : 'ru' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', $venue->name)</title>
    <meta name="description" content="@yield('description', \Illuminate\Support\Str::limit(strip_tags($venue->description_ru ?? ''), 160))">

    {{-- Open Graph / social preview --}}
    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:site_name" content="{{ $venue->name }}">
    <meta property="og:title" content="@yield('title', $venue->name)">
    <meta property="og:description" content="@yield('description', \Illuminate\Support\Str::limit(strip_tags($venue->description_ru ?? ''), 160))">
    @hasSection('og_image')
        <meta property="og:image" content="@yield('og_image')">
        <meta name="twitter:card" content="summary_large_image">
    @endif

    @yield('head')

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>[x-cloak]{display:none !important;}</style>
</head>
<body>
    @yield('body')
</body>
</html>
