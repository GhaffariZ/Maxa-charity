# Security — threat model & mitigations

This document records the threats considered for the خیرین مکسا panel and exactly
where each mitigation lives in the code. It ends with a self-audit checklist
walking every requirement.

## Trust boundaries

- **Browser ↔ API** over HTTPS only. The browser holds a short-lived JWT access
  token **in memory** (never `localStorage`) and an opaque refresh token in an
  `httpOnly` + `Secure` + `SameSite=Strict` cookie it cannot read.
- **API ↔ MySQL** via PDO with real prepared statements.
- **API ↔ SMTP / payment gateway** over TLS.
- **Secrets** live in a `.env` **above** `public_html`, never in the repo or web root.

## Authentication & session model

- Passwords hashed with `PASSWORD_ARGON2ID` (auto-fallback to `bcrypt` cost 12)
  — `api/src/Auth/PasswordPolicy.php`.
- Access token: HS256 JWT, 15-minute TTL, with `iat`/`nbf`/`exp`/`jti`/`typ` —
  `api/src/Auth/Jwt.php`. Verified per request in `AuthMiddleware`.
- Refresh token: 256-bit random opaque string, stored **hashed** (SHA-256),
  rotated on every refresh, with **reuse detection** that burns the whole token
  family on replay — `api/src/Auth/RefreshTokenService.php`.
- Refresh cookie flags set in `api/src/Support/Cookie.php`: `httpOnly`, `Secure`,
  `SameSite=Strict`, path scoped to `/api/auth`.

## Mitigations by attack class

| Threat | Mitigation | Where |
|---|---|---|
| **SQL injection** | PDO prepared statements only; `ATTR_EMULATE_PREPARES=false`; zero string-built SQL with user data | `Core/Database.php`, all `Repositories/*` |
| **XSS** | JSON-only API; strict CSP on the SPA; output escaping in emails | `public_html/.htaccess`, `Mail/EmailTemplates.php` |
| **CSRF** | `SameSite=Strict` refresh cookie; access token sent as a header (not a cookie) so cross-site requests can't ride along; CORS allowlist | `Support/Cookie.php`, `Core/Security.php` |
| **Brute force** | Sliding-window rate limit (5 fails / 15 min per email+IP) + per-account lockout (`panel_users`) with exponential backoff | `Support/RateLimiter.php`, `Repositories/UserRepository.php` |
| **Account enumeration** | Identical responses for register / login / forgot-password regardless of whether the email exists | `Controllers/AuthController.php` |
| **Timing attacks** | `hash_equals()` for token/JWT comparison; dummy `password_verify()` when the user is missing | `Auth/Jwt.php`, `AuthController::login` |
| **Email header injection** | CR/LF + `%0a/%0d` stripped from addresses, names, subjects | `Mail/Mailer.php` |
| **Malicious file upload** | MIME validated via `finfo`; size cap; GD re-encode strips EXIF/payloads; UUID filename; stored above web root; served via guarded route (never executed) | `Support/AvatarStorage.php`, `Controllers/UserController::serveAvatar` |
| **Mass assignment** | Whitelist validator; profile update only touches an explicit field list | `Core/Validator.php`, `Repositories/ProfileRepository::updateProfile` |
| **Clickjacking** | `X-Frame-Options: DENY` + CSP `frame-ancestors 'none'` | `Core/Security.php`, both `.htaccess` |
| **Transport downgrade** | Forced HTTPS redirect + HSTS (preload) | both `.htaccess`, `Core/Security.php` |
| **Token theft / replay** | Refresh rotation + family revocation on reuse; password reset/change revokes all sessions | `Auth/RefreshTokenService.php`, `AuthController` |
| **Payload abuse** | 256 KB JSON body cap; strict JSON parse with depth limit | `Core/Request.php` |
| **Info leakage** | Generic 500 message to clients; real detail logged server-side; `X-Powered-By` removed; directory listing off | `index.php`, `Support/Logger.php`, `.htaccess` |
| **Source exposure** | `/api/src` and `/api/vendor` PHP blocked; `.env/.sql/.log` blocked; only `index.php` reachable | `api/.htaccess` |

