/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
  ],
  theme: {
    extend: {
      fontFamily: {
      sans: ['Poppins', 'ui-sans-serif', 'system-ui', 'sans-serif'],
      },
      colors: {
        brand: {
          yellow: "#F0C419",    // bar promo
          green: "#177A5B",     // navbar utama
          greenDark: "#116A4E", // hover/gradient
          teal: "#1D9D74",      // aksen
        },
        grayish: "#F5F7FA",
      },
      boxShadow: {
        soft: "0 10px 25px rgba(0,0,0,0.08)",
      },
      borderRadius: {
        '4xl': '2rem',
      },
    },
    container: {
      center: true,
      padding: "1rem",
      screens: {
        sm: "640px",
        md: "768px",
        lg: "1024px",
        xl: "1200px",
      },
    },
  },
  plugins: [
    require('@tailwindcss/line-clamp'),
  ],
};


