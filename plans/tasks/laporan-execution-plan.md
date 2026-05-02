# Execution Plan: Menu Laporan (SIRKA)

## Context
Implementasi fitur Laporan Realisasi Kegiatan & Anggaran sesuai PRD `docs/prd-sirka.md` section 6–7. Fitur ini meliputi: import Excel dengan validasi, workflow approval 2 tingkat, revisi, dan approval log.

**Stack yang tersedia:** Laravel 13, Livewire 3.6, phpoffice/phpspreadsheet 3.0 (sudah terinstall), Spatie Permission, Tailwind CSS, MySQL 8, Alpine.js.

**Tidak tersedia:** Maatwebsite Excel, WebSocket/Pusher/Reverb. Untuk Excel menggunakan `phpoffice/phpspreadsheet` langsung. Untuk F05 (realtime message) di-defer atau gunakan polling sederhana.

---

## Phase 1: Foundation & Master Data

### 1.1 Migration Master Data Laporan

**File:** `database/migrations/2026_05_03_xxxxxx_create_jenis_laporans_table.php`
```php
Schema::create('jenis_laporans', function (Blueprint $table) {
    $table->id();
    $table->string('kode', 20)->unique(); // e.g. 'REN-BULANAN', 'REN-TRIWULAN'
    $table->string('nama');
    $table->text('deskripsi')->nullable();
    $table->boolean('is_active')->default(true);
    $table->timestamps();
    $table->softDeletes();
});
```

**File:** `database/migrations/2026_05_03_xxxxxx_create_master_akuns_table.php`
```php
Schema::create('master_akuns', function (Blueprint $table) {
    $table->id();
    $table->string('kode', 20)->unique();
    $table->string('nama');
    $table->boolean('is_active')->default(true);
    $table->timestamps();
    $table->softDeletes();
});
```

**File:** `database/migrations/2026_05_03_xxxxxx_create_master_kategoris_table.php`
```php
Schema::create('master_kategoris', function (Blueprint $table) {
    $table->id();
    $table->string('kode', 20)->unique();
    $table->string('nama');
    $table->boolean('is_active')->default(true);
    $table->timestamps();
    $table->softDeletes();
});
```

**File:** `database/migrations/2026_05_03_xxxxxx_create_approval_chains_table.php`
```php
Schema::create('approval_chains', function (Blueprint $table) {
    $table->id();
    $table->foreignId('jenis_laporan_id')->constrained('jenis_laporans');
    $table->foreignId('departemen_id')->constrained('departements');
    $table->foreignId('approver_level_1_id')->constrained('users');
    $table->foreignId('approver_level_2_id')->constrained('users');
    $table->timestamps();
    $table->unique(['jenis_laporan_id', 'departemen_id']);
});
```

### 1.2 Migration Laporan Core

**File:** `database/migrations/2026_05_03_xxxxxx_create_laporans_table.php`
```php
Schema::create('laporans', function (Blueprint $table) {
    $table->id();
    $table->string('kode_laporan', 30)->unique();
    $table->string('judul_laporan');
    $table->foreignId('departemen_id')->constrained('departements');
    $table->foreignId('jenis_laporan_id')->constrained('jenis_laporans');
    $table->tinyInteger('periode_bulan');
    $table->year('periode_tahun');
    $table->enum('status', ['draft','submitted','revision','approved_1','approved_2','archived','rejected'])->default('draft');
    $table->text('catatan_pic')->nullable();
    $table->timestamp('submitted_at')->nullable();
    $table->foreignId('created_by')->constrained('users');
    $table->integer('revision_count')->default(0);
    $table->timestamps();
    $table->softDeletes();
    // Unique: satu departemen + jenis laporan + periode hanya boleh punya 1 laporan aktif
    $table->unique(['departemen_id','jenis_laporan_id','periode_bulan','periode_tahun','deleted_at'], 'laporan_period_unique');
});
```

