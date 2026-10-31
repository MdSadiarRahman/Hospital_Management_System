# AGENTS.md — Hospital Management System (HMS)
**Meghna Clinic and Nursing Home, Shohid Minar Mor, Dinajpur, Bangladesh**

---

## 1. Project Overview
This project is a real-world, production-oriented Hospital Management System (HMS) developed for **Meghna Clinic and Nursing Home**.
- **Initial Profile:** 10 inpatient beds, ~14 staff, 2 doctors 24/7 (including emergency doctor), 6 nurses (8h shifts).
- **Core Departments:** Gynecology, Orthopedic, Surgery, Emergency, Reception, Billing, OT/Ward, Cleaners.
- **Three Integrated Subsystems:**
  1. Public Website (`/`)
  2. Internal Hospital Management Portal (`/portal/*`)
  3. Patient Self-Care Portal (`/patient/*`)
- **Primary Source of Truth:** All requirements, ERD, workflows, and policies are documented under [`docs/`](file:///c:/xampp/htdocs/Hospital_Management_System/docs/).

---

## 2. Approved Technology Stack
- **Backend:** PHP 8.2+, Laravel 12
- **Frontend:** HTML5, CSS3, JavaScript, Bootstrap 5, Laravel Blade
- **Database:** MySQL (InnoDB engine, utf8mb4) via XAMPP
- **Asset Bundler:** Vite
- **Testing:** PHPUnit, Laravel Feature Tests
- **Version Control:** Git & GitHub (`hospital-management-system` branch)
- **Prohibited Technologies:** React.js, Next.js, Vue.js, Node.js backend (Express/Nest), Python (FastAPI/Django), unapproved heavy packages.

---

## 3. Approved Architecture
- **Monolithic Architecture:** Laravel 12 monolith with strict separation of Public Web, Staff Portal, and Patient Portal.
- **Layered Structure:**
  - Controllers: Handle HTTP request/response only.
  - Form Requests: Handle input validation and authorization checks.
  - Service Layer: Encapsulates complex clinical, billing, and admission workflows.
  - Policies: Enforce model-level authorization per role.
  - Eloquent Models: Normalized relations, casts, and scopes.

---

## 4. Approved UI/UX Design System
- **Brand Colors:**
  - Primary Navy: `#123B5D`
  - Medical Teal: `#087F8C`
  - Soft Background: `#F4F7FA`
  - Dark Text: `#263445`
  - Status Colors: Success (`#10B981`), Warning (`#F59E0B`), Danger (`#EF4444`), Info (`#3B82F6`)
- **Typography:** Inter (English data/numbers), Hind Siliguri (Bengali text/receipts).
- **Design Style:** Bento Grid layout, Modern SaaS Healthcare Dashboard, high contrast, accessibility-first, mobile-first responsive. No excessive decorative animations or heavy gradients.

---

## 5. Database Development Rules
- Follow the approved ERD in [`docs/05-database-er-diagram.md`](file:///c:/xampp/htdocs/Hospital_Management_System/docs/05-database-er-diagram.md) and data dictionary in [`docs/06-database-design.md`](file:///c:/xampp/htdocs/Hospital_Management_System/docs/06-database-design.md).
- Strict Entity Separation:
  - Patients from Visits
  - Visits from Admissions
  - Consultations from Diagnoses and Prescriptions
  - Test Orders from Test Reports
  - Bills from Bill Items and Payments
  - Beds from Bed Assignments
- Concurrency & Double Booking: Use database transactions (`DB::transaction`) and row locking (`lockForUpdate`) for bed assignments and payment settlements.
- Never drop or modify existing tables without explicit Project Owner approval.

---

## 6. Backend Development Rules
- Follow PSR-12 coding standards and strict typing where appropriate.
- Validate all incoming data using dedicated `FormRequest` classes.
- Place reusable business logic in `app/Services/` (e.g. `AdmissionService`, `BillingService`, `LabService`).
- Pharmacy inventory, stock, and counter sales are deferred to a future phase. Prescriptions and nursing medication records (MAR) are in current scope.

---

## 7. Frontend Development Rules
- Use pure Bootstrap 5 and modular Blade components (`<x-card>`, `<x-button>`, `<x-table>`, `<x-bento-card>`).
- Keep templates clean, semantic, and mobile-friendly.
- Ensure all interactive modals, tables, and forms have unique IDs and accessible labels.

---

## 8. Security Standards
- Enforce Role-Based Access Control (RBAC) across all 9 roles.
- Patient Privacy Rule: Self-registered patient accounts must **never** automatically link to clinical records. Staff identity verification is mandatory.
- Store diagnostic reports in non-public storage (`storage/app/secure_medical_reports/`) and stream via authorized controller only.
- Strict protection against OWASP Top 10: CSRF tokens on all mutating requests, Eloquent parameter binding (no raw unescaped SQL), Blade XSS escaping.
- Never expose `.env` credentials, API keys, or database passwords.

---

## 9. Testing Standards
- Fictional Test Data Only: Strictly forbidden to use real patient names, phone numbers, or real health histories. Use Laravel Factories with synthetic data.
- Feature Tests: Validate critical workflows (duplicate patient detection, bed concurrency, dual discharge clearance, RBAC restrictions).
- Unit Tests: Validate patient ID generators (`MCNH-YYYY-XXXX`), daily bill math, and vital sign thresholds.

---

## 10. GitHub & Version Control Workflow
- Active Branch: All development must be done on `hospital-management-system`.
- Never push directly to `main`.
- Never force push.
- Check `git status` before committing and exclude `.env` and temporary files.
- **Push Policy:** The AI agent must never push code to GitHub. Git push is handled separately by the Project Owner.

---

## 11. Documentation Maintenance Rules
- [`docs/`](file:///c:/xampp/htdocs/Hospital_Management_System/docs/) is the permanent architectural reference.
- Before starting any major task, inspect the corresponding document:
  - `01-project-requirements.md` (Requirements & scope)
  - `02-system-architecture.md` (Monolith & Design tokens)
  - `03-use-case-diagram.md` (Actor use cases)
  - `04-sequence-diagrams.md` (Clinical sequences)
  - `05-database-er-diagram.md` (ERD)
  - `06-database-design.md` (Data dictionary)
  - `07-role-permission-matrix.md` (RBAC)
  - `08-business-workflows.md` (Flowchart & Clinical loop)
  - `09-api-architecture.md` (Routes & APIs)
  - `10-security-design.md` (Privacy & Security)
  - `11-testing-strategy.md` (Testing standards)
  - `12-ai-development-workflow.md` (9-step lifecycle)
- Never make unapproved architectural changes. If requirements conflict or are unclear, ask the Project Owner.
