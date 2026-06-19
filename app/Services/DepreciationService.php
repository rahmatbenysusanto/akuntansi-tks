<?php

namespace App\Services;

use App\Models\FixedAsset;
use App\Models\AssetDepreciationSchedule;
use App\Models\AccountingPeriod;
use App\Models\JournalEntry;
use App\Models\Account;
use Illuminate\Support\Facades\DB;

class DepreciationService
{
    public function generateSchedule(FixedAsset $asset): void
    {
        $monthlyDepreciation = ($asset->acquisition_cost - $asset->salvage_value) / $asset->useful_life_months;
        $accumulated = 0;

        for ($i = 1; $i <= $asset->useful_life_months; $i++) {
            $accumulated += $monthlyDepreciation;
            $bookValue = $asset->acquisition_cost - $accumulated;

            AssetDepreciationSchedule::updateOrCreate(
                ['fixed_asset_id' => $asset->id, 'period_no' => $i],
                [
                    'schedule_date' => $asset->acquisition_date->addMonths($i),
                    'depreciation_amount' => $monthlyDepreciation,
                    'accumulated_amount' => $accumulated,
                    'book_value' => max(0, $bookValue),
                    'is_posted' => false,
                ]
            );
        }
    }

    public function postMonthlyDepreciation(int $companyId, int $year, int $month): array
    {
        $period = AccountingPeriod::where('company_id', $companyId)
            ->where('year', $year)->where('month', $month)->first();
        if (!$period || $period->status === 'closed') {
            return ['success' => false, 'message' => 'Periode tidak tersedia atau sudah ditutup.'];
        }

        $assets = FixedAsset::where('company_id', $companyId)->where('status', 'active')->get();
        $posted = 0;

        DB::transaction(function () use ($assets, $period, $companyId, &$posted) {
            foreach ($assets as $asset) {
                $schedule = AssetDepreciationSchedule::where('fixed_asset_id', $asset->id)
                    ->where('is_posted', false)
                    ->whereMonth('schedule_date', $period->month)
                    ->whereYear('schedule_date', $period->year)
                    ->first();

                if (!$schedule) continue;

                $entry = JournalEntry::create([
                    'company_id' => $companyId,
                    'accounting_period_id' => $period->id,
                    'entry_date' => $schedule->schedule_date,
                    'reference_no' => 'DEP-' . $asset->asset_code . '-' . $period->label,
                    'description' => 'Penyusutan ' . $asset->name . ' periode ' . $period->label,
                    'status' => 'posted',
                    'created_by' => auth()->id(),
                    'posted_at' => now(),
                ]);

                $entry->lines()->createMany([
                    ['account_id' => $asset->depreciation_expense_account_id, 'debit' => $schedule->depreciation_amount, 'credit' => 0, 'line_order' => 1],
                    ['account_id' => $asset->accumulated_depreciation_account_id, 'debit' => 0, 'credit' => $schedule->depreciation_amount, 'line_order' => 2],
                ]);
                $entry->lines()->update(['company_id' => $companyId]);

                $schedule->update(['is_posted' => true, 'journal_entry_id' => $entry->id]);
                $posted++;
            }
        });

        return ['success' => true, 'posted' => $posted, 'message' => "$posted jurnal penyusutan berhasil di-posting."];
    }
}
