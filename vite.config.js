import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
// import { createHtmlPlugin } from 'vite-plugin-html'
//import vueDevTools from 'vite-plugin-vue-devtools';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    plugins: [
 //       vueDevTools(),
 //       createHtmlPlugin({}),
        laravel({
            input: 'resources/js/app.js',
            refresh: true,
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
    server: {
        https: true,
        host: 'inertia.prototypecodetest.site',
    },
    // build: {
    //     outDir: 'public/build',
    //     emptyOutDir: false,
    // }
});
