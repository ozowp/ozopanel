import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react-swc'
import path from 'path';
import dotenv from 'dotenv';

dotenv.config(); // Load environment variables from .env file

// https://vitejs.dev/config/
const config = defineConfig({
  plugins: [react()],
  // Add Prettier to the Vite config
  optimizeDeps: {
    include: ['prettier'],
  },
  base: '',
  build: {
    minify: true,
    outDir: 'dist',
    assetsDir: 'assets',
    assetsInlineLimit: 0, // Disable inlining assets
    chunkSizeWarningLimit: 1500, // Adjust chunk size warning limit if needed
    rollupOptions: {
      output: {
        dir: "dist",
        entryFileNames: "main.min.js",
        assetFileNames: "main.min.[ext]",
        chunkFileNames: "chunk/[name]-[hash].min.js",
      },
    },
  },
  resolve: {
    alias: {
      '@': path.resolve(__dirname, './src'),
      '@assets': path.resolve(__dirname, './src/assets'),
      '@scss': path.resolve(__dirname, './src/scss'),
      '@utils': path.resolve(__dirname, './src/utils'),
      '@contexts': path.resolve(__dirname, './src/contexts'),
      '@interfaces': path.resolve(__dirname, './src/interfaces'),
      '@reducers': path.resolve(__dirname, './src/reducers'),
      '@components': path.resolve(__dirname, './src/components'),
      '@blocks': path.resolve(__dirname, './src/components/blocks'),
      '@pages': path.resolve(__dirname, './src/pages')
    },
  },
  css: {
    preprocessorOptions: {
      sass: {
        additionalData: `@import "./src/scss/style.scss";`, // Import your main styles file
      },
    },
  },
});

// Check if OZOPANEL_DEBUG environment variable is not set or set to a truthy value
if (!process.env.OZOPANEL_DEBUG) {
  config.base = '/wp-content/plugins/ozopanel/dist/';
  config.build.rollupOptions.input = {
    main: 'src/main.tsx',
  };
}

export default config;