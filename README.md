# Laravel Admin Panel Boilerplate

Boilerplate admin panel reusable berbasis Laravel, Livewire, dan Tailwind CSS. Dirancang sebagai fondasi untuk berbagai jenis aplikasi CMS dengan sistem autentikasi, RBAC, audit trail, dan monitoring built-in.

## Stack Teknologi

| Layer | Teknologi |
|-------|-----------|
| Backend | Laravel 13.x |
| CMS UI | Livewire 3.x |
| CSS | Tailwind CSS 3.x |
| Auth | Laravel Breeze 2.4 (Livewire stack) |
| RBAC | Spatie Laravel Permission 7.x |
| Excel Export | PhpSpreadsheet 3.x |
| Rich Text | Trix Editor |
| Database | MySQL 8.x (local) / SQLite (cloud) |

## Fitur

### Publik
- **Berita** — Artikel dengan filter kategori dan arsip tahunan

### Admin CMS
- **Dashboard** — Ringkasan statistik dan aktivitas terakhir
- **Berita** — CRUD berita dengan Trix Editor, kategori, tag, dan thumbnail
- **Kategori & Tags** — Master data polymorphic (dapat digunakan untuk modul lain di masa depan)
- **Manajemen Pengguna** — Users & Roles dengan permission-based access control
- **Pengaturan** — Konfigurasi situs dan moderasi konten
- **Monitoring** — Audit log, system log, email tester, queue monitor, schedule tasks (super-admin only)
- **Profil Pengguna** — Upload foto avatar dan pengaturan akun

## Persyaratan

- PHP ^8.3
- MySQL 8.x (pengembangan lokal)
- Node.js ^20
- Composer

## Instalasi Lokal (MySQL)

```bash
# Clone repository
git clone <repo-url>
cd admin-panel

# Install dependencies
composer install
npm install

# Environment
cp .env.example .env
php artisan key:generate

# Konfigurasi database MySQL di .env, lalu:
php artisan migrate --seed

# Build assets
npm run build

# Jalankan server
composer dev
```

Setelah seeding, login admin default:
- Email: `admin@mail.com`
- Password: `password`

## Deploy ke Laravel Cloud (SQLite)

```bash
# Build
composer install
npm install
npm run build

# Deploy — auto-create SQLite, migrate, dan cache
composer deploy
```

Konfigurasi environment Laravel Cloud (`.env`):
```
DB_CONNECTION=sqlite
# DB_DATABASE akan default ke database/database.sqlite
```

Pada saat `composer install`, file `database/database.sqlite` akan dibuat otomatis sebelum `artisan package:discover` dijalankan, sehingga build tidak gagal meskipun file belum ada di repository.

## Perintah yang Sering Digunakan

```bash
# Development (serve + queue + logs + vite)
composer dev

# Deploy (migrate + cache untuk production)
composer deploy

# Run tests
php artisan test

# Build production assets
npm run build

# Fresh database dengan seeders
php artisan migrate:fresh --seed

# Lint PHP
vendor/bin/pint
```

## Struktur Aplikasi

```
app/
  Helpers/                   # DatabaseHelper, SeoHelper, dll
  Http/Controllers/Public/   # Controller halaman publik
  Livewire/Admin/            # Komponen CMS admin
  Models/                    # Eloquent models dengan SoftDeletes + HasAuditTrail
  Providers/                 # AppServiceProvider (site settings dynamic config)
  Traits/                    # HasAuditTrail, HasSlug, HasCategory, HasTags

resources/views/
  pages/                     # View halaman publik
  livewire/admin/            # View komponen Livewire
  layouts/                   # Layout publik & admin
  components/admin/          # Sidebar, navbar, dll

database/
  migrations/                # Semua tabel dengan timestamps + soft deletes
  seeders/                   # Seeder dengan data sample
```

## Hak Akses (RBAC)

| Peran | Akses |
|-------|-------|
| `super-admin` | Full access: semua modul, manajemen user & role, pengaturan sistem, monitoring |
| `editor` | CRUD konten (berita, kategori, tags) |
| `viewer` | Read-only dashboard dan berita |

## Cross-Database Compatibility

Aplikasi dirancang agar kompatibel dengan MySQL (lokal) dan SQLite (Laravel Cloud):

- **Date functions** (`YEAR`, `MONTH`, `DAY`) menggunakan `App\Helpers\DatabaseHelper` yang otomatis memilih sintaks `YEAR()` untuk MySQL atau `strftime()` untuk SQLite.
- **Auto-create DB**: `composer install` membuat file SQLite otomatis jika belum ada.
- **Build-safe booting**: `AppServiceProvider` menggunakan `try-catch` saat membaca `site_settings` agar tidak error saat database belum tersedia pada build time.

## Lisensi

[MIT license](https://opensource.org/licenses/MIT)
