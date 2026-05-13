<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" @class(['dark' => ($appearance ?? 'system') == 'dark'])>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        {{-- Inline script to detect system dark mode preference and apply it immediately --}}
        <script>
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
        <style>
            html {
                background-color: oklch(1 0 0);
            }

            html.dark {
                background-color: oklch(0.145 0 0);
            }
        </style>

        <link rel="icon" type="image/x-icon" href="/favicon.ico">
        <link rel="icon" href="/favicon.svg" type="image/svg+xml">
        <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
        <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
        <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
        <link rel="icon" type="image/png" sizes="192x192" href="/android-chrome-192x192.png">
        <link rel="icon" type="image/png" sizes="512x512" href="/android-chrome-512x512.png">
        <meta name="theme-color" content="#ffffff">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

        @viteReactRefresh
        @vite(['resources/css/app.css', 'resources/js/app.tsx', "resources/js/pages/{$page['component']}.tsx"])

        <x-inertia::head>
            <title>{{ config('app.name', 'Ruang Baca Informatika') }}</title>
            <meta data-inertia="description" name="description" content="Perpustakaan digital resmi Program Studi Teknik Informatika Universitas Malikussaleh untuk mendukung pembelajaran, riset, dan akses koleksi akademik.">
            <meta data-inertia="robots" name="robots" content="index,follow">
            <link data-inertia="canonical" rel="canonical" href="{{ url()->current() }}">

            <!-- Open Graph / Facebook -->
            <meta data-inertia="og:type" property="og:type" content="website">
            <meta data-inertia="og:url" property="og:url" content="{{ url()->current() }}">
            <meta data-inertia="og:title" property="og:title" content="{{ config('app.name', 'Ruang Baca Informatika') }}">
            <meta data-inertia="og:description" property="og:description" content="Perpustakaan digital resmi Program Studi Teknik Informatika Universitas Malikussaleh untuk mendukung pembelajaran, riset, dan akses koleksi akademik.">
            <meta data-inertia="og:image" property="og:image" content="{{ asset('images/og-image.png') }}">

            <!-- Twitter -->
            <meta data-inertia="twitter:card" property="twitter:card" content="summary_large_image">
            <meta data-inertia="twitter:title" property="twitter:title" content="{{ config('app.name', 'Ruang Baca Informatika') }}">
            <meta data-inertia="twitter:description" property="twitter:description" content="Perpustakaan digital resmi Program Studi Teknik Informatika Universitas Malikussaleh untuk mendukung pembelajaran, riset, dan akses koleksi akademik.">
            <meta data-inertia="twitter:image" property="twitter:image" content="{{ asset('images/og-image.png') }}">
        </x-inertia::head>
    </head>
    <body class="font-sans antialiased">
        <x-inertia::app />
    </body>
</html>