**File:** `database/migrations/2026_05_03_xxxxxx_create_laporan_items_table.php`
```php
Schema::create('laporan_items', function (Blueprint $table) {
    $table->id();
    $table->foreignId('laporan_id')->constrained('laporans')->cascadeOnDelete();
    $table->string('kode_kegiatan', 20);
    $table->string('nama_kegiatan');
    $table->string('kode_akun', 20);
    $table->string('kode_kategori', 20);
    $table->string('satuan', 50);
    $table->decimal('volume_rencana', 15, 2);
    $table->decimal('volume_realisasi', 15, 2);
    $table->decimal('pagu_anggaran', 18, 2);
    $table->decimal('realisasi_anggaran', 18, 2);
    $table->date('tanggal_mulai');
    $table->date('tanggal_selesai')->nullable();
    $table->enum('status_kegiatan', ['selesai','berlangsung','belum_dimulai']);
    $table->text('keterangan')->nullable();
    $table->decimal('persen_realisasi_anggaran', 6, 2)->storedAs('(CASE WHEN pagu_anggaran > 0 THEN (realisasi_anggaran / pagu_anggaran) * 100 ELSE 0 END)');
    $table->decimal('persen_realisasi_volume', 6, 2)->storedAs('(CASE WHEN volume_rencana > 0 THEN (volume_realisasi / volume_rencana) * 100 ELSE 0 END)');
    $table->decimal('sisa_anggaran', 18, 2)->storedAs('(pagu_anggaran - realisasi_anggaran)');
    $table->timestamps();
});
```

**File:** `database/migrations/2026_05_03_xxxxxx_create_laporan_approval_logs_table.php`
```php
Schema::create('laporan_approval_logs', function (Blueprint $table) {
    $table->id();
    $table->foreignId('laporan_id')->constrained('laporans')->cascadeOnDelete();
    $table->foreignId('user_id')->constrained('users');
    $table->enum('action', ['submit','approve','request_revision','reject']);
    $table->tinyInteger('level')->nullable(); // 1 or 2 for approvers
    $table->text('catatan')->nullable();
    $table->timestamps();
});
```

### 1.3 Models

**`app/Models/JenisLaporan.php`**
- Use `HasFactory`, `SoftDeletes`
- `$fillable = ['kode','nama','deskripsi','is_active']`
- `$casts = ['is_active' => 'boolean']`
- `scopeActive()`

**`app/Models/MasterAkun.php`**
- Use `HasFactory`, `SoftDeletes`
- `$fillable = ['kode','nama','is_active']`
- `scopeActive()`, `scopeSearch($query, string $term)`

**`app/Models/MasterKategori.php`**
- Same pattern as MasterAkun.

**`app/Models/ApprovalChain.php`**
- `$fillable = ['jenis_laporan_id','departemen_id','approver_level_1_id','approver_level_2_id']`
- Relations: `jenisLaporan()`, `departemen()`, `approverLevel1()` (belongsTo User), `approverLevel2()` (belongsTo User)

**`app/Models/Laporan.php`**
- Use `HasFactory`, `SoftDeletes`, `HasAuditTrail`
- `$fillable = ['judul_laporan','departemen_id','jenis_laporan_id','periode_bulan','periode_tahun','status','catatan_pic','submitted_at','created_by','revision_count']`
- Relations: `departemen()`, `jenisLaporan()`, `creator()` (belongsTo User), `items()` (hasMany LaporanItem), `approvalLogs()` (hasMany LaporanApprovalLog, ordered desc)
- Booted: auto-generate `kode_laporan` on creating:
  ```php
  // LPR-{DEPARTEMEN}-{YYYYMM}-{SEQ}
  ```
- `scopeForUser($query, User $user)` — PIC hanya lihat laporan departemen sendiri
- `scopeForApprover($query, User $user, int $level)` — approver lihat laporan sesuai rantai approval
- `scopeSubmitted()` — status = submitted
- `scopeRevision()` — status = revision
- `scopeApproved1()` — status = approved_1
- `scopePendingApproval($query, User $user, int $level)` — gabungan scope approver + status yang sesuai
- `canEdit(): bool` — hanya draft atau revision
- `isPendingApproval(): bool` — submitted atau approved_1

