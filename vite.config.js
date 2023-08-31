import {defineConfig} from 'vite'
import laravel, {refreshPaths} from 'laravel-vite-plugin'
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    plugins: [
        laravel({
            buildDirectory: 'vendor/stats4sd/laravel-odk-link',
            input: ['resources/assets/js/odk-link.js'],
            refresh: [
                ...refreshPaths,
            ],
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],
})
