import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { resolve } from 'path';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
            ],
            refresh: true,
        }),
    ],
    resolve: {
        alias: {
            '~bootstrap': resolve('./node_modules/bootstrap'),
            '~admin-lte': resolve('./node_modules/admin-lte'),
            '~bootstrap-icons': resolve('./node_modules/bootstrap-icons'),
            '~overlayscrollbars': resolve('./node_modules/overlayscrollbars'),
            '~tinymce': resolve('./node_modules/tinymce'),
            '~frappe-gantt': resolve('./node_modules/frappe-gantt'),
        }
    },
});