**`app/Models/LaporanItem.php`**
- `$fillable` sesuai migration (exclude computed columns)
- Relation: `laporan()` (belongsTo)
- Computed columns pakai `storedAs` di migration, jadi tidak perlu accessor.

**`app/Models/LaporanApprovalLog.php`**
- `$fillable = ['laporan_id','user_id','action','level','catatan']`
- Relations: `laporan()`, `user()`

### 1.4 Update PicConfig

Tambahkan kolom `jenis_laporan_id` ke `pic_configs` (nullable untuk backward-compat). Atau buat model terpisah `LaporanPicConfig`.

**Rekomendasi:** tambahkan `jenis_laporan_id` ke tabel `pic_configs` karena PIC memang Person In Charge per departemen per jenis laporan.

Migration:
```php
Schema::table('pic_configs', function (Blueprint $table) {
    $table->foreignId('jenis_laporan_id')->nullable()->after('departemen_id')->constrained('jenis_laporans');
});
```

---

## Phase 2: Admin — Master Data Manager

### 2.1 Livewire Components

| Component | View | Route |
|-----------|------|-------|
| `JenisLaporanManager` | `livewire/admin/jenis-laporan-manager.blade.php` | `/admin/jenis-laporan` |
| `MasterAkunManager` | `livewire/admin/master-akun-manager.blade.php` | `/admin/master-akun` |
| `MasterKategoriManager` | `livewire/admin/master-kategori-manager.blade.php` | `/admin/master-kategori` |
| `ApprovalChainManager` | `livewire/admin/approval-chain-manager.blade.php` | `/admin/approval-chain` |

Pattern: CRUD tabel sederhana (copy dari `DepartementManager`).

### 2.2 ApprovalChainManager

- Form: pilih `jenis_laporan_id`, pilih `departemen_id`, pilih `approver_level_1_id`, pilih `approver_level_2_id`
- Validasi: Lvl 1 != Lvl 2; tidak boleh duplikat (`jenis_laporan_id` + `departemen_id`)
- Table: tampilkan jenis laporan, departemen, approver L1, approver L2

---

## Phase 3: PIC — Import Laporan (F01)

### 3.1 Routes & Menu

Tambahkan section menu **"Laporan"** di sidebar:
- `laporan-list` → Daftar Laporan Saya (PIC/Approver/Viewer)
- `laporan-create` → Buat/Import Laporan Baru
- `laporan-approval-queue` → Antrian Persetujuan (Approver only)

### 3.2 Excel Template Download

**File:** `app/Services/LaporanTemplateService.php`

```php
public function generate(string $jenisLaporanKode): \PhpOffice\PhpSpreadsheet\Spreadsheet
```

- Sheet 1: Header kolom item kegiatan + 1 baris contoh data valid
- Sheet 2: "Referensi" — daftar kode_akun dan kode_kategori aktif
- Filename: `template-sirka-{YYYYMM}.xlsx`

### 3.3 Excel Parser & Validator Service

**File:** `app/Services/LaporanImportService.php`

Responsibility:
1. Baca file dengan `PhpSpreadsheet`
2. Mapping kolom (header matching flexible — case-insensitive, strip spasi)
3. Parse setiap baris ke array
4. Validasi per baris:
   - `kode_kegiatan`: required, regex `^[A-Z0-9_-]{1,20}$`, unique dalam file, unique lintas DB per departemen+periode
   - `kode_akun`: required, exists in `master_akuns`
   - `kode_kategori`: required, exists in `master_kategoris`
   - `pagu_anggaran`: required, numeric >= 0
   - `realisasi_anggaran`: numeric >= 0 (warning if > pagu)
   - `volume_rencana`: required, numeric > 0
   - `volume_realisasi`: numeric >= 0 (warning if > rencana)
   - `tanggal_mulai` & `tanggal_selesai`: valid date, selesai >= mulai
   - `status_kegiatan`: enum valid; jika `selesai` → realisasi_anggaran > 0; jika `belum_dimulai` → warning kalau realisasi > 0
   - Format angka: tidak boleh mengandung `Rp`, titik/koma ribuan
