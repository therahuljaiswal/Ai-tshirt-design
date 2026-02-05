import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['"Space Grotesk"', '"Inter"', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                'neon-purple': '#b026ff',
                'electric-blue': '#00f3ff',
                'cyber-black': '#0f0f1a',
            },
            backgroundImage: {
                'cyber-gradient': 'linear-gradient(to right, #b026ff, #00f3ff)',
            }
        },
    },

    plugins: [forms],
};
