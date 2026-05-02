# Product Requirement Document (PRD)
## SIRKA — Sistem Informasi Realisasi Kegiatan & Anggaran

| Atribut       | Detail                                    |
|---------------|-------------------------------------------|
| Versi         | v1.0.0                                    |
| Status        | Draft                                     |
| Dibuat oleh   | Tim Analis Sistem                         |
| Tanggal       | 2026-05-02                                |
| Last updated  | 2026-05-02                                |

---

## Daftar Isi

1. [Ringkasan Eksekutif](#1-ringkasan-eksekutif)
2. [Latar Belakang & Permasalahan](#2-latar-belakang--permasalahan)
3. [Tujuan Produk](#3-tujuan-produk)
4. [Scope & Out of Scope](#4-scope--out-of-scope)
5. [Aktor & Peran](#5-aktor--peran)
6. [Struktur Data Laporan](#6-struktur-data-laporan)
7. [Fitur & User Stories](#7-fitur--user-stories)
   - [F01 — Import Laporan](#f01--import-laporan)
   - [F02 — Konfigurasi PIC & Approval](#f02--konfigurasi-pic--approval)
   - [F03 — Approval Berjenjang 2 Tingkat](#f03--approval-berjenjang-2-tingkat)
   - [F04 — Revisi ke PIC](#f04--revisi-ke-pic)
   - [F05 — Realtime Message *(nice to have)*](#f05--realtime-message-nice-to-have)
8. [Non-Functional Requirements](#8-non-functional-requirements)
9. [Dependensi & Asumsi](#9-dependensi--asumsi)
10. [Glosarium](#10-glosarium)

---

## 1. Ringkasan Eksekutif

SIRKA adalah sistem informasi berbasis web yang memungkinkan setiap unit kerja (departemen) untuk melaporkan realisasi kegiatan dan penyerapan anggaran secara terstruktur setiap bulan. Laporan diunggah melalui mekanisme import file Excel, divalidasi secara otomatis, kemudian melewati alur persetujuan berjenjang dua tingkat sebelum diarsipkan.

Sistem ini menggantikan proses manual berbasis email dan spreadsheet yang selama ini rawan duplikasi data, tidak memiliki jejak audit, dan memperlambat proses pengambilan keputusan manajemen.

---

## 2. Latar Belakang & Permasalahan

### 2.1 Kondisi saat ini

- Setiap departemen mengirimkan laporan realisasi anggaran via email dalam format Excel.
- Tidak ada standar kolom yang disepakati antar departemen.
- Proses approval dilakukan melalui rantai email yang panjang dan tidak terlacak.
- Tidak ada mekanisme revisi yang terstruktur — revisi dilakukan dengan mengirim file baru tanpa riwayat perubahan.
- Manajemen kesulitan mendapatkan rekap konsolidasi antar departemen secara real time.

### 2.2 Pain point utama

| # | Masalah | Dampak |
|---|---------|--------|
| P1 | Data duplikat untuk periode yang sama | Laporan ganda, data tidak konsisten |
| P2 | Tidak ada validasi format & referensi data | Data tidak bisa dikonsolidasi otomatis |
| P3 | Approval via email tidak memiliki SLA | Laporan tertunda berminggu-minggu |
| P4 | Tidak ada riwayat revisi | Tidak ada jejak audit untuk audit internal |
| P5 | Komunikasi PIC-Approver terpisah dari dokumen | Konteks revisi hilang |

---

## 3. Tujuan Produk

| Kode | Tujuan | Metrik Keberhasilan |
|------|--------|---------------------|
| G1 | Menstandarisasi format laporan realisasi | 100% laporan masuk via template resmi |
| G2 | Mengurangi kesalahan data saat import | Error rate import < 5% setelah 3 bulan |
| G3 | Mempercepat proses approval | Rata-rata waktu approval < 3 hari kerja |
| G4 | Menyediakan jejak audit lengkap | Setiap perubahan status tercatat di history |
| G5 | Memudahkan rekap konsolidasi manajemen | Rekap tersedia real time tanpa proses manual |

---

## 4. Scope & Out of Scope

### 4.1 In Scope (versi ini)

- Import laporan dari file Excel/CSV dengan preview dan validasi
- Konfigurasi PIC per jenis laporan dan konfigurasi rantai approval
- Alur approval berjenjang 2 tingkat
- Mekanisme revisi dari approver ke PIC beserta catatan
- Notifikasi in-app dan email untuk setiap perubahan status
- Dashboard rekap konsolidasi untuk manajemen
- Riwayat lengkap setiap siklus revisi dan approval

### 4.2 Out of Scope (versi ini)

- Modul penganggaran (budgeting) — SIRKA hanya mencatat *realisasi*, bukan *rencana anggaran awal*
- Integrasi dengan sistem akuntansi / ERP
- Laporan jenis lain selain realisasi kegiatan & anggaran
- Mobile application native
- Realtime message *(direncanakan sebagai fitur nice-to-have, lihat F05)*

---

## 5. Aktor & Peran

| Aktor | Kode | Deskripsi | Akses utama |
|-------|------|-----------|-------------|
| Admin Sistem | `ADMIN` | Mengelola master data dan konfigurasi sistem | Konfigurasi PIC, konfigurasi approval, master data |
| PIC Unit Kerja | `PIC` | Person In Charge laporan per departemen | Import laporan, submit, revisi |
| Approver Level 1 | `APV1` | Kepala Departemen / Manajer | Review, setujui, atau minta revisi |
| Approver Level 2 | `APV2` | Direktur Keuangan / CFO | Review final, setujui, atau minta revisi |
| Viewer / Manajemen | `VIEWER` | Akses baca laporan yang sudah disetujui | Lihat dashboard & rekap konsolidasi |

---

## 6. Struktur Data Laporan

### 6.1 Header laporan

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| `kode_laporan` | VARCHAR(20) | Auto-generate: `LPR-{DEPARTEMEN}-{YYYYMM}-{SEQ}` |
| `judul_laporan` | VARCHAR(255) | Diisi PIC saat membuat laporan baru |
| `departemen_id` | BIGINT FK | Otomatis dari sesi PIC yang login |
| `periode_bulan` | TINYINT | 1–12 |
| `periode_tahun` | YEAR(4) | Minimal 2020 |
| `status` | ENUM | `draft` → `submitted` → `revision` → `approved_1` → `approved_2` → `archived` |
| `catatan_pic` | TEXT | Catatan pengantar dari PIC saat submit |
| `submitted_at` | DATETIME | Otomatis saat PIC submit |
| `created_by` | BIGINT FK | User ID PIC pembuat laporan |

### 6.2 Item kegiatan (baris import Excel)

| Kolom | Tipe | Sumber | Keterangan |
|-------|------|--------|------------|
| `kode_kegiatan` | VARCHAR(20) | Excel | **Unique key**: unik per `departemen_id + periode_bulan + periode_tahun` |
| `nama_kegiatan` | VARCHAR(255) | Excel | — |
| `kode_akun` | VARCHAR(20) FK | Excel | Referensi ke `master_akun` |
| `kode_kategori` | VARCHAR(20) FK | Excel | Referensi ke `master_kategori` |
| `satuan` | VARCHAR(50) | Excel | Contoh: kegiatan, orang, unit |
| `volume_rencana` | DECIMAL(15,2) | Excel | Harus > 0 |
| `volume_realisasi` | DECIMAL(15,2) | Excel | Harus ≥ 0 |
| `pagu_anggaran` | DECIMAL(18,2) | Excel | Angka murni tanpa format mata uang |
| `realisasi_anggaran` | DECIMAL(18,2) | Excel | Angka murni tanpa format mata uang |
| `tanggal_mulai` | DATE | Excel | Format YYYY-MM-DD |
| `tanggal_selesai` | DATE | Excel | Opsional; harus ≥ `tanggal_mulai` |
| `status_kegiatan` | ENUM | Excel | `selesai`, `berlangsung`, `belum_dimulai` |
| `keterangan` | TEXT | Excel | Opsional, max 500 karakter |
| `persen_realisasi_anggaran` | DECIMAL(6,2) | Computed | `realisasi / pagu × 100` |
| `persen_realisasi_volume` | DECIMAL(6,2) | Computed | `vol_realisasi / vol_rencana × 100` |
| `sisa_anggaran` | DECIMAL(18,2) | Computed | `pagu - realisasi` |

### 6.3 Aturan validasi import

| Kolom | Jenis | Aturan |
|-------|-------|--------|
| `kode_kegiatan` | Error | Required; regex `^[A-Z0-9_-]{1,20}$`; unique per departemen+periode (dalam file & database) |
| `kode_akun` | Error | Required; harus ada di `master_akun` |
| `kode_kategori` | Error | Required; harus ada di `master_kategori` |
| `pagu_anggaran` | Error | Required; numeric ≥ 0; angka murni |
| `realisasi_anggaran` | Warning | Boleh melebihi `pagu_anggaran`, tapi ditandai kuning |
| `volume_realisasi` | Warning | Boleh melebihi `volume_rencana`, tapi ditandai kuning |
| `tanggal_mulai` & `_selesai` | Error | `tanggal_mulai` ≤ `tanggal_selesai` jika keduanya diisi |
| `status_kegiatan = selesai` | Error | `realisasi_anggaran` harus > 0 |
| `status_kegiatan = belum_dimulai` | Warning | `realisasi_anggaran` seharusnya = 0 |
| Format angka | Error | Tidak boleh mengandung `Rp`, titik/koma ribuan |

---

## 7. Fitur & User Stories

> **Konvensi penulisan user story:**
> - Format: *Sebagai [aktor], saya ingin [aksi], sehingga [nilai bisnis].*
> - Acceptance Criteria menggunakan format **Given–When–Then**.
> - Prioritas: `P1` = Must Have, `P2` = Should Have, `P3` = Nice to Have.

---

### F01 — Import Laporan

**Deskripsi fitur:**
PIC dapat mengunggah file Excel berisi item kegiatan, melihat preview hasil parsing, mendapatkan laporan validasi per baris, dan menyimpan data jika tidak ada error blocking.

---

#### US-F01-01 — Unduh template Excel

**Prioritas:** P1

> Sebagai PIC, saya ingin mengunduh template Excel resmi sistem, sehingga saya memiliki format kolom yang benar dan tidak perlu menebak struktur file yang diterima sistem.

**Acceptance Criteria:**

```
Given  saya berada di halaman Import Laporan
When   saya mengklik tombol "Unduh Template"
Then   sistem mengunduh file Excel (.xlsx) bernama "template-sirka-{YYYYMM}.xlsx"
  And  file berisi header kolom sesuai spesifikasi kolom item kegiatan
  And  baris kedua berisi contoh data yang valid
  And  sheet kedua berisi daftar kode_akun dan kode_kategori yang tersedia
```

---

#### US-F01-02 — Upload file dan parsing

**Prioritas:** P1

> Sebagai PIC, saya ingin mengunggah file Excel laporan saya, sehingga sistem dapat membaca dan menampilkan isinya untuk saya verifikasi sebelum disimpan.

**Acceptance Criteria:**

```
Given  saya berada di halaman Import Laporan
When   saya memilih file dengan ekstensi .xlsx atau .csv
  And  ukuran file ≤ 5 MB
Then   sistem membaca file dan menampilkan preview tabel seluruh baris
  And  header kolom file dicocokkan dengan nama kolom yang diharapkan
  And  proses parsing selesai dalam waktu < 5 detik untuk file ≤ 500 baris

Given  saya mengunggah file dengan ekstensi selain .xlsx atau .csv
When   sistem memproses upload
Then   sistem menolak file dan menampilkan pesan: "Format file tidak didukung. Gunakan .xlsx atau .csv."

Given  saya mengunggah file berukuran > 5 MB
When   sistem memproses upload
Then   sistem menolak file dan menampilkan pesan batas ukuran file
```

---

#### US-F01-03 — Preview dan laporan validasi

**Prioritas:** P1

> Sebagai PIC, saya ingin melihat preview data beserta status validasi setiap baris, sehingga saya dapat memperbaiki data sebelum menyimpannya ke sistem.

**Acceptance Criteria:**

```
Given  sistem telah selesai mem-parsing file yang saya unggah
When   preview ditampilkan
Then   setiap baris memiliki indikator status: hijau (valid), merah (error), kuning (warning)
  And  sel yang bermasalah di-highlight sesuai warna status
  And  tooltip pada sel bermasalah menampilkan pesan error/warning spesifik
  And  area summary di atas tabel menampilkan: total baris, jumlah error, jumlah warning

Given  preview menampilkan baris dengan status error
When   saya melihat ringkasan validasi
Then   tombol "Simpan & Lanjutkan" dalam keadaan disabled
  And  sistem menampilkan pesan: "Perbaiki {N} error sebelum melanjutkan"

Given  preview tidak memiliki baris error (hanya warning atau semua valid)
When   saya melihat ringkasan validasi
Then   tombol "Simpan & Lanjutkan" aktif dan dapat diklik
  And  jika terdapat warning, muncul notifikasi konfirmasi sebelum simpan
```

---

#### US-F01-04 — Validasi keunikan kode kegiatan

**Prioritas:** P1

> Sebagai sistem, saya perlu memastikan tidak ada duplikasi `kode_kegiatan` dalam satu periode dan departemen, sehingga data laporan dapat diidentifikasi secara unik.

**Acceptance Criteria:**

```
Given  file yang diimpor memiliki dua baris dengan kode_kegiatan yang sama
When   sistem melakukan validasi
Then   semua baris duplikat dalam file ditandai error
  And  pesan error berbunyi: "kode_kegiatan '{KODE}' duplikat dalam file ini (baris {N} dan {M})"

Given  kode_kegiatan dalam file sudah ada di database untuk departemen dan periode yang sama
When   sistem melakukan validasi lintas database
Then   baris tersebut ditandai error
  And  pesan error berbunyi: "kode_kegiatan '{KODE}' sudah tersimpan untuk periode {BULAN}/{TAHUN}"
```

---

#### US-F01-05 — Validasi referensi ke master data

**Prioritas:** P1

> Sebagai PIC, saya ingin mendapat notifikasi langsung jika kode akun atau kategori yang saya input tidak dikenal sistem, sehingga saya dapat memperbaikinya sebelum menyimpan.

**Acceptance Criteria:**

```
Given  file mengandung kode_akun yang tidak ada di master_akun
When   sistem melakukan validasi referensi
Then   baris tersebut ditandai error
  And  pesan error berbunyi: "kode_akun '{KODE}' tidak ditemukan di master. Lihat sheet 'Referensi' pada template."

Given  file mengandung kode_kategori yang tidak ada di master_kategori
When   sistem melakukan validasi referensi
Then   baris tersebut ditandai error dengan pesan serupa
```

---

#### US-F01-06 — Simpan data import

**Prioritas:** P1

> Sebagai PIC, saya ingin menyimpan data yang telah lolos validasi, sehingga laporan saya tersimpan dengan status `draft` dan siap untuk disubmit.

**Acceptance Criteria:**

```
Given  preview tidak memiliki baris berstatus error
When   saya mengklik tombol "Simpan & Lanjutkan"
Then   sistem menyimpan seluruh baris ke tabel item_kegiatan
  And  sistem membuat record laporan_header dengan status = "draft"
  And  sistem menghitung dan menyimpan kolom computed (persen_realisasi, sisa_anggaran)
  And  saya diarahkan ke halaman detail laporan
  And  laporan baru muncul di daftar laporan saya dengan status "Draft"

Given  terjadi kesalahan database saat menyimpan
When   proses penyimpanan gagal
Then   tidak ada data yang tersimpan sebagian (transaksi di-rollback)
  And  sistem menampilkan pesan error dan mempersilakan saya mencoba kembali
```

---

### F02 — Konfigurasi PIC & Approval

**Deskripsi fitur:**
Admin dapat menetapkan siapa PIC untuk setiap departemen/jenis laporan, serta menyusun rantai approver (siapa Lvl 1 dan siapa Lvl 2) per jenis laporan.

---

#### US-F02-01 — Konfigurasi PIC laporan

**Prioritas:** P1

> Sebagai Admin, saya ingin menetapkan PIC untuk setiap kombinasi departemen dan jenis laporan, sehingga hanya user yang ditunjuk yang dapat membuat dan mengimpor laporan tersebut.

**Acceptance Criteria:**

```
Given  saya berada di halaman Konfigurasi PIC
When   saya memilih departemen, jenis laporan, dan user yang ditunjuk sebagai PIC
  And  saya mengklik "Simpan Konfigurasi"
Then   sistem menyimpan konfigurasi PIC
  And  user yang ditunjuk mendapat notifikasi bahwa mereka menjadi PIC untuk laporan tersebut
  And  user tersebut dapat mengakses menu import laporan untuk konfigurasi yang baru dibuat

Given  saya mencoba menambahkan PIC kedua untuk kombinasi departemen-laporan yang sama
When   saya menyimpan konfigurasi
Then   sistem menampilkan pesan konfirmasi bahwa PIC sebelumnya akan digantikan
  And  setelah dikonfirmasi, PIC lama kehilangan akses import untuk konfigurasi tersebut
```

---

#### US-F02-02 — Konfigurasi rantai approval

**Prioritas:** P1

> Sebagai Admin, saya ingin menetapkan Approver Level 1 dan Level 2 untuk setiap jenis laporan, sehingga setiap laporan yang disubmit langsung menuju approver yang tepat.

**Acceptance Criteria:**

```
Given  saya berada di halaman Konfigurasi Approval
When   saya memilih jenis laporan, menetapkan user sebagai Approver Lvl 1, dan user lain sebagai Approver Lvl 2
  And  saya mengklik "Simpan"
Then   sistem menyimpan rantai approval untuk jenis laporan tersebut
  And  sistem menolak jika Approver Lvl 1 dan Lvl 2 adalah user yang sama
  And  sistem menolak jika Approver adalah user yang sama dengan PIC laporan tersebut

Given  saya mencoba menyimpan konfigurasi tanpa mengisi Approver Lvl 2
When   sistem memvalidasi form
Then   sistem menampilkan pesan error: "Approver Level 2 wajib diisi"
```

---

#### US-F02-03 — Melihat daftar konfigurasi aktif

**Prioritas:** P2

> Sebagai Admin, saya ingin melihat seluruh konfigurasi PIC dan approval yang aktif dalam satu tampilan, sehingga saya dapat memantau dan mengaudit pembagian tanggung jawab.

**Acceptance Criteria:**

```
Given  saya berada di halaman Konfigurasi
When   halaman dimuat
Then   sistem menampilkan tabel berisi: departemen, jenis laporan, nama PIC, Approver Lvl 1, Approver Lvl 2
  And  tabel dapat difilter berdasarkan departemen
  And  setiap baris memiliki tombol "Edit" dan "Nonaktifkan"
```

---

### F03 — Approval Berjenjang 2 Tingkat

**Deskripsi fitur:**
Laporan yang telah disubmit PIC melewati dua tahap persetujuan secara berurutan. Approver Lvl 2 hanya dapat mereview setelah Lvl 1 menyetujui.

---

#### US-F03-01 — Submit laporan ke antrian approval

**Prioritas:** P1

> Sebagai PIC, saya ingin mengajukan laporan yang sudah saya buat ke proses approval, sehingga laporan saya dapat ditinjau oleh approver yang berwenang.

**Acceptance Criteria:**

```
Given  saya memiliki laporan dengan status "draft"
When   saya mengklik tombol "Ajukan untuk Approval"
  And  saya mengisi catatan pengantar (opsional)
  And  saya mengkonfirmasi pengajuan
Then   status laporan berubah menjadi "submitted"
  And  Approver Lvl 1 menerima notifikasi in-app dan email
  And  notifikasi berisi: nama laporan, departemen, periode, dan link langsung ke laporan
  And  saya tidak dapat lagi mengedit isi laporan selama dalam proses approval
```

---

#### US-F03-02 — Melihat antrian laporan sebagai Approver Lvl 1

**Prioritas:** P1

> Sebagai Approver Lvl 1, saya ingin melihat daftar laporan yang menunggu persetujuan saya, sehingga saya dapat memprioritaskan review.

**Acceptance Criteria:**

```
Given  saya login sebagai Approver Lvl 1
When   saya membuka halaman "Persetujuan Saya"
Then   sistem menampilkan hanya laporan dengan status "submitted" yang rantai approvalnya menetapkan saya sebagai Lvl 1
  And  tabel menampilkan: nama laporan, departemen, periode, tanggal submit, dan lama menunggu (dalam hari)
  And  laporan yang menunggu > 2 hari kerja ditandai dengan indikator kuning
```

---

#### US-F03-03 — Menyetujui laporan (Approver Lvl 1)

**Prioritas:** P1

> Sebagai Approver Lvl 1, saya ingin menyetujui laporan yang telah saya review, sehingga laporan diteruskan ke Approver Lvl 2 untuk persetujuan final.

**Acceptance Criteria:**

```
Given  saya sedang melihat detail laporan berstatus "submitted"
When   saya mengklik "Setujui"
  And  saya mengisi catatan persetujuan (opsional)
  And  saya mengkonfirmasi
Then   status laporan berubah menjadi "approved_1"
  And  record di tabel approval_log dibuat: user_id, action=approve, level=1, timestamp, catatan
  And  Approver Lvl 2 menerima notifikasi in-app dan email
  And  laporan hilang dari antrian saya dan muncul di antrian Approver Lvl 2
  And  PIC menerima notifikasi bahwa laporan telah disetujui Lvl 1
```

---

#### US-F03-04 — Menyetujui laporan (Approver Lvl 2 / Final)

**Prioritas:** P1

> Sebagai Approver Lvl 2, saya ingin memberikan persetujuan final atas laporan, sehingga laporan diarsipkan dan dapat diakses manajemen.

**Acceptance Criteria:**

```
Given  saya sedang melihat laporan berstatus "approved_1"
When   saya mengklik "Setujui Final"
  And  saya mengisi catatan (opsional) dan mengkonfirmasi
Then   status laporan berubah menjadi "approved_2"
  And  sesaat setelah itu status berubah menjadi "archived"
  And  record approval_log dibuat: action=approve, level=2, timestamp
  And  PIC, Approver Lvl 1, dan VIEWER/manajemen menerima notifikasi laporan telah final disetujui
  And  laporan muncul di dashboard rekap konsolidasi manajemen

Given  saya mencoba mengakses laporan dengan status "submitted" (belum disetujui Lvl 1)
When   saya membuka halaman persetujuan
Then   sistem menampilkan pesan: "Laporan ini belum disetujui oleh Approver Level 1"
  And  tombol "Setujui Final" tidak ditampilkan
```

---

#### US-F03-05 — Melihat riwayat approval

**Prioritas:** P2

> Sebagai PIC atau Approver, saya ingin melihat riwayat lengkap setiap tindakan approval pada suatu laporan, sehingga saya memiliki jejak audit yang jelas.

**Acceptance Criteria:**

```
Given  saya membuka halaman detail laporan mana pun
When   saya melihat tab "Riwayat"
Then   sistem menampilkan timeline seluruh perubahan status laporan secara kronologis
  And  setiap entri riwayat menampilkan: timestamp, nama user, aksi, dan catatan
  And  riwayat tidak dapat diedit atau dihapus oleh siapa pun
```

---

### F04 — Revisi ke PIC

**Deskripsi fitur:**
Approver Lvl 1 atau Lvl 2 dapat mengembalikan laporan ke PIC dengan catatan revisi. PIC memperbaiki dan mengajukan ulang tanpa kehilangan riwayat sebelumnya.

---

#### US-F04-01 — Meminta revisi dari Approver

**Prioritas:** P1

> Sebagai Approver (Lvl 1 atau Lvl 2), saya ingin mengembalikan laporan ke PIC disertai catatan revisi yang spesifik, sehingga PIC tahu persis apa yang harus diperbaiki.

**Acceptance Criteria:**

```
Given  saya sedang mereview laporan yang berada di antrian saya
When   saya mengklik "Minta Revisi"
Then   sistem menampilkan form dengan kolom "Catatan Revisi" yang wajib diisi
  And  saya dapat menyebutkan nomor baris item kegiatan yang bermasalah

When   saya mengisi catatan dan mengkonfirmasi
Then   status laporan berubah menjadi "revision"
  And  PIC menerima notifikasi in-app dan email yang berisi catatan revisi dari saya
  And  record approval_log dibuat: action=request_revision, level={saya}, catatan, timestamp
  And  laporan tidak diteruskan ke approver level berikutnya
```

---

#### US-F04-02 — Melihat catatan revisi sebagai PIC

**Prioritas:** P1

> Sebagai PIC, saya ingin melihat catatan revisi dari approver secara jelas di halaman laporan, sehingga saya memahami apa yang harus saya perbaiki.

**Acceptance Criteria:**

```
Given  laporan saya memiliki status "revision"
When   saya membuka halaman detail laporan
Then   sistem menampilkan banner peringatan berwarna kuning di bagian atas
  And  banner berisi nama approver, waktu permintaan revisi, dan isi catatan revisi
  And  jika revisi menyebut nomor baris, baris tersebut di-highlight di tabel item kegiatan
```

---

#### US-F04-03 — Mengunggah ulang laporan hasil revisi

**Prioritas:** P1

> Sebagai PIC, saya ingin dapat mengimpor ulang file yang sudah saya perbaiki dan mengajukannya kembali, sehingga siklus review dapat berlanjut tanpa membuat laporan baru dari awal.

**Acceptance Criteria:**

```
Given  laporan saya berstatus "revision"
When   saya membuka halaman laporan tersebut
Then   tombol "Import Ulang" tersedia
  And  tombol "Buat Laporan Baru" tidak muncul untuk periode yang sama

When   saya mengimpor file baru yang sudah diperbaiki dan menekan "Ajukan Kembali"
Then   data item_kegiatan lama digantikan dengan data baru
  And  status laporan kembali menjadi "submitted"
  And  riwayat revisi (catatan approver + timestamp) tetap tersimpan
  And  Approver Lvl 1 menerima notifikasi bahwa laporan telah direvisi dan diajukan ulang
  And  siklus approval dimulai dari Lvl 1 kembali
```

---

#### US-F04-04 — Batas maksimal siklus revisi

**Prioritas:** P2

> Sebagai Admin, saya ingin dapat menetapkan batas maksimal siklus revisi per laporan, sehingga tidak ada laporan yang bolak-balik tanpa batas.

**Acceptance Criteria:**

```
Given  konfigurasi sistem menetapkan batas revisi = N kali
When   laporan sudah mengalami N kali permintaan revisi
  And  approver kembali mengklik "Minta Revisi"
Then   sistem menampilkan peringatan: "Laporan ini sudah melewati batas revisi ({N} kali). Pertimbangkan untuk menolak laporan."
  And  approver masih dapat memilih untuk tetap meminta revisi atau memilih "Tolak Laporan"

Given  approver memilih "Tolak Laporan"
When   aksi dikonfirmasi dengan alasan penolakan
Then   status laporan berubah menjadi "rejected"
  And  laporan tidak dapat disubmit ulang — PIC harus membuat laporan baru
  And  PIC menerima notifikasi penolakan beserta alasannya
```

---

### F05 — Realtime Message *(nice to have)*

**Deskripsi fitur:**
Kanal pesan langsung antara PIC dan Approver yang aktif mereview laporan, sehingga komunikasi terkait laporan terdokumentasi dalam satu tempat.

---

#### US-F05-01 — Mengirim pesan dalam konteks laporan

**Prioritas:** P3

> Sebagai PIC atau Approver, saya ingin dapat mengirim pesan langsung di dalam halaman laporan, sehingga diskusi tentang laporan tidak tercecer di email atau chat eksternal.

**Acceptance Criteria:**

```
Given  laporan berada dalam status aktif (submitted, revision, approved_1)
When   saya membuka tab "Pesan" di halaman detail laporan
Then   saya dapat mengetik dan mengirim pesan
  And  pesan terkirim tampil secara realtime di sisi penerima tanpa perlu refresh halaman
  And  setiap pesan menampilkan: nama pengirim, timestamp, dan isi pesan
  And  penerima mendapat notifikasi in-app jika tab pesan tidak sedang terbuka
```

---

#### US-F05-02 — Riwayat pesan tersimpan bersama laporan

**Prioritas:** P3

> Sebagai Admin atau Auditor, saya ingin seluruh pesan dalam konteks laporan tersimpan dan dapat diakses, sehingga komunikasi antara PIC dan approver menjadi bagian dari jejak audit.

**Acceptance Criteria:**

```
Given  laporan telah diarsipkan
When   saya membuka detail laporan dan tab "Pesan"
Then   seluruh riwayat pesan selama laporan aktif masih dapat dibaca
  And  tidak ada pesan yang dapat dihapus oleh PIC atau Approver
  And  hanya Admin yang dapat menghapus pesan (dengan alasan yang dicatat di log)
```

---

## 8. Non-Functional Requirements

### 8.1 Performa

| ID | Requirement |
|----|-------------|
| NFR-P1 | Parsing file Excel ≤ 500 baris selesai dalam < 5 detik |
| NFR-P2 | Halaman preview import dengan 500 baris ter-render dalam < 3 detik |
| NFR-P3 | Notifikasi realtime (F05) terkirim dalam < 2 detik setelah pesan dikirim |
| NFR-P4 | Halaman dashboard rekap konsolidasi dimuat dalam < 4 detik |

### 8.2 Keamanan

| ID | Requirement |
|----|-------------|
| NFR-S1 | Setiap aksi approval dan revisi harus terautentikasi — tidak ada endpoint publik |
| NFR-S2 | PIC hanya dapat mengakses laporan milik departemennya sendiri |
| NFR-S3 | Approver hanya dapat melihat laporan yang rantai approvalnya menetapkan mereka |
| NFR-S4 | File yang diunggah disimpan di storage privat, tidak dapat diakses via URL langsung |
| NFR-S5 | Seluruh input divalidasi di server-side, tidak hanya di client-side |

### 8.3 Ketersediaan & Reliabilitas

| ID | Requirement |
|----|-------------|
| NFR-A1 | Uptime sistem ≥ 99% pada jam kerja (08.00–18.00 WIB, Senin–Jumat) |
| NFR-A2 | Proses simpan import menggunakan database transaction — gagal sebagian dianggap gagal total (rollback) |

### 8.4 Usability

| ID | Requirement |
|----|-------------|
| NFR-U1 | Pesan error validasi harus spesifik per baris dan per kolom, bukan pesan umum |
| NFR-U2 | Sistem mendukung browser: Chrome 110+, Firefox 110+, Edge 110+ |
| NFR-U3 | Halaman menggunakan bahasa Indonesia sebagai bahasa antarmuka default |

---

## 9. Dependensi & Asumsi

### Dependensi

- Master data `master_akun` dan `master_kategori` sudah tersedia dan dikelola di luar sistem SIRKA (oleh tim Finance).
- Data `departemen` dan `users` disinkronkan dari sistem HR atau dikelola manual oleh Admin SIRKA.
- Layanan email (SMTP) tersedia untuk notifikasi.
- Untuk F05 (realtime message): infrastruktur WebSocket (Pusher / Soketi) tersedia di environment produksi.

### Asumsi

- Satu user hanya dapat menjadi PIC untuk satu departemen dalam satu periode.
- Satu laporan hanya memiliki satu rantai approval (tidak ada percabangan paralel).
- Jika rantai approval belum dikonfigurasi untuk suatu jenis laporan, PIC tidak dapat melakukan submit hingga Admin menyelesaikan konfigurasi.
- File template Excel menggunakan encoding UTF-8.

---

## 10. Glosarium

| Istilah | Definisi |
|---------|----------|
| PIC | Person In Charge — penanggung jawab laporan dari sisi unit kerja |
| Approver | Pihak yang berwenang menyetujui atau meminta revisi laporan |
| Pagu anggaran | Total anggaran yang dialokasikan untuk suatu kegiatan |
| Realisasi anggaran | Jumlah anggaran yang benar-benar sudah digunakan |
| Computed column | Kolom yang nilainya dihitung otomatis oleh sistem dari kolom lain |
| Approval log | Tabel pencatatan seluruh tindakan approval sebagai jejak audit |
| Draft | Status awal laporan setelah diimport, sebelum disubmit |
| Archived | Status final laporan yang telah disetujui kedua approver |

---

*Dokumen ini adalah living document. Perubahan signifikan pada requirement harus melalui proses change request dan dibuktikan dengan update versi (v1.x.x).*