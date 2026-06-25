<?php

namespace App\Services;

use App\Models\Account;
use App\Models\JournalEntryLine;
use App\Models\OpeningBalance;
use Illuminate\Support\Facades\DB;

class IncomeStatementService
{
    /** @var array<string, array> Request-level cache */
    private static array $cache = [];

    /**
     * Generate income statement for a single period or range of periods.
     */
    public function generate(int $periodId, ?int $periodFromId = null): array
    {
        $cacheKey = "is:{$periodId}:" . ($periodFromId ?? 'single');

        if (isset(self::$cache[$cacheKey])) {
            return self::$cache[$cacheKey];
        }

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

        $accountIds = $accounts->pluck('id')->toArray();

        // BATCH: Fetch all opening balances in ONE query
        $openings = OpeningBalance::where('accounting_period_id', $targetPeriodId)
            ->whereIn('account_id', $accountIds)
            ->get()
            ->keyBy('account_id');

        // BATCH: Fetch all mutation aggregations in ONE query
        $mutations = JournalEntryLine::whereIn('account_id', $accountIds)
            ->whereHas('journalEntry', function ($q) use ($periodIds) {
                $q->whereIn('accounting_period_id', $periodIds)
                  ->where('status', 'posted');
            })
            ->selectRaw('account_id, COALESCE(SUM(debit), 0) as total_debit, COALESCE(SUM(credit), 0) as total_credit')
            ->groupBy('account_id')
            ->get()
            ->keyBy('account_id');

        // Compute balances from pre-fetched data (no extra queries)
        $balances = [];
        foreach ($accounts as $account) {
            $opening = $openings->get($account->id);
            $mutation = $mutations->get($account->id);

            $openingDebit = (float) ($opening?->debit ?? 0);
            $openingCredit = (float) ($opening?->credit ?? 0);

            $totalDebit = $openingDebit + (float) ($mutation->total_debit ?? 0);
            $totalCredit = $openingCredit + (float) ($mutation->total_credit ?? 0);

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

        $result = [
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

        return self::$cache[$cacheKey] = $result;
    }

    /**
     * Lightweight: get revenue & expense totals for multiple periods at once.
     * Used by dashboard chart — avoids calling generate() N times.
     *
     * @param int[] $periodIds
     * @return array<int, array{label: string, revenue: float, expense: float}>
     */
    public function batchMonthlySummary(array $periodIds): array
    {
        $cacheKey = 'is:batch:' . implode(',', $periodIds);

        if (isset(self::$cache[$cacheKey])) {
            return self::$cache[$cacheKey];
        }

        // Get all income statement accounts
        $accounts = Account::where('report_type', 'income_statement')
            ->select('id', 'code', 'category', 'normal_balance')
            ->get();

        $revenueAccountIds = $accounts->where('category', 'pendapatan')->pluck('id')->toArray();
        $hppAccountIds = $accounts->where('category', 'hpp')->pluck('id')->toArray();
        $opexAccountIds = $accounts->where('category', 'biaya_operasional')->pluck('id')->toArray();
        $interestAccountIds = $accounts->where('category', 'biaya_bunga')->pluck('id')->toArray();
        $taxAccountIds = $accounts->where('category', 'pajak_penghasilan')->pluck('id')->toArray();

        $expenseAccountIds = array_merge($hppAccountIds, $opexAccountIds, $interestAccountIds, $taxAccountIds);
        $allAccountIds = array_merge($revenueAccountIds, $expenseAccountIds);

        if (empty($allAccountIds)) {
            $result = [];
            foreach ($periodIds as $pid) {
                $result[$pid] = ['label' => '', 'revenue' => 0, 'expense' => 0];
            }
            return self::$cache[$cacheKey] = $result;
        }

        // ONE query: aggregate debit/credit per period per account group
        $rows = JournalEntryLine::whereIn('account_id', $allAccountIds)
            ->whereHas('journalEntry', function ($q) use ($periodIds) {
                $q->whereIn('accounting_period_id', $periodIds)
                  ->where('status', 'posted');
            })
            ->join('journal_entries', 'journal_entry_lines.journal_entry_id', '=', 'journal_entries.id')
            ->selectRaw('journal_entries.accounting_period_id as period_id,
                         journal_entry_lines.account_id,
                         SUM(journal_entry_lines.debit) as total_debit,
                         SUM(journal_entry_lines.credit) as total_credit')
            ->groupBy('journal_entries.accounting_period_id', 'journal_entry_lines.account_id')
            ->get();

        // Index by period_id -> account_id -> {debit, credit}
        $data = [];
        foreach ($rows as $row) {
            $data[$row->period_id][$row->account_id] = [
                'debit' => (float) $row->total_debit,
                'credit' => (float) $row->total_credit,
            ];
        }

        // Get period labels (label is an accessor, fetch month/year and build manually)
        $periods = \App\Models\AccountingPeriod::whereIn('id', $periodIds)
            ->select('id', 'month', 'year')->get();

        $months = [1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'];
        $periodLabels = [];
        foreach ($periods as $p) {
            $periodLabels[$p->id] = $months[$p->month] . ' ' . $p->year;
        }

        $result = [];
        foreach ($periodIds as $pid) {
            $revenue = 0;
            $expense = 0;

            // Calculate revenue from revenue accounts
            foreach ($revenueAccountIds as $aid) {
                $d = $data[$pid][$aid]['debit'] ?? 0;
                $c = $data[$pid][$aid]['credit'] ?? 0;
                // Revenue accounts have normal_balance = credit
                $revenue += max(0, $c - $d);
            }

            // Calculate expenses from expense accounts
            foreach ($expenseAccountIds as $aid) {
                $d = $data[$pid][$aid]['debit'] ?? 0;
                $c = $data[$pid][$aid]['credit'] ?? 0;
                // Expense accounts have normal_balance = debit
                $expense += max(0, $d - $c);
            }

            $result[$pid] = [
                'label' => $periodLabels[$pid] ?? '',
                'revenue' => $revenue,
                'expense' => $expense,
            ];
        }

        return self::$cache[$cacheKey] = $result;
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
