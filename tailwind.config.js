/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
    "./app/Livewire/**/*.php",
  ],
  darkMode: 'class', // Habilitar dark mode con clase
  theme: {
    extend: {
      colors: {
        // Colores corporativos de Gaudium
        primary: {
          DEFAULT: '#E8491B', // Naranja corporativo
          50: '#FEF2EE',
          100: '#FDE5DD',
          200: '#FBCBBB',
          300: '#F9B199',
          400: '#F79777',
          500: '#E8491B', // Color principal
          600: '#D03A0F',
          700: '#9E2C0B',
          800: '#6C1E08',
          900: '#3A1004',
        },
        // Colores base
        background: {
          light: '#FFFFFF',
          dark: '#1A1A1A',
        },
        text: {
          light: '#000000',
          dark: '#FFFFFF',
        },
      },
    },
  },
  plugins: [],
}

