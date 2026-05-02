# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

CMS for a village head campaign website ("Desa Sumber Makmur"). Full Laravel application with public frontend and admin CMS.

## Stack

| Layer | Technology |
|-------|-----------|
| Backend | Laravel 13.x |
| CMS UI | Livewire 3.6 |
| CSS | Tailwind CSS 3.1 |
| Auth | Laravel Breeze 2.4 (Livewire stack) |
| RBAC | Spatie Laravel Permission 7.3 |
| Excel Export | Maatwebsite Excel 3.1 |
| Rich Text | Trix Editor |
| Database | MySQL 8.x |
| Mail (dev) | Mailpit (SMTP port 1025, Web UI 8025) |

## Common Commands

```bash
# Development (runs artisan serve, queue listener, pail logs, and Vite in parallel)
composer dev

# Tests
php artisan test
# or
composer test

# Build assets
npm run build

# Development asset watch
npm run dev

# Fresh database with seeders
php artisan migrate:fresh --seed

# Lint PHP
vendor/bin/pint
```

## Architecture

### Public Frontend
- Blade SSR via controllers in `app/Http/Controllers/Public/`
- Views in `resources/views/pages/`
- Alpine.js for client-side interactivity (filters, search, toast notifications)
- No pagination — all filtering done client-side with `x-show`

### Admin CMS
- Livewire components in `app/Livewire/Admin/`
- Views in `resources/views/livewire/admin/`
- Routes prefixed `/admin`, protected by `auth` + permission middleware
- Super-admin only routes (users, roles, settings, audit logs, system logs, email tester, queue monitor) use `role:super-admin` middleware

### Auth
- Session-based via Laravel Breeze Livewire
- Three roles: `super-admin`, `editor`, `viewer`
- Viewer has read-only access; use `authorize()` in Livewire methods and `@can`/`@cannot` in Blade for gating

### Models & Traits

All main models use `SoftDeletes` and `HasAuditTrail`. Common traits:

| Trait | Purpose |
|-------|---------|
| `HasAuditTrail` | Auto-logs every create/update/delete to `audit_trails` table with user, IP, old/new values |
| `HasSlug` | Auto-generates unique slug from `title` on create/update; respects soft deletes |
| `HasCategory` | BelongsTo relationship to Category (for Post, Note, etc.) |
| `HasTags` | BelongsToMany relationship to Tag |

Models with `published` scope: `Post`, `Note`, `Report`. Published = `status = 'published'` + `published_at <= now()` + not null.

### Category System
- Categories are polymorphic by `module_type`: `post`, `note`, `program`, `report`, `aspiration`
- Unique constraints are composite: `module_type` + `slug` + `deleted_at` and `module_type` + `name` + `deleted_at`
- Validation rules scope uniqueness by `module_type`

### Site Settings
- `SiteSetting` model with key/value storage
- `SiteSetting::electedRole()` returns "Kepala Desa" or "Calon Kepala Desa" based on `is_elected` flag
- `SiteSetting::getValue('site_name')` used for page titles

## Design Tokens (Frontend)

Tailwind config (`tailwind.config.js`) defines:

- Primary: `#1A6FAA` / Primary Dark: `#124E7A` / Primary Light: `#E8F4FB`
- Secondary: `#2E7D52` / Secondary Light: `#E8F5EE`
- Accent: `#F5A623`
- Dark: `#1C2B39`
- Fonts: `Playfair Display` (headings, `font-display`) + `Nunito` (body, `font-body`)

## Naming Conventions

| Artifact | Convention | Example |
|----------|-----------|---------|
| Model | `App\Models\*` | `Post` |
| Livewire class | `App\Livewire\Admin\*` | `BeritaTable` |
| Livewire view | `resources/views/livewire/admin/*.blade.php` | `berita-table.blade.php` |
| Policy | `App\Policies\*` | `PostPolicy` |
| Trait | `App\Traits\*` | `HasAuditTrail` |
| Public controller | `App\Http\Controllers\Public\*` | `BeritaController` |

## Database Rules

1. **Timestamps & Soft Deletes:** Every main table has `created_at`, `updated_at`, `deleted_at`.
2. **Audit Trail:** Every CUD operation is auto-logged to `audit_trails`.
3. **Unique + Soft Delete:** Unique constraints on `slug` and `name` are composite with `module_type` and `deleted_at`.
4. **Unique + Update:** Unique validation must ignore the current record ID on update.
5. **Conditional Validation:** If `status == 'draft'`, skip mandatory validation except for `title`/`name`. Full validation runs only on `published`.

## Important File References

| File | Purpose |
|------|---------|
| `docs/prd.md` | Full PRD: user stories, ERD, acceptance criteria, permission matrix |
| `docs/frontend-design-system.md` | Public page design tokens, wireframes, component specs |
| `docs/backend-design-system.md` | CMS layout, Livewire component specs, admin design tokens |
| `plans/frontend-execution-plan.md` | Completed HTML prototype instructions |
| `plans/backend-execution-plan.md` | Laravel/Livewire implementation plan |
| `html/` | Static HTML prototypes for all 11 public pages (reference only) |

Always check these documents before implementing new features.

## Seeding

Realistic Indonesian village context seeders exist for all models:
- `RolePermissionSeeder` — roles, permissions, and a default super-admin user
- `SiteSettingSeeder` — site configuration
- `ProfileSeeder` — candidate profile, vision/mission, track record
- `ContentSeeder` — posts, notes, reports, programs
- `GallerySeeder` — albums and gallery items
- `AspirationSeeder` — sample aspirations
- `ProgramSeeder` — development programs

Run all via: `php artisan db:seed`

## Notes

- No factories exist — all test data goes through seeders.
- HTML from Trix editor output must be escaped with `strip_tags()` on public pages.
- `phpunit.xml` uses SQLite in-memory for testing, but no tests have been written yet.
