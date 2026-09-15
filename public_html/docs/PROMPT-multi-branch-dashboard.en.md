# Development Prompt: Admin Authentication + Multi-Branch Dashboard for MACSA Charity

> This file is a **complete specification (Spec)** to be handed to Claude Code to implement the
> "Admin Authentication" and "Multi-Branch Dashboard" capabilities on the current project.
> Everything must align with the **existing design language**, the **existing code structure**,
> and **professional security standards**.
>
> Note: The product UI is **Persian (RTL)**. UI labels below are given as `English (فارسی)` —
> the actual rendered strings must remain Persian.

---

## 0) Project Context (current state — read this first)

This is a Persian **(RTL)** charity website built with **plain PHP + PDO + MySQL/MariaDB**.

**Key paths:**
- Web root: `public_html/`
- Public front controller: [`public_html/index.php`](../public_html/index.php) — renders pages from the `pages` table (by `slug`); each page is an array of components loaded from `dashboard/components/{name}/component.php`.
- Public router: [`public_html/core/router.php`](../public_html/core/router.php) — handles `/news/{slug}` and `/branch/{slug}` routes (the branch route is currently a stub and its table does not exist).
- Admin dashboard: [`public_html/dashboard/index.php`](../public_html/dashboard/index.php) — the main content panel.
- DB config: [`public_html/core/db-config.php`](../public_html/core/db-config.php) — loads connection credentials from outside the web root. **Always use this loader; never hard-code DB credentials.**
- Existing auth helpers: [`public_html/core/auth.php`](../public_html/core/auth.php) (`auth()`, `user()`, `can($level)`, `forbid()`) and [`public_html/core/middleware.php`](../public_html/core/middleware.php) (`require_role($level)`).
- Sample database: [`database/schema.sql`](../database/schema.sql).

**Relevant existing tables:** `pages`, `news`, `news_categories`, `campaigns`, `hero_slides`, `components`, `page_components`, `courses`, `employee_profiles` (partners), `roles` (columns `name`, `level`), `webusers`, `settings`, `audit_log`, `login_attempts`.

### ⚠️ Current security problems that MUST be fixed in this work
1. **The dashboard has NO auth guard** — `dashboard/index.php` and other dashboard pages are accessible without any login check. Anyone can open `/dashboard/`.
2. **`dashboard/register.php` lets ANYONE self-register** and enter the panel. This must be completely removed/disabled.
3. Two inconsistent auth systems exist (`webusers` in `dashboard/login.php` and sessions in `core/auth.php`). They must be replaced by **one clean, unified system**.

---

## 1) High-Level Goal

Three interconnected capabilities:

1. **Admin login system:** Dashboard access only via authorized username/password. Cut off all unauthorized access to the dashboard. Initially there is exactly **one super admin (central HQ)**.
2. **Branches:** MACSA has multiple branches across Iran (e.g. "Imam Khomeini Hospital Branch"). Each branch has its own **dedicated home page** mirroring the central home structure, reachable at a URL like `mymacsa.ir/tabriz-branch`; it behaves like a standalone site for that branch (news, campaigns, home page, etc.).
3. **Multi-tenant dashboard:** The current dashboard becomes a multi-branch system; the central admin manages all branches, and each branch has its own admin and users who can only access that branch's content.

---

## 2) Roles & Permissions Model

Three access levels:

| Level | Description | Scope of visibility |
|-------|-------------|---------------------|
| **Central Admin (Super Admin / HQ)** | The only initial system admin | All branches + the "Branches" section (create/manage branches) |
| **Branch Admin** | Created when each branch is created | Only their own branch's content + managing their own branch's users |
| **Branch User** | Created by the branch admin | Only the sections their role allows, within their own branch |

**Golden security rule (Tenant Isolation):** Branch scoping must be enforced on **every server-side query** (`branch_id` filter), not just in the UI. No user, by any means (URL tampering, parameter manipulation, IDOR), may access another branch's content.

---

## 3) Database Changes (Schema)

> All changes must be written as a migration file under `database/migrations/` (e.g. `001_multi_branch.sql`) with a backfill script for existing data. `database/schema.sql` must also be updated.

