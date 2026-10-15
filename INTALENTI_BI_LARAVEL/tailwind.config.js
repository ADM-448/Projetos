import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
                display: ['"Space Grotesk"', 'ui-sans-serif', 'system-ui', 'sans-serif'],
            },
            colors: {
                // Dynamic Theme Tokens
                canvas: 'var(--color-canvas)',
                surface: 'var(--color-surface)',
                panel: 'var(--color-panel)',
                'border-tron': 'var(--color-border)',
                divider: 'var(--color-divider)',
                main: 'var(--color-main)',
                muted: 'var(--color-muted)',
                neon: 'var(--color-neon)',
                black: '#000000', // Keep pure black for specific uses
            },
            borderRadius: {
                'tags': '20px',
                'cards': '8px',
                'nav-pills': '200px',
            },
            boxShadow: {
                'none': '0 0 #0000',
                'neon': '0 0 15px 1px rgba(var(--color-neon-rgb), 0.4)',
            }
        },
    },

    plugins: [forms],
};
