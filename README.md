# ScyrolynX — Event Ticketing Platform

A Laravel + React event ticketing platform: browse events, buy tickets with
atomic stock locking, get a signed QR code, and staff can check tickets in
at the door. Built as a Laravel API with both a Blade frontend and an
in-progress React frontend consuming the same API.

## Requirements

- PHP 8.3+ with these extensions: `pdo_mysql`, `gd`, `intl`, `iconv`, `mbstring`, `curl`
- Composer
- Node.js + npm
- MySQL or MariaDB

## Setup

1. **Install PHP dependencies**
```bash
   composer install
```

2. **Install JS dependencies**
```bash
   npm install
```

3. **Copy the environment file and fill in real values**
```bash
   cp .env.example .env
   php artisan key:generate
```
   Edit `.env` and set your database name, username, and password
   (`DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`).

4. **Create the database and user in MySQL/MariaDB**, matching what you put
   in `.env`. Example:
```sql
   CREATE DATABASE your_db_name;
   CREATE USER 'your_user'@'%' IDENTIFIED BY 'your_password';
   GRANT ALL PRIVILEGES ON your_db_name.* TO 'your_user'@'%';
   FLUSH PRIVILEGES;
```

5. **Run migrations and seed sample data** (events, ticket types, and the
   three staff roles: Admin, Event Manager, Box Office)
```bash
   php artisan migrate --seed
```

6. **Build the frontend assets**
```bash
   npm run build
```

7. **Start the app**
```bash
   php artisan serve
```
   Visit `http://127.0.0.1:8000`.

## Creating a staff account

Staff accounts are not self-registered, by design (matches the spec: only
an Admin should be able to grant staff access). Register a normal account
through `/register`, then assign it a role manually:

```bash
php artisan tinker --execute="
\$user = App\Models\User::where('email', 'someone@example.com')->first();
\$user->assignRole('Box Office'); // or 'Event Manager' or 'Admin'
"
```

Logging in with that account will redirect to `/staff` instead of the
regular customer `/events` page.

## What's working

- Browse events, view details
- Register / login / logout (Sanctum-based)
- Buy tickets with atomic stock locking (prevents overselling under
  concurrent purchases)
- Order history / "My Tickets" page
- Signed, scannable QR codes per ticket
- Role-based staff check-in (Admin, Event Manager, Box Office), tested
  against unauthorized access and duplicate check-in attempts
- Paystack webhook integration for payment confirmation (ticket issuance
  only happens on confirmed payment, never on browser redirect alone)
- Early-stage React frontend at `/react`, consuming the same JSON API
  as the Blade pages (events list and detail page implemented so far)

## Known limitations

- No live Paystack account connected, checkout works end-to-end but
  `authorization_url` will be `null` without real API keys in `.env`
- React frontend is partial (only events list + detail); most pages are
  still Blade
- No discount codes, refunds, or sales/settlement reporting yet
- No Admin UI for creating staff accounts yet (currently done via Tinker,
  see above)
