/** @type {import('tailwindcss').Config} */
module.exports = {
  // Scan every PHP view/partial and the app JS for class names.
  content: [
    './views/**/*.php',
    './public/**/*.php',
    './public/assets/js/**/*.js',
    './src/**/*.php',
  ],
  theme: {
    // One restrained type scale. Inter for UI, Newsreader for display headings.
    fontFamily: {
      sans: ['Inter', 'ui-sans-serif', 'system-ui', 'sans-serif'],
      display: ['Newsreader', 'ui-serif', 'Georgia', 'serif'],
    },
    extend: {
      colors: {
        // Primary — a desaturated, professional blood-red. Not neon.
        brand: {
          50: '#fdf3f3',
          100: '#fce4e4',
          200: '#f9cdcd',
          300: '#f2a9a9',
          400: '#e77777',
          500: '#d64d4d',
          600: '#b83535', // default brand action color
          700: '#992c2c',
          800: '#7f2828',
          900: '#6b2626',
          950: '#3a1010',
        },
        // Neutral gray scale for surfaces/text (slate-ish, calm).
        gray: {
          50: '#f8fafc',
          100: '#f1f5f9',
          200: '#e2e8f0',
          300: '#cbd5e1',
          400: '#94a3b8',
          500: '#64748b',
          600: '#475569',
          700: '#334155',
          800: '#1e293b',
          900: '#0f172a',
          950: '#020617',
        },
        // Semantic — status badges/alerts only, never decoration.
        success: { 50: '#f0fdf4', 100: '#dcfce7', 500: '#16a34a', 600: '#15803d', 700: '#166534' },
        warning: { 50: '#fffbeb', 100: '#fef3c7', 500: '#d97706', 600: '#b45309', 700: '#92400e' },
        danger: { 50: '#fef2f2', 100: '#fee2e2', 500: '#dc2626', 600: '#b91c1c', 700: '#991b1b' },
        info: { 50: '#eff6ff', 100: '#dbeafe', 500: '#2563eb', 600: '#1d4ed8', 700: '#1e40af' },
      },
      borderRadius: {
        // Small, consistent radii. No pill-shaped cards.
        DEFAULT: '6px',
        md: '6px',
        lg: '8px',
      },
      boxShadow: {
        // Used sparingly — dropdowns and modals only.
        dropdown: '0 4px 16px -2px rgba(15, 23, 42, 0.12)',
        modal: '0 12px 40px -8px rgba(15, 23, 42, 0.25)',
      },
      maxWidth: {
        prose: '68ch',
      },
    },
  },
  plugins: [],
};
