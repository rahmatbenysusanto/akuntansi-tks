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

    /**
     * Batch: get ending balances for multiple accounts in a single query pair.
     * Much faster than calling generate() per account.
     *
     * @param int[] $accountIds
     * @return array<int, float> account_id => ending_balance
     */
    public function batchEndingBalances(array $accountIds, int $periodId): array
    {
        $accounts = Account::whereIn('id', $accountIds)->get()->keyBy('id');

        // BATCH: opening balances
        $openings = OpeningBalance::where('accounting_period_id', $periodId)
            ->whereIn('account_id', $accountIds)
            ->get()
            ->keyBy('account_id');

        // BATCH: mutation sums
        $mutations = JournalEntryLine::whereIn('account_id', $accountIds)
            ->whereHas('journalEntry', function ($q) use ($periodId) {
                $q->where('accounting_period_id', $periodId)
                  ->where('status', 'posted');
            })
            ->selectRaw('account_id, COALESCE(SUM(debit), 0) as total_debit, COALESCE(SUM(credit), 0) as total_credit')
            ->groupBy('account_id')
            ->get()
            ->keyBy('account_id');

        $result = [];
        foreach ($accountIds as $aid) {
            $account = $accounts->get($aid);
            if (!$account) {
                $result[$aid] = 0;
                continue;
            }

            $opening = $openings->get($aid);
            $mutation = $mutations->get($aid);

            $openingDebit = (float) ($opening?->debit ?? 0);
            $openingCredit = (float) ($opening?->credit ?? 0);
            $totalDebit = $openingDebit + (float) ($mutation->total_debit ?? 0);
            $totalCredit = $openingCredit + (float) ($mutation->total_credit ?? 0);

            $result[$aid] = $account->normal_balance === 'debit'
                ? $totalDebit - $totalCredit
                : $totalCredit - $totalDebit;
        }

        return $result;
    }
}