5. Return: array dengan struktur:
   ```php
   [
       'rows' => [
           [
               'data' => [...],
               'status' => 'valid' | 'error' | 'warning',
               'errors' => ['kode_kegiatan' => '...', 'pagu_anggaran' => '...'],
               'warnings' => ['realisasi_anggaran' => '...'],
           ]
       ],
       'summary' => ['total' => N, 'errors' => N, 'warnings' => N, 'valid' => N]
   ]
   ```

### 3.4 Livewire Import Component

**File:** `app/Livewire/Laporan/LaporanImport.php`

Properties:
- `public ?int $jenis_laporan_id = null`
- `public int $periode_bulan`
- `public int $periode_tahun`
- `public string $judul_laporan = ''`
- `public $file = null` (Livewire file upload)
- `public array $preview = []` — hasil parsing
- `public bool $hasErrors = false`

Flow:
1. User pilih jenis laporan, periode, judul, upload file
2. `parseFile()` → panggil `LaporanImportService`, simpan hasil ke `$preview`
3. Tampilkan preview table dengan color coding (green/yellow/red cells)
4. Jika `hasErrors = true`, tombol "Simpan & Lanjutkan" disabled
5. `save()` — DB transaction:
   - Insert `laporans` (status = draft)
   - Insert semua `laporan_items`
   - Redirect ke detail laporan

**View:** `resources/views/livewire/laporan/laporan-import.blade.php`
- Step 1: Form metadata + upload
- Step 2: Preview table (setelah parse)
- Summary badge: total/error/warning

### 3.5 File Upload Handling

- Use `WithFileUploads` trait
- `$rules['file'] = ['required', 'file', 'mimes:xlsx,csv', 'max:5120']`
- Simpan file sementara ke `storage/app/temp/laporan-imports/`
- Hapus file temp setelah disimpan atau modal ditutup

---

## Phase 4: Laporan List & Detail (PIC + Approver + Viewer)

### 4.1 Daftar Laporan

**File:** `app/Livewire/Laporan/LaporanTable.php`

- Filter: status, periode (bulan/tahun), departemen (admin only)
- Sort: periode, status, submitted_at
- Search: judul, kode_laporan
- Pagination: 10/25/50

Akses berbasis role:
- **PIC**: hanya laporan departemen sendiri (via `departemen_id` di `pic_configs`)
- **Approver Lvl 1**: laporan dengan status `submitted` yang di-chain ke user ini
- **Approver Lvl 2**: laporan dengan status `approved_1` yang di-chain ke user ini
- **Admin/Viewer**: semua laporan (admin bisa edit, viewer readonly)

### 4.2 Detail Laporan

**File:** `app/Livewire/Laporan/LaporanDetail.php`

- Header info: kode, judul, departemen, periode, status badge, PIC, catatan
- Tab 1: **Item Kegiatan** — tabel dengan computed columns highlighted
- Tab 2: **Riwayat Approval** — timeline dari `laporan_approval_logs`
- Tab 3: **Pesan** (Phase 6 / P3)
- Action buttons sesuai status & role:
  - Draft (PIC): Submit, Edit, Hapus
  - Submitted (Approver L1): Setujui, Minta Revisi
  - Revision (PIC): Import Ulang, Ajukan Kembali
  - Approved_1 (Approver L2): Setujui Final, Minta Revisi
  - Archived: readonly

---

## Phase 5: Approval Workflow (F03 + F04)

### 5.1 Submit Laporan

