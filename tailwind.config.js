import defaultTheme from 'tailwindcss/defaultTheme'
import forms from '@tailwindcss/forms'

/** @type {import('tailwindcss').Config} */
export default {
  content: [
    './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
    './storage/framework/views/*.php',
    './resources/views/**/*.blade.php',
  ],

  theme: {
    extend: {
      fontFamily: {
        sans: ['Poppins', ...defaultTheme.fontFamily.sans], // ganti ke Poppins
        serif: ['Playfair Display', 'ui-serif', 'Georgia', 'serif'],
      },
      colors: {
        maroon: {
          50:  '#bb9974',
          100: '#bb9974',
          200: '#bb9974',
          300: '#bb9974',
          400: '#bb9974',
          500: '#bb9974',
          600: '#bb9974',
          700: '#bb9974',
          800: '#bb9974',
          900: '#bb9974',
        },
        coal: {
          50:  '#f5f5f6',
          100: '#e7e7e9',
          200: '#cfcfd3',
          300: '#a8a8ad',
          400: '#73737b',
          500: '#3a3a40',
          600: '#2f2f34',
          700: '#252529',
          800: '#1b1b1f',
          900: '#121214',
        },
        ivory: {
          50:  '#ffffff',
          100: '#fefefe',
          200: '#f9f9f7',
          300: '#f2f2ef',
          400: '#e8e8e2',
          500: '#deded3',
        },
      },
      boxShadow: {
        soft: '0 6px 24px rgba(0,0,0,0.06)',
      },
    },
  },

  plugins: [forms],
}
