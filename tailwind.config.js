import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import typography from '@tailwindcss/typography';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            colors: {
                primary: {
                    DEFAULT: '#1A6FAA',
                    dark: '#124E7A',
                    light: '#E8F4FB',
                },
                secondary: {
                    DEFAULT: '#2E7D52',
                    light: '#E8F5EE',
                },
                accent: '#F5A623',
                dark: '#1C2B39',
            },
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
                display: ['"Playfair Display"', 'serif'],
                body: ['Nunito', 'sans-serif'],
            },
        },
    },

    plugins: [forms, typography],
};
