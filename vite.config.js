import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/css/dashboard.css', 'resources/js/app.js'],
            refresh: true,
        }),
        vue({}),
    ],
    server: {
        // origin: 'http://127.0.0.1:5173',
        cors: true
    },
    resolve: {
        alias: {
            '@': '/resources/js',
        }
    }
});
