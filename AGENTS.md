# AGENTS.md — VisionPark AI-Based Smart Parking System Admin

This file is a guide for AI coding agents that need to work on this project. It is derived from the actual files in the repository as of 2026-10-24.

---

## Project overview

This is a Laravel-based backend/admin application for an AI-powered smart parking system. It exposes a JSON API for mobile or frontend clients and includes a Filament admin dashboard scaffold at `/admin`.

Repository identifier from the README: `ai-parking-space-backend`. Composer still uses the default skeleton name `laravel/laravel`, and the application name defaults to `Laravel` unless overridden via `APP_NAME`.

### What the application currently does

- User registration and login via token-based API authentication (Laravel Sanctum).
- Authenticated users can manage their own vehicles.
- Authenticated users can list available parking slots and create bookings for a slot.
- Booking a slot marks the slot as `occupied`.
- A default Filament v5 admin panel is registered but no custom resources or pages exist yet.

---

## Technology stack

| Layer | Technology | Version / Notes |
|---|---|---|
| Language | PHP | `^8.3` |
| Framework | Laravel | `^13.17` |
| Admin panel | Filament | `^5.0` |
| API authentication | Laravel Sanctum | `^4.0` |
| Front-end build | Vite | `^8.0` |
| CSS framework | Tailwind CSS | `^4.0` (Vite plugin) |
| Database | MySQL / SQLite / etc. | Configured via `.env`; tests use SQLite in-memory |
| Queue | Database | `QUEUE_CONNECTION=database` by default |
| Cache/session | Database | Defaults in `.env.example` |
| Package manager | Composer + npm | — |

### Key configuration files

- `composer.json` — PHP dependencies, autoloading, and Composer scripts.
- `package.json` — Node dependencies, Vite scripts, and convenience aliases.
- `vite.config.js` — Vite build config using the Laravel plugin and Tailwind v4.
- `phpunit.xml` — PHPUnit test suites and testing environment.
- `.env.example` — Example environment variables for local development.
- `bootstrap/app.php` — Laravel application bootstrap and routing registration.
- `bootstrap/providers.php` — Registered service providers.

---

## Directory structure

```text
app/
  Http/
    Controllers/
      Api/              # API controllers (Auth, Vehicle, Slot, Booking)
      Controller.php    # Abstract base controller
    Requests/           # Form request classes for auth validation
  Models/               # User, Vehicle, Slot, Booking
  Providers/
    AppServiceProvider.php
    Filament/
      AdminPanelProvider.php
bootstrap/
  app.php               # Laravel bootstrap
  providers.php         # Provider registration
config/                 # Standard Laravel config
  app.php, auth.php, database.php, sanctum.php, ...
database/
  factories/            # Eloquent factories (UserFactory)
  migrations/           # Users, cache, jobs, Sanctum, vehicles, slots, booking
  seeders/              # DatabaseSeeder
public/                 # Web root (index.php + .htaccess)
resources/
  css/app.css           # Tailwind entry
  js/app.js             # JS entry
  views/welcome.blade.php
routes/
  api.php               # API routes
  web.php               # Web routes (welcome page only)
  console.php           # Artisan command definitions
tests/
  Feature/              # Feature tests
  Unit/                 # Unit tests
  TestCase.php
```

### Autoloading

PSR-4 autoloading is configured in `composer.json`:

