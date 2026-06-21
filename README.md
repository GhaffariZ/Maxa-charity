<div align="center">

# 🕊️ MACSA Charity Platform

### A web platform for the MACSA charity — supportive &amp; palliative care for cancer patients and their families

*Donations · Awareness · Education · Community*

<br/>

[![Status](https://img.shields.io/badge/status-under%20development-orange.svg)](#-project-status)
[![PHP](https://img.shields.io/badge/PHP-7.4%2B-777BB4.svg?logo=php&logoColor=white)](https://www.php.net/)
[![MySQL](https://img.shields.io/badge/MySQL-MariaDB-4479A1.svg?logo=mysql&logoColor=white)](https://www.mysql.com/)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind%20CSS-utility--first-38B2AC.svg?logo=tailwindcss&logoColor=white)](https://tailwindcss.com/)
[![Language](https://img.shields.io/badge/UI-Persian%20(RTL)-2ea44f.svg)](#)
[![License](https://img.shields.io/badge/license-Proprietary-lightgrey.svg)](#-license)

</div>

---

> [!WARNING]
> **🚧 This project is still under active development and is not yet complete.**
> Features, database schema, routes, and the public UI are evolving and may change without notice.
> Some pages are functional, others are placeholders or work-in-progress. See [Project Status](#-project-status).

---

## 📖 Overview

**MACSA** is a full-stack web platform built for a charity that provides **supportive and palliative
care for cancer patients and their families**. It combines a public-facing website, a custom
**component-based content management system (CMS)**, a **learning platform (LMS)**, a **donation &
campaign** system, and a set of **administrative dashboards** — all delivered with a **right-to-left
(RTL) Persian** interface.

The platform is written in **plain PHP (PDO + MySQL)** with a lightweight custom router, a
**component-driven page builder** for non-technical editors, and a separate **JWT-secured REST API**
for the donor/benefactor experience.

<div align="center">

<!--- Replace with your actual screenshot. See docs/screenshots/README.md --->
<img src="docs/screenshots/home.png" alt="MACSA home page" width="900"/>

<sub><i>Home page (RTL Persian layout). Add your screenshot at <code>docs/screenshots/home.png</code>.</i></sub>

</div>

---

## ✨ Main Features

### 🌐 Public Website
- **Component-based pages** — every page is assembled from reusable, self-contained components
  (hero, about us, mission &amp; vision, services, branches, organizational chart, history, and more).
- **Care-service sections** dedicated to the charity's mission: medical care, nursing care,
  physiotherapy, psychological &amp; spiritual care, nutrition, social work, genetic counseling,
  screening, medical equipment, and 24/7 patient support.
- **Awareness content** — palliative care, breast-cancer prevention, end-of-life care, and the
  state of palliative care in the region.
- **Fully responsive & RTL** — built for Persian readers and mobile-first browsing.

### 📰 News & Stories
- News/blog system with **slugged URLs** (`/news/{slug}`), publishing workflow, and view tracking.
- Multiple news layouts (classic, hero, card grid) and a recent-news feed.
- Patient & community **success stories** (MACSA stories).

### 💝 Donations & Campaigns
- **Online donation** flow and **fundraising campaigns** with status tracking.
- **Campaign suggestions** from registered users.
- **Tax-certificate** requests for benefactors.
- Donor-facing **benefactor dashboard**.

### 🎓 Courses (LMS)
- Course catalog, course details, **checkout**, **my-courses**, and a **learning view**.
- Admin tools to **create, manage, and edit** courses.

### 🛠️ Admin Dashboard
- **Visual page builder** — create pages by composing components and ordering them, then publish.
- **Hero / banner management**, **templates**, and a page **history / publish** workflow.
- **News management** (create, edit, status updates, deletion).
- **Financial management** and reporting views.
- **Resume / personnel** management for staff and applicants.
- **Feedback** collection and **user account** management.
- Rich UI kit (charts, tables, forms, maps, e-commerce & CRM dashboard layouts) based on a
  Bootstrap admin theme.

### 🔌 REST API (JWT-secured)
A standalone API (`/api`) powering the authenticated donor experience:

| Area           | Endpoints (selected)                                                    |
| -------------- | ----------------------------------------------------------------------- |
| **Auth**       | register, verify-email, login, refresh, logout, forgot/reset password   |
| **User**       | profile (`/user/me`), avatar upload, notification preferences, dashboard |
| **Campaigns**  | list, show by slug, suggest a campaign                                   |
| **Donations**  | create, history, gateway callback, status by reference                   |
| **Notifications** | list, mark one read, mark all read                                   |
| **Engagement** | request tax certificate                                                  |

> The API uses JWT access tokens with **refresh tokens**, a **password policy**, request
> validation, and a small custom router/middleware layer.

### 🔒 Security & Hosting
- **Forced HTTPS**, directory listing disabled, and custom **403 / 404 / 500** error pages.
- Self-hosted **Vazirmatn** webfont (no external CDN) for reliability on the Iran network.
- Sensitive files (`config.php`, `.env`, etc.) are **git-ignored**.
- Configured for **cPanel** deployment via `.cpanel.yml`.

---

## 🖼️ Screenshots

> Screenshots are placeholders until added. Drop images into
> [`docs/screenshots/`](docs/screenshots/) using the file names below — see
> [the screenshots guide](docs/screenshots/README.md).

<table>
  <tr>
    <td align="center" width="50%">
      <img src="docs/screenshots/services.png" alt="Care services page" width="420"/><br/>
      <sub><b>Supportive &amp; palliative care services</b></sub>
    </td>
    <td align="center" width="50%">
      <img src="docs/screenshots/donation.png" alt="Online donation page" width="420"/><br/>
      <sub><b>Online donation &amp; campaigns</b></sub>
    </td>
  </tr>
  <tr>
    <td align="center" width="50%">
      <img src="docs/screenshots/news.png" alt="News listing" width="420"/><br/>
      <sub><b>News &amp; stories</b></sub>
    </td>
    <td align="center" width="50%">
      <img src="docs/screenshots/courses.png" alt="Courses LMS" width="420"/><br/>
      <sub><b>Courses (LMS)</b></sub>
    </td>
  </tr>
  <tr>
    <td align="center" width="50%">
      <img src="docs/screenshots/dashboard.png" alt="Admin dashboard" width="420"/><br/>
      <sub><b>Admin dashboard</b></sub>
    </td>
    <td align="center" width="50%">
      <img src="docs/screenshots/page-builder.png" alt="Component page builder" width="420"/><br/>
      <sub><b>Component-based page builder</b></sub>
    </td>
  </tr>
</table>

<div align="center">
  <img src="docs/screenshots/mobile.png" alt="Mobile responsive view" width="260"/><br/>
  <sub><b>Responsive, mobile-first, RTL layout</b></sub>
</div>

---

## 🧰 Tech Stack

| Layer        | Technology                                                            |
| ------------ | -------------------------------------------------------------------- |
| **Backend**  | PHP (PDO), custom router & middleware                                 |
| **API**      | Custom PHP REST API, JWT auth, PHPMailer                              |
| **Database** | MySQL / MariaDB                                                       |
| **Frontend** | HTML5, Tailwind CSS, vanilla JS, Bootstrap-based admin theme         |
| **Charts/UI**| ApexCharts, DataTables, Leaflet / vector maps, SweetAlert2           |
| **Fonts**    | Vazirmatn (self-hosted, variable font)                               |
| **Caching**  | Redis (optional)                                                      |
| **Hosting**  | Apache (`.htaccess`), cPanel deployment                              |

---

## 📂 Project Structure

```text
Maxa-charity/
├── .cpanel.yml                 # cPanel deployment config
├── public_html/                # Web root
│   ├── index.php               # Front controller — renders pages from components
│   ├── .htaccess               # HTTPS, error pages, routing, security
│   ├── font.css                # Self-hosted Vazirmatn font
│   ├── api/                    # JWT-secured REST API
│   │   ├── index.php           # API front controller
│   │   └── src/
│   │       ├── Auth/           # JWT, middleware, password policy, refresh tokens
│   │       ├── Controllers/    # Auth, User, Campaign, Donation, Notification, ...
│   │       ├── Core/           # Router, Request, Response, DB, Validator, Security
│   │       ├── Repositories/   # Data access
│   │       ├── Services/       # Business logic
│   │       └── routes.php      # API route table
│   ├── core/                   # Public-site router, DB, auth, helpers
│   ├── dashboard/              # Admin dashboard + page builder + LMS
│   │   ├── index.php           # Dashboard entry
│   │   ├── components/         # Reusable page components (hero, aboutus, ...)
│   │   ├── templates/          # Page templates
│   │   ├── course-*.php        # Courses / LMS
│   │   ├── news-*.php          # News management
│   │   ├── campaign-*.php      # Campaign management
│   │   ├── hero-*.php          # Hero/banner management
│   │   └── financial-management.php
│   ├── benefactor-dashboard/   # Donor-facing dashboard
│   ├── register/               # Public registration & login
│   ├── eo/                     # Executive/decisions module
│   ├── uploads/                # User uploads (hero, news, campaigns, avatars)
│   └── assets/                 # css / js / images
└── docs/screenshots/           # README screenshots
```

> [!NOTE]
> The site is **database-driven**: pages live in a `pages` table and store an ordered list of
> component names (JSON). `index.php` loads each component's `component.php` in order to render the
> page — so editors can build pages without touching code.

---

## 🚀 Getting Started (Local Development)

> [!IMPORTANT]
> This is a work-in-progress project. There is no automated installer yet, and the database schema
> is not versioned in the repo. The steps below describe the general setup; expect to adapt them.

### Prerequisites
- PHP 7.4+ with PDO MySQL extension
- MySQL / MariaDB
- Apache with `mod_rewrite` (or a compatible stack such as XAMPP / Laragon / Docker)
- Composer (for the API's dependencies, e.g. PHPMailer)

### Setup
1. **Clone the repository**
   ```bash
   git clone https://github.com/GhaffariZ/Maxa-charity.git
   cd Maxa-charity
   ```

2. **Point your web server's document root** at `public_html/`.

3. **Create the database** and configure credentials. The app expects a config file (git-ignored) —
   create `public_html/config/database.php` (and/or `config.php`) returning a PDO connection
   matching the references in `core/database.php` and the API's `Core/Config.php`.

4. **Install API dependencies** (if working on the API):
   ```bash
   cd public_html/api
   composer install
   ```

5. **Set up the schema.** Tables include (at least) `pages`, `news`, `branches`, `campaigns`,
   `donations`, `users`, and course-related tables. A schema export is **not yet committed** —
   coordinate with the maintainer for the current SQL dump.

6. **Visit the site** at your local host. The home page renders from the `home` page record's
   components.

---

## 🗺️ Project Status

This README reflects an **in-progress** build. Rough state of the major areas:

| Module                       | Status            |
| ---------------------------- | ----------------- |
| Public site & page builder   | 🟡 In progress    |
| Component library            | 🟢 Mostly built   |
| News & stories               | 🟡 In progress    |
| Donations & campaigns        | 🟡 In progress    |
| Courses (LMS)                | 🟡 In progress    |
| Admin dashboard              | 🟡 In progress    |
| REST API (auth/donations)    | 🟡 In progress    |
| Documentation & installer    | 🔴 Not started    |

🟢 working · 🟡 partial / under active development · 🔴 planned

### Roadmap (high level)
- [ ] Commit a versioned database schema / migrations
- [ ] Provide an `.env.example` / config template
- [ ] Complete the donation payment-gateway integration
- [ ] Finish the LMS (enrollment, progress, certificates)
- [ ] Harden and document the REST API
- [ ] Add automated tests and CI

---

## 🤝 Contributing

This is a private, in-development project. If you're a collaborator:
1. Create a feature branch from `main` (`Name/feature-description`).
2. Keep secrets out of git (see `.gitignore`).
3. Open a pull request for review.

---

## 📜 License

Proprietary — © MACSA Charity. All rights reserved.
Not licensed for public reuse or redistribution at this time.

---

<div align="center">
  <sub>Built with ❤️ to support cancer patients and their families.</sub>
</div>
