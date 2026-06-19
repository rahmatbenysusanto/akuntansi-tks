<?php

namespace Database\Seeders;

use App\Models\AccountingPeriod;
use App\Models\JournalEntry;
use App\Models\OpeningBalance;
use App\Models\Account;
use App\Models\User;
use Illuminate\Database\Seeder;

class DummyTransactionSeeder extends Seeder
{
    public $companyId;

    public function run(): void
    {
        $admin = User::where('email', 'admin@transkargo.co.id')->first();
        if (!$admin) return;

        $period = AccountingPeriod::where('status', 'open')->orderBy('year')->orderBy('month')->first();
        if (!$period) return;

        // Cari akun berdasarkan data real dari CSV user
        $kasAccount = Account::where('code', '1.1.01.01.01')->first(); // KAS - IDR
        $bankAccount = Account::where('code', '1.1.01.02.01')->first(); // BANK DANAMON
        $modalAccount = Account::where('code', '3.1.01.00.00')->first(); // MODAL DISETOR
        $revenueAccount = Account::where('code', '4.1.02.00.00')->first(); // PENDAPATAN JASA
        $piutangAccount = Account::where('code', '1.1.04.01.01')->first(); // PIUTANG USAHA - IDR
        $gajiAccount = Account::where('code', '6.2.01.01.00')->first(); // BIAYA GAJI
        $sewaAccount = Account::where('code', '6.2.08.03.00')->first(); // BIAYA SEWA KANTOR
        $utangAccount = Account::where('code', '2.1.01.01.00')->first(); // HUTANG USAHA

        if ($this->command) $this->command->info('--- Account Lookup ---');
        if ($this->command) $this->command->info('Kas: ' . ($kasAccount?->code ?? 'NOT FOUND'));
        if ($this->command) $this->command->info('Modal: ' . ($modalAccount?->code ?? 'NOT FOUND'));
        if ($this->command) $this->command->info('Revenue: ' . ($revenueAccount?->code ?? 'NOT FOUND'));
        if ($this->command) $this->command->info('Piutang: ' . ($piutangAccount?->code ?? 'NOT FOUND'));
        if ($this->command) $this->command->info('Gaji: ' . ($gajiAccount?->code ?? 'NOT FOUND'));
        if ($this->command) $this->command->info('Sewa: ' . ($sewaAccount?->code ?? 'NOT FOUND'));
        if ($this->command) $this->command->info('Utang: ' . ($utangAccount?->code ?? 'NOT FOUND'));

        // Set opening balances (saldo awal)
        if ($kasAccount && $modalAccount) {
            OpeningBalance::updateOrCreate(
                ['accounting_period_id' => $period->id, 'account_id' => $kasAccount->id],
                ['company_id' => $this->companyId, 'debit' => 50000000, 'credit' => 0]
            );
        }
        if ($bankAccount) {
            OpeningBalance::updateOrCreate(
                ['accounting_period_id' => $period->id, 'account_id' => $bankAccount->id],
                ['debit' => 200000000, 'credit' => 0]
            );
        }
        if ($modalAccount) {
            OpeningBalance::updateOrCreate(
                ['accounting_period_id' => $period->id, 'account_id' => $modalAccount->id],
                ['debit' => 0, 'credit' => 250000000]
            );
        }

        // Transaction 1: Revenue from services (Piutang -> Pendapatan Jasa)
        if ($revenueAccount && $piutangAccount) {
            $entry = JournalEntry::create([
                'accounting_period_id' => $period->id,
                'company_id' => $this->companyId,
                'entry_date' => now()->startOfMonth()->addDays(5),
                'reference_no' => 'INV-001',
                'description' => 'Pendapatan Jasa - PT Customer A',
                'status' => 'posted',
                'created_by' => $admin->id,
                'posted_at' => now(),
            ]);
            $entry->lines()->createMany([
                ['account_id' => $piutangAccount->id, 'debit' => 75000000, 'credit' => 0, 'line_order' => 1],
                ['account_id' => $revenueAccount->id, 'debit' => 0, 'credit' => 75000000, 'line_order' => 2],
            ]);
        }

        // Transaction 2: Payment from customer (Kas -> Piutang)
        if ($kasAccount && $piutangAccount) {
            $entry = JournalEntry::create([
                'accounting_period_id' => $period->id,
                'entry_date' => now()->startOfMonth()->addDays(12),
                'reference_no' => 'BKM-001',
                'description' => 'Penerimaan Pembayaran dari PT Customer A',
                'status' => 'posted',
                'created_by' => $admin->id,
                'posted_at' => now(),
            ]);
            $entry->lines()->createMany([
                ['account_id' => $kasAccount->id, 'debit' => 50000000, 'credit' => 0, 'line_order' => 1],
                ['account_id' => $piutangAccount->id, 'debit' => 0, 'credit' => 50000000, 'line_order' => 2],
            ]);
        }

        // Transaction 3: Salary expense (Biaya Gaji -> Hutang)
        if ($gajiAccount && $utangAccount) {
            $entry = JournalEntry::create([
                'accounting_period_id' => $period->id,
                'entry_date' => now()->startOfMonth()->addDays(10),
                'reference_no' => 'BKM-002',
                'description' => 'Beban Gaji Karyawan Bulan Ini',
                'status' => 'posted',
                'created_by' => $admin->id,
                'posted_at' => now(),
            ]);
            $entry->lines()->createMany([
                ['account_id' => $gajiAccount->id, 'debit' => 35000000, 'credit' => 0, 'line_order' => 1],
                ['account_id' => $utangAccount->id, 'debit' => 0, 'credit' => 35000000, 'line_order' => 2],
            ]);
        }

        // Transaction 4: Office rent expense (Sewa Kantor -> Kas)
        if ($sewaAccount && $kasAccount) {
            $entry = JournalEntry::create([
                'accounting_period_id' => $period->id,
                'entry_date' => now()->startOfMonth()->addDays(15),
                'reference_no' => 'BKM-003',
                'description' => 'Pembayaran Sewa Kantor Bulan Ini',
                'status' => 'posted',
                'created_by' => $admin->id,
                'posted_at' => now(),
            ]);
            $entry->lines()->createMany([
                ['account_id' => $sewaAccount->id, 'debit' => 10000000, 'credit' => 0, 'line_order' => 1],
                ['account_id' => $kasAccount->id, 'debit' => 0, 'credit' => 10000000, 'line_order' => 2],
            ]);
        }

        // Transaction 5: Purchase of office supplies (via Kas)
        $suppliesAccount = Account::where('code', 'LIKE', '6.2.%')
            ->where('name', 'LIKE', '%PERLENGKAPAN%')
            ->orWhere('name', 'LIKE', '%ATK%')
            ->first();
        if (!$suppliesAccount) {
            $suppliesAccount = Account::where('category', 'biaya_operasional')
                ->where('code', 'LIKE', '6.2.%')
                ->where('is_header', false)
                ->first();
        }

        if ($suppliesAccount && $kasAccount) {
            $entry = JournalEntry::create([
                'accounting_period_id' => $period->id,
                'entry_date' => now()->startOfMonth()->addDays(18),
                'reference_no' => 'BKM-004',
                'description' => 'Pembelian Perlengkapan Kantor',
                'status' => 'posted',
                'created_by' => $admin->id,
                'posted_at' => now(),
            ]);
            $entry->lines()->createMany([
                ['account_id' => $suppliesAccount->id, 'debit' => 5000000, 'credit' => 0, 'line_order' => 1],
                ['account_id' => $kasAccount->id, 'debit' => 0, 'credit' => 5000000, 'line_order' => 2],
            ]);
        }

        if ($this->command) $this->command->info('Dummy transactions seeded successfully.');
    }
}
