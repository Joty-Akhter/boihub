# Quire — online bookshop

A book-selling site with a storefront and a staff admin panel, built with Laravel 10, Blade, Alpine.js and MySQL.

**Storefront:** home page (featured books, categories, new arrivals), a catalogue with search, category filter and sort, book detail pages, a session basket, guest checkout with cash on delivery, and an order confirmation page.

**Admin (`/admin`):**

- **Books:** add, edit, delete, activate or deactivate, upload or replace covers, set price and stock, assign a category, mark as featured.
- **Categories:** add, edit, delete, show or hide.
- **Orders:** list with status tabs and search, customer details, books ordered, totals, status changes.

## Requirements

- PHP 8.1+ with `pdo_mysql`, `mbstring`, `fileinfo`
- Composer
- MySQL 8

No Node build step is needed. The CSS is plain (`public/css/app.css`) and Alpine.js loads from a CDN.

## Setup

```bash
composer install
cp .env.example .env          # skip if .env already exists
php artisan key:generate      # skip if APP_KEY is already set
```

Create the database and set the credentials in `.env`:

```sql
CREATE DATABASE boihub CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

```dotenv
DB_DATABASE=boihub
DB_USERNAME=root
DB_PASSWORD=your-password
```

Then:

```bash
php artisan migrate --seed    # tables, admin user, 6 categories, 18 sample books
php artisan storage:link      # makes uploaded covers public
php artisan serve             # http://localhost:8000
```

Admin login: **http://localhost:8000/admin**, `admin@quire.test` / `password`.
Change these with `ADMIN_EMAIL` / `ADMIN_PASSWORD` in `.env` before seeding, and always before going live.

## Shop settings (`.env`)

| Key | Default | Meaning |
| --- | --- | --- |
| `SHOP_NAME` | Quire | Name shown in the header, footer and page titles |
| `SHOP_CURRENCY` | ৳ | Currency symbol for display |
| `SHOP_SHIPPING_FLAT` | 60 | Flat delivery fee |
| `SHOP_SHIPPING_FREE_OVER` | 1500 | Orders at or above this subtotal ship free (0 turns this off) |
| `SHOP_LOW_STOCK` | 5 | Threshold for "low stock" warnings in the admin |

## How it behaves

- **Stock:** placing an order takes stock inside a locked transaction, so two shoppers can't buy the last copy. Cancelling an order puts the stock back. Reopening a cancelled order takes it again, or refuses if there isn't enough.
- **Visibility:** inactive books, and every book in a hidden category, disappear from the shop. They stay editable in the admin.
- **Deleting:** you can delete a book at any time, because each order keeps its own copy of the title, author and price. You can't delete a category while it still has books; move them or hide the category instead.
- **Covers:** books without an uploaded cover get a generated typographic one.

## Tests

The tests run against a separate `boihub_test` database (see `phpunit.xml`), so they never touch your data:

```sql
CREATE DATABASE boihub_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

```bash
php artisan test
```

## Code map

| Area | Files |
| --- | --- |
| Storefront | `app/Http/Controllers/{Home,Book,Cart,Checkout}Controller.php`, `resources/views/shop/` |
| Admin | `app/Http/Controllers/Admin/`, `app/Http/Requests/Admin/`, `resources/views/admin/` |
| Basket | `app/Support/Cart.php` (session storage; prices re-read from the database) |
| Models | `app/Models/{Book,Category,Order,OrderItem}.php` |
| Styles | `public/css/app.css` (design tokens at the top) |
| Settings | `config/shop.php` |
