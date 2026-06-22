<?php

namespace App\Services;

use App\Models\Account;
use App\Models\JournalEntryLine;
use App\Models\OpeningBalance;

class BalanceSheetService
{
    public function generate(int $periodId): array
    {
        $accounts = Account::where('report_type', 'balance_sheet')
            ->orderBy('code')
            ->get()
            ->keyBy('id');

        // Get net income from income statement for current period
        $incomeService = app(IncomeStatementService::class);
        $incomeStatement = $incomeService->generate($periodId);
        $netIncome = $incomeStatement['net_income'];

        // Get balances
        $aktivaAccounts = $accounts->where('category', 'aktiva');
        $kewajibanAccounts = $accounts->where('category', 'kewajiban');
        $modalAccounts = $accounts->where('category', 'modal');

        $aktiva = $this->getStructured($aktivaAccounts, $periodId);
        $kewajiban = $this->getStructured($kewajibanAccounts, $periodId);
        $modal = $this->getStructured($modalAccounts, $periodId);

        // Add net income to Laba Ditahan or Laba Tahun Berjalan in modal
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

        return [
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
    }

    private function getStructured($accounts, int $periodId): array
    {
        $total = 0;
        $details = [];

        foreach ($accounts as $account) {
            $opening = OpeningBalance::where('accounting_period_id', $periodId)
                ->where('account_id', $account->id)
                ->first();

            $openingDebit = (float) ($opening?->debit ?? 0);
            $openingCredit = (float) ($opening?->credit ?? 0);

            $mutations = JournalEntryLine::where('account_id', $account->id)
                ->whereHas('journalEntry', function ($q) use ($periodId) {
                    $q->where('accounting_period_id', $periodId)
                      ->where('status', 'posted');
                })
                ->selectRaw('COALESCE(SUM(debit), 0) as total_debit, COALESCE(SUM(credit), 0) as total_credit')
                ->first();

            $totalDebit = $openingDebit + (float) $mutations->total_debit;
            $totalCredit = $openingCredit + (float) $mutations->total_credit;

            // Net balance = debit - credit
            // For aktiva: normal balance is debit, so positive = asset balance
            // For kewajiban/modal: normal balance is credit, so positive = liability/equity
            // Contra-asset accounts (aktiva with normal_balance=credit, like accumulated depreciation):
            //   their balance reduces total assets
            $balance = $totalDebit - $totalCredit;

            // For display, store the raw net balance (can be negative for contra accounts)
            $displayBalance = $balance;

            // For total calculation:
            // Aktiva: debit-normal accounts add to total, credit-normal (contra) subtract
            // Kewajiban/Modal: credit-normal accounts add to total, debit-normal subtract
            if ($account->normal_balance === 'debit') {
                $total += $balance;
            } else {
                // Credit-normal accounts: positive balance adds, negative subtracts
                // For contra-asset (acc. depreciation under aktiva), credit-normal balance > 0 reduces assets
                $total -= $balance;
            }

            $details[] = [
                'account' => $account,
                'balance' => max(0, abs($balance)),
                'is_negative' => $balance < 0,
                'debit' => $totalDebit,
                'credit' => $totalCredit,
            ];
        }

        return [
            'details' => $details,
            'total' => $total,
        ];
    }
}