### 3-1) `branches` table
```sql
CREATE TABLE branches (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name          VARCHAR(150) NOT NULL,            -- e.g. Imam Khomeini Hospital Branch
  slug          VARCHAR(100) NOT NULL UNIQUE,     -- e.g. tabriz-branch  (only a-z0-9-)
  is_hq         TINYINT(1) NOT NULL DEFAULT 0,    -- central HQ = 1 (exactly one row)
  status        ENUM('active','disabled') NOT NULL DEFAULT 'active',
  created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```
- Seed one **central HQ** row (`is_hq=1`, e.g. `slug='hq'`, `name='MACSA Central HQ'`).

### 3-2) Add `branch_id` to content tables
Add `branch_id INT UNSIGNED NOT NULL` with a `FOREIGN KEY` to `branches(id)` and an index to:
`pages`, `news`, `campaigns`, `hero_slides`, and any other content table that becomes per-branch (`courses`, `employee_profiles`, …).
- **Backfill:** assign all existing rows to the central HQ `branch_id` (current content belongs to the central branch).
- For `pages`/`news` which have a unique `slug`, uniqueness must become **composite**: `UNIQUE(branch_id, slug)` so two branches can each have their own `home` page.

### 3-3) Dashboard users — `dashboard_users`
> Instead of the scattered current tables (`webusers`), one clean unified table. `webusers` was only for the public benefactor panel; do not mix it with this.
```sql
CREATE TABLE dashboard_users (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  branch_id     INT UNSIGNED NOT NULL,            -- the branch this user belongs to
  username      VARCHAR(60)  NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,            -- password_hash(PASSWORD_DEFAULT)
  full_name     VARCHAR(120) DEFAULT NULL,
  role_id       INT UNSIGNED DEFAULT NULL,        -- role/position (for branch users)
  is_super      TINYINT(1) NOT NULL DEFAULT 0,    -- central admin = 1
  is_branch_admin TINYINT(1) NOT NULL DEFAULT 0,  -- branch admin = 1
  status        ENUM('active','disabled') NOT NULL DEFAULT 'active',
  last_login_at DATETIME DEFAULT NULL,
  created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (branch_id) REFERENCES branches(id),
  FOREIGN KEY (role_id)   REFERENCES dashboard_roles(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### 3-4) Roles & permissions — `dashboard_roles`
```sql
CREATE TABLE dashboard_roles (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  branch_id   INT UNSIGNED NOT NULL,              -- roles are defined per-branch
  name        VARCHAR(80) NOT NULL,               -- e.g. Reporter (خبرنگار)
  permissions JSON NOT NULL,                      -- e.g. ["news"] or ["news","campaigns"]
  is_preset   TINYINT(1) NOT NULL DEFAULT 0,      -- predefined roles like Reporter
  created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (branch_id) REFERENCES branches(id),
  UNIQUE(branch_id, name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```
- **Preset roles** such as "Reporter / خبرنگار" (`permissions: ["news"]`) should be seeded for each branch.

### 3-5) Per-branch content features — `branch_features`
Which sections are enabled for a branch (determined by checkboxes at branch creation):
```sql
CREATE TABLE branch_features (
  branch_id INT UNSIGNED NOT NULL,
  feature   VARCHAR(50)  NOT NULL,   -- hero | news | partners | campaigns | courses | pages | financial | feedback | medical | ...
  enabled   TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (branch_id, feature),
  FOREIGN KEY (branch_id) REFERENCES branches(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### 3-6) Donations table (final decision)
Add a direct **`branch_id INT UNSIGNED NOT NULL`** column to `panel_donations`, populated **at donation creation time** per this rule:
- If the donation is paid to a **campaign** (or course) belonging to a branch → `branch_id` = that campaign/course's branch.
- If the donation is **general** (an online donation with no campaign, `campaign_id IS NULL`) → `branch_id` = **central HQ**.

Display rules:
- **A branch's total donations** = only donations whose `branch_id` equals that branch (i.e. donations to the branch's own campaigns).
- **General donations are counted and shown only at central HQ** and are **not visible at all** in other branches' dashboards.
- Why a direct column (instead of a pure `campaign_id` join): `campaign_id` in `panel_donations` is nullable and general donations exist; also, deleting a campaign must not break a branch's stats.

---

## 4) Part One — Login System and Locking Down the Dashboard

### 4-1) Unified login page
- Build a clean login page (suggestion: rewrite `dashboard/login.php`) using the **existing dashboard design language** (same Vazirmatn font, colors, cards, RTL).
- Username and password fields only. No registration link.
- **`dashboard/register.php` must be completely removed or disabled** (no self-registration may exist).

### 4-2) Auth-guard bootstrap
- Build a bootstrap file (e.g. `dashboard/_guard.php`) that is `require`d at the **top of every dashboard page** and:
  1. Starts the secure session.
  2. If the user is not logged in → redirect to the login page.
  3. Loads the user, the active branch, and their permissions into `$_SESSION`.
  4. If the user's `status = disabled` → immediate logout and message.
  5. Checks each page's access based on feature/permission (e.g. the news page only for someone with the `news` permission).
- This guard must be applied to **all dashboard files** (index.php and every `*-create.php`, `*-list.php`, `*-save.php`, …). No dashboard endpoint may be left without a guard.

### 4-3) Seed the central admin
- A secure CLI script (e.g. `database/seed-admin.php` runnable only from the command line, not the web) that creates a central admin record with `is_super=1` and the HQ `branch_id`, storing the password via `password_hash()`. The initial username/password should come from input or an environment variable (not hard-coded).

---

## 5) Part Two — Dedicated Branch Pages (public front-end)

- Each branch's URL: `mymacsa.ir/{branch-slug}` (e.g. `mymacsa.ir/tabriz-branch`).
- Routing (`public_html/index.php` and/or `core/router.php` and `.htaccess`) must:
  1. First check whether `{slug}` matches a `branches.slug` → if yes, render **that branch's home page** (from `pages` with that branch's `branch_id` and `slug='home'`).
  2. Also support the branch's internal routes: `mymacsa.ir/{branch-slug}/news/{news-slug}`, campaigns, etc. — exactly like the central structure but scoped to that branch.
- **URL structure (final decision): top-level, no prefix** — exactly `mymacsa.ir/{slug}` like `mymacsa.ir/tabriz-branch`. **Do not use any `/branch/` prefix.**
- **Collision prevention (mandatory):** the slug namespace is shared between branches and central pages, so:
  - When creating a branch, the slug must be unique against **both** `branches.slug` **and** the central `pages.slug` (global uniqueness check + a reserved-slug list).
  - **Router resolution order:** check `branches.slug` first; if matched, render the branch home page. Otherwise fall through to the current central-pages logic.
- The branch page rendering must use the **same existing component-based mechanism** (the same `dashboard/components/*`), only reading data from `branch_id`-scoped rows.

---

## 6) Part Three — Multi-Branch Dashboard (UI/UX)

> Refer to the provided screenshot: "Box 1" = the header at the top of the sidebar (the "Management Dashboard / داشبورد مدیریت" button); "Box 2" = the sidebar menu items.

### 6-1) Box 1 → Branch selector (dropdown)
- "Box 1" becomes a **dropdown** listing the defined branches.
- **Central HQ** is selected by default.
- **Only the central admin** sees this dropdown with all branches. A branch admin/user sees only their own branch (dropdown disabled or single-option).
- The selected branch is kept in the session and **scopes the Box 2 menu and the entire dashboard content** accordingly.

### 6-2) Box 2 → Menu dependent on the selected branch
- The menu shows only items that are enabled for the selected branch in `branch_features` and that the user has permission for.
- If central HQ is selected: the current structure (heroes, news, partners, campaigns, courses, components & pages, financial, …).

### 6-3) "Branches / شعب" section (central admin only)
- Only at the central HQ access level, a new sidebar heading named **"Branches / شعب"** (at the same level as "Content Management" and "Financial") with two sub-items:
  - **Create New Branch / تعریف شعبه جدید**
  - **Manage Branches / مدیریت شعبه‌ها**

---

## 7) Part Four — "Create New Branch"

A page that collects these inputs:

1. **Branch name** and **branch tag/slug** (for the URL and for attributing data in the database). Sanitize the slug (only `a-z0-9-`) and check uniqueness.
2. **Branch admin username and password** — a `dashboard_users` record with `is_branch_admin=1` and that branch's `branch_id` is created (password via `password_hash`).
3. **Branch content permissions** — a **checkbox** for each section below (the final, complete list of delegatable sections):
   `hero` (هیروها), `news` (خبرها), `campaigns` (کمپین‌ها), `partners` (همکاران), `courses` (دوره‌ها), `pages` (کامپوننت‌ها و صفحات), `financial` (گزارش مالی), `feedback` (انتقادات و پیشنهادات), `medical` (پرونده‌های پزشکی).
   Each enabled checkbox:
   - Creates a row in `branch_features`.
   - Provisions that section's independent subsystem for the branch (e.g. if "hero" is enabled, an independent hero system for that branch with its own `branch_id`).
4. **"Create Branch" button.**

### Branch directory
- Create a `public_html/branches/` directory, and inside it a folder named after the **branch tag** (e.g. `public_html/branches/tabriz-branch/`).
- That branch's uploaded files and assets (hero, news, campaign images) are stored in this folder.
- **Note:** the source of truth for content is the database (the `branch_id`-scoped rows); this folder is for that branch's **files/uploads**. Both must be scoped.
- A default `home` page for the branch should be created in the `pages` table with its `branch_id` and `slug='home'` so `mymacsa.ir/{tag}` works immediately.

> ⚠️ Folder creation security: the folder name must be built exactly from the validated slug (never from raw input). Prevent path traversal.

---

## 8) Part Five — Branch Admin's Dedicated Dashboard

- When a branch admin (e.g. Tabriz) logs in, they are directed to the **same main dashboard**, but with **limited access**: they only see and edit their own branch's content.
- On the dashboard home page, **branch-specific stats** are shown:
  - Number of active campaigns for that branch.
  - Total donations collected that were paid **only** to that branch's campaigns/courses (`panel_donations.branch_id` = that branch).
  - **General donations (online, no campaign) are NOT shown in a branch dashboard** (HQ only).
  - All other stats scoped to `branch_id`.
- The branch admin must **not** see the "Branches" section, the multi-branch dropdown, or any other branch's content.

### Central HQ dashboard (bird's-eye view)
- In addition to its own content, central HQ also sees **all campaigns of all branches**; each campaign in the list is labeled with the **tag of the branch that created it** (show `branch.name`/`branch.slug` next to each campaign).
- General (online) donations are included only in central HQ's calculations and stats.

---

## 9) Part Six — Branch User Management (for the branch admin)

In the branch admin's sidebar, a new heading with two options:

### 9-1) "Add User / افزودن کاربر"
- User's first and last name.
- Username and password.
- **Role:**
  - Select from predefined roles (such as "Reporter / خبرنگار" which only has access to that branch's news section).
  - Or the **"Add new role / افزودن سمت جدید"** option: role name + content-permission checkboxes (news, campaigns, …) → a `dashboard_roles` row with `permissions` as JSON.
- The created user gets that branch's `branch_id` and the selected `role_id`.

### 9-2) "Manage Users / مدیریت کاربران"
- A list of the branch's created users along with their role and permission level.
- Ability to **enable/disable** each user (`status`). When disabled, the user can no longer log in to the dashboard (the guard checks at login and on every request).
- A branch admin sees and manages only their own branch's users.

---

## 10) Security Hardening Requirements — Mandatory

The implementation must be resilient against cyber threats:

- **Password hashing:** only `password_hash()` / `password_verify()` (bcrypt/argon2). Never plaintext or md5/sha1.
- **SQL Injection:** only parameterized prepared statements with PDO. No query built by string-concatenating input.
- **CSRF:** CSRF tokens for all dashboard forms and POST/PUT/DELETE requests.
- **XSS:** escape all user data on output with `htmlspecialchars()`. Validate inputs.
- **Session security:** `session_regenerate_id(true)` after login; cookie with `HttpOnly`, `Secure`, `SameSite=Strict`/`Lax`; idle and absolute timeouts.
- **Brute-force:** rate-limit login attempts using the existing `login_attempts` table (temporary lock after several failed attempts).
- **Authorization (most important):** branch isolation on **every server-side query** (`branch_id` filter). Never trust the UI alone. Prevent IDOR: before any action on a record, verify that record's branch ownership.
- **Privilege checks:** check permission on every endpoint, not just show/hide in the menu.
- **Least privilege:** a branch admin cannot create a super-admin or touch central resources.
- **File upload/dir:** create folders and uploads only with a sanitized name; prevent path traversal; disable PHP execution in upload folders.
- **DB credentials:** only via `core/db-config.php` (outside git). No credentials in code.
- **Audit:** log sensitive operations (branch creation, user creation/disabling, login) in the `audit_log` table.

---

## 11) Design (UI) Requirements — Mandatory

- **Fully consistent with the existing dashboard design language:** the same colors, typography (Vazirmatn font), corner radii, cards, spacing, icons, and the existing sidebar pattern.
- **RTL and Persian** on all new pages.
- The branch dropdown, branch-creation form, add-user form, and user-management table must be consistent with the existing dashboard components (the same button/input/table/modal styles).
- Responsive and mobile-friendly (like the rest of the dashboard).

---

## 12) Constraints and Important Notes

- **No existing capability may break.** The current dashboard for central HQ must work exactly as before (just now scoped to the central branch and behind login).
- The public benefactor panel (`webusers`, the API in `public_html/api/`, `benefactor-dashboard/`) is a separate system; do not mix it with the dashboard authentication.
- The migration must be safe on existing data (backfill the central branch).
- Clean code, Persian comments consistent with the existing files, and no leaking of error details to the user (in production).

---

## 13) Expected Deliverables

1. A migration file under `database/migrations/` + updated `database/schema.sql`.
2. A central-admin seed script + central-branch seed + preset roles.
3. A unified login page + an auth-guard bootstrap (`_guard.php`) applied to all dashboard pages.
4. Removal/disabling of `register.php`.
5. The branch selector (Box 1) + scoped menu (Box 2) + the "Branches" section.
6. The "Create New Branch" page + branch directory creation + section provisioning.
7. Front-end routing `mymacsa.ir/{slug}` for branch pages.
8. The branch admin dashboard with scoped stats.
9. The "Add User" and "Manage Users" pages with the role system.
10. Full application of the Section 10 security requirements.

---

## 14) Suggested Execution Order (phasing)

> It is recommended to work phase-by-phase and test after each phase:

1. **Phase 1 — Base security:** unified login system + guard across the whole dashboard + remove register + seed central admin. (The current dashboard becomes locked behind login.)
2. **Phase 2 — Branch model:** `branches` table + add `branch_id` to content + central backfill. (No visual change; data gets scoped.)
3. **Phase 3 — Multi-branch dashboard:** branch selector + scoped menu + "Branches" section + "Create New Branch" + directory/provisioning.
4. **Phase 4 — Branch front-end:** `mymacsa.ir/{slug}` routing + the branch home page and sections.
5. **Phase 5 — Branch users:** roles + add/manage users + enable/disable.
6. **Phase 6 — Final security hardening and light pen-testing:** review all points (CSRF, IDOR, isolation, rate-limit).

---

### Finalized architectural decisions (confirmed by the client)
1. **Donation-to-branch attribution:** a direct `branch_id` column on `panel_donations`. A donation to a branch's campaign/course → that branch; a general online donation (no campaign) → central HQ only, not shown in branches. Central HQ sees all campaigns of all branches, each tagged with its branch. (Sections 3-6 and 8)
2. **Branch URL:** top-level, no prefix (`mymacsa.ir/tabriz-branch`), with a global slug-uniqueness check and branch-first resolution order in the router. (Section 5)
3. **Delegatable sections:** `hero`, `news`, `campaigns`, `partners`, `courses`, `pages`, `financial`, `feedback`, `medical`. (Section 7-3)
