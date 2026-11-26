import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

const vitePort = Number(process.env.VITE_PORT || 5173);
const appUrl = process.env.APP_URL || `http://localhost:${process.env.APP_PORT || 8000}`;
const appOrigin = new URL(appUrl).origin;

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        host: '0.0.0.0',
        port: 5173,
        strictPort: true,
        origin: `http://localhost:${vitePort}`,
        cors: {
            origin: appOrigin,
        },
        hmr: {
            host: 'localhost',
            clientPort: vitePort,
        },
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
