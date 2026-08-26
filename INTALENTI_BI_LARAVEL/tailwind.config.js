import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Poppins', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                runey: {
                    midnight: '#ffffff', // Cards background (White)
                    gunmetal: '#f1f5f9', // Main background (Light Gray - slate-100)
                    green: '#10b981', // Emerald 500
                    dark: '#ffffff', // Sidebar background (White)
                    border: '#e2e8f0', // slate-200
                    textMain: '#0f172a', // slate-900
                    textMuted: '#64748b', // slate-500
                }
            }
        },
    },

    plugins: [forms],
};
