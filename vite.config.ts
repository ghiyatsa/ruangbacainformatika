import inertia from '@inertiajs/vite';
import { wayfinder } from '@laravel/vite-plugin-wayfinder';
import tailwindcss from '@tailwindcss/vite';
import react, { reactCompilerPreset } from '@vitejs/plugin-react';
import babel from '@rolldown/plugin-babel';
import laravel from 'laravel-vite-plugin';
import { defineConfig } from 'vite';

// Vendor chunks hanya untuk build client.
// Saat build SSR, grouping ini membuat vite:css-post gagal membaca
// chunk metadata ("Cannot read properties of undefined (reading 'viteMetadata')").
const vendorGroups = [
    {
        name: 'react-vendor',
        test: /node_modules[\\/](react|react-dom|scheduler|use-sync-external-store)/,
        priority: 30,
    },
    {
        name: 'inertia-vendor',
        test: /node_modules[\\/]@inertiajs/,
        priority: 25,
    },
    {
        name: 'motion-vendor',
        test: /node_modules[\\/]motion[\\/]/,
        priority: 24,
    },
    {
        name: 'icons-vendor',
        test: /node_modules[\\/]lucide-react[\\/]/,
        priority: 23,
    },
    {
        name: 'radix-vendor',
        test: /node_modules[\\/](?:@radix-ui|radix-ui)[\\/]/,
        priority: 22,
    },
    {
        name: 'search-vendor',
        test: /node_modules[\\/]cmdk[\\/]/,
        priority: 21,
    },
    {
        name: 'toast-vendor',
        test: /node_modules[\\/]sonner[\\/]/,
        priority: 20,
    },
    {
        name: 'qr-vendor',
        test: /node_modules[\\/]jsqr[\\/]/,
        priority: 19,
    },
    {
        name: 'turnstile-vendor',
        test: /node_modules[\\/]@marsidev[\\/]react-turnstile[\\/]/,
        priority: 18,
    },
];

export default defineConfig(({ command, isSsrBuild }) => ({
    build: {
        // Entry & output eksplisit agar `isSsrBuild` terdeteksi saat
        // dijalankan `vite build --ssr`.
        ssr: isSsrBuild ? 'resources/js/ssr.tsx' : false,
        outDir: isSsrBuild ? 'bootstrap/ssr' : 'public/build',
        // Hanya kelompokkan vendor chunk pada build browser.
        ...(isSsrBuild
            ? {}
            : {
                  rolldownOptions: {
                      output: {
                          codeSplitting: {
                              minSize: 20_000,
                              groups: vendorGroups,
                          },
                      },
                  },
              }),
    },
    server: {
        host: 'localhost',
        cors: {
            origin: ['http://localhost:8000', 'http://127.0.0.1:8000'],
        },
        hmr: {
            host: 'localhost',
        },
    },
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.tsx'],
            refresh: [
                'app/Filament/**/*.php',
                'app/Providers/Filament/**/*.php',
                'app/Livewire/**/*.php',
                'resources/views/**/*.blade.php',
                'routes/**/*.php',
            ],
        }),
        inertia({
            // SSR diaktifkan via env VITE_SSR=true (Production 2 only).
            // Production 1 tidak support SSR — biarkan false atau hapus var ini dari .env.
            ssr: process.env.VITE_SSR === 'true',
        }),
        react(),
        babel({
            presets: [reactCompilerPreset()],
        }),
        tailwindcss(),
        ...(command === 'build'
            ? [
                  wayfinder({
                      formVariants: true,
                  }),
              ]
            : []),
    ],
}));
