import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        tailwindcss(),
    ],
    // Add environment variable support for production builds
    define: {
        // Make Laravel environment variables available to frontend
        'import.meta.env.VITE_APP_NAME': JSON.stringify(process.env.VITE_APP_NAME || 'Silsila Keluarga'),
        'import.meta.env.VITE_APP_ENV': JSON.stringify(process.env.APP_ENV || 'local'),
        'import.meta.env.VITE_APP_URL': JSON.stringify(process.env.APP_URL || 'http://localhost'),
    },
    // Configure build for production deployment
    build: {
        // Ensure assets are built correctly for production
        rollupOptions: {
            output: {
                // Consistent chunk naming for better caching
                chunkFileNames: 'assets/[name]-[hash].js',
                entryFileNames: 'assets/[name]-[hash].js',
                assetFileNames: 'assets/[name]-[hash].[ext]'
            }
        },
        // Optimize for production
        minify: 'terser',
        terserOptions: {
            compress: {
                drop_console: process.env.APP_ENV === 'production', // Remove console.log in production
                drop_debugger: process.env.APP_ENV === 'production'
            }
        }
    },
    // Server configuration for development
    server: {
        host: '0.0.0.0',
        port: 5173,
        // Configure for Laravel backend proxy if needed
        proxy: process.env.APP_ENV === 'local' ? {
            '/api': {
                target: 'http://localhost:8000',
                changeOrigin: true,
                secure: false,
            }
        } : undefined
    }
});
