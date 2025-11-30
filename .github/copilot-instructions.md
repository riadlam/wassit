<!-- .github/copilot-instructions.md - repo-specific guidance for AI coding agents -->
# Copilot / AI Agent Instructions (concise)

Purpose: help an AI contributor be productive immediately in this Laravel app.

- **Big picture:** This is a Laravel (v12) monolithic app with both web and API surfaces:
  - Web frontend routes live in `routes/web.php` and drive blade/views and controllers under `app/Http/Controllers`.
  - API endpoints live in `routes/api.php` and use `auth:sanctum` for protected routes.
  - Real-time features use Laravel Broadcasting; `Broadcast::routes(...)` appears at the top of `routes/web.php` (must stay before catch-all routes).
  - Domain models are in `app/Models` and migrations are under `database/migrations` (timestamped — newer features use 2025_11_26_* files).

- **How the app is structured (quick):**
  - `routes/web.php`: site pages, language-switching, OAuth callbacks, dashboard/account pages, and a catch-all account detail route at the bottom.
  - `routes/api.php`: REST endpoints — games, accounts, sellers, orders, reviews; guarded with `auth:sanctum` where applicable.
  - `app/Http/Controllers`: controllers are grouped by feature (GameController, AccountController, DashboardController, ChatController, etc.).
  - `app/Http/Middleware`: custom guards like `EnsureUserIsSeller` restrict seller-only routes.
  - `database/seeders` and `database/factories` used for test/dev data.

- **Important routing/convention notes agents must respect:**
  - The app relies on route ordering. Keep `Broadcast::routes()` and dashboard/auth routes before any catch-all route. Do not move the final `/{slug}/accounts/{id}` route earlier.
  - Chat endpoints are nested under `account/chat` in the web routes; matching behavior exists in `routes/api.php` for API consumers.
  - Use `auth` middleware for session/web routes and `auth:sanctum` for API routes.

- **Developer workflows / commands** (copyable):
  - One-shot setup: `composer run setup` (creates .env from example, generates app key, runs migrations, installs npm deps, builds assets) — run in project root.
  - Start dev environment (multi-process): `composer run dev` (uses `concurrently` to run `php artisan serve`, queue listener, pail logger, and Vite dev server). Requires Node installed.
  - Frontend dev: `npm run dev` (starts Vite dev server). Build: `npm run build`.
  - Tests: `composer run test` (clears config cache and runs Laravel tests). Alternatively `php artisan test`.
  - Artisan common commands: `php artisan migrate`, `php artisan tinker`, `php artisan queue:listen`.

- **Project-specific dependencies and integrations:**
  - Uses `laravel/sanctum` for API authentication.
  - Uses `pusher/pusher-php-server` and Laravel Broadcasting for real-time chat/events.
  - Uses `spatie/laravel-query-builder` for flexible query filtering patterns in controllers — look for its usage in controller index actions.
  - Frontend uses `vite`, `tailwindcss` and `laravel-vite-plugin` with `package.json` scripts.

- **Patterns and idioms to follow in PRs/code changes:**
  - Keep route ordering; add new public routes before catch-all and broadcast auth lines when necessary.
  - Prefer using controller methods already present (e.g., `GameController::getAttributes`, `ChatController::sendMessage`) rather than adding route-level logic.
  - Database changes: add a migration in `database/migrations` with a timestamped filename matching existing style (YYYY_MM_DD_HHMMSS_description.php).
  - When changing auth behavior, update both `routes/web.php` and `routes/api.php` if the feature spans web + API.

- **Where to look for examples:**
  - Broadcast auth and catch-all routing: `routes/web.php` (top & bottom ordering).
  - API patterns and `auth:sanctum` usage: `routes/api.php`.
  - Middleware example: `app/Http/Middleware/EnsureUserIsSeller.php` (seller gating).
  - Composer scripts and setup flow: `composer.json` -> `scripts.setup` and `scripts.dev`.

If anything here is unclear or you want deeper examples (controller snippets, common query/DTO patterns, or mapping between specific endpoints and views), tell me which area and I will expand or merge additional examples into this file.
