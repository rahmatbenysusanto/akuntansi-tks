<?php

namespace App\Services;

use App\Models\Account;
use App\Models\JournalEntryLine;
use App\Models\OpeningBalance;
use Illuminate\Support\Facades\DB;

class TrialBalanceService
{
    public function generate(int $periodId): array
    {
        $accounts = Account::orderBy('code')->get();
        $lines = [];

        foreach ($accounts as $account) {
            $openingBalance = OpeningBalance::where('accounting_period_id', $periodId)
                ->where('account_id', $account->id)
                ->first();

            $openingDebit = (float) ($openingBalance?->debit ?? 0);
            $openingCredit = (float) ($openingBalance?->credit ?? 0);

            // Mutations from journal entries
            $mutations = JournalEntryLine::where('account_id', $account->id)
                ->whereHas('journalEntry', function ($q) use ($periodId) {
                    $q->where('accounting_period_id', $periodId)
                      ->where('status', 'posted');
                })
                ->selectRaw('COALESCE(SUM(debit), 0) as total_debit, COALESCE(SUM(credit), 0) as total_credit')
                ->first();

            $mutationDebit = (float) $mutations->total_debit;
            $mutationCredit = (float) $mutations->total_credit;

            $endingDebit = $openingDebit + $mutationDebit;
            $endingCredit = $openingCredit + $mutationCredit;

            // For ending balance, compute the net based on normal balance
            $netBalance = $endingDebit - $endingCredit;
            $endingBalanceDebit = $netBalance > 0 ? $netBalance : 0;
            $endingBalanceCredit = $netBalance < 0 ? abs($netBalance) : 0;

            // Income statement columns
            $incomeDebit = 0;
            $incomeCredit = 0;
            $balanceDebit = 0;
            $balanceCredit = 0;

            if ($account->report_type === 'income_statement') {
                if ($netBalance > 0) {
                    $incomeDebit = $netBalance;
                } else {
                    $incomeCredit = abs($netBalance);
                }
            } else {
                if ($netBalance > 0) {
                    $balanceDebit = $netBalance;
                } else {
                    $balanceCredit = abs($netBalance);
                }
            }

            $lines[] = [
                'account' => $account,
                'opening_debit' => $openingDebit,
                'opening_credit' => $openingCredit,
                'mutation_debit' => $mutationDebit,
                'mutation_credit' => $mutationCredit,
                'ending_debit' => $endingDebit,
                'ending_credit' => $endingCredit,
                'ending_balance_debit' => $endingBalanceDebit,
                'ending_balance_credit' => $endingBalanceCredit,
                'income_statement_debit' => $incomeDebit,
                'income_statement_credit' => $incomeCredit,
                'balance_sheet_debit' => $balanceDebit,
                'balance_sheet_credit' => $balanceCredit,
            ];
        }

        // Totals
        $totals = [
            'opening_debit' => collect($lines)->sum('opening_debit'),
            'opening_credit' => collect($lines)->sum('opening_credit'),
            'mutation_debit' => collect($lines)->sum('mutation_debit'),
            'mutation_credit' => collect($lines)->sum('mutation_credit'),
            'ending_debit' => collect($lines)->sum('ending_debit'),
            'ending_credit' => collect($lines)->sum('ending_credit'),
            'income_statement_debit' => collect($lines)->sum('income_statement_debit'),
            'income_statement_credit' => collect($lines)->sum('income_statement_credit'),
            'balance_sheet_debit' => collect($lines)->sum('balance_sheet_debit'),
            'balance_sheet_credit' => collect($lines)->sum('balance_sheet_credit'),
        ];

        return [
            'lines' => $lines,
            'totals' => $totals,
        ];
    }
}
