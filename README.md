# Roznamcha ERP

Small, practical daily bookkeeping ERP for Pakistani businesses.

Tracks **Roznamcha**, **Aamdan / Income**, **Kharcha / Expense**, and **Daily Net Balance**.

## Stack

- Laravel 13
- SQLite (default) or MySQL
- Blade + Bootstrap 5

## Quick start

```bash
composer install
cp .env.example .env   # if needed
php artisan key:generate
php artisan migrate:fresh --seed
php artisan serve
```

Open http://localhost:8000

### Default logins

| Role   | Email / Username           | Password |
|--------|----------------------------|----------|
| Admin  | admin@roznamcha.local      | password |
| Editor | editor@roznamcha.local     | password |

Change these passwords after first login.

## MySQL (optional)

In `.env`:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=roznamcha_erp
DB_USERNAME=root
DB_PASSWORD=
```

Create the database, then run `php artisan migrate:fresh --seed`.

## Features (Phase 1)

- Login with hashed passwords & secure sessions
- Roles: Admin / Editor (admin routes protected server-side)
- Dashboard totals + date filters + income vs expense chart
- Fast Add Aamdan / Add Kharcha forms
- Roznamcha ledger with filters, search, pagination
- Totals on every report (Aamdan, Kharcha, Net Balance)
- User management (create, disable, reset password, change role)
- Expense category management
- Print + CSV + Excel export
- Settings (company name, allow editors to edit entries)
- Database backup download (Admin)
- Audit fields: created_by, updated_by, timestamps

## Editor permissions

Editors can login and add income/expense entries.

Editors cannot delete entries, manage users/settings/categories, or access admin pages.

Editing existing entries is off by default; Admin can enable it in Settings.

## Navigation

**Admin:** Dashboard · Roznamcha · Add Aamdan · Add Kharcha · Reports · Expense Categories · Users · Settings · Logout

**Editor:** Dashboard · Roznamcha · Add Aamdan · Add Kharcha · Logout
