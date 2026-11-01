<p align="center">
  <img src="public/images/smart-holidays-logo.png" alt="Smart Holidays" width="180">
</p>

<h1 align="center">Smart Holidays</h1>

<p align="center">
  A travel website, client booking portal, and admin workspace for Smart Holidays and Smart Muslim by Roystar Travel &amp; Tours.
</p>

<p align="center">
  <img src="https://img.shields.io/badge/PHP-8.3%2B-777BB4?style=flat-square&logo=php&logoColor=white" alt="PHP 8.3 or later">
  <img src="https://img.shields.io/badge/Laravel-13-FF2D20?style=flat-square&logo=laravel&logoColor=white" alt="Laravel 13">
  <img src="https://img.shields.io/badge/Tailwind_CSS-4-06B6D4?style=flat-square&logo=tailwindcss&logoColor=white" alt="Tailwind CSS 4">
  <img src="https://img.shields.io/badge/Vite-8-646CFF?style=flat-square&logo=vite&logoColor=white" alt="Vite 8">
</p>

<p align="center">
  <a href="#screenshots">Screenshots</a> ·
  <a href="#features">Features</a> ·
  <a href="#local-development">Local development</a> ·
  <a href="#run-tests">Run tests</a>
</p>

<p align="center">
  <img src="public/images/readme/smart-holidays-home.jpg" alt="Smart Holidays homepage with the headline: Your world of journeys, thoughtfully planned" width="900">
</p>

## Screenshots

> Client names, bookings, and revenue in these screenshots are demo data.

### Public website

<table>
  <tr>
    <td width="50%"><img src="public/images/readme/smart-muslim-home.jpg" alt="Smart Muslim homepage campaign slider"></td>
    <td width="50%"><img src="public/images/readme/series-departures.jpg" alt="Series Departures 2026 tour package page"></td>
  </tr>
  <tr>
    <td align="center"><b>Smart Muslim</b><br>Campaigns, services, and consultants</td>
    <td align="center"><b>Series departures</b><br>Fixed-date group tours</td>
  </tr>
  <tr>
    <td><img src="public/images/readme/island-honeymoon.jpg" alt="Island and Honeymoon page"></td>
    <td><img src="public/images/readme/company-trip-mice.jpg" alt="Company Trip (MICE) page"></td>
  </tr>
  <tr>
    <td align="center"><b>Island &amp; Honeymoon</b><br>Private getaways</td>
    <td align="center"><b>Company Trip (MICE)</b><br>Corporate travel and quotations</td>
  </tr>
</table>

### Client portal

<table>
  <tr>
    <td width="50%"><img src="public/images/readme/client-dashboard.jpg" alt="Client overview showing the next trip and a countdown"></td>
    <td width="50%"><img src="public/images/readme/client-bookings.jpg" alt="Client bookings list with upcoming trips"></td>
  </tr>
  <tr>
    <td align="center"><b>Overview</b><br>Next trip, countdown, and account summary</td>
    <td align="center"><b>My bookings</b><br>Upcoming, past, and cancelled trips</td>
  </tr>
</table>

### Admin workspace

<table>
  <tr>
    <td width="50%"><img src="public/images/readme/admin-dashboard.jpg" alt="Admin travel overview with revenue chart and popular destinations"></td>
    <td width="50%"><img src="public/images/readme/admin-bookings.jpg" alt="Admin bookings table with status filters"></td>
  </tr>
  <tr>
    <td align="center"><b>Travel overview</b><br>Revenue, clients, and popular destinations</td>
    <td align="center"><b>Bookings</b><br>Search and filter by status</td>
  </tr>
  <tr>
    <td><img src="public/images/readme/admin-online-booking.jpg" alt="Online booking settings with seat limits per package"></td>
    <td><img src="public/images/readme/admin-tour-editor.jpg" alt="Tour package editor with theme colour choices"></td>
  </tr>
  <tr>
    <td align="center"><b>Online booking</b><br>Open or close packages and set seat limits</td>
    <td align="center"><b>Package editor</b><br>Step-by-step page editing with colour themes</td>
  </tr>
</table>

### On mobile

