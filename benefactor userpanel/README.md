# خیرین مکسا — Benefactor User Panel

A Persian/RTL charity donation panel (React) with a plain-PHP REST backend for
shared cPanel hosting (LAMP, MySQL/MariaDB, PHP 8.1+). JWT access tokens +
httpOnly refresh cookies, a pluggable payment gateway, and a hardened security
posture throughout.

- **Frontend:** React 18 + TypeScript + Vite 6, served as static files at
  `https://erfanteymuri.ir/benefactor-dashboard/`
- **Backend:** PHP 8.1+ (no Composer required on the server), at
  `https://erfanteymuri.ir/api/`
- **Database:** MySQL/MariaDB (`utf8mb4`)

---

## Repository layout

```
benefactor userpanel/
├── api/                      → deploy to public_html/api/
│   ├── index.php             front controller
│   ├── .htaccess             routing + hardening
│   └── src/                  PHP source (autoloaded; not web-reachable)
│       ├── Core/             Config, Database, Router, Request/Response, Security, Validator
│       ├── Auth/             Jwt, PasswordPolicy, RefreshTokenService, AuthMiddleware
│       ├── Controllers/      one per resource
│       ├── Repositories/     all SQL (PDO prepared statements)
│       ├── Services/         DonationService + Gateway/ (Stub, Zarinpal, Factory)
│       ├── Mail/             Mailer + EmailTemplates
│       ├── Support/          Logger, Audit, RateLimiter, Cookie, Str, AvatarStorage
│       └── routes.php
├── database/
│   ├── schema.sql            full dump (paste into phpMyAdmin)
│   └── migrations/           incremental 001/002/003
├── public_html/.htaccess     → deploy to public_html/.htaccess (SPA + HTTPS + CSP)
├── src/                      React app
│   ├── api/client.ts         fetch wrapper (in-memory token, auto-refresh)
│   ├── contexts/AuthContext.tsx
│   └── app/                  views, components, routes
├── .env.example              backend secrets template
└── dist/                     built React (after `npm run build`)
```

---

## Target server layout (cPanel)

```
/home/<cpanel_user>/
├── maksa-private/                ← OUTSIDE web root (secrets + logs + uploads)
│   ├── .env                      (chmod 600)
│   ├── logs/app.log
│   └── uploads/avatars/
└── public_html/
    ├── .htaccess                 ← from public_html/.htaccess (this repo)
    ├── api/                      ← from api/ (this repo) + vendor/ (PHPMailer)
    └── benefactor-dashboard/     ← contents of dist/ (built React)
        ├── index.html
        └── assets/
```

> The app runs in the `/benefactor-dashboard/` subfolder (Vite `base` and the
> React Router `basename` are already set to it). The API is at the domain root
> under `/api`, so the app and API share an origin — cookies and CORS "just work".

---

## Deployment — step by step

### 1. Add the panel tables to the EXISTING database (phpMyAdmin)

The panel lives **inside your existing `erfantey_macsacharity` database**. Its
tables are prefixed `panel_` so they coexist with the site's own tables, and the
panel **reads campaigns from your existing `campaigns` table** (and bumps its
`collected_amount` when a panel donation succeeds). Your existing
`users` / `webusers` / `campaigns` / `donations` tables are never modified.

1. cPanel → **phpMyAdmin** → select **`erfantey_macsacharity`** → **SQL** tab →
   paste the entire contents of [`database/schema.sql`](database/schema.sql) → **Go**.
   - Prefer incremental tracking? Run the files in
     [`database/migrations/`](database/migrations/) in order (001 → 002 → 003) instead.
2. Verify: you should now have `panel_users`, `panel_donations`, `donor_tiers`
   (seeded), token tables, `notifications`, etc. — and your original `campaigns`
   rows are untouched.
3. Ensure your DB user has privileges on this database (it already does if it owns
   the site). Put its name/password in `.env` (`DB_NAME=erfantey_macsacharity`).

### 2. Place secrets ABOVE the web root

1. cPanel → **File Manager** → in your home dir (one level above `public_html`)
   create a folder `maksa-private`, and inside it `logs/` and `uploads/avatars/`.
2. Copy [`.env.example`](.env.example) to `maksa-private/.env` and fill it in:
   - `DB_*` from step 1.
   - `JWT_SECRET` — generate one: in cPanel **Terminal** (or any PHP host) run
     `php -r "echo bin2hex(random_bytes(32));"` and paste the 64-char result.
   - `MAIL_*` — create an email account in cPanel (e.g. `no-reply@erfanteymuri.ir`)
     and use its SMTP credentials. Port 465 (`tls`/SMTPS) is typical on cPanel.
   - `LOG_PATH=/home/<cpanel_user>/maksa-private/logs/app.log`
   - `UPLOAD_DIR=/home/<cpanel_user>/maksa-private/uploads`
   - `APP_URL=https://erfanteymuri.ir/benefactor-dashboard`
   - `CORS_ALLOWED_ORIGINS=https://erfanteymuri.ir`
   - `PAYMENT_GATEWAY=stub` for now (switch to `zarinpal` + `ZARINPAL_MERCHANT_ID`
     when you have a merchant account).
3. Set the `.env` permissions to **600** (File Manager → right-click → Change
   Permissions → owner read/write only).

