import forms from '@tailwindcss/forms'
import colors from 'tailwindcss/colors'

const semanticScale = (solid, hover) => ({
  50: '#F4F4F5', 100: '#F4F4F5', 200: '#D4D4D8', 300: '#D4D4D8',
  400: solid, 500: solid, 600: solid, 700: solid, 800: hover, 900: hover, 950: hover,
})

/** @type {import('tailwindcss').Config} */
export default {
  content: [
    './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
    './storage/framework/views/*.php',
    './resources/views/**/*.blade.php',
  ],

  theme: {
    extend: {
      borderRadius: {
        DEFAULT: '8px', sm: '8px', md: '8px', lg: '8px',
        xl: '8px', '2xl': '8px', '3xl': '8px',
      },
      fontSize: {
        base: ['14px', { lineHeight: '1.5' }],
      },
      fontFamily: {
        sans: ['Inter', 'sans-serif'],
        serif: ['Inter', 'sans-serif'], // Compatibility alias; UI never switches to a serif font.
      },
      colors: {
        'primary-dark': '#18181B',
        background: '#FFFFFF',
        surface: '#FFFFFF',
        'neutral-surface': '#F4F4F5',
        border: '#D4D4D8',
        brown: '#713F12',
        action: '#1D4ED8',
        danger: '#DC2626',
        success: '#15803D',
        warning: '#C2410C',
        secondary: '#52525B',
        blue: semanticScale('#1D4ED8', '#1E40AF'),
        red: semanticScale('#DC2626', '#B91C1C'),
        rose: semanticScale('#DC2626', '#B91C1C'),
        green: semanticScale('#15803D', '#166534'),
        emerald: semanticScale('#15803D', '#166534'),
        orange: semanticScale('#C2410C', '#9A3412'),
        amber: semanticScale('#C2410C', '#9A3412'),
        yellow: semanticScale('#C2410C', '#9A3412'),
        gray: {...colors.zinc, 400: '#52525B', 500: '#52525B'},
        slate: {...colors.zinc, 400: '#52525B', 500: '#52525B'},
        maroon: {
          ...semanticScale('#713F12', '#542F0E'),
        },
        coal: {
          ...colors.zinc,
          50: '#F4F4F5', 100: '#F4F4F5', 200: '#D4D4D8', 300: '#D4D4D8',
          400: '#52525B', 500: '#52525B', 600: '#52525B',
          700: '#18181B', 800: '#18181B', 900: '#18181B',
        },
        ivory: {
          50:  '#ffffff',
          100: '#ffffff',
          200: '#F4F4F5',
          300: '#eeeeee',
          400: '#e4e4e4',
          500: '#d4d4d4',
        },
        gold: '#D4AF37',
      },
      boxShadow: {
        soft: 'none',
      },
      dropShadow: {
        gold: '0 0 12px rgba(212,175,55,.35)',
      },
    },
  },

  plugins: [forms],
}
