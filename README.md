<p align="center">
  <img src="public/images/smart-holidays-logo.png" alt="Smart Holidays" width="180">
</p>

<h1 align="center">Smart Holidays</h1>

<p align="center">
  Travel experiences and Muslim-friendly tour content, powered by Laravel.
</p>

<p align="center">
  <img src="https://img.shields.io/badge/PHP-8.3%2B-777BB4?style=flat-square&logo=php&logoColor=white" alt="PHP 8.3 or later">
  <img src="https://img.shields.io/badge/Laravel-13-FF2D20?style=flat-square&logo=laravel&logoColor=white" alt="Laravel 13">
  <img src="https://img.shields.io/badge/Tailwind_CSS-4-06B6D4?style=flat-square&logo=tailwindcss&logoColor=white" alt="Tailwind CSS 4">
</p>

---

## Welcome

Smart Holidays is a travel website with public destination and tour-package pages, together with an admin dashboard for managing site content. The Smart Muslim Tour section includes private tour packages and editable destination pages.

## What’s inside

| Area | Highlights |
| --- | --- |
| 🌍 Public website | Smart Holidays and Smart Muslim Tour pages, destinations, and private tour packages |
| 🧭 Admin dashboard | Sign-in, content management, and responsive navigation |
| 🖼️ Site content | Homepage campaigns and services, Island & Honeymoon content, destinations, and custom tour pages |

### Technology

- **Backend:** PHP 8.3+, Laravel 13
- **Frontend:** Blade, Vite, Tailwind CSS 4, and npm
- **Database:** SQLite by default; connection settings can be changed in `.env`

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

6. Make uploaded images available and build the frontend assets:

   ```sh
   php artisan storage:link
   npm run build
   ```

7. Start the Laravel development server:

   ```sh
   php artisan serve
   ```

   Open the local URL printed by Artisan. The admin sign-in page is at `/admin/login`.

For live frontend development, run `npm run dev` in a separate terminal.

## Run tests

```sh
php artisan test
```

## Handy commands

| Command | Purpose |
| --- | --- |
| `php artisan route:list` | List application routes |
| `php artisan migrate:status` | Check migration status |
| `npm run build` | Build production frontend assets |

---

<p align="center">
  Made for memorable journeys.
</p>
