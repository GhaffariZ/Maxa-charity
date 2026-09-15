# Financial Reporting System & Role Isolation Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Implement a comprehensive financial reporting system with multi-sheet Excel export and strict dashboard isolation for the Financial Officer role in the MACSA charity platform.

**Architecture:** Add a dedicated "مسئول مالی" (Financial Officer) preset role into the database and auth system. Intercept login and dashboard entry to route financial officers directly to `financial-management.php` while isolating the sidebar navigation to only financial reporting and blocking all other admin routes with HTTP 403. Upgrade `financial-management.php` with all-source aggregation (direct donations, campaigns, stands/orders, courses), flexible time/dimension filters, and a dedicated multi-sheet XML Spreadsheet export engine (`financial-export.php`).

**Tech Stack:** PHP 7.4+ (PDO MySQL), XML Spreadsheet 2003 (native multi-sheet Excel without external dependencies), HTML5/CSS3 (Vazirmatn RTL), JavaScript (Chart.js, Persian Datepicker).

**Spec:** [docs/superpowers/specs/2026-09-15-financial-reporting-system-design.md](file:///d:/Project/React/Maxa-charity/docs/superpowers/specs/2026-09-15-financial-reporting-system-design.md)

## Global Constraints

- Preserve all existing multi-branch tenant isolation rules (`dash_active_branch_id()`).
- All user-facing text and labels must be in Persian (Farsi) with RTL layout.
- Excel exports must support UTF-8 BOM and RTL direction with no garbled Persian characters.
- Strict server-side gatekeeping via `_guard.php` and `dash_require('financial')`.
- Zero external composer package additions (use native clean PHP and XML Spreadsheet format).

---

### Task 1: Database Migration for Financial Officer Role

**Files:**
- Create: `database/migrations/014_financial_officer_role.sql`

**Interfaces:**
- Produces: Preset role `'مسئول مالی'` in table `dashboard_roles` with `permissions = JSON_ARRAY('financial')` and `is_preset = 1` for headquarters (id=1) and all active branches.

- [ ] **Step 1: Write SQL migration file**

```sql
-- Migration 014: Financial Officer Preset Role
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- 1. Insert preset role 'مسئول مالی' for Central HQ (branch_id = 1) if not exists
INSERT INTO `dashboard_roles` (`branch_id`, `name`, `permissions`, `is_preset`)
SELECT 1, 'مسئول مالی', JSON_ARRAY('financial'), 1
WHERE NOT EXISTS (SELECT 1 FROM `dashboard_roles` WHERE `branch_id` = 1 AND `name` = 'مسئول مالی');

-- 2. Insert preset role 'مسئول مالی' for all other active branches
INSERT INTO `dashboard_roles` (`branch_id`, `name`, `permissions`, `is_preset`)
SELECT b.`id`, 'مسئول مالی', JSON_ARRAY('financial'), 1
FROM `branches` b
WHERE b.`is_hq` = 0 AND b.`status` = 'active'
AND NOT EXISTS (SELECT 1 FROM `dashboard_roles` r WHERE r.`branch_id` = b.`id` AND r.`name` = 'مسئول مالی');

SET FOREIGN_KEY_CHECKS = 1;
```

- [ ] **Step 2: Verify migration syntax and idempotency**

Execute test parse with PHP PDO script in scratch directory to verify query execution against the database connection if DB is reachable, or test syntax.

- [ ] **Step 3: Commit migration**

```bash
git add database/migrations/014_financial_officer_role.sql
git commit -m "feat(db): add migration 014 for financial officer preset role"
```

---

### Task 2: Core Authentication & Role Helper Functions

**Files:**
- Modify: `public_html/core/dashboard-auth.php`

**Interfaces:**
- Consumes: Session data `$_SESSION['dash_user']`
- Produces: `dash_is_finance_only(): bool` function to check if the authenticated user has strictly financial privileges without general admin privileges.

- [ ] **Step 1: Implement `dash_is_finance_only()` in `public_html/core/dashboard-auth.php`**

```php
/**
 * آیا کاربر جاری منحصراً «مسئول مالی» است؟
 * یعنی سوپرادمین یا ادمین شعبه نیست، و دسترسی‌های او صرفاً محدود به financial است.
 */
function dash_is_finance_only(): bool
{
    $u = dash_user();
    if (!$u) {
        return false;
    }
    if (!empty($u['is_super']) || !empty($u['is_branch_admin'])) {
        return false;
    }
    $perms = $u['permissions'] ?? [];
    return in_array('financial', $perms, true) && count($perms) === 1;
}
```

- [ ] **Step 2: Self-heal database roles on auth startup**

Add fallback check in `core/dashboard-auth.php` or `_guard.php` so that if migration 014 hasn't been manually applied to MySQL, the `'مسئول مالی'` preset role is ensured automatically if missing.

- [ ] **Step 3: Test auth function with mock session**

Run a CLI test script checking `dash_is_finance_only()` with various role structures (super admin, branch admin, reporter, finance officer).

- [ ] **Step 4: Commit core auth changes**

```bash
git add public_html/core/dashboard-auth.php
git commit -m "feat(auth): add dash_is_finance_only helper"
```

---

### Task 3: Login Redirection & Route Isolation

**Files:**
- Modify: `public_html/dashboard/login.php`
- Modify: `public_html/dashboard/index.php`

**Interfaces:**
- Consumes: `dash_is_finance_only()`, `dash_user()`, `dash_can('financial')`
- Produces: Direct redirection on login for financial officers to `financial-management.php`, redirection from `index.php` to `financial-management.php`, and isolation of sidebar navigation items in `index.php`.

- [ ] **Step 1: Update login redirection in `public_html/dashboard/login.php`**

When authentication succeeds, check if the user is a financial-only officer and redirect directly:
```php
        $res = dash_attempt_login($username, $password);
        if ($res['ok']) {
            if (dash_is_finance_only()) {
                header('Location: /dashboard/financial-management.php');
            } else {
                header('Location: /dashboard/index.php');
            }
            exit;
        }
```
Also handle already logged in redirect:
```php
if (dash_is_authenticated()) {
    if (dash_is_finance_only()) {
        header('Location: /dashboard/financial-management.php');
    } else {
        header('Location: /dashboard/index.php');
    }
    exit;
}
```

- [ ] **Step 2: Protect `public_html/dashboard/index.php` from unauthorized general overview**

If `dash_is_finance_only()` enters `index.php`, redirect them to `financial-management.php`:
```php
if (dash_is_finance_only()) {
    header('Location: /dashboard/financial-management.php');
    exit;
}
```

- [ ] **Step 3: Clean up sidebar navigation logic in `public_html/dashboard/index.php`**

Ensure `tickets.php` and `donation-impacts.php` are only pushed to `NAV` if the user has appropriate permissions or is not restricted to financial-only.

- [ ] **Step 4: Commit route and navigation isolation**

```bash
git add public_html/dashboard/login.php public_html/dashboard/index.php
git commit -m "feat(dash): isolate navigation and add login redirection for financial officer"
```

---

### Task 4: User Management UI Support for Financial Officer Preset Role

**Files:**
- Modify: `public_html/dashboard/user-add.php`
- Modify: `public_html/dashboard/user-edit.php`

**Interfaces:**
- Consumes: `dashboard_roles` table
- Produces: Dropdown option and visual indicator for assigning the "مسئول مالی" preset role when creating or editing users.

- [ ] **Step 1: Check `public_html/dashboard/user-add.php` and `user-edit.php`**

Ensure preset roles query includes `'مسئول مالی'` and highlight it with a badge/tag or convenient preset button so branch admins and central admins can quickly create financial officers.

- [ ] **Step 2: Test user creation logic with CLI or unit script**

Verify that inserting a user with role `مسئول مالی` assigns `role_id` and grants solely `['financial']` permission.

- [ ] **Step 3: Commit user management updates**

```bash
git add public_html/dashboard/user-add.php public_html/dashboard/user-edit.php
git commit -m "feat(users): add financial officer role preset in user management"
```

---

### Task 5: Multi-sheet Excel Export Engine (`financial-export.php`)

**Files:**
- Create: `public_html/dashboard/financial-export.php`

**Interfaces:**
- Consumes: `GET` parameters: `start_date`, `end_date`, `source`, `campaign_id`, `branch_id`, `status`
- Produces: Downloadable `.xls` file with 3 XML Worksheets:
  1. `خلاصه مدیریتی` (Executive summary with KPI cards, source breakdown, and date metadata)
  2. `ریز تراکنش‌ها` (Detailed transactions: tracking code, Jalali date & time, donor name, contact, source/campaign, branch, amount, status)
  3. `حامیان برتر` (Top contributors list)

- [ ] **Step 1: Implement query builder with branch tenant isolation**

Support filters:
- Date range: `start_date` and `end_date` (Gregorian YYYY-MM-DD)
- Source: `all`, `direct`, `campaign`, `stands`, `courses`
- Status: `success` (default), `all`, `pending`, `failed`
- Branch: if `dash_is_super()` and `dash_is_hq_view()`, respect `$_GET['branch_id']`; otherwise strictly lock to `dash_active_branch_id()`.

- [ ] **Step 2: Implement XML Spreadsheet generator with UTF-8 and RTL formatting**

Generate compliant XML with `<Workbook ss:RightToLeft="1">`:
```xml
<?xml version="1.0" encoding="UTF-8"?>
<?mso-application progid="Excel.Sheet"?>
<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"
 xmlns:o="urn:schemas-microsoft-com:office:office"
 xmlns:x="urn:schemas-microsoft-com:office:excel"
 xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet">
 ...
```
Include custom cell styles: Header style (teal `#007b7a` with white text), Subheader style (gold `#f4a61e`), Currency/Number style (`#,##0`), and Date style.

- [ ] **Step 3: Populate the 3 Worksheets**

- **Worksheet 1 (`خلاصه مدیریتی`):**
  - Report header, generation date (Jalali), branch name, date interval.
  - Table of Financial Inflow Sources (کمک مستقیم، کمپین‌ها، استندها، دوره‌ها) with amounts, share percentage, and total.
  - Campaign breakdown table with title and total collected.
- **Worksheet 2 (`ریز تراکنش‌ها`):**
  - Columns: ردیف, کد پیگیری / شناسه پرداخت, تاریخ و ساعت پرداخت, نام پرداخت‌کننده, اطلاعات تماس, منبع مالی, کمپین / توضیحات, شعبه, مبلغ (تومان), وضعیت.
- **Worksheet 3 (`حامیان برتر`):**
  - Columns: ردیف, نام خیر, ایمیل / تماس, تعداد پرداخت‌ها, مجموع کمک‌ها (تومان).

- [ ] **Step 4: Send appropriate download headers**

```php
header('Content-Type: application/vnd.ms-excel; charset=utf-8');
header('Content-Disposition: attachment; filename="MACSA-Financial-Report-' . date('Y-m-d') . '.xls"');
header('Pragma: no-cache');
header('Expires: 0');
```

- [ ] **Step 5: Write unit test script to test export engine and validate XML structure**

Run PHP CLI test checking that `financial-export.php` generates valid XML, closes all tags, and includes the 3 worksheets.

- [ ] **Step 6: Commit export engine**

```bash
git add public_html/dashboard/financial-export.php
git commit -m "feat(finance): add multi-sheet excel export engine"
```

---

### Task 6: Comprehensive Reporting Dashboard & UI Enhancement

**Files:**
- Modify: `public_html/dashboard/financial-management.php`

**Interfaces:**
- Consumes: All financial tables (`panel_donations`, `orders`, `campaigns`), Persian Datepicker, `financial-export.php`
- Produces: Upgraded financial reporting UI with all-sources aggregation, quick-filter time chips, source selector, interactive charts, and export modal.

- [ ] **Step 1: Upgrade data aggregation to include all financial sources**

- Calculate `onlineHelpTotal`: Direct donations without campaign
- Calculate `campaignHelpTotal`: Donations tied to campaigns
- Calculate `ordersTotal`: Total from `orders` table (stands) for the active branch and date range
- Calculate `coursesTotal`: Total from course payments if present
- Calculate `grandTotalCollected`: Sum of all sources

- [ ] **Step 2: Add quick-filter time chips and source dropdown**

In the top filter bar:
- Quick date filter buttons: `امروز`, `۷ روز گذشته`, `۳۰ روز گذشته`, `ماه جاری (شمسی)`, `کل دوره`
- Source filter: `تمام منابع`, `کمک مستقیم آنلاین`, `کمپین‌ها`, `استندهای خیریه`
- Branch filter (for Super Admin / Central HQ only)

- [ ] **Step 3: Connect Export Modal to `financial-export.php`**

Update `openReportModal()` and the submit handler so clicking "دریافت فایل اکسل" opens or triggers `financial-export.php` with the current filters and downloads the multi-sheet Excel file seamlessly.

- [ ] **Step 4: Add top navigation / back button and isolation for financial officer**

If user is `dash_is_finance_only()`, render a clean topbar with user profile, branch name, theme toggle, and logout button, ensuring they feel completely at home without broken links.

- [ ] **Step 5: Commit UI enhancements**

```bash
git add public_html/dashboard/financial-management.php
git commit -m "feat(finance): upgrade reporting dashboard with all sources and export integration"
```

---

### Task 7: Verification & End-to-End Testing

**Files:**
- Create: `scratch/test_financial_system.php`

- [ ] **Step 1: Write integration test script**

Create test script covering:
1. Role resolution: Verify role `'مسئول مالی'` assigns `['financial']` and triggers `dash_is_finance_only() === true`.
2. Login routing: Verify financial officer routes to `financial-management.php`.
3. Guard protection: Verify `_guard.php` blocks unauthorized feature access (`news`, `pages`, etc.) for financial officers.
4. Data aggregation: Verify totals calculation matches database queries.
5. Export engine: Execute `financial-export.php` logic and verify valid multi-sheet XML output.

- [ ] **Step 2: Execute integration test script**

Run: `php scratch/test_financial_system.php`
Expected: ALL TESTS PASS.

- [ ] **Step 3: Verify in browser subagent or manual check**

Verify layout, styles, dark/light theme compatibility, and button interactions.

- [ ] **Step 4: Final commit and cleanup**

```bash
git add scratch/test_financial_system.php
git commit -m "test(finance): add comprehensive integration test for financial reporting system"
```
