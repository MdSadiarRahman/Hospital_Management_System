# 13. UI/UX Design System Specification
**Meghna Clinic and Nursing Home, Shohid Minar Mor, Dinajpur, Bangladesh**

---

## 1. Design System Philosophy & Principles
The design system for Meghna Clinic & Nursing Home combines corporate healthcare trustworthiness with modern SaaS speed and enterprise data density.

### Approved Core Combination
1. **Minimalism:** Stripped of unnecessary decorative gradients, glossy reflections, or distracting transitions.
2. **Material Design Principles:** Purposeful elevation, semantic color coding, clear focus rings, and tactile interactive feedback.
3. **Modern SaaS Dashboard:** High information density, compact action bars, scannable KPI widgets, and sticky headers.
4. **Bento Grid Architecture:** Modular, flexible grid layouts that cluster related operational data (e.g. 10-bed live map, emergency triage metrics).
5. **Corporate Healthcare:** Clean, authoritative Navy (`#123B5D`) paired with Medical Teal (`#087F8C`) to inspire patient confidence and clinical focus.
6. **Enterprise Data-Driven UI:** Tabular figures (`tabular-nums`) for currency (৳), patient codes (`MCNH-YYYY-XXXX`), and vital signs (`BP`, `SpO2`).
7. **Accessibility-First:** Minimum 4.5:1 contrast ratio for body text (WCAG AA), minimum 44px tap targets for mobile, explicit `focus-visible` outlines.
8. **Mobile-First Responsive Design:** Full usability across smartphones (360px+), tablets (768px), and clinical desktop workstations (1200px+).

---

## 2. Color Tokens & Contrast Matrix

### Brand Palette Tokens
| Token Name | CSS Custom Property | HEX Value | Role & Usage | Contrast vs White |
|---|---|---|---|---|
| **Primary Navy** | `--hms-primary` | `#123B5D` | Dominant brand, headers, primary buttons, sidebar | 10.8:1 (AAA) |
| **Primary Hover** | `--hms-primary-hover` | `#0D2A43` | Button hover & active states | 13.5:1 (AAA) |
| **Primary Light** | `--hms-primary-light` | `#E8F0F7` | Navy badge background, selected rows | - |
| **Medical Teal** | `--hms-teal` | `#087F8C` | Key clinical actions, links, focus indicators | 4.6:1 (AA) |
| **Medical Teal Hover**| `--hms-teal-hover` | `#066772` | Teal hover & pressed state | 6.2:1 (AAA) |
| **Teal Light** | `--hms-teal-light` | `#E6F7F9` | Subtle teal chip backgrounds | - |
| **Body Canvas** | `--hms-bg-body` | `#F4F7FA` | Global background canvas | - |
| **Surface** | `--hms-bg-surface` | `#FFFFFF` | Card, modal & table background | 1:1 |
| **Dark Neutral Text**| `--hms-text-main` | `#263445` | High-contrast body text | 11.4:1 (AAA) |
| **Secondary Text** | `--hms-text-secondary` | `#475569` | Form labels, table headers, subheadings | 7.1:1 (AAA) |
| **Muted Text** | `--hms-text-muted` | `#64748B` | Helper hints, timestamps, meta badges | 4.7:1 (AA) |
| **Border Color** | `--hms-border-color` | `#E2E8F0` | Subtle card & input borders | - |

### Clinical Status & Severity Matrix
| Status Category | Semantic Meaning | Text Token | Background Token | Border Token |
|---|---|---|---|---|
| **Success** | Normal vitals, paid invoices, cleared discharge, available bed | `#065F46` | `#ECFDF5` | `#A7F3D0` |
| **Warning** | Pending clearance, partial payment, 80% bed capacity alert | `#78350F` | `#FFFBEB` | `#FDE68A` |
| **Danger** | Critical triage, walk-in emergency, overdue bill, drug allergy | `#991B1B` | `#FEF2F2` | `#FECACA` |
| **Info** | Routine follow-up, general consultation, outpatient visit | `#1E40AF` | `#EFF6FF` | `#BFDBFE` |
| **Neutral** | Draft prescription, archived log, inactive catalog item | `#334155` | `#F1F5F9` | `#CBD5E1` |

---

## 3. Typography Architecture

### Typefaces
- **Primary English & Numerical Data:** `Inter` (Google Fonts, weights 400, 500, 600, 700).
- **Bangla Text & Receipts:** `Hind Siliguri` (Google Fonts, weights 400, 500, 600, 700).
- **Clinical Monospace & Tabular:** `ui-monospace, SFMono-Regular, Consolas, monospace`.

