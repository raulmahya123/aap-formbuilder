# Mandala UI system

## Direction

White workspace and surfaces, dark text, solid blue primary actions, and restrained Mandala brown identity/navigation. Secondary buttons use white with neutral borders and dark text. Destructive actions use red; approval and activation use green; warnings use orange. Product screens use solid fills without gradients.

| Token | Value | Purpose |
| --- | --- | --- |
| Primary Dark | `#18181B` | Text and headings |
| Background / Surface | `#FFFFFF` | Workspace, cards, tables, modals |
| Neutral Surface | `#F4F4F5` | Secondary areas and table headings |
| Border | `#D4D4D8` | Component borders |
| Brown | `#713F12` | Identity and active navigation |
| Blue | `#1D4ED8` | Primary actions, edit, links, focus |
| Red | `#DC2626` | Destructive actions and critical status |
| Green | `#15803D` | Success, activation and approval |
| Orange | `#C2410C` | Warning and pending |
| Gray | `#52525B` | Secondary text |

White text on the specified red meets AA at 4.83:1. Small red text on the neutral surface needs the darker `--danger-text: #B91C1C` (5.89:1); the required red token remains unchanged. Inputs use a stronger border to meet the 3:1 control-boundary requirement. Pale semantic utility backgrounds resolve to the neutral surface, and shared status badges use solid semantic colors with white text. Cards and tables remain neutral.

## Sources of truth

- `resources/css/design-system.css`: semantic tokens, shared components, shell, responsive rules and focus states.
- `resources/css/foundations.css`: Tailwind entry and existing utility helpers.
- `resources/css/app.css`: ordered imports; system styles follow utilities.
- `resources/css/typography.css`: Inter font face and shared type scale.
- `resources/css/spacing.css`: spacing scale, component dimensions, responsive page padding, table rows and modal geometry.
- `resources/css/page-header.css`: shared page header alignment, description limits, action layout and breadcrumb position.
- `resources/fonts/InterVariable.woff2`: bundled Inter variable font from the official rsms/inter repository, with its license in `resources/fonts/LICENSE-Inter.txt`. Vite versions the font asset; app and guest layouts preload the same URL without third-party font requests.
- `resources/views/components/ui/`: shared Blade primitives.

Use `x-ui.page` for a page container, `x-ui.heading` for its title, `x-ui.panel` for a surface, `x-ui.table` for an independently scrollable table, and `x-ui.button` / `x-ui.link` for actions. Button variants are `primary`, `secondary`, `danger`, `success`, `warning`, and `ghost`. Use `x-ui.badge` with `neutral`, `info`, `success`, `warning`, or `danger` for statuses. Existing Breeze button components delegate to these primitives.

Keep layout utilities on components. Avoid adding background, border, typography, padding or hover utilities to actions: the variant owns their appearance. Preserve action URLs, methods, names, values, permissions and validation directives. Native Alpine bindings on Blade components require the escaped prefix (`::disabled`, `::class`); PHP bindings use one colon (`:disabled="$readOnly"`). Conditional PHP attributes must use a bound attribute rather than an inline `@if` inside the opening component tag.

Do not wrap dynamically positioned editor canvas elements in panel components. Their geometry and selection styling belong to the editor. PDF and email templates retain their separate output styling.

## Typography

Inter is the only UI font. Page, section, and card headings use shared `x-ui.heading`, `x-ui.section-title`, and `x-ui.card-title` components. Serif utilities resolve to the same font for compatibility; product markup no longer requests a serif face. Decorative uppercase transforms and oversized text utilities were removed from product headings and labels. Acronyms and user content retain their original case.

| Role | Size | Weight |
| --- | --- | --- |
| Page title | 24px | 600 |
| Section title | 18px | 600 |
| Card title | 15px | 600 |
| Body | 14px | 400 |
| Table header | 12px | 600 |
| Table cell | 13px | 400 |
| Button | 13px | 500 |
| Form label | 13px | 500 |
| Helper | 12px | 400 |
| Breadcrumb | 13px | 400 |

Document content retains the editor's existing font choices and layout settings. Daily-note description text retains the user's explicit text-size control, with a 14px default; its table header follows the fixed 12px standard.

## Spacing and layout

The spacing scale is 4/8/12/16/20/24/32px. Desktop/tablet page padding is 24px; small screens use 16px. Sections use 20px gaps and cards use 20px padding. Single-line inputs and action buttons are 40px high; multiline textareas and listboxes retain usable expanded heights. Data rows are at least 48px and grow for wrapped text, controls or images.

Component radius is 8px; modal radius is 12px. Avatar circles remain circular. The full sidebar is 256px wide and its collapsed rail is 80px. Cards use borders without decorative shadows; overlays use a restrained shadow.

