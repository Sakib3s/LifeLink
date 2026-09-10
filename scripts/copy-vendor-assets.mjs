// Post-build step for `npm run build:css`.
//
// 1. Copies the Tabler Icons webfont files into public/assets/fonts/
// 2. Rewrites the (possibly relative) font url()s inside the compiled
//    public/assets/css/app.css to the absolute web path /assets/fonts/...
//
// This keeps everything served from our own origin — no CDN, one compiled
// stylesheet — regardless of how postcss-import rebased the url()s.

import { existsSync, mkdirSync, readdirSync, copyFileSync, readFileSync, writeFileSync } from 'node:fs';
import { dirname, join, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';

const root = resolve(dirname(fileURLToPath(import.meta.url)), '..');
const fontSrcDir = join(root, 'node_modules', '@tabler', 'icons-webfont', 'dist', 'fonts');
const fontDestDir = join(root, 'public', 'assets', 'fonts');
const cssFile = join(root, 'public', 'assets', 'css', 'app.css');

if (!existsSync(fontSrcDir)) {
  console.error('[copy-vendor-assets] Tabler webfont not found. Run `npm install` first.');
  process.exit(1);
}

mkdirSync(fontDestDir, { recursive: true });

let copied = 0;
for (const file of readdirSync(fontSrcDir)) {
  // woff2 covers every modern browser; woff/ttf are the fallbacks.
  // Skip the ~34MB legacy SVG font — nothing references it.
  if (/^tabler-icons\.(woff2?|ttf)$/.test(file)) {
    copyFileSync(join(fontSrcDir, file), join(fontDestDir, file));
    copied++;
  }
}
console.log(`[copy-vendor-assets] copied ${copied} Tabler font file(s) to public/assets/fonts/`);

// jQuery — vendored so no CDN is needed at runtime.
const jquerySrc = join(root, 'node_modules', 'jquery', 'dist', 'jquery.min.js');
const jqueryDestDir = join(root, 'public', 'assets', 'js', 'vendor');
if (existsSync(jquerySrc)) {
  mkdirSync(jqueryDestDir, { recursive: true });
  copyFileSync(jquerySrc, join(jqueryDestDir, 'jquery.min.js'));
  console.log('[copy-vendor-assets] copied jquery.min.js to public/assets/js/vendor/');
} else {
  console.warn('[copy-vendor-assets] jquery not found — run `npm install`.');
}

if (existsSync(cssFile)) {
  let css = readFileSync(cssFile, 'utf8');
  // Normalise any relative reference to the tabler font files.
  css = css.replace(
    /url\((['"]?)(?:\.?\.?\/)*(?:fonts\/)?(tabler-icons\.(?:woff2?|eot|ttf|svg)[^)'"]*)\1\)/g,
    'url($1/assets/fonts/$2$1)',
  );
  writeFileSync(cssFile, css);
  console.log('[copy-vendor-assets] rewrote Tabler font url()s in app.css → /assets/fonts/');
}