## Logging & audit

- Security events (`login`, failures, lockouts, refresh reuse, password changes,
  account deletion, profile updates, donations) → `audit_log` table via
  `Support/Audit.php`. **Secrets and full tokens are never logged.**
- Unhandled errors → file log outside web root via `Support/Logger.php`.

## Data handling

- Money stored as `BIGINT` Toman (no float rounding bugs).
- Timestamps stored UTC; Jalali rendering is frontend-only.
- Donation records survive account deletion (anonymized via `ON DELETE SET NULL`)
  for financial integrity; all other user-owned rows cascade-delete.

---

## Security self-audit checklist

Walking every item from the project's security requirements:

### Authentication & passwords
- [x] Argon2id hashing with bcrypt(12) fallback — `PasswordPolicy::hash`
- [x] Minimum 10-char passwords + variety + blocklist (zxcvbn-lite) — `PasswordPolicy::validate`
- [x] JWT access tokens: 15-min, HS256, strong env secret, `iat`/`exp`/`jti` — `Jwt`
- [x] Refresh tokens: 256-bit opaque, stored hashed, httpOnly/Secure/SameSite=Strict cookie — `RefreshTokenService`, `Cookie`
- [x] Token rotation on refresh + reuse detection invalidates the family — `RefreshTokenService::rotate`

### Input handling
- [x] All DB access via PDO prepared statements, no string concatenation — `Repositories/*`
- [x] Server-side whitelist validation on every input — `Validator`
- [x] Output escaping where HTML is produced (emails) — `EmailTemplates`
- [x] Strict JSON parsing with a size limit — `Request::parseBody`

### Transport & headers
- [x] Forced HTTPS + HSTS (`max-age=31536000; includeSubDomains; preload`) — `.htaccess`, `Security`
- [x] `X-Content-Type-Options`, `X-Frame-Options: DENY`, `Referrer-Policy`, CSP, `Permissions-Policy` — `Security`, `.htaccess`
- [x] CORS restricted to a specific origin allowlist, never `*` — `Security::handleCors`

### Attack mitigations
- [x] SQL injection — prepared statements only
- [x] XSS — JSON API + strict CSP + escaping
- [x] CSRF — SameSite=Strict cookie + header-based access token + CORS
- [x] Brute force — rate limiting + lockout + exponential backoff + `login_attempts`
- [x] Account enumeration — identical responses on register/login/forgot
- [x] Timing attacks — `hash_equals()` + dummy `password_verify()`
- [x] Email injection — header sanitization in the mailer
- [x] File upload — finfo MIME, size cap, EXIF strip, UUID name, stored outside web root
- [x] Session fixation — N/A (stateless JWT; no PHP session is used for auth)
- [x] Clickjacking — `X-Frame-Options` + CSP `frame-ancestors 'none'`
- [x] Mass assignment — explicit field whitelisting on updates

### Secrets
- [x] `.env` never committed (`.gitignore`); only `.env.example` is tracked
- [x] DB creds + JWT secret loaded from `.env` placed above `public_html`
- [x] Documented `.env` placement + chmod 600 — `README.md`

### Code quality
- [x] `declare(strict_types=1)` + type hints throughout; PSR-12-ish style
- [x] Separated Controllers / Services / Repositories / Middleware / Validators
- [x] Centralized error handler → sanitized JSON to client, real detail to file log
- [x] Logging for auth events, failed validations, server errors — no secrets/tokens

### Notes / operational follow-ups
- Set `APP_DEBUG=false` in production (default). It only ever returns generic 500s.
- Switch `PAYMENT_GATEWAY=stub` → `zarinpal` before accepting real money.
- Consider a cron to prune expired rows in the token + `login_attempts` tables.
- HSTS `preload` implies you intend to submit the domain to the preload list and
  keep HTTPS permanently; drop `preload` if that's not the intent.
