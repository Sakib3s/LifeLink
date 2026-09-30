# Deploying LifeLink to Hostinger (subpath: `https://3digit.agency/rtm/lifelink`)

## 1. Build the package (on your machine)

```bash
ddev exec bash scripts/build-deploy.sh
```

Produces `dist/` (git-ignored):

| Path | Upload to |
|------|-----------|
| `dist/lifelink-app/` | `~/lifelink-app/` — **next to** `public_html`, not inside it |
| `dist/public_html/rtm/lifelink/*` | `~/public_html/rtm/lifelink/` |
| `dist/lifelink-deploy.zip` | both of the above in one archive |
| `dist/ADMIN-PASSWORD.txt` | your first admin password — read it, then delete it |

`lifelink-app/.env` is generated from `.env.production` (git-ignored; holds the live DB credentials).

## 2. Upload (hPanel → File Manager)

1. Upload `lifelink-deploy.zip` to the account home (the folder that contains `public_html`).
2. Extract it there. You get `lifelink-app/` and `public_html/rtm/lifelink/`
   (merge into the existing `public_html`).
3. Confirm `~/public_html/rtm/lifelink/.htaccess` exists (enable "show hidden files").
4. hPanel → PHP Configuration: choose **PHP 8.2 or newer**; ensure `pdo_mysql`, `mbstring`, `openssl` are on.

## 3. Database (hPanel → phpMyAdmin → select `u294243475_rtm_lifelink`)

Import, in order:
1. `lifelink-app/database/migrations.sql`
2. `lifelink-app/database/create-admin.sql` — creates `admin@3digit.agency` with the password in `ADMIN-PASSWORD.txt`

Do **not** import `seed.sql` (demo accounts with a public password); it is excluded from the package.

## 4. Verify

- https://3digit.agency/rtm/lifelink/ — landing page with styles and icons
- `/login` as `admin@3digit.agency`, then change nothing until you have stored the password safely
- Register a test donor → appears in **Admin → Verifications**

## Notes

- Everything (links, redirects, assets, session cookie path) derives from `APP_URL`; to move the app, change only that value.
- Only `public_html/rtm/lifelink/` is web-reachable; `src/`, `vendor/`, `.env` and `database/` live outside it.
- Errors are logged, not displayed (`APP_DEBUG=false`). Check hPanel → Error logs if a page 500s.
- Re-deploy: rebuild, then re-upload `lifelink-app/` (keep the server's `.env` if you edited it) and `public_html/rtm/lifelink/`.
