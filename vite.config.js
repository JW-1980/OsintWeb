import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import { fileURLToPath, URL } from 'node:url';
import { resolve } from 'node:path';
import fs from 'node:fs';

// Plugin to copy service worker to public directory
function serviceWorkerPlugin() {
    return {
        name: 'service-worker-plugin',
        writeBundle() {
            // Copy service worker to public directory after build
            const swSource = resolve(__dirname, 'resources/js/service-worker.js');
            const swDest = resolve(__dirname, 'public/service-worker.js');

            if (fs.existsSync(swSource)) {
                fs.copyFileSync(swSource, swDest);
                console.log('Service worker copied to public directory');
            }
        },
        configureServer(server) {
            // Serve service worker during development
            server.middlewares.use((req, res, next) => {
                if (req.url === '/service-worker.js') {
                    const swPath = resolve(__dirname, 'resources/js/service-worker.js');
                    if (fs.existsSync(swPath)) {
                        res.setHeader('Content-Type', 'application/javascript');
                        res.setHeader('Service-Worker-Allowed', '/');
                        fs.createReadStream(swPath).pipe(res);
                        return;
                    }
                }
                next();
            });
        },
    };
}

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.ts'],
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
        serviceWorkerPlugin(),
    ],
    resolve: {
        alias: {
            '@': fileURLToPath(new URL('./resources/js', import.meta.url)),
            'vue': 'vue/dist/vue.esm-bundler.js',
        },
    },
    server: {
        host: '0.0.0.0',
        port: 5173,
        hmr: {
            host: 'localhost',
        },
    },
    build: {
        manifest: true,
        outDir: 'public/build',
        rollupOptions: {
            output: {
                manualChunks: {
                    'vue-vendor': ['vue', 'pinia', 'vue-router'],
                    'map-vendor': ['leaflet', '@vue-leaflet/vue-leaflet', 'leaflet-draw'],
                    'chart-vendor': ['chart.js'],
                },
            },
        },
        chunkSizeWarningLimit: 1000,
    },
    optimizeDeps: {
        include: [
            'vue',
            'pinia',
            'vue-router',
            'leaflet',
            '@vue-leaflet/vue-leaflet',
            'axios',
            'date-fns',
        ],
    },
});
