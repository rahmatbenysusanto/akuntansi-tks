# CLAUDE.md — Sistem Akuntansi Web (PT. Transkargo Solusindo)

Dokumen ini adalah instruksi kerja untuk Claude Code. Baca seluruh isi file ini sebelum menulis kode apa pun. Ikuti urutan fase di bagian **Rencana Eksekusi** — jangan melompat ke fase berikutnya sebelum fase sebelumnya selesai dan teruji.

---

## 1. Ringkasan Proyek

Membangun **sistem akuntansi double-entry berbasis web** untuk PT. Transkargo Solusindo (perusahaan jasa cargo/logistik), menggantikan template Excel manual yang saat ini mereka pakai. Sistem ini mendigitalkan siklus akuntansi penuh: Chart of Account → Jurnal Umum → Buku Besar (otomatis) → Neraca Lajur (otomatis) → Laporan Laba Rugi & Neraca → Catatan Atas Laporan Keuangan → Rasio Keuangan (Financial Highlight).

**Pengguna**: hanya staff akunting internal perusahaan ini (single company, tidak perlu multi-tenant). Tetap siapkan struktur role agar mudah ditambah approver di kemudian hari, tapi untuk versi awal cukup 1 role (`admin`/akunting) yang bisa CRUD penuh.

**Prinsip arsitektur paling penting**: Buku Besar, Neraca Lajur, Laba Rugi, dan Neraca **BUKAN tabel tersendiri** — semua adalah hasil query/agregasi dari satu sumber data: `journal_entry_lines`. Ini berbeda dari Excel asli klien (yang manual link antar-sheet dan rawan `#REF!`). Satu sumber kebenaran data → semua laporan otomatis konsisten.

---

## 2. Tech Stack (WAJIB, jangan diganti)

