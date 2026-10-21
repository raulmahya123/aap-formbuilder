# Phase 7 — Module implementation and verification

## Shared implementation

The existing Laravel/Blade, Tailwind and Alpine architecture is retained. Operational tables delegate to the shared data-table wrapper; paginated backend listings use `Listing::perPage()` (10/25/50/100) and the shared paginator. PDF tables remain document layouts. Headers, actions, toolbar/search, badges, feedback and modal hosts share the existing UI component library.

Create/edit links for simple resources open the actual authorized server form in one shared modal. CSRF, method, multipart uploads, validation, old input and native submission remain intact. Without JavaScript, the same links open their normal pages. Complex builders and CCM form flows retain their dedicated pages.

## Module coverage

| Module | Applied changes |
| --- | --- |
| Operational Dashboard | Shared header/KPIs, authorized site filter, data-based chart and quick access |
| Wellness | Shared KPIs/table/filters/pagination, complete detail dialog, legacy answers retained |
| Departments | Search, pagination, create/edit dialogs, icon actions and membership management |
| Mandala | Compact Company/Department/Site selectors, document tabs, search, metadata dialogs, upload/CSV/builder flows retained |
| Tanya Jawab | Search/site/status, create dialog, compact conversations, latest-message query optimization; existing replies/history retained |
| Contracts | Shared listing/upload dialog, existing preview/download and viewer ACL retained |
| Manage Users | Search/role/site/status filters, permission-aware icon actions, shared reset/confirmation dialogs |
| CCM | Site/date/search filters, shared listing/detail dialog; existing create/edit flow retained |
| HIPO / Nearmiss | Search/site/status/risk filters, risk/readiness badges, edit dialog and existing detail/delete policies |
| Sites | Search/company, shared create/edit and context-selection confirmation |
| Companies | Search/status, shared forms/dialogs/table |
| Indicator Groups | Search/pagination, repaired missing edit view and checkbox persistence |
| Indicators | Paginated searchable listing with group filter and shared edit/create forms |
| Input Harian | Added missing administrative index; shared site/period listing and pagination |
| Additional menus | Documents, templates, entries, site access, monthly reports, daily notes and front/user listings standardized through shared components |

Site selection resolves the permitted session context when no local filter is supplied. Explicit local filters remain supported. Switching context clears stale site/page query parameters. Department-admin daily listings now use assigned-site permissions instead of treating every administrator as unrestricted.

**User decision:** Sites retain the existing active session context. No lifecycle status, deactivation rule or new business restriction was introduced.

## Deployment requirement

`2026_10_21_000004_allow_legacy_wellness_answers_to_be_null.php` permits the current questionnaire to save without inventing answers for mandatory legacy mood/stress fields. Historical values remain available. This migration was tested only against isolated SQLite, and has **not** been applied to the operational database. Apply through the project's normal migration/deployment process.

## Verification

- Production asset build passed.
- 172 Blade templates compiled and syntax-checked; public login/register/wellness views rendered.
- Full automated suite: **40 tests, 188 assertions passed**. Includes 20 module index routes, department CRUD/validation, Mandala metadata save, wellness current/legacy data, site switching, unauthorized site selection and assigned-site daily/wellness listings.
- Browser review used synthetic records in a separate SQLite database: core module listings plus documents/templates/entries/reports/site access; department create and duplicate validation, Mandala dependent selectors/type, Wellness detail dialog, and a 375px embedded viewport.
- Browser console checks during core-module review reported no errors. This is not exhaustive real-device, file-upload, every-role or every-business-branch regression coverage. Existing production data and external services were not mutated.

Temporary preview authentication, router, responsive shell and synthetic database are removed after review. Screenshots use synthetic records only.