<p align="center">
  <img src="public/images/readme/mobile-home.jpg" alt="Smart Muslim homepage on a phone" width="240">
  &nbsp;&nbsp;
  <img src="public/images/readme/mobile-island.jpg" alt="Island and Honeymoon page on a phone" width="240">
</p>

## Features

| Area | Highlights |
| --- | --- |
| 🌍 Public website | Group, ground, and domestic tours, Smart Muslim packages, China Muslim tours, Island & Honeymoon, MICE, ticketing, and gallery pages |
| 🗣️ Reaching customers | Enquiry forms, WhatsApp links, package likes, and English, Malay, and Chinese through Google Translate |
| 🧳 Client portal | Email-code sign-up and password reset, online package booking, trip requests, booking history, and a guided tour |
| 📊 Admin operations | Revenue overview, bookings, enquiries, client accounts and suspensions, package like stats, and light or dark themes |
| 🎟️ Seat management | Open or close each package, with seat limits per package or series departure; confirming a booking takes its seats and cancelling returns them |
| 🖼️ Content management | Edit homepage campaigns, services, consultants, destinations, tour packages, menus, and other site pages without changing code |

### Technology

- **Backend:** PHP 8.3+, Laravel 13
- **Frontend:** Blade, Vite, Tailwind CSS 4, and JavaScript
- **Database:** SQLite by default; connection settings can be changed in `.env`
- **Tests:** PHPUnit 12

### Where to find things

| Path | URL | Who it is for |
| --- | --- | --- |
| [`resources/views/SmartHolidays.blade.php`](resources/views/SmartHolidays.blade.php) | `/` | Visitors |
| [`resources/views/Smart-Muslim/`](resources/views/Smart-Muslim/) | `/smart-muslim` and its sections | Visitors |
| [`resources/views/client/`](resources/views/client/) | `/client/login`, `/my-account` | Clients |
| [`resources/views/Admin/`](resources/views/Admin/) | `/admin/login`, `/admin` | Administrators |
| [`routes/web.php`](routes/web.php) | All routes | Developers |

## Local development

### Requirements

- PHP 8.3 or later with the extensions required by Laravel
- Composer
- Node.js and npm

### Get started

1. Install project dependencies:

   ```sh
   composer install
   npm ci
   ```

2. Create your local environment file. Use `Copy-Item .env.example .env` in PowerShell, or `cp .env.example .env` on macOS/Linux.

3. In `.env`, set `APP_NAME`, `APP_URL`, and credentials for the admin account:

   ```dotenv
   ADMIN_NAME="Smart Holidays Admin"
   ADMIN_EMAIL=admin@example.com
   ADMIN_PASSWORD=use-a-unique-password-at-least-12-characters
   ```

   Keep `.env` private and never commit real credentials.

4. Generate the application key and create the default SQLite database file:

   ```sh
   php artisan key:generate
   php -r "file_exists('database/database.sqlite') || touch('database/database.sqlite');"
   ```

5. Run migrations and create the admin account:

   ```sh
   php artisan migrate
   php artisan db:seed --class="Database\Seeders\AdminUserSeeder"
   ```

   The migrations also add the starting site content, so the public pages work straight away.

6. Make uploaded images available and build the frontend assets:

   ```sh
   php artisan storage:link
   npm run build
   ```

7. Start the Laravel development server:

   ```sh
   php artisan serve
   ```

   Open the local URL printed by Artisan. The admin sign-in page is at `/admin/login`, and the client sign-in page is at `/client/login`.

For live frontend development, run `npm run dev` in a separate terminal.

> [!TIP]
> With the default `MAIL_MAILER=log`, sign-up and password reset codes are written to `storage/logs/laravel.log` instead of being emailed.

## Run tests

```sh
php artisan test
```

> [!WARNING]
> Tests reset the database they connect to. `phpunit.xml` selects an in-memory SQLite database, but any `DB_*` variables exported in your shell take priority over it. Check that none are set first, or your local database could be wiped.

## Handy commands

| Command | Purpose |
| --- | --- |
| `php artisan route:list` | List application routes |
| `php artisan migrate:status` | Check migration status |
| `npm run build` | Build production frontend assets |
| `vendor/bin/pint --dirty` | Format changed PHP files |

---

<p align="center">
  Made for memorable journeys.
</p>