- **Backend**: Laravel (versi terbaru stable — gunakan `laravel new` untuk pull versi terkini, jangan pin ke versi lama)
- **Database**: MySQL
- **CSS**: Tailwind CSS (gunakan Laravel's built-in Vite + Tailwind starter kit jika tersedia di versi Laravel terbaru)
- **Frontend rendering**: Blade + Livewire (rekomendasi, karena tidak butuh SPA terpisah dan cocok untuk form-heavy accounting app). Jika Livewire dirasa menambah kompleksitas, alternatif: Blade + Alpine.js untuk interaktivitas ringan (dropdown akun, baris dinamis jurnal). **Pilih satu di awal dan konsisten dipakai di seluruh aplikasi.**
- **Auth**: Laravel Breeze (paling ringan, cocok untuk single-role internal app)
- **PDF Export**: `barryvdh/laravel-dompdf` untuk export Laba Rugi/Neraca ke PDF
- **Excel Export**: `maatwebsite/excel` untuk export laporan ke Excel

---

## 3. Istilah Akuntansi Indonesia → Inggris (untuk konsistensi penamaan tabel/variabel)

Gunakan istilah Inggris untuk nama tabel/kolom database dan kode, tapi label UI tetap Bahasa Indonesia sesuai istilah asli klien.

| Istilah Klien (UI) | Nama Teknis (DB/kode) |
|---|---|
| Kode Akun / Chart of Account | `accounts` |
| Jurnal Umum | `journal_entries` + `journal_entry_lines` |
| Buku Besar | view/query `general_ledger` (tidak ada tabel fisik) |
| Neraca Lajur | view/query `trial_balance` |
| Laba Rugi | view/query `income_statement` |
| Neraca | view/query `balance_sheet` |
| Catatan Atas Laporan Keuangan | view/query `financial_notes` |
| Financial Highlight | view/query `financial_ratios` |
| Saldo Awal | `opening_balances` |
| Periode Akuntansi | `accounting_periods` |
| Debet / Kredit | `debit` / `credit` |
| Pos Saldo (saldo normal akun) | `normal_balance` (enum: `debit`/`credit`) |
| Pos Laporan (akun masuk laporan apa) | `report_type` (enum: `balance_sheet`/`income_statement`) |

---

## 4. Skema Database

### 4.1 `accounts` (Chart of Account)
```
id                  bigint PK
code                varchar(20) unique     -- format "1.1.01.01.01"
name                varchar(255)
parent_code         varchar(20) nullable   -- untuk hierarki akun (induk-anak)
level               tinyint                -- 1-5, dihitung dari jumlah segmen kode yang non-zero
category            enum: aktiva, kewajiban, modal, pendapatan, hpp, biaya_operasional, pendapatan_biaya_lain, biaya_bunga, pajak_penghasilan
normal_balance      enum: debit, credit
report_type         enum: balance_sheet, income_statement
is_header           boolean default false  -- akun header/grup, tidak bisa dipakai langsung di jurnal
is_active           boolean default true
created_at, updated_at
```
Catatan: kode akun 5 segmen (`X.X.XX.XX.XX`) merepresentasikan hierarki. Akun dengan segmen akhir `00` umumnya adalah header/grup (tidak diinput transaksi langsung, hanya untuk subtotal). Akun "daun" (leaf, segmen terakhir bukan 00) adalah akun yang dipakai di jurnal. Tandai `is_header = true` untuk baris yang dalam Excel berwarna merah/coklat/orange/kuning sesuai catatan asli klien di sheet Kode Akun ("note: fill merah, coklat, orange, dan kuning di kolom B pada nama akun adalah HEADER, gunakan akun warna putih"). Karena warna tidak ikut di-extract, gunakan heuristik: jika kode berakhiran lebih dari satu segmen `00` DAN punya child di data seed → `is_header = true`.

### 4.2 `accounting_periods`
```
id                  bigint PK
month               tinyint  (1-12)
year                smallint
status              enum: open, closed
closed_at           timestamp nullable
closed_by           bigint nullable FK users.id
created_at, updated_at
unique(month, year)
```

### 4.3 `opening_balances`
```
id                  bigint PK
accounting_period_id bigint FK
account_id          bigint FK
debit                decimal(18,2) default 0
credit               decimal(18,2) default 0
created_at, updated_at
unique(accounting_period_id, account_id)
```

### 4.4 `journal_entries`
```
id                  bigint PK
accounting_period_id bigint FK
entry_date          date
reference_no        varchar(50)   -- "Bukti Transaksi" / nomor bukti
description         text          -- "Keterangan"
status              enum: draft, posted   -- draft = belum final, posted = sudah masuk ledger
created_by          bigint FK users.id
posted_at           timestamp nullable
created_at, updated_at
```

### 4.5 `journal_entry_lines`
```
id                  bigint PK
journal_entry_id    bigint FK
account_id          bigint FK
debit               decimal(18,2) default 0
credit               decimal(18,2) default 0
line_order          smallint      -- urutan baris dalam 1 jurnal
created_at, updated_at
```

### 4.6 `users`
```
id, name, email, password, role (enum: admin, staff), ...default Breeze fields
```

**Aturan integritas WAJIB ditegakkan di level aplikasi (gunakan Laravel transaction `DB::transaction`):**
1. Setiap `journal_entry` minimal punya 2 baris `journal_entry_lines`.
2. SUM(debit) harus SAMA DENGAN SUM(credit) dalam satu `journal_entry` sebelum bisa disimpan/posted.
3. Tidak bisa input jurnal ke `accounting_period` yang statusnya `closed`.
4. Akun dengan `is_header = true` TIDAK BOLEH dipilih di baris jurnal (validasi di form).
5. Saat periode di-close, sistem harus generate `opening_balances` otomatis untuk periode berikutnya (saldo akhir periode ini = saldo awal periode depan).

---

## 5. Modul & Halaman Aplikasi

### Modul 1: Master Data
- **Chart of Account**: list (tree/hierarki sesuai kode), create, edit, deactivate (jangan hard delete jika sudah dipakai transaksi). Import dari CSV (gunakan file `coa_seed.csv` yang disediakan — lihat bagian 7).
- **Periode Akuntansi**: list periode, buka periode baru, tutup periode (dengan konfirmasi + cek balance dulu).
- **Saldo Awal**: form input saldo awal per akun untuk periode pertama kali pakai sistem.

### Modul 2: Transaksi
- **Jurnal Umum**:
    - List jurnal (filter by periode, tanggal, status draft/posted)
    - Form input: tanggal, no. bukti, keterangan, lalu baris-baris debet/kredit (dynamic rows, tambah/hapus baris dengan Livewire/Alpine)
    - Real-time validation: tampilkan total debet vs total kredit, highlight merah jika belum balance, disable tombol simpan jika tidak balance
    - Bisa simpan sebagai draft atau langsung posting
    - Edit/hapus hanya untuk status draft dan periode masih `open`

### Modul 3: Laporan (read-only, generated)
- **Buku Besar**: pilih akun + periode → tampilkan semua mutasi (tanggal, no. bukti, keterangan, debet, kredit, saldo berjalan)
- **Neraca Lajur**: tabel semua akun dengan kolom Saldo Awal (Debet/Kredit), Mutasi (Debet/Kredit), Saldo Akhir (Debet/Kredit) — kolom Laba Rugi dan Neraca terpisah sesuai `report_type` akun, mirip struktur asli di Excel sheet "Neraca Lajur"
- **Laba Rugi**: struktur sesuai Excel asli — Pendapatan (Penjualan Bruto - Retur - Potongan = Penjualan Netto), dikurangi HPP, dikurangi Biaya Operasional (Biaya Penjualan + Biaya Administrasi Umum), lalu Pendapatan/Biaya Lain di Luar Operasi, Biaya Bunga, Pajak Penghasilan Badan → Laba Bersih
- **Neraca**: struktur sesuai Excel asli — Aktiva Lancar + Aktiva Tidak Lancar = Total Aktiva; Kewajiban Lancar + Kewajiban Jangka Panjang + Modal = Total Kewajiban & Modal. **Validasi: Total Aktiva HARUS SAMA DENGAN Total Kewajiban & Modal** — tampilkan warning jika tidak balance (indikasi ada jurnal yang error).
- **Catatan Atas Laporan Keuangan**: rincian per akun header (drill-down dari Neraca/Laba Rugi ke akun-akun detailnya)
- **Financial Highlight**: hitung rasio otomatis dari data live:
    - Profitability: Net Profit Margin, ROI, ROE (Return on Net Worth), ROCE
    - Liquidity: Current Ratio, Quick Ratio, Absolute Liquidity Ratio
    - Efficiency: Sales to Liquid Assets, dll (lihat sheet F.H di Excel asli untuk formula lengkap)
- Semua laporan: filter by rentang periode, tombol export PDF & Excel

### Modul 4: User Management (sederhana)
- CRUD user, set role (admin/staff)

---

## 6. UI/Styling Guidelines (Tailwind)

- Gunakan tema warna netral profesional (slate/gray untuk base, satu warna aksen misal indigo/blue untuk tombol utama dan status aktif)
- Tabel laporan keuangan: gunakan font monospace atau tabular-nums untuk kolom angka agar rapi sejajar
- Format angka: gunakan format Indonesia (titik sebagai pemisah ribuan, contoh `1.000.000`), nilai 0 ditampilkan sebagai `-` (sesuai konvensi laporan keuangan), angka negatif dalam kurung `(1.000.000)` bukan minus
- Layout: sidebar kiri untuk navigasi modul (Master Data, Transaksi, Laporan), header menampilkan periode aktif yang sedang dipilih
- Form Jurnal Umum: baris debet/kredit harus mudah ditambah/hapus, dengan dropdown akun yang bisa di-search (gunakan Alpine.js + simple search atau Livewire `wire:model.live` dengan filter)
- Tabel jurnal lajur/neraca/laba-rugi: indentasi visual mengikuti level hierarki akun (header lebih bold/besar, sub-akun di-indent)
- Responsive tidak wajib prioritas tinggi (internal tool, asumsikan dipakai di desktop), tapi jangan sampai rusak di layar kecil

---

## 7. Data Seed (Chart of Account Asli Klien)

Klien sudah punya Chart of Account lengkap (368 akun) dari template Excel mereka. File `coa_seed.csv` (disediakan terpisah, letakkan di `database/seeders/data/coa_seed.csv`) berisi kolom:

```
nama_akun, kode_akun, pos_saldo, pos_laporan
```

Buat `AccountSeeder` yang membaca CSV ini dan insert ke tabel `accounts` dengan mapping:
- `nama_akun` → `name`
- `kode_akun` → `code`
- `pos_saldo` (`DEBET`/`KREDIT`) → `normal_balance` (`debit`/`credit`)
- `pos_laporan` (`NERACA`/`LABA RUGI`) → `report_type` (`balance_sheet`/`income_statement`)
- `parent_code`: hitung dari kode (potong 1 segmen terakhir, cari kode yang match, jika tidak ada set null)
- `level`: hitung dari jumlah segmen yang bukan `00`
- `category`: derive dari segmen pertama kode:
    - `1.x` → aktiva
    - `2.x` → kewajiban
    - `3.x` → modal
    - `4.x` → pendapatan
    - `5.x` → hpp
    - `6.x` → biaya_operasional
    - `7.x` → pendapatan_biaya_lain
    - `8.x` → biaya_bunga
    - `9.x` → pajak_penghasilan
- `is_header`: true jika kode punya child lain di CSV yang merupakan turunannya (prefix match), false jika leaf node

**PENTING**: jangan hardcode logic ini di seeder dengan cara manual ketik 368 baris — baca dari CSV secara programatic, supaya jika klien revisi COA, cukup update CSV.

---

## 8. Rencana Eksekusi (kerjakan berurutan, jangan lompat)

### Fase 0 — Setup Project
1. `laravel new transkargo-accounting` (pilih starter kit dengan Breeze + Livewire jika ditawarkan, atau install manual)
2. Setup `.env` untuk MySQL lokal
3. Install Tailwind (via Vite, biasanya sudah default di starter kit Laravel terbaru)
4. Jalankan migration awal Breeze, pastikan auth (login/register) jalan dulu sebelum lanjut

### Fase 1 — Master Data & COA
1. Migration: `accounts`, `accounting_periods`, `opening_balances`
2. Model + relasi (`Account` punya `parent()`/`children()` self-relation by `parent_code`)
3. Seeder COA dari `coa_seed.csv` (lihat bagian 7)
4. CRUD UI untuk Chart of Account (list bentuk tree/indented, create, edit)
5. CRUD UI untuk Periode Akuntansi (buka/tutup periode)
6. Form Saldo Awal per periode

### Fase 2 — Jurnal Umum (jantung sistem)
1. Migration: `journal_entries`, `journal_entry_lines`
2. Model + validasi balance (Form Request custom validation: total debit === total credit)
3. UI input jurnal dengan dynamic rows
4. List & filter jurnal, status draft/posted
5. Cegah edit/hapus jika periode closed atau status posted (sesuai aturan di bagian 4)
6. **Test manual**: input beberapa jurnal contoh, pastikan validasi balance benar-benar mencegah simpan jika tidak balance

### Fase 3 — Buku Besar & Neraca Lajur (query layer)
1. Buat Query Builder/Service class `LedgerService` yang generate Buku Besar per akun per periode (saldo awal + mutasi + saldo akhir, running balance)
2. Service `TrialBalanceService` yang generate Neraca Lajur (semua akun, agregasi debit/kredit dari `journal_entry_lines` + saldo awal)
3. UI tampilkan kedua laporan ini dengan filter periode
4. **Test**: pastikan total debet = total kredit di Neraca Lajur (basic accounting check)

### Fase 4 — Laporan Akhir
1. Service `IncomeStatementService` — generate Laba Rugi sesuai struktur di bagian 5
2. Service `BalanceSheetService` — generate Neraca, dengan validasi Total Aktiva = Total Kewajiban & Modal
3. UI kedua laporan + export PDF & Excel
4. **Test**: cross-check angka Laba Bersih di Laba Rugi sama dengan angka "Laba Periode Berjalan" di Neraca (ini adalah cek konsistensi akuntansi paling penting)

### Fase 5 — Catatan LK & Financial Highlight
1. Service untuk drill-down Catatan Atas Laporan Keuangan
2. Service `FinancialRatioService` menghitung semua rasio dari data live
3. UI kedua laporan ini

### Fase 6 — Polish
1. Dashboard ringkasan (total aktiva, laba bulan ini, dll)
2. Validasi & error handling menyeluruh
3. Seed data dummy transaksi untuk testing/demo
4. Review keamanan: middleware auth di semua route, validasi role

---

## 9. Catatan Tambahan / Keputusan yang Sudah Diambil

- **Tidak perlu multi-currency aktif di versi awal** meski COA punya akun USD/EUR terpisah — treat sebagai akun biasa (nilai sudah dalam rupiah saat diinput), tidak perlu konversi kurs otomatis. Bisa jadi fitur fase 2 di luar scope ini.
- **Tidak perlu approval workflow** (input-approve terpisah) di versi awal karena hanya staff akunting internal yang pakai — cukup status draft/posted untuk kontrol dasar.
- **Single company** — tidak perlu tabel `companies`, asumsikan 1 instalasi = 1 perusahaan (PT. Transkargo Solusindo).
- Jangan tambahkan fitur di luar yang disebutkan di dokumen ini tanpa konfirmasi — fokus pada scope yang sudah didefinisikan supaya sesuai kontrak yang sudah fix dengan klien.

---

## 10. Definition of Done

Sistem dianggap selesai untuk versi awal jika:
- [ ] User bisa login, lihat dashboard
- [ ] CRUD Chart of Account jalan, 368 akun dari klien sudah ter-seed dengan benar
- [ ] Bisa buka/tutup periode akuntansi
- [ ] Bisa input saldo awal
- [ ] Bisa input Jurnal Umum dengan validasi balance debet=kredit, tidak bisa simpan jika tidak balance
- [ ] Buku Besar per akun menampilkan mutasi dan saldo berjalan dengan benar
- [ ] Neraca Lajur menampilkan agregasi semua akun, total debet = total kredit
- [ ] Laba Rugi menampilkan struktur lengkap sesuai bagian 5, angka Laba Bersih benar
- [ ] Neraca menampilkan struktur lengkap, Total Aktiva = Total Kewajiban & Modal (balance)
- [ ] Export PDF dan Excel berfungsi untuk Laba Rugi & Neraca
- [ ] Tidak ada error/warning di console maupun log Laravel saat menjalankan semua flow di atas

## 11. Keamanan & Akses

- **Matikan registrasi publik**: Breeze secara default menyediakan route `/register` yang bisa diakses siapa saja. Untuk internal tool ini, route `register` (GET & POST) dan link "Register" di halaman login HARUS dihapus/dinonaktifkan. User baru hanya boleh dibuat oleh admin lewat Modul User Management (bagian 5, Modul 4).
- **Seeder admin pertama**: buat `UserSeeder` yang membuat 1 akun admin default saat `php artisan migrate --seed` pertama kali dijalankan (misal email `admin@transkargo.local`, password di-generate random lalu ditampilkan di output console — JANGAN hardcode password sederhana di kode).
- **Middleware**: pastikan semua route di luar `login`/`logout` dibungkus middleware `auth`. Route Modul User Management dibungkus middleware tambahan `role:admin`.
- **Rate limiting login**: pastikan default Laravel throttle pada route login tetap aktif (jangan dihapus saat kustomisasi Breeze).

## 12. Locale, Timezone & Format Angka

- Set `'timezone' => 'Asia/Jakarta'` dan `'locale' => 'id'` di `config/app.php`.
- Buat helper terpusat untuk format Rupiah, jangan format manual berulang-ulang di tiap Blade view. Contoh:
  ```php
  // app/Helpers/NumberFormatter.php atau gunakan Number facade Laravel
  // Format: 1.000.000  (titik = pemisah ribuan, tanpa desimal)
  // Nol ditampilkan sebagai "-"
  // Negatif dalam kurung: (1.000.000)
  function formatRupiah($value): string {
      if ($value == 0) return '-';
      $formatted = number_format(abs($value), 0, ',', '.');
      return $value < 0 ? "($formatted)" : $formatted;
  }
  ```
- Daftarkan sebagai Blade directive `@rupiah($value)` agar dipakai konsisten di semua tabel laporan (Buku Besar, Neraca Lajur, Laba Rugi, Neraca, Catatan LK).
- Format tanggal Indonesia di tampilan (`d/m/Y`), tapi tetap simpan sebagai `date` standar di database.

## 13. Audit Trail

- Tambah kolom `updated_by` (FK `users.id`, nullable) di tabel `journal_entries`, isi otomatis lewat model event (`saving`) dengan `auth()->id()`.
- Install `spatie/laravel-activitylog` dan aktifkan logging untuk model `JournalEntry`, `Account`, dan `AccountingPeriod` — minimal catat: siapa, kapan, aksi apa (create/update/delete), dan apa yang berubah (old value → new value).
- Buat halaman sederhana "Riwayat Aktivitas" (read-only, bisa diakses admin) untuk menampilkan log ini, terutama untuk jurnal yang diedit/dihapus.
- Jurnal yang sudah `posted` tidak boleh dihapus permanen — kalau perlu dibatalkan, gunakan jurnal koreksi/reversal baru (jurnal balik), bukan delete. Ini standar praktik akuntansi agar histori tetap utuh untuk audit.

## 14. Testing (Wajib Minimal)

Gunakan Pest atau PHPUnit (default Laravel). Buat test untuk skenario kritis berikut SEBELUM lanjut ke fase berikutnya:

1. **Test validasi balance jurnal**: input jurnal dengan total debet ≠ total kredit → harus gagal disimpan, muncul error validasi.
2. **Test lock periode**: coba input/edit jurnal di periode yang `status = closed` → harus ditolak.
3. **Test akun header tidak bisa dipakai di jurnal**: pilih akun dengan `is_header = true` di baris jurnal → harus gagal validasi.
4. **Test konsistensi laporan** (paling penting): setelah input beberapa jurnal sample, generate Laba Rugi dan Neraca untuk periode yang sama → assert bahwa nilai "Laba Bersih" di Laba Rugi SAMA DENGAN nilai "Laba Periode Berjalan" di baris Modal pada Neraca.
5. **Test balance Neraca**: assert Total Aktiva = Total Kewajiban & Modal setelah beberapa transaksi sample diinput.
6. **Test tutup periode**: setelah periode ditutup, assert saldo awal periode berikutnya otomatis terisi sama dengan saldo akhir periode yang ditutup.

Jalankan `php artisan test` dan pastikan semua hijau sebelum lanjut ke Fase 5/6.

## 15. Cetak Laporan (PDF)

Layout PDF untuk Laba Rugi dan Neraca harus menyertakan:
- Kop: nama perusahaan "PT. Transkargo Solusindo", judul laporan, periode pelaporan (format "Periode 1 Januari 2026 s/d 31 Januari 2026")
- Tabel laporan sesuai struktur di bagian 5 (gunakan format Rupiah dari bagian 12)
- Footer/blok tanda tangan: dua kolom — "Dibuat oleh" (Staff Akunting) dan "Disetujui oleh" (Direktur/Owner), masing-masing dengan garis tanda tangan dan nama (nama bisa kosong/placeholder dulu, jangan hardcode nama orang)
- Tanggal cetak otomatis di pojok kanan bawah
- Gunakan view Blade terpisah khusus untuk PDF (`resources/views/reports/pdf/...`) yang stylingnya disederhanakan (dompdf tidak support semua fitur Tailwind/CSS modern — gunakan CSS inline sederhana untuk view PDF ini, jangan reuse layout Tailwind utama).

## 16. Setup Git & Environment

- Pastikan `.gitignore` mencakup default Laravel (`vendor/`, `node_modules/`, `.env`, `storage/*.key`, dll) — biasanya sudah otomatis dari `laravel new`, tapi cek ulang.
- Commit `.env.example` dengan semua key yang dibutuhkan (DB_*, APP_*, MAIL_* jika dipakai) terisi placeholder, supaya developer lain tinggal copy ke `.env`.
- Catat requirement versi di README:
    - PHP versi minimum sesuai requirement Laravel versi yang dipakai (cek dengan `php artisan --version` setelah instalasi, dan pastikan PHP lokal/server memenuhi)
    - Node.js untuk build asset Vite/Tailwind
    - MySQL versi 8.x+
- Tambahkan `composer.json` script `"post-create-project-cmd"` atau catatan manual di README untuk langkah setup awal: `composer install`, `npm install && npm run build`, `php artisan migrate --seed`, `php artisan serve`.

---

## Update pada Bagian 10 (Definition of Done) — tambahan checklist

- [ ] Route `/register` sudah dinonaktifkan, hanya admin yang bisa membuat user baru
- [ ] Format Rupiah konsisten di semua laporan (titik ribuan, nol jadi "-", negatif dalam kurung)
- [ ] Audit trail aktif untuk jurnal, akun, dan periode — bisa dilihat di halaman Riwayat Aktivitas
- [ ] Semua test minimal di bagian 14 lulus (`php artisan test` hijau)
- [ ] PDF Laba Rugi & Neraca punya kop perusahaan, periode, dan blok tanda tangan
- [ ] `.env.example` dan README setup instruction lengkap

## 17. PERUBAHAN ARSITEKTUR: Multi-Tenant (Multi-Perusahaan)

> **PENTING — baca ini dulu sebelum lanjut.** Bagian 1 di CLAUDE.md utama bilang "single company, tidak perlu tabel `companies`". Itu DIBATALKAN. Karena sistem ini akan dipakai untuk banyak PT berbeda, harus multi-tenant dari awal. Mengubah ke multi-tenant setelah aplikasi setengah jadi jauh lebih mahal daripada mendesainnya dari awal — jadi lakukan ini di **Fase 0**, sebelum modul lain dibangun.

### 17.1 Tabel `companies`
```
id                  bigint PK
name                varchar(255)
npwp                varchar(30) nullable      -- NPWP perusahaan
address             text nullable
phone               varchar(30) nullable
logo_path           varchar(255) nullable     -- untuk kop laporan PDF
default_currency    varchar(3) default 'IDR'
created_at, updated_at
```

### 17.2 Strategi Tenant Scoping
Gunakan pendekatan **shared database, shared schema, dibedakan kolom `company_id`** (paling praktis untuk skala kecil-menengah, lebih mudah maintenance dibanding database-per-tenant):

- SEMUA tabel transaksional dan master data (`accounts`, `accounting_periods`, `opening_balances`, `journal_entries`, `customers`, `vendors`, `items`, dst — semua yang didefinisikan di seluruh dokumen ini) WAJIB punya kolom `company_id bigint FK`.
- Buat Global Scope Eloquent (`CompanyScope`) yang otomatis filter semua query berdasarkan `company_id` dari company yang sedang aktif di session user — supaya tidak ada developer/AI agent lupa nambahin `where('company_id', ...)` manual di satu tempat dan bocor data antar perusahaan.
- Tambah kolom `current_company_id` di tabel `users`, atau buat tabel pivot `company_user` (`user_id`, `company_id`, `role`) jika satu user bisa akses lebih dari satu perusahaan (misal akuntan publik yang pegang banyak klien).
- UI: dropdown "switch company" di header, tersimpan di session.
- **Test wajib**: buat 2 company dummy, pastikan data jurnal company A TIDAK PERNAH muncul saat login sebagai user company B.

### 17.3 Setup Wizard untuk Company Baru
Saat company baru dibuat, sediakan wizard onboarding:
1. Input data perusahaan (nama, NPWP, alamat, logo)
2. Pilih template Chart of Account (bisa pilih "Jasa/Trading", "Manufaktur", "Kosong") — gunakan `coa_seed.csv` yang ada sebagai salah satu template default ("Jasa Logistik")
3. Set periode akuntansi pertama
4. Input saldo awal (opsional, bisa skip dan isi nanti)

---

## 18. Master Data Tambahan: Customer & Vendor

```
customers: id, company_id, code, name, address, phone, npwp, payment_term_days, credit_limit, ar_account_id (FK accounts — default akun piutang), is_active
vendors:   id, company_id, code, name, address, phone, npwp, payment_term_days, ap_account_id (FK accounts — default akun hutang), is_active
```
Tanpa ini, AR/AP Subsidiary Ledger (bagian 21) tidak bisa dibuat per pelanggan/vendor — sebelumnya sistem hanya tahu total piutang di level akun, bukan piutang ke siapa.

---

## 19. Modul Penjualan (Sales)

```
sales_invoices: id, company_id, customer_id, invoice_no, invoice_date, due_date, status (draft/posted/paid/partial/void), subtotal, tax_amount, total, currency, exchange_rate
sales_invoice_lines: id, sales_invoice_id, item_id (nullable, bisa jasa tanpa item), description, qty, unit_price, discount, tax_rate, line_total
sales_payments: id, company_id, customer_id, payment_date, amount, payment_method, reference_no
sales_payment_allocations: id, sales_payment_id, sales_invoice_id, amount  -- 1 pembayaran bisa cover beberapa invoice
```
**Logic wajib**: saat invoice di-posting, sistem otomatis generate `journal_entry` (Debit Piutang Usaha, Kredit Pendapatan + Hutang PPN Keluaran jika ada pajak) — jangan minta user jurnal manual untuk transaksi penjualan rutin.

## 20. Modul Pembelian (Purchase)

```
purchase_invoices: id, company_id, vendor_id, invoice_no, invoice_date, due_date, status, subtotal, tax_amount, total, currency, exchange_rate
purchase_invoice_lines: id, purchase_invoice_id, item_id, description, qty, unit_price, discount, tax_rate, line_total
purchase_payments: id, company_id, vendor_id, payment_date, amount, payment_method, reference_no
purchase_payment_allocations: id, purchase_payment_id, purchase_invoice_id, amount
```
Sama seperti Sales: posting invoice pembelian otomatis generate jurnal (Debit Persediaan/Biaya + Pajak Masukan, Kredit Hutang Usaha).

---

## 21. Modul Hutang & Piutang (AR/AP Subsidiary Ledger)

- **Kartu Piutang per Customer**: list semua invoice + pembayaran per customer, saldo outstanding, aging (0-30/31-60/61-90/>90 hari)
- **Kartu Hutang per Vendor**: sama, untuk hutang
- **Laporan Aging Piutang & Hutang**: ringkasan semua customer/vendor dengan kolom umur hutang/piutang — laporan ini WAJIB ada di sistem akuntansi PT yang serius, dipakai tim finance untuk follow-up penagihan/pembayaran tiap minggu
- Saldo di Kartu Piutang/Hutang harus selalu sinkron dengan saldo akun "Piutang Usaha"/"Hutang Usaha" di Neraca (akun tersebut jadi **control account**, kartu per customer/vendor jadi rinciannya — total kartu harus sama dengan saldo control account)

---

## 22. Modul Aset Tetap & Depresiasi Otomatis

```
fixed_assets: id, company_id, asset_code, name, account_id (FK akun aset, mis. "KENDARAAN"), accumulated_depreciation_account_id, depreciation_expense_account_id, acquisition_date, acquisition_cost, useful_life_months, depreciation_method (straight_line/declining_balance), salvage_value, status (active/disposed)
asset_depreciation_schedules: id, fixed_asset_id, period (bulan/tahun), depreciation_amount, accumulated_amount, book_value, is_posted, journal_entry_id nullable
```
- Generate schedule depresiasi otomatis saat aset dibuat (berdasarkan metode & umur ekonomis)
- Setiap tutup periode, sistem cek aset mana yang punya schedule depresiasi bulan ini & belum di-posting → tombol "Posting Depresiasi Bulan Ini" generate jurnal otomatis (Debit Biaya Penyusutan, Kredit Akumulasi Penyusutan) untuk semua aset sekaligus
- Modul disposal aset (jual/hapus aset) yang otomatis hitung laba/rugi penjualan aset tetap (akun "Laba/Rugi Penjualan Aktiva Tetap" sudah ada di COA klien)

---

## 23. Modul Persediaan (Inventory)

```
items: id, company_id, sku, name, unit, category, costing_method (fifo/average), inventory_account_id, cogs_account_id, sales_account_id, min_stock
stock_movements: id, company_id, item_id, movement_date, type (in/out/adjustment), qty, unit_cost, reference_type, reference_id, journal_entry_id
item_stock_balances: id, item_id, qty_on_hand, average_cost  -- bisa juga dihitung on-the-fly dari stock_movements, pilih sesuai performa
```
- Stock masuk dari Purchase Invoice, stock keluar dari Sales Invoice — otomatis, user tidak input manual dua kali
- Laporan: Kartu Stok per item (mutasi + saldo), Laporan Nilai Persediaan (untuk cross-check ke akun Persediaan di Neraca)
- Untuk versi awal, cukup metode **Average Cost** (lebih simpel diimplementasikan dibanding FIFO penuh) — FIFO bisa jadi peningkatan di rilis berikutnya

---

## 24. Modul Bank & Rekonsiliasi

```
bank_accounts: id, company_id, account_id (FK accounts, mis. "BANK DANAMON"), bank_name, account_number, account_holder_name
bank_statement_imports: id, bank_account_id, statement_date, opening_balance, closing_balance, imported_at
bank_statement_lines: id, bank_statement_import_id, transaction_date, description, debit, credit, is_reconciled, matched_journal_entry_line_id nullable
```
- Import rekening koran (CSV/Excel dari bank, format bisa beda-beda per bank — buat parser fleksibel atau form mapping kolom manual saat import)
- UI rekonsiliasi: tampilkan sisi kiri (mutasi buku/jurnal di akun bank) vs sisi kanan (mutasi rekening koran), user match satu-satu atau sistem auto-match berdasarkan tanggal+nominal yang cocok
- Laporan selisih yang belum cocok (outstanding check, deposit in transit)

---

## 25. Modul Pajak

- **PPN**: setiap transaksi penjualan/pembelian dengan pajak otomatis catat ke akun "Hutang PPN - PPN Keluaran" / "Pajak Dibayar Dimuka - PPN Masukan" (akun sudah ada di COA). Laporan rekap PPN Keluaran vs Masukan per periode (dasar untuk lapor SPT PPN, walau tidak langsung terintegrasi e-Faktur)
- **PPh 21/23/25/29**: modul sederhana untuk catat potongan/setoran pajak karyawan & badan, akunnya sudah tersedia di COA. Untuk versi awal cukup pencatatan, tidak perlu hitung otomatis dari PTKP/tarif progresif (itu lebih cocok jadi modul payroll terpisah, scope besar sendiri)
- **PPh Badan**: di akhir tahun, hitung dari Laba Rugi fiskal — untuk versi awal cukup field manual input (akuntan tetap perlu hitung koreksi fiskal secara terpisah, tidak realistis full-otomatis tanpa modul pajak khusus)

---

## 26. Laporan Tambahan: Arus Kas & Perubahan Modal

### Laporan Arus Kas (Cash Flow Statement)
Gunakan **metode tidak langsung (indirect method)** — lebih mudah dihasilkan otomatis dari data jurnal dibanding metode langsung:
- **Arus Kas Operasi**: mulai dari Laba Bersih, + penyusutan/amortisasi (non-cash), +/- perubahan piutang, persediaan, hutang usaha
- **Arus Kas Investasi**: pembelian/penjualan aset tetap, investasi
- **Arus Kas Pendanaan**: penarikan/pelunasan hutang bank/leasing, modal disetor, prive
- Hasil akhir harus match dengan perubahan saldo kas & bank antara awal-akhir periode (cross-check otomatis)

### Laporan Perubahan Modal (Statement of Changes in Equity)
- Saldo Modal Awal periode → + Laba Periode Berjalan → - Prive/Dividen → = Saldo Modal Akhir periode
- Datanya sebenarnya sudah ada di akun Modal (bagian "M O D A L" di COA), laporan ini hanya menyajikan ulang dalam format pergerakan, bukan tabel baru

---

## 27. Multi-Currency Penuh

Karena akan dipakai perusahaan lain yang mungkin transaksi valas aktif (COA klien sudah punya akun USD/EUR), upgrade dari "treat sebagai rupiah" jadi proper multi-currency:

```
exchange_rates: id, company_id, currency_code, rate_date, rate_to_idr
```
- Setiap transaksi (jurnal, invoice) punya field `currency` dan `exchange_rate` — jika currency ≠ IDR, simpan nilai asli DAN nilai konversi ke IDR (fungsional currency)
- Laba/Rugi Selisih Kurs dihitung otomatis saat ada perbedaan kurs antara tanggal transaksi vs tanggal pembayaran/tutup periode (akun "Laba/Rugi Selisih Kurs" sudah ada di COA)
- Laporan keuangan utama tetap disajikan dalam IDR (mata uang fungsional), tapi bisa drill-down lihat nilai asli mata uang asing

---

## 28. Role & Permission Granular

Upgrade dari 2 role sederhana (admin/staff) jadi permission-based, supaya fleksibel untuk berbagai struktur tim klien berbeda-beda:

Gunakan package `spatie/laravel-permission`. Role contoh (bisa disesuaikan per company):
- **Super Admin** (Anda/pengembang) — akses semua company
- **Owner/Direktur** — full akses 1 company, termasuk approval, tidak bisa diutak-atik staff
- **Finance Manager** — approve jurnal, tutup periode, lihat semua laporan
- **Staff Akunting** — input jurnal/invoice (draft), tidak bisa posting tanpa approval
- **Staff Sales/Purchasing** — hanya akses modul invoice terkait, tidak lihat laporan keuangan
- **Viewer** — read-only semua laporan, untuk auditor eksternal misalnya

Implementasikan approval workflow (yang sebelumnya di-skip di scope awal): jurnal/invoice berstatus `draft` → `pending_approval` → `posted`, dengan log siapa approve.

---

## 29. Dashboard & Analytics

- Grafik tren Pendapatan vs Biaya per bulan (line/bar chart — gunakan Chart.js)
- Cash position (saldo kas & bank gabungan) real-time
- Top 5 customer dengan piutang terbesar, top 5 vendor dengan hutang terbesar
- Rasio keuangan utama (dari Financial Highlight) ditampilkan sebagai card ringkas di dashboard
- Reminder/notifikasi: invoice yang akan jatuh tempo dalam 7 hari, periode yang belum ditutup lebih dari 1 bulan

---

## 30. Catatan Realistis tentang Scope & Timeline

Saya perlu jujur soal ini supaya Anda bisa atur ekspektasi (ke diri sendiri atau ke calon klien berikutnya):

- **Versi di CLAUDE.md awal (bagian 1-16)**: cukup untuk 1 klien dengan kebutuhan sesuai Excel mereka. Realistis dikerjakan 1 developer + AI agent dalam **beberapa minggu**.
- **Dengan semua fitur di addendum ini (bagian 17-29)**: ini sudah setara aplikasi akuntansi/ERP kecil-menengah yang dijual sebagai produk (mirip early-stage Accurate/Jurnal.id). Realistis butuh **beberapa bulan**, bahkan untuk tim kecil — terutama modul Inventory, Multi-Currency, dan Bank Reconciliation yang masing-masing punya banyak edge case.
- **Rekomendasi urutan kalau mau jadi produk reusable**: jangan bangun semua sekaligus. Selesaikan dulu versi inti (1-16) + multi-tenant (17) sebagai **fondasi produk**, lalu tambahkan modul 18-29 satu-satu sebagai rilis berikutnya, idealnya divalidasi dengan klien nyata di setiap rilis (klien Transkargo bisa jadi "klien pertama" untuk validasi versi inti, baru modul lain ditambah saat ada klien lain yang butuh).
- Membangun semuanya sekaligus lewat satu sesi AI agent berisiko menghasilkan kode yang saling tumpang tindih dan sulit di-debug. Sarankan ke Claude Code: **kerjakan bagian 17 (multi-tenant) dan bagian 1-16 dulu sampai tuntas dan teruji**, baru lanjut modul lain satu per satu di sesi terpisah.

## 31. Modul Cicilan / Angsuran (Hutang Bank & Leasing)

Saat ini sistem cuma punya akun "Hutang Bank"/"Hutang Leasing" di COA, tapi tidak ada yang generate jadwal cicilan otomatis — masih harus jurnal manual tiap bulan. Modul ini memperbaiki itu.

```
loan_facilities: id, company_id, name (mis. "KMK Bank Danamon" / "Leasing Mobil Pajero"),
                  type (bank_loan/leasing/kpr/kredit_investasi),
                  liability_account_id (FK accounts, mis. "HUTANG BANK KREDIT MODAL KERJA"),
                  interest_expense_account_id (FK accounts, mis. "BUNGA LEASING KENDARAAN"),
                  principal_amount, interest_rate_per_year, tenor_months,
                  start_date, installment_amount (jika flat), calculation_method (flat/anuitas/efektif),
                  counterparty (nama bank/leasing co), status (active/paid_off)

loan_installment_schedules: id, loan_facility_id, installment_no, due_date,
                  principal_amount, interest_amount, total_amount,
                  status (unpaid/paid/overdue), paid_date, journal_entry_id nullable
```

- Saat `loan_facility` dibuat, sistem **otomatis generate seluruh jadwal cicilan** (`loan_installment_schedules`) sesuai tenor & metode hitung (flat paling simpel untuk versi awal — anuitas/efektif bisa menyusul)
- Tombol "Bayar Cicilan" di tiap baris jadwal → otomatis generate jurnal (Debit Hutang Bank/Leasing sebesar pokok + Debit Biaya Bunga, Kredit Kas/Bank sebesar total cicilan)
- Status `overdue` otomatis kalau `due_date` lewat dan belum `paid`
- **Laporan Jadwal Angsuran**: per fasilitas, tampilkan sisa pokok, sisa tenor, total bunga yang akan dibayar
- **Dashboard reminder**: cicilan yang jatuh tempo 7 hari ke depan
- Ini berlaku juga untuk **Hutang Kredit Investasi (KI)** dan **KPR** yang sudah ada akunnya di COA klien — satu modul ini cover semua jenis hutang berjadwal

---

## 32. Modul Kasbon / Uang Muka Karyawan

COA klien sudah punya akun "UANG MUKA KARYAWAN A" sampai "ADVANCE TO Others" — tapi tanpa modul ini, tracking siapa pinjam berapa dan sudah dikembalikan berapa masih manual di kepala.

```
employees: id, company_id, employee_no, name, department, position, bank_account_no, is_active

cash_advances (kasbon): id, company_id, employee_id, advance_no, advance_date,
                  amount, reason, account_id (FK accounts — akun uang muka karyawan),
                  settlement_method (potong_gaji/kembali_tunai/campuran),
                  status (outstanding/partial/settled), journal_entry_id (saat kasbon dikeluarkan)

cash_advance_settlements: id, cash_advance_id, settlement_date, amount,
                  method (potong_gaji/kembali_tunai), journal_entry_id
```

- Kasbon diberikan → form input → otomatis jurnal (Debit "Uang Muka Karyawan [Nama]", Kredit Kas/Bank)
- Pelunasan kasbon (baik dipotong gaji bulanan atau dikembalikan tunai) → otomatis jurnal (Debit Kas/Bank atau Debit Biaya Gaji [jika potong gaji], Kredit Uang Muka Karyawan) — saldo kasbon otomatis berkurang
- **Kartu Kasbon per Karyawan**: riwayat kasbon + pelunasan + saldo outstanding, mirip Kartu Piutang tapi untuk karyawan
- **Laporan Saldo Kasbon Seluruh Karyawan**: untuk HR/Finance cross-check saat closing bulanan
- Saldo total di modul ini harus selalu sama dengan saldo akun "Uang Muka Karyawan"/"Advance to..." gabungan di Neraca (akun jadi *control account*, sama prinsipnya seperti Piutang/Hutang di bagian 21)

---

## 33. Pelaporan Pajak — Supaya "Enak" Dipakai untuk Lapor

> Catatan jujur dulu: integrasi otomatis langsung ke sistem DJP (e-Faktur, e-SPT, Coretax) butuh **sertifikat elektronik resmi dan API khusus dari DJP**, itu di luar kapasitas aplikasi internal biasa dan butuh izin/registrasi tersendiri. Yang realistis dan tetap sangat membantu: sistem ini **menyiapkan semua data pajak dalam format siap pakai**, sehingga tim akunting tinggal copy/import ke aplikasi pajak resmi, bukan rekap manual dari nol setiap bulan. Itu yang akan dibangun di modul ini.

```
tax_transactions: id, company_id, tax_type
                  (ppn_keluaran/ppn_masukan/pph21/pph23/pph25/pph29/pph4a2/pph15/pph26),
                  transaction_date, reference_type (sales_invoice/purchase_invoice/journal_entry/payroll),
                  reference_id,
                  counterparty_name, counterparty_npwp,
                  dpp (dasar pengenaan pajak), tax_rate, tax_amount,
                  document_no (no. faktur pajak / no. bukti potong),
                  period_month, period_year,
                  status (belum_lapor/sudah_lapor), reported_at nullable
```

### 33.1 PPN (Pajak Pertambahan Nilai)
- Setiap Sales Invoice (bagian 19) dengan pajak → otomatis insert `tax_transactions` jenis `ppn_keluaran`
- Setiap Purchase Invoice (bagian 20) dengan pajak → otomatis insert jenis `ppn_masukan`
- **Laporan Rekap PPN Keluaran & Masukan per periode**: kolom-kolomnya dibuat **mendekati format upload e-Faktur** (No. Faktur, Tanggal, NPWP Lawan Transaksi, Nama Lawan Transaksi, DPP, PPN) — export ke Excel/CSV, tinggal disesuaikan sedikit ke template resmi e-Faktur DJP
- Saldo PPN Keluaran - PPN Masukan = PPN yang harus disetor/lebih bayar bulan itu, tampilkan sebagai summary di laporan

### 33.2 PPh 21 (Pajak Karyawan)
- Modul payroll sederhana (di luar scope penuh, tapi minimal): per karyawan per bulan, input gaji bruto + PPh 21 yang dipotong (perhitungan tarif PTKP/progresif tetap dilakukan akuntan secara manual/aplikasi payroll terpisah — sistem ini hanya **mencatat hasilnya**, bukan menghitung tarif pajak otomatis, karena itu modul payroll besar tersendiri)
- Generate **Bukti Potong PPh 21 (1721-A1) sederhana** per karyawan per tahun, PDF, untuk dibagikan ke karyawan
- Rekap total PPh 21 dipotong per bulan untuk dasar setor & lapor SPT Masa PPh 21

### 33.3 PPh 23 (Pajak Jasa ke Vendor)
- Saat Purchase Invoice dari vendor jasa (konsultan, sewa, dll) dengan PPh 23 → otomatis insert `tax_transactions`
- Generate **Bukti Potong PPh 23** otomatis (PDF) untuk diberikan ke vendor — ini sering jadi permintaan vendor tiap transaksi, kalau manual cukup merepotkan
- Rekap PPh 23 dipotong per periode

### 33.4 PPh 25 & PPh 29 (Pajak Badan)
- PPh 25: catat cicilan pajak bulanan yang harus disetor (nilai biasanya dari SPT tahunan sebelumnya, input manual per tahun, sistem cuma jadwalkan & track pembayaran tiap bulan — mirip modul cicilan di bagian 31 tapi untuk pajak)
- PPh 29: di akhir tahun fiskal, hitung dari Laba Rugi tahunan (Laba Akuntansi → perlu koreksi fiskal manual oleh akuntan, sistem sediakan field untuk input koreksi fiskal lalu hitung PPh Badan terutang dikurangi PPh 25 yang sudah disetor = kurang/lebih bayar PPh 29)

### 33.5 Dashboard Pajak
- Ringkasan semua jenis pajak per periode: sudah disetor vs belum, jatuh tempo lapor (tanggal 20 untuk PPN, tanggal 10/15 untuk PPh tergantung jenis — tampilkan sebagai reminder)
- Export semua rekap pajak satu klik per periode (zip berisi beberapa file Excel: PPN Keluaran, PPN Masukan, PPh 21, PPh 23) untuk diserahkan ke konsultan pajak atau diinput ke aplikasi DJP

---

## Update Checklist Definition of Done (tambahan dari addendum ini)

- [ ] Hutang bank/leasing punya jadwal cicilan otomatis, bisa dibayar per baris, jurnal otomatis terbentuk
- [ ] Kasbon karyawan tercatat dari pemberian sampai pelunasan, ada kartu per karyawan
- [ ] Rekap PPN Keluaran/Masukan bisa diexport dalam format siap-upload e-Faktur
- [ ] Bukti Potong PPh 21 & PPh 23 bisa di-generate otomatis sebagai PDF
- [ ] Saldo control account (Piutang, Hutang, Uang Muka Karyawan) di Neraca selalu sama dengan total kartu/sub-ledger masing-masing — buat 1 query/test otomatis yang cross-check ini setiap kali laporan digenerate, supaya kalau ada selisih langsung kelihatan