> **How config is found:** `api/src/Core/Config.php` looks for the `.env` at
> `MAKSA_ENV_PATH` (if set), then `<home>/maksa-private/.env`, then `api/.env`
> (dev only). Real environment variables always override file values.

### 3. Upload the API

1. Upload the `api/` folder to `public_html/api/`.
2. **PHPMailer** (no Composer on server): download the PHPMailer release zip from
   github.com/PHPMailer/PHPMailer and upload it so that these three files exist:
   `api/vendor/PHPMailer/src/PHPMailer.php`, `.../SMTP.php`, `.../Exception.php`.
   The front controller auto-detects this layout (and also a Composer
   `api/vendor/autoload.php` if you have one). **Without PHPMailer the app falls
   back to PHP `mail()`, which shared hosts routinely drop — so transactional
   email won't arrive.** Use authenticated SMTP for reliable delivery.
3. cPanel → **MultiPHP Manager**: confirm the domain runs **PHP 8.1+**.
4. Smoke test: visit `https://erfanteymuri.ir/api/health` — you should get
   `{"success":true,"data":{"status":"ok",...}}`.

### 4. Build and upload the React app

On your machine:

```bash
npm install
npm run build      # outputs to dist/
```

Upload the **contents of `dist/`** into `public_html/benefactor-dashboard/`
(so `public_html/benefactor-dashboard/index.html` exists).

- The API base URL defaults to **`/api`** (same origin) — no rebuild needed if you
  change domains later. To point a local dev build at a remote API, create a
  `.env` in the project root with `VITE_API_URL=https://erfanteymuri.ir/api`.

### 5. Root `.htaccess`

Upload [`public_html/.htaccess`](public_html/.htaccess) to `public_html/.htaccess`.
It forces HTTPS, sets security headers + CSP, serves the SPA (React Router
fallback) under `/benefactor-dashboard/`, and leaves `/api` to the API's own
`.htaccess`.

### 6. Verify end to end

1. Open `https://erfanteymuri.ir/benefactor-dashboard/` → you should land on the
   login page.
2. Register → check your email for the verification link (`/benefactor-dashboard/verify?token=…`).
3. Verify → log in → you reach the dashboard.
4. Make a donation (with `PAYMENT_GATEWAY=stub` it round-trips instantly and the
   donation shows as successful in History).

---

## API reference (summary)

All responses: `{ "success": bool, "data": …, "error": { code, message } | null }`.
Base path: `/api`. Access token via `Authorization: Bearer <token>`; refresh
token via the `maksa_rt` httpOnly cookie.

| Method | Path | Auth | Purpose |
|---|---|---|---|
| POST | `/auth/register` | – | create account, send verification |
| POST | `/auth/verify-email` | – | activate via token |
| POST | `/auth/resend-verification` | – | resend link |
| POST | `/auth/login` | – | access token + refresh cookie |
| POST | `/auth/refresh` | cookie | rotate refresh, new access token |
| POST | `/auth/logout` | cookie | revoke refresh |
| POST | `/auth/forgot-password` | – | send reset link (always 200) |
| POST | `/auth/reset-password` | – | set new password from token |
| POST | `/auth/change-password` | ✓ | change while logged in |
| GET | `/user/me` | ✓ | profile |
| PATCH | `/user/me` | ✓ | update profile (whitelisted) |
| DELETE | `/user/me` | ✓ | delete account (`confirm:"DELETE"`) |
| POST | `/user/me/avatar` | ✓ | avatar upload (multipart) |
| GET | `/user/avatar/{file}` | – | serve avatar |
| GET/PUT | `/user/notification-prefs` | ✓ | notification toggles |
| GET | `/user/dashboard` | ✓ | KPIs, chart series, activity |
| GET | `/campaigns?status=` | – | list |
| GET | `/campaigns/{slug}` | – | detail |
| POST | `/campaigns/suggest` | ✓ | suggest a campaign |
| POST | `/donations` | ✓ | start donation → gateway redirect |
| GET | `/donations/callback` | – | gateway return (302 to SPA) |
| GET | `/donations/{reference}` | ✓ | donation status |
| GET | `/donations?page=&q=` | ✓ | paginated history |
| GET | `/notifications` | ✓ | list + unread count |
| POST | `/notifications/{id}/read`, `/notifications/read-all` | ✓ | mark read |
| POST | `/tax-certificate` | ✓ | request tax certificate |

---

## Going live with payments (Zarinpal)

1. Obtain a Zarinpal merchant ID.
2. In `.env`: `PAYMENT_GATEWAY=zarinpal`, `ZARINPAL_MERCHANT_ID=…`,
   `ZARINPAL_SANDBOX=false`.
3. No code change needed — `GatewayFactory` resolves the implementation. To add a
   different gateway (IDPay, NextPay…), implement
   [`PaymentGateway`](api/src/Services/Gateway/PaymentGateway.php) and register it
   in the factory.

---

## Local development

- Frontend: `npm run dev` (Vite dev server). Set `VITE_API_URL` to your API.
- Backend: any PHP 8.1+ with a local MySQL and a local `api/.env`. Use
  `APP_DEBUG=true` locally only — it returns real error messages.

See [SECURITY.md](SECURITY.md) for the threat model and the implemented mitigations.
