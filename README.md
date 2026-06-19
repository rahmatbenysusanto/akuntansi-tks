# Sistem Akuntansi PT. Transkargo Solusindo

Sistem akuntansi **double-entry** berbasis web untuk PT. Transkargo Solusindo (perusahaan jasa cargo/logistik).

## Fitur

- **Chart of Account** — 368 akun sesuai COA asli klien, tampilan tree/hierarki
- **Periode Akuntansi** — Buka/tutup periode dengan auto-generate saldo awal
- **Saldo Awal** — Input saldo awal per akun per periode
- **Jurnal Umum** — Dynamic rows, real-time balance validation, draft/posting
- **Buku Besar** — Mutasi per akun dengan running balance
- **Neraca Lajur** — 12 kolom (saldo awal, mutasi, saldo akhir, Laba Rugi, Neraca)
- **Laba Rugi** — Struktur lengkap (Pendapatan → HPP → Biaya → Laba Bersih)
- **Neraca** — Aktiva = Kewajiban + Modal, dengan validasi balance
- **Catatan Atas LK** — Drill-down rincian per akun
- **Financial Highlight** — 8 rasio keuangan otomatis
- **Export PDF & Excel** — Untuk Laba Rugi & Neraca
- **Audit Trail** — Riwayat perubahan jurnal, akun, dan periode
- **User Management** — CRUD user dengan role admin/staff

## Requirements

| Software | Versi Minimum |
|---|---|
| PHP | ^8.2 |
| MySQL | 8.x+ |
| Node.js | 18.x+ |
| Composer | 2.x+ |

## Instalasi

```bash
# 1. Clone repositori
git clone <repo-url> transkargo-accounting
cd transkargo-accounting

# 2. Install dependencies PHP
composer install

# 3. Buat file environment
cp .env.example .env
# Edit .env sesuai konfigurasi database MySQL

# 4. Generate application key
php artisan key:generate

# 5. Install & build frontend assets
npm install
npm run build

# 6. Buat database MySQL
mysql -u root -p -e "CREATE DATABASE trans_kargo_akuntansi CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# 7. Jalankan migrasi + seed data
php artisan migrate --seed

# 8. Jalankan development server
php artisan serve
# Buka http://localhost:8000
```

## Login Default

Setelah `php artisan migrate --seed`, password random akan tampil di console.

## Testing

```bash
php artisan test
```

## Catatan

- Register publik dinonaktifkan — hanya admin yang bisa membuat user baru
- Jurnal yang sudah `posted` tidak bisa dihapus, gunakan jurnal koreksi
- Format angka: Rupiah Indonesia (titik ribuan, nol = "-", negatif dalam kurung)
- Timezone: Asia/Jakarta
