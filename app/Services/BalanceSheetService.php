<?php

namespace App\Services;

use App\Models\Account;
use App\Models\JournalEntryLine;
use App\Models\OpeningBalance;

class BalanceSheetService
{
    /** @var array<string, array> Request-level cache */
    private static array $cache = [];

    public function generate(int $periodId): array
    {
        $cacheKey = "bs:{$periodId}";

        if (isset(self::$cache[$cacheKey])) {
            return self::$cache[$cacheKey];
        }

        $accounts = Account::where('report_type', 'balance_sheet')
            ->orderBy('code')
            ->get()
            ->keyBy('id');

        $accountIds = $accounts->pluck('id')->toArray();

        // BATCH: fetch all opening balances in ONE query
        $openings = OpeningBalance::where('accounting_period_id', $periodId)
            ->whereIn('account_id', $accountIds)
            ->get()
            ->keyBy('account_id');

        // BATCH: fetch all mutation aggregations in ONE query
        $mutations = JournalEntryLine::whereIn('account_id', $accountIds)
            ->whereHas('journalEntry', function ($q) use ($periodId) {
                $q->where('accounting_period_id', $periodId)
                  ->where('status', 'posted');
            })
            ->selectRaw('account_id, COALESCE(SUM(debit), 0) as total_debit, COALESCE(SUM(credit), 0) as total_credit')
            ->groupBy('account_id')
            ->get()
            ->keyBy('account_id');

        // Get net income from income statement for current period (uses its own cache)
        $incomeService = app(IncomeStatementService::class);
        $incomeStatement = $incomeService->generate($periodId);
        $netIncome = $incomeStatement['net_income'];

        // Calculate balances for each account using pre-fetched data
        $allBalances = [];
        foreach ($accounts as $account) {
            $opening = $openings->get($account->id);
            $mutation = $mutations->get($account->id);

            $openingDebit = (float) ($opening?->debit ?? 0);
            $openingCredit = (float) ($opening?->credit ?? 0);

            $totalDebit = $openingDebit + (float) ($mutation->total_debit ?? 0);
            $totalCredit = $openingCredit + (float) ($mutation->total_credit ?? 0);

            $balance = $totalDebit - $totalCredit;

            $allBalances[$account->id] = [
                'account' => $account,
                'balance' => $balance,
                'debit' => $totalDebit,
                'credit' => $totalCredit,
            ];
        }

        // Build structured sections
        $aktivaAccounts = $accounts->where('category', 'aktiva');
        $kewajibanAccounts = $accounts->where('category', 'kewajiban');
        $modalAccounts = $accounts->where('category', 'modal');

        $aktiva = $this->buildStructured($aktivaAccounts, $allBalances);
        $kewajiban = $this->buildStructured($kewajibanAccounts, $allBalances);
        $modal = $this->buildStructured($modalAccounts, $allBalances);

        // Add net income to Laba Tahun Berjalan in modal
        foreach ($modal['details'] as &$m) {
            if (stripos($m['account']->name, 'laba tahun berjalan') !== false
                || stripos($m['account']->name, 'laba periode berjalan') !== false) {
                $m['balance'] += $netIncome;
                $modal['total'] += $netIncome;
                break;
            }
        }

        $totalAktiva = $aktiva['total'];
        $totalKewajiban = $kewajiban['total'];
        $totalModal = $modal['total'];
        $totalKewajibanModal = $totalKewajiban + $totalModal;

        $result = [
            'period_id' => $periodId,
            'aktiva' => $aktiva,
            'kewajiban' => $kewajiban,
            'modal' => $modal,
            'total_aktiva' => $totalAktiva,
            'total_kewajiban' => $totalKewajiban,
            'total_modal' => $totalModal,
            'total_kewajiban_modal' => $totalKewajibanModal,
            'net_income' => $netIncome,
            'is_balanced' => abs($totalAktiva - $totalKewajibanModal) < 1,
            'difference' => $totalAktiva - $totalKewajibanModal,
        ];

        return self::$cache[$cacheKey] = $result;
    }

    /**
     * Build structured details from pre-computed balance data (no DB queries).
     */
    private function buildStructured($accounts, array $allBalances): array
    {
        $total = 0;
        $details = [];

        foreach ($accounts as $account) {
            $data = $allBalances[$account->id] ?? ['balance' => 0, 'debit' => 0, 'credit' => 0];
            $balance = $data['balance'];

            // For total calculation:
            // Aktiva: debit-normal accounts add to total, credit-normal (contra) subtract
            // Kewajiban/Modal: credit-normal accounts add to total, debit-normal subtract
            if ($account->normal_balance === 'debit') {
                $total += $balance;
            } else {
                $total -= $balance;
            }

            $details[] = [
                'account' => $account,
                'balance' => max(0, abs($balance)),
                'is_negative' => $balance < 0,
                'debit' => $data['debit'],
                'credit' => $data['credit'],
            ];
        }

        return [
            'details' => $details,
            'total' => $total,
        ];
    }
}
