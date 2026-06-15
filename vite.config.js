import {defineConfig} from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue'

export default defineConfig({
    base: '/',
    plugins: [
        vue(),
        laravel({
            input: ['resources/sass/app.scss', 'resources/sass/teletext-inline.scss',  'resources/js/app.js', 'resources/sass/promo/index.scss', 'resources/js/promo/index.ts'],
            refresh: true,
        }),
    ],
    resolve: {
        alias: {
            vue: 'vue/dist/vue.esm-bundler.js',
        },
    },
});
