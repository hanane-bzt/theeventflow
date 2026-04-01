/** @type {import('tailwindcss').Config} */
export default {
  content: [
    './index.html',
    './src/**/*.{vue,js,ts,jsx,tsx}',
  ],
  theme: {
    extend: {
      colors: {
        primary: {
          50:  '#fff8f0',
          100: '#ffecd6',
          200: '#fdd0a8',
          300: '#faad70',
          400: '#f68038',
          500: '#f06318',
          600: '#dc4d0a',
          700: '#b63c0a',
          800: '#923110',
          900: '#782b10',
        }
      },
      fontFamily: {
        sans: ['Inter', 'system-ui', 'sans-serif'],
      }
    },
  },
  plugins: [],
}
