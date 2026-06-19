<?php

namespace Database\Seeders;

use App\Models\Account;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AccountSeeder extends Seeder
{
    public $companyId;

    public function run(): void
    {
        $this->companyId = $this->companyId ?? (defined('static::SEEDING_COMPANY_ID') ? static::SEEDING_COMPANY_ID : null);
        // Prioritaskan csv/coa_seed.csv, fallback ke database/seeders/data/coa_seed.csv
        $csvPath = base_path('csv/coa_seed.csv');
        if (!file_exists($csvPath)) {
            $csvPath = database_path('seeders/data/coa_seed.csv');
        }

        if (!file_exists($csvPath)) {
            if ($this->command) $this->command->error("File coa_seed.csv tidak ditemukan. Letakkan di csv/coa_seed.csv atau database/seeders/data/coa_seed.csv");
            return;
        }

        // Deteksi delimiter: jika baris pertama mengandung ; gunakan ; jika tidak gunakan ,
        $file = fopen($csvPath, 'r');
        $firstLine = fgets($file);
        $delimiter = str_contains($firstLine, ';') ? ';' : ',';
        rewind($file);

        $header = fgetcsv($file, 0, $delimiter); // skip header

        $accounts = [];

        while (($row = fgetcsv($file, 0, $delimiter)) !== false) {
            if (count($row) < 4) continue;

            $namaAkun = trim($row[0]);
            $kodeAkun = trim($row[1]);
            $posSaldo = strtoupper(trim($row[2]));
            $posLaporan = strtoupper(trim($row[3]));

            if (empty($kodeAkun)) continue;

            // Hitung level dari jumlah segmen non-zero
            $segments = explode('.', $kodeAkun);
            $level = 0;
            foreach ($segments as $seg) {
                if ((int)$seg !== 0) $level++;
            }

            // Hitung parent_code: potong segmen terakhir
            $parentCode = null;
            $segCount = count($segments);
            for ($i = $segCount - 1; $i >= 0; $i--) {
                if ((int)$segments[$i] !== 0) {
                    $parentSegments = $segments;
                    $parentSegments[$i] = '00';
                    $parentCode = implode('.', $parentSegments);
                    // Jika hasilnya semua nol (00.0.00.00.00), set null (root level)
                    $allZero = true;
                    foreach (explode('.', $parentCode) as $seg) {
                        if ((int)$seg !== 0) { $allZero = false; break; }
                    }
                    if ($allZero) $parentCode = null;
                    break;
                }
            }

            // Mapping kategori dari segmen pertama
            $firstSeg = (int)$segments[0];
            $categoryMap = [
                1 => 'aktiva',
                2 => 'kewajiban',
                3 => 'modal',
                4 => 'pendapatan',
                5 => 'hpp',
                6 => 'biaya_operasional',
                7 => 'pendapatan_biaya_lain',
                8 => 'biaya_bunga',
                9 => 'pajak_penghasilan',
            ];

            $category = $categoryMap[$firstSeg] ?? 'biaya_operasional';

            // Map normal balance: override berdasarkan kategori untuk akun yang seharusnya KREDIT
            // CSV user semua bernilai DEBET, jadi kita koreksi berdasarkan kategori akuntansi
            $creditCategories = ['kewajiban', 'modal', 'pendapatan'];
            $normalBalance = in_array($category, $creditCategories) ? 'credit' : 'debit';

            // Map report type
            $reportType = strtolower($posLaporan === 'LABA RUGI' ? 'income_statement' : 'balance_sheet');

            $accounts[] = [
                'company_id' => $this->companyId,
                'code' => $kodeAkun,
                'name' => $namaAkun,
                'parent_code' => $parentCode,
                'level' => $level,
                'category' => $category,
                'normal_balance' => $normalBalance,
                'report_type' => $reportType,
                'is_header' => false, // will be determined later
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        fclose($file);

        // Perbaiki parent_code: jika tidak ada account dengan code tersebut, set null
        $codes = array_column($accounts, 'code');
        foreach ($accounts as &$account) {
            if ($account['parent_code'] !== null && !in_array($account['parent_code'], $codes)) {
                $account['parent_code'] = null;
            }
        }
        unset($account);

        // Determine is_header: an account is a header if it has children
        foreach ($accounts as &$account) {
            // Check if any other account has this account's code as parent_code
            $hasChildren = false;
            foreach ($accounts as $other) {
                if ($other['parent_code'] === $account['code']) {
                    $hasChildren = true;
                    break;
                }
            }
            $account['is_header'] = $hasChildren;
        }
        unset($account);

        // Insert in chunks to avoid memory issues
        $chunks = array_chunk($accounts, 100);
        DB::beginTransaction();
        try {
            foreach ($chunks as $chunk) {
                Account::insert($chunk);
            }
            DB::commit();
            if ($this->command) $this->command->info('Successfully seeded ' . count($accounts) . ' accounts.');
        } catch (\Exception $e) {
            DB::rollBack();
            if ($this->command) $this->command->error('Error seeding accounts: ' . $e->getMessage());
        }
    }
}
