# SurfPan
> Surfing Competition Management Platform

## Overview

SurfPan is a SaaS platform for managing surfing competitions end-to-end. It gives surfing organizations the tools to create events, build division brackets, schedule heats, assign judges, collect wave scores in real-time, compute standings, and process entry fees — all from a single web application.

Built on the [Trongate PHP Framework](https://trongate.io) (v1), SurfPan is designed for XAMPP-based local development and Apache-hosted production deployments.

---

## Features

**Organizers**
- Create and manage competitions with multiple divisions
- Generate heat brackets automatically (single elimination, double elimination, second-chanse)
- Build and publish heat schedules
- Invite and assign judges to competitions
- Track athlete registration and entry-fee payments
- View live heat progress and final standings

**Judges**
- Dedicated judge dashboard accessible via invitation link
- Score individual waves in real-time during a heat
- View assigned heats and countdowns
- Accept or decline competition invitations

**Athletes / Participants**
- Register for competitions and divisions
- View heat draw and schedules
- Check results and final standings

**Admin**
- Manage users, organizations, and user-level assignments
- Oversee subscription plans and billing records
- Access the full Trongate admin panel

---

## Tech Stack

| Layer | Technology |
|---|---|
| Language | PHP 8+ |
| Framework | Trongate v1 (HAVC architecture) |
| Database | MySQL / MariaDB |
| Web server | Apache with `mod_rewrite` |
| Payments | EveryPay (demo and live modes) |
| Email | Brevo (transactional email via API) |
| Local dev | XAMPP |

---

## Project Structure

```
surfpan/
├── config/        # Application configuration (database, URLs, API keys, routing)
├── engine/        # Trongate framework core (do not modify)
├── modules/       # HAVC modules — all application logic lives here
├── templates/     # Page layout templates (public, judges_area, admin)
└── public/        # Publicly accessible assets (CSS, JS, images)
```

---

## Modules

| Module | Type | Responsibility |
|---|---|---|
| `competitions` | Custom | Competition records, judge login/dashboard, live heat timer |
| `heats` | Custom | Heat generation, elimination brackets, scheduling, advancement logic |
| `judges` | Custom | Judge invitations, wave scoring, organizer judge management |
| `organizations` | Custom | Organization registration, profiles, dashboards, timezone handling |
| `results` | Custom | Final standings, per-division results, public results search |
| `billings` | Custom | Subscription plans, EveryPay payment processing, event-pass credits |
| `users` | Custom | SurfPan user accounts, role assignments, password resets |
| `welcome` | Custom | Landing page, database setup guide, privacy policy, terms |
| `trongate_administrators` | Framework | Platform administrator accounts and admin panel |
| `trongate_users` | Framework | Core user records and user-level assignments |
| `trongate_tokens` | Framework | Authentication tokens |
| `trongate_security` | Framework | Route-level access control |
| `trongate_comments` | Framework | Internal comment system |
| `trongate_filezone` | Framework | File upload and management |

---

## User Roles

| Role | Description |
|---|---|
| **Administrator** | Full platform access via the Trongate admin panel. Manages all users, organizations, and subscriptions. |
| **Organizer** | Runs one or more surfing organizations. Creates competitions, manages heats, assigns judges, and views results. |
| **Judge** | Invited to competitions by an organizer. Scores waves in real-time via the judge dashboard. |
| **Participant** | Registers for competitions and divisions. Views heat draws, schedules, and results. |

---

## Database Overview

**Trongate core tables**

| Table | Description |
|---|---|
| `trongate_users` | Core user record with user-level assignment |
| `trongate_user_levels` | Role/level definitions (e.g. `admin`) |
| `trongate_administrators` | Admin panel credentials |
| `trongate_tokens` | Session and authentication tokens |
| `trongate_comments` | Internal comments |

**SurfPan domain tables**

| Table | Description |
|---|---|
| `comp_name` | Competition records (name, year, status, dates) |
| `comp_organizations` | Surfing organization profiles |
| `comp_users` | SurfPan user accounts |
| `comp_roles` | Available role definitions |
| `comp_users_roles` | User-to-role assignments |
| `comp_competition_divisions` | Divisions within a competition |
| `comp_participants` | Athletes registered for a competition |
| `comp_heats` | Individual heats (status, start/end time, round) |
| `comp_heat_participants` | Athletes assigned to a specific heat |
| `comp_heat_advancement` | Bracket advancement rules and outcomes |
| `comp_heat_results` | Final result records per heat |
| `comp_judge_scores` | Individual wave scores submitted by judges |
| `comp_wave_averages` | Computed average scores per wave |
| `comp_final_standings` | Final standings per division |
| `comp_judges` | Judge user records |
| `comp_org_judges` | Judges affiliated with an organization |
| `competition_judges` | Judge-to-competition assignments and invite status |
| `comp_password_resets` | Password reset tokens |
| `billing_products` | Subscription plan definitions |
| `billing_subscriptions` | Active organization subscriptions |
| `billing_charges` | Payment charge records |
| `billing_pass_uses` | Event-pass credit consumption log |
| `countries` | Country reference data |

---

## Getting Started (Local Development)

### Prerequisites

- PHP 8+
- MySQL or MariaDB
- Apache with `mod_rewrite` enabled
- [XAMPP](https://www.apachefriends.org/) (recommended for local dev)

### Installation

1. **Clone or copy** the project into your XAMPP `htdocs` directory:
   ```
   /Applications/XAMPP/xamppfiles/htdocs/surfpan/
   ```

2. **Create a MySQL database** via phpMyAdmin or the command line:
   ```sql
   CREATE DATABASE surfpan CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
   ```

3. **Configure the database connection** in `config/database.php`:
   ```php
   define('HOST', '127.0.0.1');
   define('PORT', '3306');
   define('USER', 'root');
   define('PASSWORD', '');
   define('DATABASE', 'surfpan');
   ```

4. **Configure the application** in `config/config.php`:
   ```php
   define('BASE_URL', 'http://localhost/surfpan/');
   define('ENV', 'dev');

   // Brevo (email)
   define('BREVO_API_KEY', 'your-brevo-api-key');

   // EveryPay — use demo credentials for local testing
   define('EVERYPAY_API_USERNAME', 'your-username');
   define('EVERYPAY_AUTH_KEY', 'your-auth-key');
   define('EVERYPAY_PAYLINK', 'https://igw-demo.every-pay.com/lp');
   define('EVERYPAY_PAYLINK_TOKEN', 'your-token');
   define('EVERYPAY_URL', 'https://igw-demo.every-pay.com/api/v4/payments/');
   ```

5. **Run the database setup** by visiting:
   ```
   http://localhost/surfpan/welcome/database_setup
   ```
   Copy the SQL from that page into phpMyAdmin and execute it to create the Trongate starter tables. Then import any additional SurfPan schema files.

6. **Visit the application:**
   ```
   http://localhost/surfpan
   ```

---

## Configuration

| File | Purpose |
|---|---|
| `config/config.php` | `BASE_URL`, environment, EveryPay credentials, Brevo API key |
| `config/database.php` | Database host, port, username, password, and database name |
| `config/custom_routing.php` | Custom URL route overrides |
| `config/themes.php` | Admin panel UI theme selection |
| `config/site_owner.php` | Site owner contact details used in emails |

---

## Third-Party Integrations

### EveryPay
Payment gateway used for entry fees and subscription purchases. Two environments are supported:

- **Demo** (`igw-demo.every-pay.com`) — for development and testing; no real charges
- **Live** (`pay.every-pay.eu`) — for production; requires live credentials

Switch between them by commenting/uncommenting the corresponding constants in `config/config.php`.

### Brevo
Transactional email provider used for organization confirmation emails, judge invitations, and password resets. Configured via `BREVO_API_KEY` in `config/config.php`.

---

## License

See `license.txt` for details.
