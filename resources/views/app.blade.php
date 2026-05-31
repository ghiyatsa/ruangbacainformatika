<!DOCTYPE html>
<html
    lang="{{ str_replace('_', '-', app()->getLocale()) }}"
    data-app-name="{{ $siteMeta['title'] ?? config('app.name', 'Ruang Baca Informatika') }}"
    @class(['dark' => ($appearance ?? 'system') == 'dark'])
>
    <head>
        @php
            $pageMeta = $meta ?? [];
            $metaTitle = $pageMeta['title'] ?? ($siteMeta['title'] ?? config('app.name', 'Ruang Baca Informatika'));
            $metaDescription = $pageMeta['description'] ?? ($siteMeta['description'] ?? 'Perpustakaan digital resmi Program Studi Teknik Informatika Universitas Malikussaleh untuk mendukung pembelajaran, riset, dan akses koleksi akademik.');
            $metaKeywords = $pageMeta['keywords'] ?? ($siteMeta['keywords'] ?? null);
            $metaRobots = $pageMeta['robots'] ?? ($siteMeta['robots'] ?? 'index,follow');
            $canonicalUrl = $pageMeta['canonicalUrl'] ?? url()->current();
            $metaOgType = $pageMeta['type'] ?? 'website';
            $metaOgImage = $pageMeta['ogImage'] ?? ($siteMeta['ogImage'] ?? asset('images/og-image.png'));
        @endphp

        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        {{-- Inline script to detect system dark mode preference and apply it immediately --}}
        <script nonce="{{ \Illuminate\Support\Facades\Vite::cspNonce() }}">
            (function() {
                const appearance = '{{ $appearance ?? "system" }}';

                if (appearance === 'system') {
                    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

                    if (prefersDark) {
                        document.documentElement.classList.add('dark');
                    }
                }
            })();
        </script>

        {{-- Inline style to set the HTML background color based on our theme in app.css --}}
        <style nonce="{{ \Illuminate\Support\Facades\Vite::cspNonce() }}">
            html {
                background-color: oklch(1 0 0);
            }

            html.dark {
                background-color: oklch(0.145 0 0);
            }
        </style>

        <link rel="icon" type="image/png" href="{{ $siteMeta['favicon'] ?? asset('favicon-32x32.png') }}">
        <link rel="icon" href="{{ $siteMeta['faviconSvg'] ?? asset('favicon.svg') }}" type="image/svg+xml">
        <link rel="icon" type="image/png" sizes="16x16" href="{{ $siteMeta['favicon'] ?? asset('favicon-16x16.png') }}">
        <link rel="icon" type="image/png" sizes="32x32" href="{{ $siteMeta['favicon'] ?? asset('favicon-32x32.png') }}">
        <link rel="apple-touch-icon" sizes="180x180" href="{{ $siteMeta['appleTouchIcon'] ?? asset('apple-touch-icon.png') }}">
        <link rel="icon" type="image/png" sizes="192x192" href="{{ $siteMeta['favicon'] ?? asset('android-chrome-192x192.png') }}">
        <link rel="icon" type="image/png" sizes="512x512" href="{{ $siteMeta['favicon'] ?? asset('android-chrome-512x512.png') }}">
        <meta name="theme-color" content="{{ $siteMeta['themeColor'] ?? '#ffffff' }}">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

        @viteReactRefresh
        @vite(['resources/css/app.css', 'resources/js/app.tsx'])

        <x-inertia::head>
            <title>{{ $metaTitle }}</title>
            <meta data-inertia="description" name="description" content="{{ $metaDescription }}">
            <meta data-inertia="robots" name="robots" content="{{ $metaRobots }}">
            @if (filled($metaKeywords))
                <meta data-inertia="keywords" name="keywords" content="{{ $metaKeywords }}">
            @endif
            <link data-inertia="canonical" rel="canonical" href="{{ $canonicalUrl }}">

            <!-- Open Graph / Facebook -->
            <meta data-inertia="og:type" property="og:type" content="{{ $metaOgType }}">
            <meta data-inertia="og:url" property="og:url" content="{{ $canonicalUrl }}">
            <meta data-inertia="og:title" property="og:title" content="{{ $metaTitle }}">
            <meta data-inertia="og:description" property="og:description" content="{{ $metaDescription }}">
            <meta data-inertia="og:image" property="og:image" content="{{ $metaOgImage }}">
            <meta data-inertia="og:image:type" property="og:image:type" content="image/png">
            <meta data-inertia="og:image:width" property="og:image:width" content="1200">
            <meta data-inertia="og:image:height" property="og:image:height" content="630">

            <!-- Twitter -->
            <meta data-inertia="twitter:card" property="twitter:card" content="summary_large_image">
            <meta data-inertia="twitter:title" property="twitter:title" content="{{ $metaTitle }}">
            <meta data-inertia="twitter:description" property="twitter:description" content="{{ $metaDescription }}">
            <meta data-inertia="twitter:image" property="twitter:image" content="{{ $metaOgImage }}">
        </x-inertia::head>
    </head>
    <body class="font-sans antialiased">
        <x-inertia::app />
    </body>
</html>
