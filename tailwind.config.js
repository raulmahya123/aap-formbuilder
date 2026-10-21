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
          50:  '#f5f2ef',
          100: '#ebe4de',
          200: '#d8c9bc',
          300: '#bda895',
          400: '#96735a',
          500: '#603813',
          600: '#553110',
          700: '#603813',
          800: '#4a2c10',
          900: '#38200c',
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
          100: '#ffffff',
          200: '#f5f5f5',
          300: '#eeeeee',
          400: '#e4e4e4',
          500: '#d4d4d4',
        },
        gold: '#D4AF37',
      },
      boxShadow: {
        soft: '0 2px 10px rgba(17,17,17,0.06)',
      },
      dropShadow: {
        gold: '0 0 12px rgba(212,175,55,.35)',
      },
    },
  },

  plugins: [forms],
}
