/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './resources/**/*.vue',
        './src/**/*.php',
    ],
    safelist: [
        // Status colors for fix-all-progress
        'bg-green-100', 'bg-green-500', 'bg-green-600', 'dark:bg-green-600/30',
        'bg-red-100', 'bg-red-500', 'bg-red-600', 'dark:bg-red-600/30',
        'bg-blue-100', 'bg-blue-500', 'bg-blue-600', 'dark:bg-blue-600/30',
        'bg-yellow-100', 'bg-yellow-500', 'bg-yellow-600', 'dark:bg-yellow-600/30',
        'bg-gray-100', 'bg-gray-500', 'bg-gray-600', 'dark:bg-gray-600/30',
        'text-green-600', 'dark:text-green-400',
        'text-red-600', 'dark:text-red-400',
        'text-blue-600', 'dark:text-blue-400',
        'text-yellow-600', 'dark:text-yellow-400',
        'text-gray-600', 'dark:text-gray-400',
        'from-green-50', 'to-green-100', 'from-green-500', 'to-green-600',
        'from-red-50', 'to-red-100', 'from-red-500', 'to-red-600',
        'from-blue-50', 'to-blue-100', 'from-blue-500', 'to-blue-600',
        'from-yellow-50', 'to-yellow-100', 'from-yellow-500', 'to-yellow-600',
        'from-gray-50', 'to-gray-100', 'from-gray-500', 'to-gray-600',
        'dark:from-green-900/20', 'dark:to-green-800/20',
        'dark:from-red-900/20', 'dark:to-red-800/20',
        'dark:from-blue-900/20', 'dark:to-blue-800/20',
        'dark:from-yellow-900/20', 'dark:to-yellow-800/20',
        'dark:from-gray-900/20', 'dark:to-gray-800/20',
    ],
    darkMode: 'class',
    theme: {
        extend: {
            colors: {
                primary: {
                    50: '#eff6ff',
                    100: '#dbeafe',
                    200: '#bfdbfe',
                    300: '#93c5fd',
                    400: '#60a5fa',
                    500: '#3b82f6',
                    600: '#2563eb',
                    700: '#1d4ed8',
                    800: '#1e40af',
                    900: '#1e3a8a',
                    950: '#172554',
                },
                indigo: {
                    50: '#eef2ff',
                    100: '#e0e7ff',
                    200: '#c7d2fe',
                    300: '#a5b4fc',
                    400: '#818cf8',
                    500: '#6366f1',
                    600: '#4f46e5',
                    700: '#4338ca',
                    800: '#3730a3',
                    900: '#312e81',
                    950: '#1e1b4b',
                },
            },
            fontFamily: {
                sans: ['Figtree', 'ui-sans-serif', 'system-ui', 'sans-serif'],
            },
            animation: {
                'fade-in': 'fadeIn 0.5s ease-in-out',
                'slide-in': 'slideIn 0.3s ease-out',
                'bounce-in': 'bounceIn 0.6s ease-out',
            },
            keyframes: {
                fadeIn: {
                    '0%': { opacity: '0' },
                    '100%': { opacity: '1' },
                },
                slideIn: {
                    '0%': { transform: 'translateY(-10px)', opacity: '0' },
                    '100%': { transform: 'translateY(0)', opacity: '1' },
                },
                bounceIn: {
                    '0%': { transform: 'scale(0.3)', opacity: '0' },
                    '50%': { transform: 'scale(1.05)' },
                    '70%': { transform: 'scale(0.9)' },
                    '100%': { transform: 'scale(1)', opacity: '1' },
                },
            },
        },
    },
    plugins: [
        require('@tailwindcss/forms')({
            strategy: 'class',
        }),
        require('@tailwindcss/typography'),
    ],
};