- `App\` → `app/`
- `Database\Factories\` → `database/factories/`
- `Database\Seeders\` → `database/seeders/`
- `Tests\` → `tests/` (dev)

---

## Code organization

### Models

- `User` — `HasApiTokens`, authentication. Fillable: `name`, `email`, `password`. Has many `Vehicle`.
- `Vehicle` — belongs to `User`, has many `Booking`. Fillable: `user_id`, `name`, `number`.
- `Slot` — parking slot. Fillable: `name`, `coordinates`, `status`. Coordinates are cast to `array`.
- `Booking` — maps to the `booking` table. Fillable: `vehicle_id`, `slot_id`, `entry_time`, `exit_time`. Belongs to `Vehicle` and `Slot`.

### API routes (`routes/api.php`)

| Method | Route | Middleware | Controller | Action |
|---|---|---|---|---|
| POST | `/api/signup` | public | `AuthController@signup` | Register a user |
| POST | `/api/singin` | public | `AuthController@signin` | Login and receive token |
| POST | `/api/logout` | `auth:sanctum` | `AuthController@logout` | Revoke current token |
| POST | `/api/vehicle` | `auth:sanctum` | `VehicleController@store` | Create vehicle |
| GET | `/api/vehicles` | `auth:sanctum` | `VehicleController@index` | List user vehicles |
| DELETE | `/api/vehicle/{id}` | `auth:sanctum` | `VehicleController@destroy` | Delete user vehicle |
| GET | `/api/parking-slots` | `auth:sanctum` | `SlotController@index` | List all slots |
| POST | `/api/booking/{slot_id}` | `auth:sanctum` | `BookingController@store` | Book a slot |
| GET | `/api/bookings` | `auth:sanctum` | `BookingController@index` | List user bookings |

Note: the sign-in route is currently spelled `/singin` (not `/signin`). Do not rename unless explicitly asked.

### Controllers

- `AuthController` — uses `SignupRequest` / `SigninRequest` for validation, returns JSON tokens.
- `VehicleController` — inline `Validator` usage; scopes all operations to the authenticated user.
- `SlotController` — returns all slots.
- `BookingController` — validates `vehicle_id`, `entry_time`, `exit_time`; ensures the vehicle belongs to the user and the slot is not occupied before creating a booking.

### Validation

- `SignupRequest` — `name`, `email` (unique), `password` (min 8, confirmed).
- `SigninRequest` — `email`, `password`.
- Inline `Validator` is used in `VehicleController` and `BookingController`.

### Admin panel

`App\Providers\Filament\AdminPanelProvider` registers a default Filament panel at `/admin` with login enabled. It discovers resources/pages/widgets under `app/Filament/{Resources,Pages,Widgets}`. Those directories do not exist yet, so the panel only shows the dashboard.

---

## Build and development commands

Install dependencies and set up the application:

```bash
composer install
npm install
```

Copy the environment file and generate an application key:

```bash
cp .env.example .env
php artisan key:generate
```

Run migrations:

```bash
php artisan migrate
```

Build assets for production:

```bash
npm run build
```

Start the local development stack:

```bash
# Laravel dev server only
php artisan serve

# Vite dev server only
npm run dev

# Convenience: Vite + server concurrently via npm
npm run watch

# Composer dev script: server + queue worker + Vite concurrently
composer dev
```

The `composer dev` script runs:

- `php artisan serve`
- `php artisan queue:listen --tries=1`
- `npm run dev`

### Useful npm aliases

- `npm run php <command>` → `php artisan <command>`
- `npm run serve`
- `npm run migrate`
- `npm run key`
- `npm run log` → `tail -f storage/logs/laravel.log`
- `npm run clear` → clears config, route, and cache

### One-command setup

```bash
composer setup
```

This runs: `composer install`, copies `.env.example` to `.env`, generates the key, runs migrations, installs npm packages, and builds assets.

---

## Testing instructions

Tests are configured in `phpunit.xml`:

- Unit suite: `tests/Unit`
- Feature suite: `tests/Feature`
- Code coverage source: `app/`
- Testing env uses SQLite in-memory (`:memory:`), array cache/session, sync queue, and disables Pulse/Telescope/Nightwatch.

Run tests:

```bash
composer test
```

Equivalent to:

```bash
php artisan config:clear
php artisan test
```

Or directly with PHPUnit:

```bash
vendor/bin/phpunit
```

Current tests only contain placeholder examples:

- `tests/Unit/ExampleTest.php` — asserts `true`.
- `tests/Feature/ExampleTest.php` — asserts the `/` route returns `200`.

When writing new feature tests for authenticated routes, use Sanctum's `actingAs` helper or create tokens via `$user->createToken(...)`.

---

## Code style guidelines

- **Formatter:** Laravel Pint is installed (`vendor/bin/pint`).
- **Laravel style:** Follow Laravel conventions — Eloquent models, controllers grouped under `App\Http\Controllers`, form requests under `App\Http\Requests`.
- **PHP version:** Use PHP 8.3+ syntax. The `User` model already uses PHP 8 attributes (`#[Fillable]`, `#[Hidden]`) for Eloquent configuration.
- **Namespaces:** Maintain PSR-4 namespace mapping.
- **Validation:** Prefer FormRequest classes for reusable validation rules (already used for auth). Inline `Validator` is acceptable for one-off cases.
- **Return format:** API controllers return JSON responses with consistent `message` keys and HTTP status codes.
- **Front-end assets:** CSS lives in `resources/css/app.css`; JS lives in `resources/js/app.js`. Tailwind v4 is imported via `@import 'tailwindcss';` and uses the Vite plugin.

