// PostCSS pipeline used by the Tailwind CLI.
// postcss-import lets input.css pull in the Tabler Icons webfont stylesheet
// from node_modules so it ends up in the single compiled app.css.
module.exports = {
  plugins: {
    'postcss-import': {},
    tailwindcss: {},
    autoprefixer: {},
  },
};
