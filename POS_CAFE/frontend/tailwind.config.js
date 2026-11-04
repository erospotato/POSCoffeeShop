import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import colors from 'tailwindcss/colors';

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
                sans: ['Plus Jakarta Sans', 'Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                // Map amber directly to a luxurious purple scale
                amber: {
                    50: '#faf5ff',
                    100: '#f3e8ff',
                    200: '#e9d5ff',
                    300: '#d8b4fe',
                    400: '#c084fc',
                    500: '#a855f7',
                    600: '#9333ea',
                    700: '#7e22ce',
                    800: '#6b21a8',
                    900: '#581c87',
                    950: '#3b0764',
                },
                purple: colors.purple,
                violet: colors.violet,
                dark: {
                    base: '#0b0914',
                    surface: '#151026',
                    card: '#16122d',
                    elevated: '#1f183d',
                    header: '#0f0c1d',
                    sidebar: '#090712',
                    border: '#281e42',
                    borderLight: '#392a5d',
                },
            },
            boxShadow: {
                'purple-glow': '0 0 25px -4px rgba(168, 85, 247, 0.45)',
                'purple-sm': '0 4px 14px 0 rgba(147, 51, 234, 0.35)',
            },
        },
    },

    plugins: [forms],
};
