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

    safelist: [
        'bg-blue-400', 'bg-blue-500', 'bg-blue-600',
        'bg-green-400', 'bg-green-500', 'bg-green-600',
        'bg-indigo-400', 'bg-indigo-500', 'bg-indigo-600',
        'bg-yellow-400', 'bg-yellow-500', 'bg-yellow-600',
  
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
        },
    },

    plugins: [forms],
};
