# LifeLink — Blood Donation & Emergency Donor Finder

LifeLink coordinates emergency blood donation: patients post requests, a matching
engine finds compatible + available + verified donors nearby, donors accept or
decline, and blood banks publish inventory and record donations.

Custom PHP 8 (no framework) · PDO + prepared statements · MariaDB/MySQL ·
Tailwind CSS (CLI build) · Tabler Icons · jQuery.

---

## Requirements

* PHP 8.2+ with `pdo_mysql`, `mbstring`, `openssl`, `json`
* MySQL 8 or MariaDB 10.6+ (InnoDB, utf8mb4)
* Composer 2
* Node 18+ / npm (for the Tailwind build only)

The repo ships a [DDEV](https://ddev.com) config (`.ddev/`) that provides all of
the above (PHP 8.4, MariaDB 11.8, Node 24). If you use DDEV, prefix the PHP/DB/
Node commands below with `ddev` (e.g. `ddev composer install`).

---

## Setup

```bash
# 1. Dependencies
composer install
npm install

# 2. Environment
cp .env.example .env          # adjust DB_* if you are not using the bundled DDEV

# 3. Database
mysql < database/migrations.sql
mysql < database/seed.sql     # demo users, requests, inventory

# 4. Front-end assets (compiled to public/assets/css/app.css)
npm run build:css             # one-off build
npm run watch:css             # rebuild on change during development

# 5. Serve (web root is /public)
php -S localhost:8000 -t public
#   …or with DDEV:  ddev start   →   https://blood-bank.ddev.site
```

### With DDEV (recommended)

```bash
ddev start
ddev composer install
ddev npm install
ddev npm run build:css
ddev mysql < database/migrations.sql
ddev mysql < database/seed.sql
ddev launch
```

---

## Demo accounts

See `database/seed.sql`. All demo users share the password **`Password123!`**.

| Role       | Email                     |
|------------|---------------------------|
| Admin      | `admin@lifelink.test`     |
| Donor      | `donor.o.neg@lifelink.test` |
| Patient    | `patient.amina@lifelink.test` |
| Blood bank | `citycentral@lifelink.test` |

---

## Project layout

```
public/           web root — front controller + compiled assets only
src/Core/         Router, Database (PDO), Session, Csrf, Auth, Validator, …
src/Controllers/  HTTP entry points
src/Models/       thin PDO data-access classes (no ORM)
src/Services/     MatchingService, NotificationService, ReportService, EligibilityService
src/Middleware/   Auth / Role / Csrf guards
views/            plain-PHP templates (layouts, partials, role pages)
database/         migrations.sql, seed.sql
resources/        Tailwind source + config
tests/            PHPUnit (MatchingService, Validator, compatibility lookup)
```

---

## Tests

```bash
composer test          # or: ./vendor/bin/phpunit
```
