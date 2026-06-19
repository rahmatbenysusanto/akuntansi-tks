<?php

namespace App\Http\Controllers;

use App\Models\AccountingPeriod;
use App\Models\OpeningBalance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AccountingPeriodController extends Controller
{
    public function index()
    {
        $periods = AccountingPeriod::with('closedBy')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get();

        return view('accounting-periods.index', compact('periods'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2020|max:2099',
        ]);

        $exists = AccountingPeriod::where('month', $validated['month'])
            ->where('year', $validated['year'])
            ->exists();

        if ($exists) {
            return back()->with('error', 'Periode sudah ada.');
        }

        AccountingPeriod::create($validated);

        return redirect()->route('accounting-periods.index')
            ->with('success', 'Periode berhasil ditambahkan.');
    }

    public function close(AccountingPeriod $accountingPeriod)
    {
        if ($accountingPeriod->status === 'closed') {
            return back()->with('error', 'Periode sudah ditutup.');
        }

        DB::transaction(function () use ($accountingPeriod) {
            // Generate opening balances for next period
            $nextPeriod = AccountingPeriod::where('year', $accountingPeriod->year)
                ->where('month', $accountingPeriod->month + 1)
                ->first();

            if (!$nextPeriod) {
                // Next year
                if ($accountingPeriod->month === 12) {
                    $nextPeriod = AccountingPeriod::firstOrCreate([
                        'month' => 1,
                        'year' => $accountingPeriod->year + 1,
                    ]);
                }
            }

            if ($nextPeriod && $nextPeriod->status === 'open') {
                $accounts = \App\Models\Account::all();
                $incomeService = app(\App\Services\IncomeStatementService::class);
                $incomeData = $incomeService->generate($accountingPeriod->id);
                $netIncome = $incomeData['net_income'];

                foreach ($accounts as $account) {
                    // Akun Laba Rugi (income_statement): tidak carry forward - mulai dari 0
                    if ($account->report_type === 'income_statement') continue;

                    $totalDebit = $account->journalEntryLines()
                        ->whereHas('journalEntry', function ($q) use ($accountingPeriod) {
                            $q->where('accounting_period_id', $accountingPeriod->id)
                              ->where('status', 'posted');
                        })
                        ->sum('debit');

                    $totalCredit = $account->journalEntryLines()
                        ->whereHas('journalEntry', function ($q) use ($accountingPeriod) {
                            $q->where('accounting_period_id', $accountingPeriod->id)
                              ->where('status', 'posted');
                        })
                        ->sum('credit');

                    $openingBalance = $account->openingBalances()
                        ->where('accounting_period_id', $accountingPeriod->id)
                        ->first();

                    $debit = ($openingBalance?->debit ?? 0) + $totalDebit;
                    $credit = ($openingBalance?->credit ?? 0) + $totalCredit;

                    // Tambah laba bersih ke akun "Laba Periode Berjalan" (modal)
                    if (stripos($account->name, 'laba periode berjalan') !== false
                        || stripos($account->name, 'laba tahun berjalan') !== false) {
                        if ($netIncome > 0) {
                            $credit += $netIncome;
                        } else {
                            $debit += abs($netIncome);
                        }
                    }

                    OpeningBalance::updateOrCreate(
                        [
                            'accounting_period_id' => $nextPeriod->id,
                            'account_id' => $account->id,
                        ],
                        ['debit' => $debit, 'credit' => $credit]
                    );
                }
            }

            $accountingPeriod->update([
                'status' => 'closed',
                'closed_at' => now(),
                'closed_by' => auth()->id(),
            ]);
        });

        return redirect()->route('accounting-periods.index')
            ->with('success', 'Periode berhasil ditutup.');
    }
}