Use `:padded="false"` on `x-ui.panel` for a flush table container or a modal with separately padded header/body/footer. Standard panel padding is supplied by the shared component; do not add local responsive padding classes. Page padding belongs to the layout, not nested page wrappers. Explicit grid/flex page arrangements retain their responsive behavior. Editor canvas dimensions and document layout data remain independent of UI spacing.

## Migration coverage

All 75 app/guest pages use the same compositional header:

```blade
<x-ui.page-header>
    <div class="ui-page-header__copy">
        <x-ui.page-title>Departments</x-ui.page-title>
        <x-ui.page-description>Kelola daftar department dan anggotanya.</x-ui.page-description>
    </div>
    <x-ui.page-actions>
        @can('create', App\Models\Department::class)
            <x-ui.link variant="primary" href="{{ route('admin.departments.create') }}">+ Tambah Department</x-ui.link>
        @endcan
    </x-ui.page-actions>
</x-ui.page-header>
```

PageHeader renders a transparent header with an 80px minimum content row, left-aligned Inter title/description, and right-aligned actions. Descriptions are limited to two lines. Long titles truncate with their full text available in the title attribute. Narrow screens stack the copy above right-aligned actions. Dashboard analytics remain independent of this header.

Breadcrumbs render above the title row through `x-ui.breadcrumbs`; provide a `breadcrumbs` slot or the existing Blade `breadcrumbs` section. Legacy `actions` sections render through PageActions rather than the global topbar. GET search/filter forms live below the header. POST workflow actions retain their policies, CSRF tokens, routes and methods. Safe native submit actions on 13 form pages use the HTML `form` attribute to submit the original form from the header; inputs, required validation and submit listeners stay on that form.

Decorative title panels were removed from the dashboard, public wellness, confirmation and access-denied pages. Company identity metadata and daily-note context remain in the page body. The QA conversation header sits above its content card and sidebar.

Shared primitives are used across administration (dashboard, companies, departments, sites, access, documents, document templates, form builder, entries, HIPO, wellness, CCM, QA, contracts, indicators and reports), user workflows (daily input, notes, HIPO and contracts), public forms, profile and authentication. Login and registration now share the guest layout and local Vite assets. Layout permissions, routes, controllers, API endpoints and database schemas were not changed.

The authenticated shell has one shared desktop/mobile navigation tree, a sticky desktop sidebar, compact topbar, keyboard skip link, Escape dismissal for the mobile drawer and a single Alpine installation. Tables retain native table semantics and a keyboard-focusable horizontal scroll region. Existing native form controls retain their names, required flags and validation.

## Validation

- `npm run build`: passed.
- `php artisan view:cache`: passed.
- `php scripts/check-ui.php`: all 135 templates compile and parse; login, registration and public wellness render successfully.
- `node scripts/check-ui-colors.mjs`: supported text/background pairs meet WCAG AA; input boundaries meet 3:1. This is a token-level check, not full accessibility certification of every page.
- `php scripts/preview-actions.php`: rendered action preview, verified 4-action inline threshold and overflow trigger.

During compilation, pre-existing invalid PHP was corrected in form rendering (namespace imports inside conditional blocks) and the monthly report (an invalid empty-object expression). No business calculation was changed.

## Phase 3: Table Actions & Standardized Buttons

- **Primary Button**: `#1D4ED8` background, white text, 8px radius, 40px height, hover darker blue `#1E40AF`. Used with plus icon for create actions.
- **Secondary Button**: White `#FFFFFF` background, `#D4D4D8` border, `#18181B` text.
- **Danger Button**: `#DC2626` background, white text, 8px radius. Used for destructive confirmation.
- **Success Button**: `#15803D` background, white text, 8px radius. Used for approval/activation.
- **Action Icons (34×34px, 8px radius, 16px Lucide icon)**:
  - View (`eye` / black `#18181B`)
  - Edit (`pencil` / blue `#1D4ED8`)
  - Delete (`trash-2` / red `#DC2626`)
  - Members (`users` / black `#18181B`)
  - Activate (`circle-check` / green `#15803D`)
  - Deactivate (`ban` / orange `#C2410C`)
  - Download (`download` / blue `#1D4ED8`)
  - Upload (`upload` / blue `#1D4ED8`)
  - Reset password (`key-round` / orange `#C2410C`)
  - More actions (`ellipsis` / gray `#52525B`)
  - Duplicate (`copy` / blue `#1D4ED8`)
  - Assign (`user-round-plus` / blue `#1D4ED8`)
  - History (`history` / gray `#52525B`)
- Up to 4 actions render inline; 5+ automatically collapse into an overflow dropdown menu (`x-ui.action-menu`).
- All text-based buttons ("Edit", "Hapus", "Detail", "Members") have been removed from table action columns across all modules.

## Phase 4: Global Data Table System

