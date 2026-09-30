#!/usr/bin/env bash
# Builds a ready-to-upload production package in ./dist
#
# Run inside the project (with DDEV:  ddev exec bash scripts/build-deploy.sh)
#
# Output layout — mirrors the Hostinger account:
#   dist/lifelink-app/            → upload to  ~/lifelink-app/          (NOT web-accessible)
#   dist/public_html/rtm/lifelink → upload to  ~/public_html/rtm/lifelink/ (web root of the app)
#   dist/lifelink-deploy.zip      → both of the above, zipped
set -euo pipefail
cd "$(dirname "$0")/.."

ROOT="$(pwd)"
DIST="$ROOT/dist"
APP="$DIST/lifelink-app"
WEB="$DIST/public_html/rtm/lifelink"

[ -f .env.production ] || { echo "Missing .env.production"; exit 1; }

echo "▸ Cleaning dist/"
rm -rf "$DIST"
mkdir -p "$APP" "$WEB"

echo "▸ Building front-end assets"
npm run build:css --silent

echo "▸ Installing production-only PHP dependencies"
TMP="$(mktemp -d)"
cp composer.json composer.lock "$TMP/"
mkdir -p "$TMP/src" && cp src/helpers.php "$TMP/src/"   # 'files' autoload target
( cd "$TMP" && composer install --no-dev --optimize-autoloader --no-interaction --quiet )

echo "▸ Assembling application folder"
cp -r src views routes database "$APP/"
cp -r "$TMP/vendor" "$APP/vendor"
cp composer.json "$APP/"
cp .env.production "$APP/.env"
rm -rf "$TMP"
# Demo seed data must never reach production.
rm -f "$APP/database/seed.sql"

echo "▸ Assembling public web folder"
cp -r public/. "$WEB/"

echo "▸ Generating admin account SQL"
ADMIN_PASS="$(php -r 'echo substr(str_replace(["+","/","="], "", base64_encode(random_bytes(12))), 0, 14) . "!9";')"
ADMIN_HASH="$(php -r 'echo password_hash($argv[1], PASSWORD_DEFAULT);' "$ADMIN_PASS")"
cat > "$APP/database/create-admin.sql" <<SQL
-- Creates the first administrator. Run once, AFTER migrations.sql.
INSERT INTO users (name, email, password_hash, role, status)
VALUES ('LifeLink Admin', 'admin@3digit.agency', '$ADMIN_HASH', 'admin', 'active')
ON DUPLICATE KEY UPDATE password_hash = VALUES(password_hash), role = 'admin', status = 'active';
SQL
echo "$ADMIN_PASS" > "$DIST/ADMIN-PASSWORD.txt"

echo "▸ Sanity checks"
for f in "$APP/src/bootstrap.php" "$APP/vendor/autoload.php" "$APP/.env" "$WEB/index.php" "$WEB/.htaccess" "$WEB/assets/css/app.css" "$WEB/assets/js/vendor/jquery.min.js" "$WEB/assets/fonts/tabler-icons.woff2"; do
  [ -e "$f" ] || { echo "MISSING: $f"; exit 1; }
done
[ ! -d "$APP/vendor/phpunit" ] || { echo "dev deps leaked into vendor"; exit 1; }
find "$APP" "$WEB" -name '*.php' -not -path '*/vendor/*' -print0 | xargs -0 -n1 php -l >/dev/null

echo "▸ Zipping"
( cd "$DIST" && (zip -qr lifelink-deploy.zip lifelink-app public_html || tar -czf lifelink-deploy.tar.gz lifelink-app public_html) )

echo
echo "✔ Package ready in dist/"
du -sh "$DIST" | sed 's/^/  total size: /'
