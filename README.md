# Al Badar — Roznamcha / ERP System

Daily bookkeeping for Aamdan (income), Kharcha (expense), and net balance.

## Stack

- Laravel 13
- SQLite (default) or MySQL
- Blade + Bootstrap 5

## Quick start

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed
php artisan serve
```

Create the first admin user from the Users screen after an existing administrator logs in, or from `php artisan tinker` in a secure environment. Do not commit or display login credentials.

Production checklist:

- `APP_DEBUG=false`
- `APP_ENV=production`
- HTTPS with `SESSION_SECURE_COOKIE=true`
- Keep regular database backups from Settings

## MySQL (optional)

In `.env`:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=albadar_erp
DB_USERNAME=root
DB_PASSWORD=
```

Then run `php artisan migrate`.

## Features

- Al Badar branding with local logo
- Login with hashed passwords and role-based access (Admin / Editor)
- Dashboard totals, date filters, income vs expense chart
- Fast Add Aamdan / Add Kharcha
- Roznamcha ledger with filters, search, pagination
- Soft delete with restore
- Full audit trail for create / edit / delete / restore
- Print, CSV, and Excel export
- Admin backup download
