# Meghna Clinic and Nursing Home — Hospital Management System (HMS)

[![Laravel 12](https://img.shields.io/badge/Laravel-12.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![PHP 8.2+](https://img.shields.io/badge/PHP-8.2%2B-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
[![MySQL 8.0](https://img.shields.io/badge/MySQL-8.0%20(InnoDB)-4479A1?style=for-the-badge&logo=mysql&logoColor=white)](https://mysql.com)
[![Bootstrap 5](https://img.shields.io/badge/Bootstrap-5.3-7952B3?style=for-the-badge&logo=bootstrap&logoColor=white)](https://getbootstrap.com)
[![Tests Passing](https://img.shields.io/badge/Tests-336%20Passed%20(1947%20Assertions)-10B981?style=for-the-badge&logo=checkmarx&logoColor=white)](phpunit.xml)
[![Security Audited](https://img.shields.io/badge/Security-OWASP%20Hardened-087F8C?style=for-the-badge&logo=dependabot&logoColor=white)](DEPLOYMENT.md)
[![License: MIT](https://img.shields.io/badge/License-MIT-blue?style=for-the-badge)](LICENSE)

> **Meghna Clinic and Nursing Home**  
> *Location:* Shohid Minar Mor, Dinajpur, Bangladesh  
> *Facility Profile:* 10 Inpatient Beds, 14 Staff Members, 24/7 Emergency & Resident Doctors, 3-Shift Nursing Team.

---

## Table of Contents
- [1. Project Overview](#1-project-overview)
- [2. Three Integrated Subsystems](#2-three-integrated-subsystems)
- [3. Complete Feature Breakdown](#3-complete-feature-breakdown)
- [4. Role-Based Access Control (RBAC) & Demo Credentials](#4-role-based-access-control-rbac--demo-credentials)
- [5. Technology Stack](#5-technology-stack)
- [6. Architecture & Design Patterns](#6-architecture--design-patterns)
- [7. UI/UX Healthcare Design System](#7-uiux-healthcare-design-system)
- [8. Security & Data Protection](#8-security--data-protection)
- [9. How to Host & Run Live from GitHub](#9-how-to-host--run-live-from-github)
- [10. Local Development Setup](#10-local-development-setup)
- [11. Automated Testing & Quality Assurance](#11-automated-testing--quality-assurance)
- [12. Production Deployment & Rollback](#12-production-deployment--rollback)
- [13. License & Maintainers](#13-license--maintainers)

---

## 1. Project Overview

The **Meghna Clinic and Nursing Home HMS** is an enterprise-grade, production-ready Hospital Management System engineered to digitize every aspect of clinical, inpatient, diagnostic, surgical, and financial workflows in a private secondary healthcare facility in Bangladesh.

Unlike generic hospital templates, this application is custom-built with deep domain compliance for Bangladesh healthcare operations:
- **Strict Entity Separation:** Independent lifecycles for Patients, Outpatient Visits, Inpatient Admissions, Consultations, Lab Orders, OT Bookings, and Bills.
- **Dual-Clearance Discharge:** Discharge requires both Clinical Clearance (attending doctor) and Financial Settlement (billing/cashier).
- **Concurrency Protection:** Row-level database locks (`lockForUpdate`) prevent double-booking of beds and race conditions during payment processing.
- **Bilingual Receipts & Invoices:** Thermal and A4 billing printouts support both English numbers and Bengali script (Hind Siliguri font).
- **Offline/Power Outage Resilient:** Optimized lightweight server payload and fast page loads suited for regional clinic connectivity.

---

## 2. Three Integrated Subsystems

```
                                 ┌────────────────────────────────────────────────────────┐
                                 │       MEGHNA CLINIC & NURSING HOME (HMS MONOLITH)      │
                                 └───────────────────────────┬────────────────────────────┘
                                                             │
                  ┌──────────────────────────────────────────┼──────────────────────────────────────────┐
                  │                                          │                                          │
                  ▼                                          ▼                                          ▼
     ┌─────────────────────────┐                ┌─────────────────────────┐                ┌─────────────────────────┐
     │      PUBLIC PORTAL      │                │   STAFF CLINICAL PORTAL │                │    PATIENT SELF-CARE    │
     │          ( / )          │                │      ( /portal/* )      │                │       ( /patient/* )    │
     ├─────────────────────────┤                ├─────────────────────────┤                ├─────────────────────────┤
     │ • Clinic Profile        │                │ • Doctor Consultation   │                │ • Patient Registration  │
     │ • Doctor Directory      │                │ • Inpatient Admissions  │                │ • Appointment Requests  │
     │ • Services & Pricing    │                │ • Bed Board & Transfers │                │ • View Prescriptions    │
     │ • Online Appointments   │                │ • Nursing MAR & Vitals  │                │ • Download Lab Reports  │
     │ • Emergency Hotline     │                │ • Lab Orders & Results  │                │ • View Invoices & Due   │
     │ • Patient & Staff Login │                │ • OT Schedules & Notes  │                │ • Medical History       │
     │                         │                │ • Billing & Receipts    │                │ • Profile Settings      │
     │                         │                │ • Audit Logs & Reports  │                │                         │
     └─────────────────────────┘                └─────────────────────────┘                └─────────────────────────┘
```

---

## 3. Complete Feature Breakdown

| Domain | Key Capabilities & Technical Implementations | Targeted Roles |
|---|---|---|
| **Patient Registration & Verification** | Duplicate checking by Phone, National ID (NID), and Birth Certificate. Auto-generated unique patient code (`MCNH-YYYY-XXXX`). Separation of unverified self-registered web accounts from official clinical charts. | Receptionist, Administrator |
| **Outpatient (OPD) & Queue Triage** | Token assignment, department routing (Gynecology, Orthopedics, Surgery, Emergency), vital signs recording prior to doctor entry, waiting queue tracking. | Receptionist, Nurse |
| **Doctor Consultation & Digital Rx** | Chief complaints, provisional/final ICD-10 diagnoses, physical examinations, drug prescriptions with dosage, frequency, duration, and meal instructions (Bengali/English). Requisition of diagnostic lab tests with clinical justification. | Doctor, Administrator |
| **Inpatient (IPD) & Bed Management** | Real-time interactive bed visualizer (10 inpatient beds across GYN, ORTHO, SURG, EMERG wards). Atomic bed allocation using `lockForUpdate` database transactions to prevent double-booking. Bed transfer audit trails with daily auto-pro-rated bed charges. | Doctor, Nurse, Receptionist, Administrator |
| **Nursing Station (MAR & Vitals)** | **Medication Administration Record (MAR):** Dose scheduling, administered/missed/held tracking with nurse signatures.<br>**Vitals Flowsheet:** Blood pressure, pulse, SpO2, body temperature, respiratory rate monitoring with automatic high/low threshold warning badges. Nursing handover notes. | Nurse, Doctor, Administrator |
| **Diagnostic Laboratory (Internal & External)** | Full catalog of hematology, biochemistry, and serology tests. Specimen tracking (`Pending Sample` &rarr; `Processing` &rarr; `Completed`). External reference lab dispatch routing. Secure PDF/Image report upload with encrypted server-side file access control. | Lab Technician, Doctor |
| **Operation Theatre (OT) Management** | Surgical scheduling, primary surgeon and anesthesiologist assignment, pre-op verification checklist, intra-operative surgical notes, post-op recovery orders. | OT Staff, Doctor, Nurse |
| **Billing, Receipts & Cash Management** | Itemized invoice generation bundling consultation fees, lab investigation charges, surgical packages, and automated daily bed rent. Multi-tiered settlement: Full payment, partial advance, authorized discounts, and recorded refunds. Dual-language (English/Bengali) print receipts. | Billing Staff, Administrator |
| **Dual-Clearance Discharge Workflow** | Two-step clearance gate: **Step 1:** Attending Doctor submits Clinical Clearance & Discharge Summary &rarr; **Step 2:** Billing Officer audits ledger, collects remaining balance, and issues Financial Clearance. Patient cannot be discharged without both clearances. | Doctor, Billing Staff, Administrator |
| **Audit Logging & System Security** | Immutable logging of user logins, role modifications, record mutations, report downloads, and financial adjustments. Sensitive data masked. Role-appropriate dashboard notifications. | Administrator, Super Admin |

---

## 4. Role-Based Access Control (RBAC) & Demo Credentials

The system provides 9 distinct role tiers enforcing strict multi-layer server authorization. All pre-seeded accounts are active and ready for testing:

> **Universal Demo Password for All Accounts:** `Meghna@2026!`

| Role Name | Demo Email | Designation / Responsibilities | Accessible Portal Route |
|---|---|---|---|
| **Super Administrator** | `superadmin@meghnaclinic.com` | Managing Director; unrestricted access to all modules, financial audits, settings, and database backups. | `/portal/admin/*` |
| **Hospital Administrator** | `admin@meghnaclinic.com` | Daily clinic oversight, staff management, department metrics, revenue summaries, and discount approvals. | `/portal/admin/*` |
| **Senior Doctor (Gynecology)** | `doctor@meghnaclinic.com` | Senior Consultant; clinical consultations, digital prescriptions, lab orders, OT surgeries, and clinical discharges. | `/portal/doctor/*` |
| **Consultant Doctor (Ortho)** | `doctor.ortho@meghnaclinic.com` | Orthopedic Surgeon; trauma consultation, surgical notes, inpatient rounds. | `/portal/doctor/*` |
| **Emergency Resident Doctor** | `doctor.emerg@meghnaclinic.com` | 24/7 Emergency Triage, acute inpatient admissions, resuscitation orders. | `/portal/doctor/*` |
| **Senior In-Charge Nurse** | `nurse@meghnaclinic.com` | Inpatient surveillance, Medication Administration Record (MAR), vital sign tracking, and bedside care. | `/portal/nurse/*` |
| **Front Desk Receptionist** | `reception@meghnaclinic.com` | Patient registration, appointment scheduling, OPD token issuance, and visitor inquiries. | `/portal/reception/*` |
| **Billing & Cashier Officer** | `billing@meghnaclinic.com` | Invoice generation, collecting payments, issuing money receipts, managing discounts, and financial clearances. | `/portal/billing/*` |
| **Medical Lab Technologist** | `lab@meghnaclinic.com` | Specimen accessioning, diagnostic test processing, reference range validation, and report file uploads. | `/portal/lab/*` |
| **OT & Ward Attendant** | `otward@meghnaclinic.com` | Operating theatre preparation, sterilization tracking, and bed availability maintenance. | `/portal/ot-ward/*` |
| **Verified Patient (Self-Care)** | `patient@meghnaclinic.com` | Verified patient; access personal prescriptions, appointments, bills, and download official lab reports. | `/patient/dashboard` |
| **Unlinked New Patient** | `newpatient@meghnaclinic.com` | Self-registered online patient awaiting clinic identity verification. | `/patient/dashboard` |

---

## 5. Technology Stack

- **Backend:** PHP 8.2+, Laravel 12 (Monolithic Architecture)
- **Database Engine:** MySQL 8.0+ (InnoDB Engine, `utf8mb4_unicode_ci`, Foreign Key Cascading, Row-Level Concurrency Locking)
- **Frontend Framework:** Pure Bootstrap 5.3, Blade Components, HTML5, Vanilla JavaScript (Strictly zero heavy Node/React/Vue dependencies for high performance)
- **Asset Bundler:** Vite 6 (Pre-compiled production bundles)
- **Typography:** Inter (for numeric precision, clinical charts) & Hind Siliguri (for native Bengali receipts and notes)
- **Testing Framework:** PHPUnit 11 with Laravel Feature & Unit test suites
- **Web Server Compatibility:** Nginx / Apache on Linux Ubuntu 22.04/24.04 LTS or XAMPP on Windows

---

## 6. Architecture & Design Patterns

```
app/
├── Http/
│   ├── Controllers/          # Thin controllers: Request/Response orchestration only
│   │   ├── Admin/            # Staff management, reports, audit logs
│   │   ├── Auth/             # Login, registration, password resets
│   │   ├── Doctor/           # Prescriptions, consultations, discharges
│   │   ├── Nurse/            # MAR logs, vitals flowsheets
│   │   ├── Patient/          # Self-care portal controllers
│   │   ├── Staff/            # Shared reception, billing, and clinical endpoints
│   │   └── Public/           # Landing page, doctor directory, appointment form
│   ├── Requests/             # Dedicated FormRequest classes enforcing validation & RBAC
│   └── Middleware/           # Role verification, session security, force HTTPS
├── Models/                   # Eloquent models with strict casts, relationships & scopes
├── Services/                 # Reusable Business Logic Layer
│   ├── AdmissionService.php  # Atomic bed allocations & transfers
│   ├── BillingService.php    # Daily bed rent auto-calculator, receipt balancing
│   ├── LabOrderService.php   # Test requisition, internal/external assignment
│   └── PatientService.php    # ID generator (MCNH-YYYY-XXXX), duplicate detection
└── Providers/                # AppServiceProvider, AuthServiceProvider, RouteServiceProvider
```

---

## 7. UI/UX Healthcare Design System

The visual design is engineered around a modern, distraction-free **Healthcare Bento Grid System**:
- **Primary Navy (`#123B5D`):** Professional authority and high-contrast structural framing.
- **Medical Teal (`#087F8C`):** Clinical accents, active status indicators, and primary action triggers.
- **Soft Background (`#F4F7FA`):** Low ocular fatigue for doctors and nurses on long 8-hour shifts.
- **Dark Slate Text (`#263445`):** Optimal readability across low-cost hospital monitors and mobile devices.
- **Status Codes:**
  - `Success (#10B981)`: Paid bills, available beds, completed lab tests.
  - `Warning (#F59E0B)`: Partial payments, pending samples, abnormal vitals.
  - `Danger (#EF4444)`: Critical vitals alert, unpaid discharges, emergency admissions.
  - `Info (#3B82F6)`: Scheduled appointments, active admissions.

---

## 8. Security & Data Protection

- **Horizontal & Vertical Privilege Escalation Protection:** All patient documents and clinical notes are gated behind Laravel Policies (`authorizeReportAccess`). Altering URL parameters (`/patient/reports/{id}`) produces a `403 Forbidden` for unauthorized users.
- **Secure File Storage:** Medical reports and sensitive records are stored in a non-public directory (`storage/app/secure_medical_reports/`) completely inaccessible from the web root. Files are streamed exclusively through authenticated controller methods.
- **SQL Injection Prevention:** 100% parameter-bound queries via Laravel Eloquent ORM; no unescaped raw SQL concatenations.
- **Session Security:** Configured with `HttpOnly`, `SameSite=Lax`, and `Secure` cookie flags.
- **Audit Trails:** Every clinical diagnosis, prescription update, payment receipt, and discharge approval records the responsible employee ID, client IP address, and timestamp.

---

## 9. How to Host & Run Live from GitHub

> ⚠️ **Important Technical Note on GitHub Pages:**  
> GitHub Pages is designed **strictly for static websites (HTML/CSS/JS only)**. Because this Hospital Management System requires a **PHP 8.2+ runtime** and an active **MySQL Database** to process real-time patient registrations, doctor prescriptions, and live billing records, it cannot run directly inside GitHub Pages alone.
>
> However, you can **host this application 100% live for free or low-cost using your GitHub repository** with cloud platforms that automatically connect to GitHub!

### Option A: Free Cloud Deployment via GitHub (Render / Railway / Koyeb)

You can connect your GitHub repository to **[Render.com](https://render.com)** or **[Railway.app](https://railway.app)** in less than 5 minutes. Every time you push code to GitHub, the live website automatically updates!

1. **Fork or Push** this repository to your personal/organization GitHub account.
2. Sign up at **[Render.com](https://dashboard.render.com/)** (Free tier available).
3. Click **New +** &rarr; **Web Service** &rarr; Select your GitHub repository `Hospital_Management_System`.
4. Configure the service settings:
   - **Environment:** `PHP` or `Docker`
   - **Build Command:**
     ```bash
     composer install --no-dev --optimize-autoloader && npm install && npm run build && php artisan config:cache && php artisan route:cache && php artisan view:cache
     ```
   - **Start Command:**
     ```bash
     php artisan migrate --force && apache2-foreground # or php -S 0.0.0.0:10000 -t public
     ```
5. Click **New +** &rarr; **PostgreSQL** or **MySQL Database** on Render, and copy the database credentials.
6. In your Render Web Service, add Environment Variables:
   - `APP_ENV=production`
   - `APP_DEBUG=false`
   - `APP_KEY=` *(Generate using `php artisan key:generate --show`)*
   - `DB_CONNECTION=mysql`
   - `DB_HOST=your-cloud-db-host`
   - `DB_DATABASE=your-cloud-db-name`
   - `DB_USERNAME=your-cloud-db-user`
   - `DB_PASSWORD=your-cloud-db-password`
7. Click **Create Web Service**. Your hospital system will now be live with a public URL (e.g., `https://meghna-clinic.onrender.com`), with a working real-time database where visitors can register and log in!

### Option B: Linux VPS Deployment (Production Standard)

For a real clinic setup on a dedicated Linux VPS (Ubuntu 22.04/24.04 with Nginx and MySQL):
- Follow our comprehensive production guide: [`DEPLOYMENT.md`](DEPLOYMENT.md).
- Includes ready-to-use Nginx VirtualHost configurations, automated midnight database backup scripts (`backup-mcnh.sh`), Supervisor queue workers, and zero-downtime deployment scripts (`deploy.sh`).

---

## 10. Local Development Setup

To run this application on your local machine using **XAMPP** or **Docker**:

### Prerequisites
- **PHP:** `^8.2` with extensions `bcmath`, `ctype`, `cURL`, `DOM`, `fileinfo`, `JSON`, `mbstring`, `OpenSSL`, `PDO`, `pdo_mysql`, `tokenizer`, `XML`
- **Composer:** `^2.x`
- **Node.js & npm:** Node `^20.x` & npm `^10.x`
- **Database:** MySQL `^8.0` (or MariaDB `^10.4` via XAMPP)

### Step-by-Step Installation

```bash
# 1. Clone the repository
git clone https://github.com/MdSadiarRahman/Hospital_Management_System.git
cd Hospital_Management_System

# 2. Install PHP dependencies
composer install

# 3. Install JavaScript dependencies
npm install

# 4. Create environment file
cp .env.example .env

# 5. Generate secure application key
php artisan key:generate

# 6. Configure database in .env
# Open .env and set your local MySQL credentials:
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=hospital_management_system
# DB_USERNAME=root
# DB_PASSWORD=

# 7. Create database & run migrations with seed data
# (This seeds all 10 beds, doctors, nurses, test catalogs, and demo patients)
php artisan migrate:fresh --seed

# 8. Create storage symlink
php artisan storage:link

# 9. Compile frontend assets
npm run build

# 10. Start the local server
php artisan serve
```

Now open [http://127.0.0.1:8000](http://127.0.0.1:8000) in your browser. Use any of the credentials from the [Demo Accounts Table](#4-role-based-access-control-rbac--demo-credentials) to test different roles.

---

## 11. Automated Testing & Quality Assurance

The application features a comprehensive test suite guaranteeing zero regressions across all critical workflows:

```bash
# Run the entire test suite
php artisan test
```

### Test Coverage Highlights:
- **336 Tests & 1,947 Assertions (100% Passing)**
- **Patient Registration & Duplicate Detection:** Verifies NID, phone number uniqueness, and patient code generation.
- **Atomic Bed Allocation:** Concurrency tests validating `lockForUpdate` prevents simultaneous booking of the same bed.
- **Dual-Clearance Discharge:** Asserts that neither an unpaid bill nor an unapproved clinical discharge can bypass patient release.
- **RBAC Security Boundaries:** Tests all 9 roles against every route to verify unauthorized access yields `403 Forbidden` or `302 Redirect`.
- **Financial Balances:** Verifies partial payment math, discount authorizations, and daily bed pro-rata charges.

---

## 12. Production Deployment & Rollback

For enterprise production rollout, review:
- **[DEPLOYMENT.md](DEPLOYMENT.md):** 17-point production server setup, security hardening, Let's Encrypt SSL, Nginx VirtualHost, automated midnight backup cron jobs, and rollback procedures.
- **[.env.production.example](.env.production.example):** Pre-configured template for production environment variables.

---

## 13. License & Maintainers

- **License:** Open-source software released under the [MIT License](LICENSE).
- **Institution:** Meghna Clinic and Nursing Home, Dinajpur, Bangladesh.
- **Project Repository:** [https://github.com/MdSadiarRahman/Hospital_Management_System](https://github.com/MdSadiarRahman/Hospital_Management_System)
