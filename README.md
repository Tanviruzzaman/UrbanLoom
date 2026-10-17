# Nilambar

A cash-on-delivery e-commerce store for a Bangladeshi clothing and jewellery
brand. Laravel 13, Blade + Tailwind CSS, Filament v5 admin panel, MySQL.

Prices are in Bangladeshi Taka (৳). Delivery is ৳60 inside Dhaka and ৳120
outside, set in `config/store.php`.

---

## What it does

**Storefront**

- Home page with featured categories and products
- Shop page with category filter, price sorting, search and pagination
- Product pages with an image gallery and size selector
  (unsized products such as jewellery skip the size picker entirely)
- Session cart, keyed by product variant
- Cash-on-delivery checkout — stock is locked and decremented inside a
  transaction, and item names and prices are snapshotted onto the order
- Customer order tracking at `/track`, using order number **and** phone number

**Admin** (`/admin`)

- Dashboard: today's orders and revenue, pending orders, monthly revenue,
  packing list, cash still held by the courier, 30-day return rate,
  a 14-day orders/revenue chart, recent orders, repeat customers, low stock
- Orders: status tabs, quick status actions, "needs action" filter, printable
  invoices (single or batched for a courier run), Steadfast courier booking
- Products: variants and image uploads, bulk activate/feature, stock totals
- Categories

**Notifications**

- Order confirmation email (skipped silently when no email was given)
- Order received / order shipped SMS through a configurable gateway
- Both are deferred until after the HTTP response, so checkout never waits on
  a slow mail or SMS server

---

## Requirements

- PHP 8.3+
- MySQL 8
- Node 20+ (build only — the server does not need it)
- `ext-zip`, `ext-gd`, `ext-intl`

---

## Local setup

```sh
composer install
npm install

cp .env.example .env
php artisan key:generate

# set DB_DATABASE / DB_USERNAME / DB_PASSWORD in .env first
php artisan migrate --seed

php artisan storage:link
npm run build
```

Create an admin login:

```sh
php artisan make:filament-user
```

Then visit the site, and `/admin` for the panel.

---

## Tests

```sh
php artisan test
```

Tests run against **MySQL**, not SQLite, because the app relies on behaviour
SQLite does not enforce — notably `ONLY_FULL_GROUP_BY`, which once let a broken
dashboard query pass tests and crash in the browser. Create the database once:

```sql
CREATE DATABASE mystore_testing;
```

---

## Configuration

Most shop settings live in `config/store.php` — brand name, logo, contact
details, delivery charges and the cart item limit. Changing the name there
updates the storefront, the admin panel, page titles and emails together.

The brand colour is defined once as `--color-brand-*` in
`resources/css/app.css`.

Optional integrations stay dormant until their keys are set in `.env`:

| Keys | Without them |
|---|---|
| `SMS_API_URL`, `SMS_API_KEY`, `SMS_SENDER_ID` | Messages are written to the log instead of sent |
| `STEADFAST_API_KEY`, `STEADFAST_SECRET` | "Book Courier" reports that it is not configured |

---

## Backups

```sh
php artisan backup:database
```

Dumps to `storage/app/backups` and keeps the newest 14. Scheduled nightly at
02:30 — which only runs if the server has Laravel's scheduler in cron:

```
* * * * * cd /path/to/site && php artisan schedule:run >> /dev/null 2>&1
```

---

## Deploying to shared hosting

The application must sit **outside** the web root so `.env` can never be
downloaded:

```
/home/user/
├── nilambar/      <- everything except public/
└── public_html/   <- contents of public/, with index.php pointing at ../nilambar
```

---

## Known limitations

- Variants have no individual price, so every size of a product costs the same
- Uploaded images are not resized, so large phone photos are served full size
- No discount or coupon codes
- Every admin user has full access; there are no roles
- The Steadfast and SMS integrations are built to their documented shapes but
  have not been exercised against the live APIs
# UrbanLoom
