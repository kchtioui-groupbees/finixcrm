import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    darkMode: 'class',
    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                finix: {
                    orange: '#f97316',
                    pink: '#db2777',
                    purple: '#7c3aed',
                    blue: '#2563eb',
                    dark: '#0a0a0a',
                    card: '#121212',
                }
            },
            backgroundImage: {
                'phoenix-gradient': 'linear-gradient(to right, #f97316, #db2777, #7c3aed, #2563eb)',
                'phoenix-glow': 'radial-gradient(circle at center, rgba(124, 58, 237, 0.15) 0%, transparent 70%)',
            },
            boxShadow: {
                'glow': '0 0 20px -5px rgba(124, 58, 237, 0.3)',
            }
        },
    },

    plugins: [forms],
};
