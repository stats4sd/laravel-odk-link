import {defineConfig} from 'vite'
import laravel, {refreshPaths} from 'laravel-vite-plugin'
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    plugins: [
        laravel({
            // Set this to the name/namespace of your package to ensure
            // it is unique and easy to find.
            buildDirectory: 'vendor/stats4sd/odk-link',

            // add the list of JavaScript / CSS files
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
