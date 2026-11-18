import { defineConfig } from 'vite';
import tailwindcss from '@tailwindcss/vite';
import { readdirSync } from 'node:fs';
import { resolve } from 'node:path';
import htmlKit from './build/html-kit.js';
import classicScripts from './build/classic-scripts.js';

const root = import.meta.dirname;

// every .html file in the project root is a page
const input = Object.fromEntries(
  readdirSync(root)
    .filter((f) => f.endsWith('.html'))
    .map((f) => [f.replace(/\.html$/, ''), resolve(root, f)]),
);

export default defineConfig({
  base: './',
  plugins: [tailwindcss(), htmlKit(), classicScripts()],
  build: {
    rollupOptions: { input },
    chunkSizeWarningLimit: 700, // ECharts is most of the bundle
  },
  server: {
    host: '0.0.0.0',
    port: 3000,
  },
});