**Method:** `LaporanDetail::submit()`
- Validasi: status = draft atau revision; approval chain sudah dikonfigurasi
- Update status → `submitted`
- Set `submitted_at = now()`
- Insert `approval_log` (action=submit, user=PIC)
- Kirim notifikasi email ke Approver Lvl 1 (queue)
- Dispatch browser notification ke Approver Lvl 1 (if online)

### 5.2 Approve Lvl 1

**Method:** `LaporanDetail::approveLevel1(string $catatan = '')`
- Validasi: user adalah approver L1 di chain; status = submitted
- Update status → `approved_1`
- Insert `approval_log` (action=approve, level=1)
- Notifikasi ke Approver L2 dan PIC

### 5.3 Approve Lvl 2 (Final)

**Method:** `LaporanDetail::approveLevel2(string $catatan = '')`
- Validasi: user adalah approver L2; status = approved_1
- Update status → `approved_2`
- Insert `approval_log` (action=approve, level=2)
- Update status → `archived` (immediately after approved_2)
- Notifikasi ke PIC, Approver L1, dan VIEWER/management

### 5.4 Request Revision

**Method:** `LaporanDetail::requestRevision(string $catatan, ?int $maxRevision = 3)`
- Validasi: catatan wajib diisi; user adalah approver yang sedang mereview
- Update status → `revision`
- Increment `revision_count`
- Insert `approval_log` (action=request_revision, level=current approver level)
- Notifikasi ke PIC

### 5.5 Reject Laporan

**Method:** `LaporanDetail::reject(string $alasan)`
- Validasi: alasan wajib; user adalah approver
- Update status → `rejected`
- Insert `approval_log` (action=reject, level=current approver level)
- PIC tidak bisa submit ulang — harus buat laporan baru

### 5.6 Re-import (Revision Mode)

**Method:** `LaporanDetail::reImport()`
- Hanya jika status = revision
- Buka modal/form re-upload file (reuse `LaporanImportService`)
- Replace semua `laporan_items` (delete lama, insert baru)
- Status tetap revision sampai PIC klik "Ajukan Kembali"

### 5.7 Re-submit After Revision

**Method:** `LaporanDetail::reSubmit(string $catatan = '')`
- Hanya jika status = revision
- Update status → `submitted`
- Reset `submitted_at`
- Insert `approval_log` (action=submit)
- Siklus approval dimulai dari Lvl 1 lagi

---

## Phase 6: Dashboard & Reporting (Optional / P2-P3)

### 6.1 Management Dashboard

**File:** `app/Livewire/Laporan/LaporanDashboard.php`

KPI Cards:
- Total laporan periode aktif
- Menunggu approval L1
- Menunggu approval L2
- Dalam revisi
- Sudah diarsipkan

Charts (optional, Alpine.js + API):
- Realisasi anggaran per departemen
- % realisasi volume per jenis laporan
- Trend submit per bulan

### 6.2 Realtime Message (F05 / P3 — Deferred)

Tidak diimplementasi di phase awal karena memerlukan WebSocket infrastructure.
Alternatif: gunakan polling setiap 30 detik pada halaman detail laporan untuk cek pesan baru.

---

## Phase 7: Seeders, Permissions & Polish

### 7.1 Permissions

Tambahkan ke `RolePermissionSeeder`:
```php
'laporan-list', 'laporan-create', 'laporan-edit', 'laporan-delete',
'laporan-submit', 'laporan-approve',
'jenis-laporan-list', 'jenis-laporan-create', 'jenis-laporan-edit', 'jenis-laporan-delete',
'master-akun-list', 'master-akun-create', 'master-akun-edit', 'master-akun-delete',
'master-kategori-list', 'master-kategori-create', 'master-kategori-edit', 'master-kategori-delete',
'approval-chain-list', 'approval-chain-create', 'approval-chain-edit', 'approval-chain-delete',
```

### 7.2 Seeders

