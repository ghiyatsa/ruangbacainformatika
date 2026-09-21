<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="robots" content="noindex, nofollow">
        <title>Sedang Maintenance — {{ config('app.name', 'Ruang Baca Informatika') }}</title>

        {{-- Ditulis mandiri (tanpa @vite) agar tetap tampil saat aset sedang
             dibangun ulang. Struktur & warna sengaja disamakan dengan halaman
             error bersama (resources/js/pages/error/index.tsx). --}}
        <style>
            :root {
                color-scheme: light dark;
                --background: oklch(0.99 0.002 277);
                --foreground: oklch(0.145 0.002 277);
                --card: oklch(1 0 0);
                --primary: oklch(0.457 0.24 277.023);
                --primary-foreground: oklch(0.962 0.018 272.314);
                --muted-foreground: oklch(0.556 0.006 277);
                --border: oklch(0.87 0.006 277);
                --shadow: 0 30px 80px -35px rgba(0, 0, 0, 0.25);
            }

            @media (prefers-color-scheme: dark) {
                :root {
                    --background: oklch(0.129 0.006 281);
                    --foreground: oklch(0.985 0.002 281);
                    --card: oklch(0.165 0.006 281);
                    --primary: oklch(0.72 0.16 281);
                    --primary-foreground: oklch(0.205 0.02 281);
                    --muted-foreground: oklch(0.65 0.008 281);
                    --border: oklch(0.22 0.008 281);
                    --shadow: 0 30px 80px -35px rgba(0, 0, 0, 0.5);
                }
            }

            * {
                box-sizing: border-box;
            }

            html,
            body {
                margin: 0;
                padding: 0;
                min-height: 100%;
            }

            body {
                background-color: var(--background);
                color: var(--foreground);
                font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont,
                    'Segoe UI', Roboto, 'Helvetica Neue', Arial, 'Noto Sans', sans-serif,
                    'Apple Color Emoji', 'Segoe UI Emoji';
                -webkit-font-smoothing: antialiased;
                line-height: 1.5;
            }

            .pattern {
                position: fixed;
                inset: 0;
                z-index: 0;
                pointer-events: none;
                opacity: 0.03;
                background-image: radial-gradient(circle at 1px 1px, currentColor 1px, transparent 0);
                background-size: 24px 24px;
            }

            @media (prefers-color-scheme: dark) {
                .pattern {
                    opacity: 0.05;
                }
            }

            .wrap {
                position: relative;
                display: flex;
                align-items: center;
                justify-content: center;
                min-height: 100vh;
                overflow: hidden;
                padding: 2.5rem 1.5rem;
            }

            .shell {
                width: 100%;
                max-width: 64rem;
                margin: 0 auto;
            }

            .card {
                display: grid;
                width: 100%;
                gap: 2.5rem;
                padding: 2rem;
                background-color: var(--card);
                border: 1px solid color-mix(in oklab, var(--border) 50%, transparent);
                border-radius: 2rem;
                box-shadow: var(--shadow);
            }

            .col {
                display: flex;
                flex-direction: column;
                justify-content: center;
                gap: 2rem;
            }

            .badge {
                display: inline-flex;
                align-items: center;
                gap: 0.5rem;
                width: fit-content;
                padding: 0.5rem 1rem;
                border: 1px solid color-mix(in oklab, var(--primary) 20%, transparent);
                border-radius: 9999px;
                background-color: color-mix(in oklab, var(--primary) 5%, transparent);
                box-shadow: 0 0 0 1px color-mix(in oklab, var(--primary) 10%, transparent);
                color: var(--primary);
                font-size: 0.875rem;
                font-weight: 500;
            }

            .badge svg {
                width: 1rem;
                height: 1rem;
            }

            .text {
                display: flex;
                flex-direction: column;
                gap: 1rem;
            }

            .status {
                margin: 0;
                color: var(--muted-foreground);
                font-size: 0.875rem;
                font-weight: 600;
                letter-spacing: 0.35em;
                text-transform: uppercase;
            }

            h1 {
                margin: 0;
                max-width: 36rem;
                font-size: 2.25rem;
                font-weight: 700;
                letter-spacing: -0.025em;
                line-height: 1.1;
            }

            .desc {
                margin: 0;
                max-width: 42rem;
                color: var(--muted-foreground);
                font-size: 1rem;
                line-height: 1.625;
            }

            .actions {
                display: flex;
                flex-direction: column;
                gap: 0.75rem;
            }

            .btn {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: 0.5rem;
                padding: 0.625rem 1.25rem;
                border: 1px solid transparent;
                border-radius: 0.5rem;
                font-family: inherit;
                font-size: 0.875rem;
                font-weight: 500;
                text-decoration: none;
                cursor: pointer;
                transition: background-color 150ms ease, border-color 150ms ease, color 150ms ease;
            }

            .btn svg {
                width: 1rem;
                height: 1rem;
            }

            .btn-primary {
                background-color: var(--primary);
                color: var(--primary-foreground);
                box-shadow: 0 1px 2px rgba(0, 0, 0, 0.08);
            }

            .btn-primary:hover {
                background-color: color-mix(in oklab, var(--primary) 90%, black);
            }

            .btn-outline {
                background-color: var(--background);
                border-color: var(--border);
                color: var(--foreground);
            }

            .btn-outline:hover {
                background-color: color-mix(in oklab, var(--foreground) 5%, transparent);
            }

            .btn-ghost {
                background-color: transparent;
                color: var(--foreground);
            }

            .btn-ghost:hover {
                background-color: color-mix(in oklab, var(--foreground) 5%, transparent);
            }

            .btn:focus-visible {
                outline: 2px solid var(--primary);
                outline-offset: 2px;
            }

            .panel-wrap {
                display: flex;
                align-items: center;
                justify-content: center;
            }

            .panel {
                display: flex;
                align-items: center;
                justify-content: center;
                width: 100%;
                min-height: 18rem;
                padding: 2rem;
                border: 1px solid color-mix(in oklab, var(--border) 50%, transparent);
                border-radius: 1.75rem;
                background-color: var(--background);
                box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.04);
            }

            .panel-inner {
                text-align: center;
            }

            .code {
                display: block;
                font-size: 6rem;
                font-weight: 900;
                letter-spacing: -0.05em;
                line-height: 1;
                background-image: linear-gradient(
                    to bottom,
                    var(--primary),
                    color-mix(in oklab, var(--primary) 60%, transparent)
                );
                -webkit-background-clip: text;
                background-clip: text;
                color: transparent;
            }

            .brand {
                margin: 1rem 0 0;
                color: color-mix(in oklab, var(--muted-foreground) 60%, transparent);
                font-size: 0.75rem;
                font-weight: 700;
                letter-spacing: 0.4em;
                text-transform: uppercase;
            }

            @media (min-width: 640px) {
                .actions {
                    flex-direction: row;
                    align-items: center;
                }
            }

            @media (min-width: 768px) {
                .card {
                    grid-template-columns: 1.1fr 0.9fr;
                    padding: 3rem;
                }

                h1 {
                    font-size: 3rem;
                }

                .desc {
                    font-size: 1.125rem;
                }

                .code {
                    font-size: 8rem;
                }
            }
        </style>
    </head>
    <body>
        <div class="pattern" aria-hidden="true"></div>

        <main class="wrap" role="main">
            <div class="shell">
                <div class="card">
                    <div class="col">
                        <div class="badge">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3"/><path d="M12 9v4"/><path d="M12 17h.01"/></svg>
                            Informasi Kendala
                        </div>

                        <div class="text">
                            <p class="status">Status 503</p>
                            <h1>Sedang maintenance</h1>
                            <p class="desc">
                                Layanan sedang dalam pemeliharaan terjadwal untuk meningkatkan
                                kualitas akses. Silakan kunjungi kembali beberapa saat lagi.
                            </p>

                            @isset($retryAfter)
                                @if (filled($retryAfter))
                                    <p class="desc">Perkiraan selesai: {{ $retryAfter }}.</p>
                                @endif
                            @endisset
                        </div>

                        <div class="actions">
                            <a href="{{ url('/') }}" class="btn btn-primary">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 21v-8a1 1 0 0 0-1-1h-4a1 1 0 0 0-1 1v8"/><path d="M3 10a2 2 0 0 1 .709-1.528l7-5.999a2 2 0 0 1 2.582 0l7 5.999A2 2 0 0 1 21 10v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg>
                                Kembali ke beranda
                            </a>

                            <button type="button" class="btn btn-outline" onclick="window.location.reload()">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/></svg>
                                Coba lagi
                            </button>
                        </div>
                    </div>

                    <div class="panel-wrap">
                        <div class="panel">
                            <div class="panel-inner">
                                <span class="code">503</span>
                                <p class="brand">Ruang Baca</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </body>
</html>