### Type Scale & Hierarchy
| Level | Font Size | Line Height | Weight | Letter Spacing | Element / Class |
|---|---|---|---|---|---|
| **Display** | 30px / 1.875rem | 1.2 | Bold (700) | -0.02em | Hero titles |
| **Heading 1** | 24px / 1.5rem | 1.25 | Bold (700) | -0.015em | `h1`, page titles |
| **Heading 2** | 20px / 1.25rem | 1.3 | Semibold (600) | -0.01em | `h2`, section titles |
| **Heading 3** | 17px / 1.0625rem | 1.35 | Semibold (600) | 0 | `h3`, card titles |
| **Heading 4 / Label** | 14px / 0.875rem | 1.4 | Semibold (600) | 0 | `h4`, form labels |
| **Body (Base)** | 14px / 0.875rem | 1.5 | Regular (400) | 0 | `body`, paragraphs |
| **Small / Meta** | 12px / 0.75rem | 1.4 | Medium (500) | +0.02em | Badges, timestamps, hints |

### Tabular Numeric Rule
All monetary figures (`৳ 1,500.00`), clinical vitals (`120/80 mmHg`, `98.4°F`), patient identifiers (`MCNH-2026-0042`), and invoice numbers MUST use the `.tabular-nums` class or `.code-badge` utility to guarantee column alignment in financial and clinical tables.

---

## 4. Spacing, Elevation & Radii Tokens

### 4px Baseline Grid
- `--hms-space-1`: 4px (`0.25rem`)
- `--hms-space-2`: 8px (`0.5rem`)
- `--hms-space-3`: 12px (`0.75rem`)
- `--hms-space-4`: 16px (`1rem`)
- `--hms-space-5`: 20px (`1.25rem`)
- `--hms-space-6`: 24px (`1.5rem`)
- `--hms-space-8`: 32px (`2rem`)

### Border Radii
- `--hms-radius-xs`: 4px (Badges, small tags)
- `--hms-radius-sm`: 6px (Buttons, inputs)
- `--hms-radius-md`: 10px (Cards, tables)
- `--hms-radius-lg`: 14px (Modals, callouts)
- `--hms-radius-pill`: 9999px (Status pills)

### Elevation & Shadows
- `--hms-shadow-xs`: `0 1px 2px 0 rgba(18, 59, 93, 0.04)` (Card resting)
- `--hms-shadow-sm`: `0 1px 3px 0 rgba(18, 59, 93, 0.07)` (Card hover, button active)
- `--hms-shadow-md`: `0 4px 6px -1px rgba(18, 59, 93, 0.07)` (Dropdowns, popovers)
- `--hms-shadow-lg`: `0 10px 15px -3px rgba(18, 59, 93, 0.08)` (Modal dialogs)
- `--hms-shadow-focus`: `0 0 0 3px rgba(8, 127, 140, 0.25)` (Accessible Teal Focus Ring)

---

## 5. Bento Grid Layout System
The Bento Grid layout (`.bento-grid`) uses a 12-column responsive layout with standardized CSS classes:
- `.bento-col-12`: Full width (100%)
- `.bento-col-8`: Two-thirds width on desktop (tablets/mobile stack to 12)
- `.bento-col-6`: Half width (50%)
- `.bento-col-4`: One-third width (33.33%)
- `.bento-col-3`: One-quarter width (25%) - ideal for KPI metric cards

### KPI Card Specification
- **Top Bar:** Category label (uppercase 12px bold) + colored icon badge.
- **Value:** 26px bold tabular numeric metric.
- **Footer Meta:** Trend indicator or subtext.
- **Left Accent Line:** 4px vertical brand indicator line.

---

## 6. Modular Blade Component Library
All core interface elements are encapsulated in reusable Laravel Blade components under `resources/views/components/`:

