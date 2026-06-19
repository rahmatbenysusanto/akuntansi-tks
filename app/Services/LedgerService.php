<?php

namespace App\Services;

use App\Models\Account;
use App\Models\JournalEntryLine;
use App\Models\OpeningBalance;
use Illuminate\Support\Facades\DB;

class LedgerService
{
    public function generate(int $accountId, int $periodId): array
    {
        $account = Account::findOrFail($accountId);

        // Get opening balance
        $openingBalance = OpeningBalance::where('accounting_period_id', $periodId)
            ->where('account_id', $accountId)
            ->first();

        $openingDebit = $openingBalance?->debit ?? 0;
        $openingCredit = $openingBalance?->credit ?? 0;

        // Get journal entry lines for this account in this period
        $lines = JournalEntryLine::where('account_id', $accountId)
            ->whereHas('journalEntry', function ($q) use ($periodId) {
                $q->where('accounting_period_id', $periodId)
                  ->where('status', 'posted');
            })
            ->with(['journalEntry' => function ($q) {
                $q->select('id', 'entry_date', 'reference_no', 'description', 'status');
            }])
            ->orderBy('journal_entry_id')
            ->orderBy('line_order')
            ->get()
            ->map(function ($line) {
                return [
                    'date' => $line->journalEntry->entry_date,
                    'reference_no' => $line->journalEntry->reference_no,
                    'description' => $line->journalEntry->description,
                    'debit' => (float) $line->debit,
                    'credit' => (float) $line->credit,
                ];
            });

        // Calculate running balance
        $runningBalance = $account->normal_balance === 'debit'
            ? $openingDebit - $openingCredit
            : $openingCredit - $openingDebit;

        $mutations = [];
        foreach ($lines as $line) {
            if ($account->normal_balance === 'debit') {
                $runningBalance += $line['debit'] - $line['credit'];
            } else {
                $runningBalance += $line['credit'] - $line['debit'];
            }
            $mutations[] = [
                'date' => $line['date'],
                'reference_no' => $line['reference_no'],
                'description' => $line['description'],
                'debit' => $line['debit'],
                'credit' => $line['credit'],
                'balance' => $runningBalance,
            ];
        }

        $endingBalance = $runningBalance;

        return [
            'account' => $account,
            'opening_balance_debit' => $openingDebit,
            'opening_balance_credit' => $openingCredit,
            'opening_balance' => $account->normal_balance === 'debit'
                ? $openingDebit - $openingCredit
                : $openingCredit - $openingDebit,
            'mutations' => $mutations,
            'total_debit' => $lines->sum('debit'),
            'total_credit' => $lines->sum('credit'),
            'ending_balance' => $endingBalance,
        ];
    }

    public function getAccountsWithBalance(int $periodId): array
    {
        $accounts = Account::orderBy('code')->get();
        $result = [];

        foreach ($accounts as $account) {
            $ledger = $this->generate($account->id, $periodId);
            $result[] = $ledger;
        }

        return $result;
    }
}
