<?php

namespace Database\Seeders;

use App\Models\AccountingPeriod;
use Illuminate\Database\Seeder;

class AccountingPeriodSeeder extends Seeder
{
    public $companyId;

    public function run(): void
    {
        $currentYear = now()->year;

        for ($month = 1; $month <= 12; $month++) {
            AccountingPeriod::create([
                'company_id' => $this->companyId,
                'month' => $month,
                'year' => $currentYear,
                'status' => 'open',
            ]);
        }

        if (now()->month > 1) {
            $prevYear = $currentYear - 1;
            for ($month = 1; $month <= 12; $month++) {
                AccountingPeriod::create([
                    'company_id' => $this->companyId,
                    'month' => $month,
                    'year' => $prevYear,
                    'status' => 'closed',
                    'closed_at' => now()->startOfYear()->subDay(),
                ]);
            }
        }

        if ($this->command) $this->command->info('Accounting periods seeded successfully.');
    }
}
