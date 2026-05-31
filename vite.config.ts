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
                            name: 'ui-vendor',
                            test: /node_modules[\\/](?:radix-ui|motion|lucide-react|class-variance-authority|clsx|tailwind-merge|cmdk|sonner)/,
                            priority: 20,
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
            refresh: true,
        }),
        inertia({
            ssr: command === 'build' ? undefined : false,
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