A reusable, unified data table suite for all modules:
- `<x-ui.data-table>`: Outer container with toolbar, content slot, and pagination.
- `<x-ui.table>`: Standardized table with `#F4F4F5` header, uppercase 12px/600 text, 48px min-height rows, neutral hover, and horizontal scrolling (`.ui-table-scroll`).
- `<x-ui.table-search>`: Integrated search input with Lucide search icon, 400ms debounce, clear button (`x`), and form auto-submit.
- `<x-ui.table-filter>`: Standardized filter dropdowns for Site, Company, Status, Department, Role, etc.
- `<x-ui.table-pagination>`: Server-side pagination with item count ("Menampilkan 1–10 dari 150 data"), rows-per-page selector (10, 25, 50, 100), page numbers with ellipsis window, and previous/next navigation.
- `<x-ui.table-skeleton>`: Reusable skeleton placeholder rows for async loading states.
- `<x-ui.table-empty-state>`: Empty state display with icon, title, message, and action link.
- `<x-ui.table-error-state>`: Error state display with warning icon and retry message.
- `<x-ui.status-badge>`: Shared status badges for consistent status indication across tables.

## Phase 5: Standard Modal System

Unified modal dialogs built on native HTML `<dialog>` with standard styling (`resources/css/modal.css`) and responsive bottom-sheet behavior on mobile (screen width ≤ 640px):

- **`<x-ui.modal>`**: Flexible base dialog supporting sizes `sm` (420px), `md` (580px), `lg` (760px), `xl` (980px).
  - Header: Icon semantik + title + description + close button (`x`).
  - Body: Scrollable area with standard form field styling (`.ui-form-group`, `.ui-form-label`, `.ui-form-input`, `.ui-form-error`, `.ui-upload-area`).
  - Footer: Right-aligned buttons ([Batal] [Simpan Data]).
- **`<x-ui.modal-form>`**: Standardized Create & Update modal container:
  - Form wrapper with automatic `@csrf`, `@method`, and configurable HTTP action.
  - Sticky header & footer with dirty change check (`data-dirty-check="true"`) preventing accidental dismissal.
  - Submit button with spinner loading state and double-submission protection (`data-ui-submitting="true"`).
- **`<x-ui.modal-delete>`**: Standardized delete confirmation modal:
  - Red trash icon (`#DC2626`) with light red badge background (`#FEE2E2`).
  - Specific entity extraction from confirmation trigger (e.g., "Hapus Department?", "Department Engineering akan dihapus").
  - Warning banner: "Tindakan ini tidak dapat dibatalkan."
  - Solid red delete button (`#DC2626`) with loading feedback on submission.
- **`<x-ui.modal-detail>`**: Standardized detail/view modal:
  - Clean sections using `<x-ui.detail-section>` and `<x-ui.detail-row>` for strict label/value alignment (label 160px font-medium gray, value font-normal text-primary).
  - Support for attachment and image preview slots.
  - Action footer with Close button and optional policy-gated Edit action link.
- **`<x-ui.action-dialogs>`**: Global layout provider including universal confirmation and password-reset modal instances across the authenticated experience.

## Phase 6: Shared feedback

- One `x-ui.toast-provider` in application, guest and public HTML layouts; old per-page flash duplicates removed. Four semantic types, five-second normal lifetime paused on hover/focus, persistent errors, dismiss controls and live regions. Exception/SQL/credential diagnostics are sanitized through `App\Support\Feedback`. AJAX callers can dispatch `ui:toast`; Axios and builder saves report safe failures.
- Shared form-field, form-label, form-input, form-select, form-textarea, form-error and form-helper-text. Existing input-label/text-input/input-error delegate to these. Public wellness fields use the complete field composition. Global validation connects inline errors and native constraints, focuses the first visible invalid field, clears locally validated errors, and isolates named Laravel error bags. Server-only business errors remain until the server validates a resubmission. Backend rules are unchanged.
- Page/card/table/form/dashboard/detail skeletons have deterministic dimensions and reduced-motion support. Server-rendered pages do not introduce artificial loading delays. Skeletons are available to future async sections; existing table skeleton shares the same primitives.
- Twenty table empty branches now use a shared empty state. Search, active filters and initial empty states differ; existing policy-gated CTAs are retained. Shared error state covers API/network/401/403/404/422/500/503 with recovery. HTTP error views avoid raw exception text.
- Global site selection now feeds Dashboard, Wellness, HIPO, CCM and Input Harian queries; permissions remain checked. Wellness summary and rows share the same authorized filter. Switching sites removes stale local site/page filters; the header selector is usable at mobile widths. Contracts/documents have no site relationship in their current domain and retain their own ACL rules.

Validation: 8 targeted tests / 30 assertions; Blade syntax and public-view rendering checks; production build; browser review of inline validation, error clearing, radio-group errors, notification dismissal and persistent error lifetime, with no console errors or desktop overflow. Authenticated role-by-role CRUD and full mobile visual regression are still separate QA work; this is not a claim that all redesign phases are complete.
