<?php

namespace App\Services;

use App\Models\Account;
use App\Models\JournalEntryLine;
use App\Models\OpeningBalance;
use Illuminate\Support\Facades\DB;

class IncomeStatementService
{
    /**
     * Generate income statement for a single period or range of periods.
     */
    public function generate(int $periodId, ?int $periodFromId = null): array
    {
        $targetPeriodId = $periodId;
        $periodIds = [$periodId];

        // If periodFromId is provided and different, include all periods in range
        if ($periodFromId && $periodFromId !== $periodId) {
            $periodIds = $this->getPeriodRangeIds($periodFromId, $periodId);
            // For range-based income statement, use the last period's opening balances
            $targetPeriodId = $periodFromId;
        }

        // Collect all income statement accounts
        $accounts = Account::where('report_type', 'income_statement')
            ->orderBy('code')
            ->get()
            ->keyBy('id');

        // Get balances per account
        $balances = [];
        foreach ($accounts as $account) {
            // Opening balance: use the starting period's opening balance
            $opening = OpeningBalance::where('accounting_period_id', $targetPeriodId)
                ->where('account_id', $account->id)
                ->first();

            $openingDebit = (float) ($opening?->debit ?? 0);
            $openingCredit = (float) ($opening?->credit ?? 0);

            // Mutations: aggregate across ALL periods in range
            $mutations = JournalEntryLine::where('account_id', $account->id)
                ->whereHas('journalEntry', function ($q) use ($periodIds) {
                    $q->whereIn('accounting_period_id', $periodIds)
                      ->where('status', 'posted');
                })
                ->selectRaw('COALESCE(SUM(debit), 0) as total_debit, COALESCE(SUM(credit), 0) as total_credit')
                ->first();

            $totalDebit = $openingDebit + (float) $mutations->total_debit;
            $totalCredit = $openingCredit + (float) $mutations->total_credit;

            // Net balance
            if ($account->normal_balance === 'debit') {
                $balance = $totalDebit - $totalCredit;
            } else {
                $balance = $totalCredit - $totalDebit;
            }

            $balances[$account->id] = [
                'account' => $account,
                'debit' => $totalDebit,
                'credit' => $totalCredit,
                'balance' => max(0, $balance),
            ];
        }

        // Structure: Pendapatan (4.x)
        $revenueAccounts = $accounts->where('category', 'pendapatan');
        $revenueTotal = 0;
        $revenueDetails = [];
        foreach ($revenueAccounts as $acc) {
            $bal = $balances[$acc->id]['balance'] ?? 0;
            $revenueDetails[] = [
                'account' => $acc,
                'balance' => $bal,
            ];
            $revenueTotal += $bal;
        }

        // HPP (5.x)
        $hppAccounts = $accounts->where('category', 'hpp');
        $hppTotal = 0;
        $hppDetails = [];
        foreach ($hppAccounts as $acc) {
            $bal = $balances[$acc->id]['balance'] ?? 0;
            $hppDetails[] = [
                'account' => $acc,
                'balance' => $bal,
            ];
            $hppTotal += $bal;
        }

        // Gross Profit
        $grossProfit = $revenueTotal - $hppTotal;

        // Biaya Operasional (6.x)
        $opexAccounts = $accounts->where('category', 'biaya_operasional');
        $opexTotal = 0;
        $opexDetails = [];
        foreach ($opexAccounts as $acc) {
            $bal = $balances[$acc->id]['balance'] ?? 0;
            $opexDetails[] = [
                'account' => $acc,
                'balance' => $bal,
            ];
            $opexTotal += $bal;
        }

        // Operating Profit
        $operatingProfit = $grossProfit - $opexTotal;

        // Pendapatan/Biaya Lain (7.x)
        $otherAccounts = $accounts->where('category', 'pendapatan_biaya_lain');
        $otherTotal = 0;
        $otherDetails = [];
        foreach ($otherAccounts as $acc) {
            $bal = $balances[$acc->id]['balance'] ?? 0;
            $codePrefix = substr($acc->code, 0, 4);
            if ($codePrefix === '7.1.') {
                $otherTotal += $bal;
            } else {
                $otherTotal -= $bal;
            }
            $otherDetails[] = [
                'account' => $acc,
                'balance' => $bal,
            ];
        }

        $profitBeforeInterestTax = $operatingProfit + $otherTotal;

        // Biaya Bunga (8.x)
        $interestAccounts = $accounts->where('category', 'biaya_bunga');
        $interestTotal = 0;
        $interestDetails = [];
        foreach ($interestAccounts as $acc) {
            $bal = $balances[$acc->id]['balance'] ?? 0;
            $interestDetails[] = ['account' => $acc, 'balance' => $bal];
            $interestTotal += $bal;
        }

        $profitBeforeTax = $profitBeforeInterestTax - $interestTotal;

        // Pajak Penghasilan (9.x)
        $taxAccounts = $accounts->where('category', 'pajak_penghasilan');
        $taxTotal = 0;
        $taxDetails = [];
        foreach ($taxAccounts as $acc) {
            $bal = $balances[$acc->id]['balance'] ?? 0;
            $taxDetails[] = ['account' => $acc, 'balance' => $bal];
            $taxTotal += $bal;
        }

        $netIncome = $profitBeforeTax - $taxTotal;

        return [
            'period_id' => $periodId,
            'period_from_id' => $periodFromId,
            'revenues' => $revenueDetails,
            'total_revenue' => $revenueTotal,
            'hpp' => $hppDetails,
            'total_hpp' => $hppTotal,
            'gross_profit' => $grossProfit,
            'operating_expenses' => $opexDetails,
            'total_operating_expenses' => $opexTotal,
            'operating_profit' => $operatingProfit,
            'other_income_expenses' => $otherDetails,
            'total_other' => $otherTotal,
            'profit_before_interest_tax' => $profitBeforeInterestTax,
            'interest_expenses' => $interestDetails,
            'total_interest' => $interestTotal,
            'profit_before_tax' => $profitBeforeTax,
            'tax_expenses' => $taxDetails,
            'total_tax' => $taxTotal,
            'net_income' => $netIncome,
        ];
    }

    /**
     * Get all period IDs in a range (inclusive).
     */
    private function getPeriodRangeIds(int $fromId, int $toId): array
    {
        $periods = \App\Models\AccountingPeriod::whereBetween('id', [$fromId, $toId])
            ->orderBy('year')
            ->orderBy('month')
            ->pluck('id')
            ->toArray();

        return $periods ?: [$toId];
    }
}
