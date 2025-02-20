/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    './templates/**/*.twig',
    './src/**/*.js',
    './*.html',
    './node_modules/flowbite/**/*.js'  // Add this line
  ],
  theme: {
    extend: {
      colors: {
        'custom-yellow': '#f7b305',
        'custom-yellow-light': '#fad275',
        'custom-red': '#a4303f',
        'custom-red-dark': '#20090C',
        'custom-black': '#061214',
        'custom-black-hover': '#33292f',
        'custom-gray': '#738290',
        'custom-gray-light': '#dde1e4',
      },
      fontFamily: {
        'work-sans': ['Work Sans', 'Noto Sans KR', 'ui-sans-serif', 'system-ui', '-apple-system', 'BlinkMacSystemFont', 'Segoe UI', 'Roboto', 'Helvetica Neue', 'Arial', 'sans-serif'],

        'quicksand': ['Quicksand', 'Noto Sans KR', 'ui-sans-serif', 'system-ui', '-apple-system', 'BlinkMacSystemFont', 'Segoe UI', 'Roboto', 'Helvetica Neue', 'Arial', 'sans-serif'],
        
        'noto-sans-kr': ['Noto Sans KR', 'ui-sans-serif', 'system-ui', '-apple-system', 'BlinkMacSystemFont', 'Segoe UI', 'Roboto', 'Helvetica Neue', 'Arial', 'sans-serif'],
      }
    },
  },
  plugins: [
    require('flowbite')  // Add this line
  ],
};