| Component Tag | File Location | Key Props | Description |
|---|---|---|---|
| 1. **Primary Button** | `<x-button variant="primary">` | `resources/views/components/button.blade.php` | `variant`, `size`, `icon`, `disabled`, `type` | Dominant Navy button with hover and active states. |
| 2. **Secondary Button** | `<x-button variant="secondary">` | `resources/views/components/button.blade.php` | `variant="secondary"`, `size`, `icon` | Medical Teal action button. |
| 3. **Icon Button** | `<x-icon-button>` | `resources/views/components/icon-button.blade.php` | `icon`, `title`, `variant`, `disabled` | 36px square action button for tables and toolbars. |
| 4. **Input Field** | `<x-form-input>` | `resources/views/components/form-input.blade.php` | `name`, `label`, `type`, `placeholder`, `required`, `hint`, `error` | Accessible form input with required asterisk and validation. |
| 5. **Select Field** | `<x-form-select>` | `resources/views/components/form-select.blade.php` | `name`, `label`, `options`, `selected`, `placeholder`, `hint`, `error` | Stylized select dropdown with option support. |
| 6. **Textarea** | `<x-form-textarea>` | `resources/views/components/form-textarea.blade.php` | `name`, `label`, `rows`, `placeholder`, `required`, `hint`, `error` | Multiline clinical notes input. |
| 7. **Validation Message**| `<x-form-error>` | `resources/views/components/form-error.blade.php` | `name`, `message` | Inline error message with warning icon. |
| 8. **Status Badge** | `<x-badge>` | `resources/views/components/badge.blade.php` | `status` (success, warning, danger, info, teal, navy, neutral), `dot` | High-contrast clinical status pill with pulse dot. |
| 9. **Information Card** | `<x-card>` | `resources/views/components/card.blade.php` | `title`, `subtitle`, `icon`, `action`, `footer` | Standardized white surface card. |
| 10. **Statistics Card** | `<x-stat-card>` | `resources/views/components/stat-card.blade.php` | `label`, `value`, `icon`, `variant`, `meta`, `trend`, `trendValue` | KPI widget with trend arrow and metric number. |
| 11. **Data Table** | `<x-table>` | `resources/views/components/table.blade.php` | `headers` | Enterprise clinical table with zebra row hover. |
| 12. **Pagination** | `<x-pagination>` | `resources/views/components/pagination.blade.php` | `paginator`, `from`, `to`, `total` | Responsive pagination controls. |
| 13. **Search Field** | `<x-search-input>` | `resources/views/components/search-input.blade.php` | `name`, `placeholder`, `value`, `id` | Input with embedded search icon. |
| 14. **Filter Panel** | `<x-filter-panel>` | `resources/views/components/filter-panel.blade.php` | `title`, `action`, `resetUrl` | Collapsible clinical filter toolbar with reset button. |
| 15. **Modal** | `<x-modal>` | `resources/views/components/modal.blade.php` | `id`, `title`, `icon`, `size`, `footer` | Accessible Bootstrap modal dialog. |
| 16. **Confirm Dialog** | `<x-confirm-dialog>` | `resources/views/components/confirm-dialog.blade.php` | `id`, `title`, `message`, `confirmText`, `confirmVariant`, `icon`, `action` | Action confirmation modal with danger/warning icon. |
| 17. **Alert Message** | `<x-alert>` | `resources/views/components/alert.blade.php` | `type` (success, warning, danger, info), `dismissible` | Left-bordered clinical feedback callout. |
| 18. **Breadcrumb** | `<x-breadcrumb>` | `resources/views/components/breadcrumb.blade.php` | `items` | Accessible navigation breadcrumbs with home icon. |
| 19. **Page Header** | `<x-page-header>` | `resources/views/components/page-header.blade.php` | `title`, `subtitle`, `icon`, `breadcrumbs`, `actions` | Standard portal page title bar with action slot. |
| 20. **Empty State** | `<x-empty-state>` | `resources/views/components/empty-state.blade.php` | `icon`, `title`, `description`, `actionText`, `actionUrl` | Engaging placeholder when 0 records exist. |
| 21. **Loading State** | `<x-loading-state>` | `resources/views/components/loading-state.blade.php` | `type` (spinner, skeleton, skeleton-table), `rows`, `message` | Async skeleton pulse loader and circular spinner. |

---

## 7. Icon System
The HMS standardizes on **Bootstrap Icons (v1.11+)** compiled via Vite into `/public/build/assets/`.
- Hospital / Clinics: `bi-hospital`, `bi-heart-pulse`, `bi-stethoscope`, `bi-activity`
- Inpatients & Beds: `bi-door-open`, `bi-person-check`, `bi-clipboard2-pulse`
- Diagnostics: `bi-virus`, `bi-droplet`, `bi-file-earmark-medical`, `bi-file-earmark-pdf`
- Billing: `bi-cash-coin`, `bi-receipt`, `bi-credit-card`
- Actions: `bi-person-plus`, `bi-pencil-square`, `bi-arrow-left-right`, `bi-printer`

---

## 8. Interactive Review Showcase
The complete design system and all 21 components are available for live review at:
- **Components Demo:** [`/components`](file:///c:/xampp/htdocs/Hospital_Management_System/resources/views/components-demo.blade.php) and [`/`](file:///c:/xampp/htdocs/Hospital_Management_System/resources/views/components-demo.blade.php)
- **Design Tokens Showcase:** [`/design-system`](file:///c:/xampp/htdocs/Hospital_Management_System/resources/views/design-system/index.blade.php)