**`JenisLaporanSeeder`** — 2–3 jenis laporan default (Rencana Bulanan, Triwulan, Tahunan).
**`MasterAkunSeeder`** — 10–15 akun contoh.
**`MasterKategoriSeeder`** — 5–8 kategori contoh.
**`ApprovalChainSeeder`** — rantai approval untuk kombinasi dept + jenis laporan.

### 7.3 Notifications

**File:** `app/Notifications/LaporanStatusChanged.php`

Kirim email via queue untuk event:
- Submitted → Approver L1
- Approved L1 → Approver L2 + PIC
- Approved L2 → PIC + Approver L1 + Management
- Revision Requested → PIC
- Rejected → PIC

Gunakan Laravel Queue (database driver) — sudah tersedia di stack.

---

## File Summary

### New Files
| File | Purpose |
|------|---------|
| `app/Models/JenisLaporan.php` | Model jenis laporan |
| `app/Models/MasterAkun.php` | Model master akun |
| `app/Models/MasterKategori.php` | Model master kategori (laporan) |
| `app/Models/ApprovalChain.php` | Model rantai approval |
| `app/Models/Laporan.php` | Model header laporan |
| `app/Models/LaporanItem.php` | Model item kegiatan |
| `app/Models/LaporanApprovalLog.php` | Model log approval |
| `app/Services/LaporanTemplateService.php` | Generate template Excel |
| `app/Services/LaporanImportService.php` | Parse & validate Excel |
| `app/Livewire/Admin/JenisLaporanManager.php` | CRUD jenis laporan |
| `app/Livewire/Admin/MasterAkunManager.php` | CRUD master akun |
| `app/Livewire/Admin/MasterKategoriManager.php` | CRUD master kategori |
| `app/Livewire/Admin/ApprovalChainManager.php` | CRUD approval chain |
| `app/Livewire/Laporan/LaporanImport.php` | Import laporan (PIC) |
| `app/Livewire/Laporan/LaporanTable.php` | Daftar laporan |
| `app/Livewire/Laporan/LaporanDetail.php` | Detail + workflow approval |
| `app/Livewire/Laporan/LaporanDashboard.php` | Dashboard manajemen |
| `app/Notifications/LaporanStatusChanged.php` | Email notifikasi |
| 7 migration files | Schema database |
| 7 Blade views | UI components |

### Modified Files
| File | Change |
|------|--------|
| `database/seeders/RolePermissionSeeder.php` | Tambah permission laporan |
| `resources/views/components/admin/sidebar.blade.php` | Menu section "Laporan" |
| `routes/web.php` | Routes baru |
| `app/Models/PicConfig.php` | Tambah relasi `jenisLaporan` |
| `database/migrations/*_pic_configs_table.php` (alter) | Tambah `jenis_laporan_id` |

---

## Execution Order

1. **Foundation**: Semua migration + model + seeder
2. **Master Data CRUD**: JenisLaporan, MasterAkun, MasterKategori, ApprovalChain
3. **Import Flow**: Template service → Import service → Livewire Import component
4. **List & Detail**: LaporanTable + LaporanDetail (readonly first)
5. **Workflow**: Submit → Approve L1 → Approve L2 → Revision → Reject
6. **Notifications**: Queue + email
7. **Dashboard**: KPI + charts
8. **Polish**: Validation messages, error handling, responsive UI

## Estimasi Kompleksitas

| Phase | Kompleksitas | Estimasi File |
|-------|-------------|---------------|
| Phase 1 | Medium | 7 migration + 7 model |
| Phase 2 | Low | 4 Livewire + 4 view |
| Phase 3 | **High** | 2 service + 1 Livewire + 1 view |
| Phase 4 | Medium | 2 Livewire + 2 view |
| Phase 5 | **High** | Workflow logic + notifications |
| Phase 6 | Medium | 1 Livewire + API |
| Phase 7 | Low | Seeder + permission |

**Critical path:** Phase 1 → Phase 3 → Phase 5. Phase 2, 4, 6, 7 bisa parallel atau di-swap order-nya.
