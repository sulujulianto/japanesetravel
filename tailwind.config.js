import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.{js,ts,vue}',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['system-ui', ...defaultTheme.fontFamily.sans],
                display: ['system-ui', ...defaultTheme.fontFamily.sans],
            },
        },
    },

    plugins: [forms],
};
