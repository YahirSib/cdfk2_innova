import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import inject from '@rollup/plugin-inject';
import tailwindcss from '@tailwindcss/vite';
import { sync as globSync } from 'glob';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', ...globSync('resources/js/**/*.js')],
            refresh: true,
        }),
        inject({
            $: 'jquery',
            jQuery: 'jquery',
        }),
        tailwindcss(),
    ],
    server: {
        host: '127.0.0.1',
        cors: true,
        hmr: {
            host: '127.0.0.1',
        },
    },
});
