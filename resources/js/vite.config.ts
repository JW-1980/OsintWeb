import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';
import { resolve } from 'path';

export default defineConfig({
  plugins: [vue()],
  resolve: {
    alias: {
      '@': resolve(__dirname, './')
    }
  },
  build: {
    outDir: '../../public/build',
    emptyOutDir: true,
    manifest: true,
    rollupOptions: {
      input: resolve(__dirname, 'app.ts')
    }
  },
  server: {
    hmr: {
      host: 'localhost'
    }
  }
});