---

## Security considerations

- **Application key:** `APP_KEY` must be kept secret and generated per environment (`php artisan key:generate`). Never commit `.env`.
- **Debug mode:** Keep `APP_DEBUG=false` in production.
- **Authentication:** API endpoints use Laravel Sanctum bearer tokens. Protect sensitive routes with `auth:sanctum` middleware.
- **Authorization:** Controllers that operate on user-owned resources currently scope queries manually (`where('user_id', $request->user()->id)`). Verify this pattern is applied to new endpoints.
- **Validation:** All public API inputs are validated, but there is currently no rate limiting configured. Consider adding throttling for `signup`/`signin` in production.
- **CSRF:** Web routes use standard Laravel CSRF protection. API routes are stateless and use tokens.
- **SQL injection:** Use Eloquent/Query Builder; avoid raw SQL without parameter binding.
- **XSS:** Blade templates escape output by default.
- **File storage:** Default disk is `local`. If uploads are added, run `php artisan storage:link` and store files under `storage/app/public`.
- **Secrets in VCS:** `.gitignore` excludes `.env`, `.env.*`, `auth.json`, `storage/*.key`, `/vendor`, `/node_modules`, and build artifacts.

---

## Deployment notes

There are no Dockerfiles, GitHub Actions, or platform-specific deploy configs in the repository. Deployment follows standard Laravel practices:

1. Install PHP dependencies: `composer install --no-dev --optimize-autoloader`
2. Install and build Node assets: `npm ci && npm run build`
3. Ensure the document root points to the `public/` directory.
4. Set environment variables in `.env` (production database, `APP_DEBUG=false`, strong `APP_KEY`).
5. Run migrations: `php artisan migrate --force`
6. Optimize for production:

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

7. Ensure the following directories are writable by the web server:
   - `storage/`
   - `bootstrap/cache/`
8. If using queues or scheduled tasks, configure a queue worker and cron for `php artisan schedule:run`.

---

## Environment quick reference

Key variables from `.env.example`:

- `APP_NAME=Laravel`
- `APP_ENV=local`
- `APP_KEY=` (generate)
- `APP_DEBUG=true`
- `APP_URL=http://127.0.0.1:8000`
- `DB_CONNECTION=mysql`, `DB_HOST=127.0.0.1`, `DB_PORT=3306`, `DB_DATABASE=ai_parking_backend`
- `SESSION_DRIVER=database`
- `CACHE_STORE=database`
- `QUEUE_CONNECTION=database`
- `BROADCAST_CONNECTION=log`
- `MAIL_MAILER=log`
- `VITE_APP_NAME="${APP_NAME}"`

---

## What is not present yet

- Custom Filament resources, pages, or widgets.
- Rate limiting / throttling middleware.
- Policies or gates for authorization beyond manual query scoping.
- Real-time broadcasting or queue job classes beyond the default setup.
- CI/CD pipelines or container deployment files.

When extending the project, follow the existing patterns: Eloquent models, API controllers under `App\Http\Controllers\Api`, validation via FormRequest where reusable, and JSON responses with explicit HTTP status codes.
