# dooo store: Laravel storefront and admin

The dooo website (shop, product pages, bag, checkout, about, shipping and contact) runs on a Laravel 12 backend with an admin dashboard. It needs no Node or Vite build, so it runs on any cPanel host with PHP 8.2+ and MySQL.

**Putting it online:** see [DEPLOY-CPANEL.md](DEPLOY-CPANEL.md).

## Run it locally

```bash
composer install
cp .env.example .env && php artisan key:generate   # first time only
php artisan migrate:fresh --seed                    # catalogue + sample orders + admin login
php artisan serve
```

- Shop: http://127.0.0.1:8000
- Admin: http://127.0.0.1:8000/admin, signed in as `admin@dooo.lk` / `password` (local seed only; change it under **My profile**)

Run the tests with `php artisan test`.

## Where things live

| Area | Files |
|---|---|
| Storefront pages | `resources/views/store/*.blade.php`, layout `resources/views/layouts/store.blade.php` |
| Storefront CSS/JS | `public/assets/css`, `public/assets/js/app.js` (bag, wishlist, quick-add) |
| Product data sent to the shop | `app/Support/StoreCatalog.php` → `window.DOOO` |
| Orders and contact API | `app/Http/Controllers/OrderController.php`, `ContactController.php` |
| Admin | `app/Http/Controllers/Admin/*`, `resources/views/admin/*`, `public/admin-assets/*` |
| Fixed lists (colours, sizes, statuses, districts) | `config/store.php` |
| Editable settings (phone, WhatsApp, social, announcement) | Admin › Settings (`settings` table) |
| Catalogue seed (26 products, 5 categories) | `database/seeders/CatalogSeeder.php` |
| Web installer | `/install`, `app/Http/Controllers/InstallController.php` |

## Security

- `app/Http/Middleware/SecurityHeaders.php` sets CSP (nonce-based scripts), X-Frame-Options, nosniff, Referrer-Policy, Permissions-Policy and HSTS. Any new inline script must use `<script nonce="{{ csp_nonce() }}">`, and inline `onclick=` handlers are blocked.
- `ForceHttps` middleware is controlled by `FORCE_HTTPS` in `.env`, which also makes session cookies HTTPS-only.
- Admin login: per-IP throttle plus a per-account lockout (`config/security.php`). The password rule is `Password::defaults()`: 10+ characters with letters and numbers.
- Honeypot field `website` on checkout and contact. Uploads are validated by content and served from `public/uploads`, where `.htaccess` blocks script execution.
- The audit trail is written by `App\Support\Audit` to `storage/logs/security-*.log`.
- Run `composer audit` before every deploy. Tests: `tests/Feature/SecurityTest.php`.

## Notes

- Prices are always calculated on the server from the database. The browser's bag only sends design numbers, sizes, colours and quantities.
- Products with no price show "Price on request". Staff set the agreed price on the order page, and the totals recalculate.
- Product photos are uploaded to `public/uploads/products`, so no storage symlink is needed on cPanel.
- Roles: **owner** and **admin** can manage everything; **staff** can't open Settings or Team.
