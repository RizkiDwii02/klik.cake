import defaultTheme from 'tailwindcss/defaultTheme';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/**/*.blade.php',
        './resources/**/*.js',
    ],
    theme: {
        extend: {
            colors: {
                cream: '#FFF8F0',
                strawberry: {
                    DEFAULT: '#E5527A',
                    deep: '#B82850',  // High-contrast CTA matching PDF screen buttons & WCAG AA
                    hover: '#9E2043',
                    light: '#FCE8EF',
                },
                blush: {
                    DEFAULT: '#FDE3EA',
                    dark: '#F8C8D5',
                    light: '#FFF5F7',
                },
                butter: {
                    DEFAULT: '#F4B942',
                    light: '#FEF5DF',
                    dark: '#D89E28',
                },
                cocoa: {
                    DEFAULT: '#3B2320',
                    muted: '#7A5C58',
                    light: '#A89290',
                },
            },
            fontFamily: {
                serif: ['Fraunces', ...defaultTheme.fontFamily.serif],
                sans: ['Nunito', ...defaultTheme.fontFamily.sans],
            },
            borderRadius: {
                '2xl': '16px',
            },
            boxShadow: {
                soft: '0 4px 20px -2px rgba(59, 35, 32, 0.06)',
                pill: '0 4px 14px 0 rgba(184, 40, 80, 0.25)',
            },
        },
    },
    plugins: [],
};
