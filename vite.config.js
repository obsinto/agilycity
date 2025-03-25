import {defineConfig} from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/components/dashboard/index.js'
            ],
            refresh: true,
        }),
    ],
    resolve: {
        alias: {
            '$': 'jquery',
            'jquery': 'jquery'
        }
    },
    optimizeDeps: {
        include: ['jquery', 'moment', 'echarts']
    },
    // Adicione estas configurações para HTTPS
    server: {
        https: true,
    },
    // Garanta que as URLs geradas usem HTTPS
    build: {
        // Se você estiver usando um CDN ou domínio personalizado, configure aqui
        // Por exemplo:
       base: 'https://gestao.itagi.agilytech.com/',
    }
});