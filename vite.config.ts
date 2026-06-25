import inertia from '@inertiajs/vite';
import { wayfinder } from '@laravel/vite-plugin-wayfinder';
import tailwindcss from '@tailwindcss/vite';
import react, { reactCompilerPreset } from '@vitejs/plugin-react';
import babel from '@rolldown/plugin-babel';
import laravel from 'laravel-vite-plugin';
import { defineConfig } from 'vite';

export default defineConfig(({ command }) => ({
    build: {
        rolldownOptions: {
            output: {
                codeSplitting: {
                    minSize: 20_000,
                    groups: [
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
                        {
                            name: 'otp-vendor',
                            test: /node_modules[\\/]input-otp[\\/]/,
                            priority: 17,
                        },
                        {
                            name: 'ui-vendor',
                            test: /node_modules[\\/](?:class-variance-authority|clsx|tailwind-merge)/,
                            priority: 15,
                        },
                        {
                            name: 'vendor',
                            test: /node_modules/,
                            priority: 10,
                        },
                    ],
                },
            },
        },
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
            ssr: false,
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
