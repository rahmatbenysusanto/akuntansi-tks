<?php

namespace App\Http\Controllers;

use App\Models\AccountingPeriod;
use App\Models\OpeningBalance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

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
            'year' => [
                'required', 'integer', 'min:2020', 'max:2099',
                Rule::unique('accounting_periods', 'year')
                    ->where('month', $request->input('month'))
                    ->where('company_id', auth()->user()->current_company_id),
            ],
        ]);

        AccountingPeriod::create($validated);

        return redirect()->route('accounting-periods.index')
            ->with('success', 'Periode berhasil ditambahkan.');
    }

    public function close(AccountingPeriod $accountingPeriod)
    {
        if ($accountingPeriod->status === 'closed') {
            return back()->with('error', 'Periode sudah ditutup.');
        }

        // Cek apakah masih ada jurnal draft di periode ini
        $draftCount = $accountingPeriod->journalEntries()->where('status', 'draft')->count();
        if ($draftCount > 0) {
            return back()->with(
                'error',
                "Tidak bisa tutup periode. Masih ada {$draftCount} jurnal draft yang belum diposting."
            );
        }

        DB::transaction(function () use ($accountingPeriod) {
            // Cari atau buat periode berikutnya
            $nextMonth = $accountingPeriod->month === 12 ? 1 : $accountingPeriod->month + 1;
            $nextYear = $accountingPeriod->month === 12 ? $accountingPeriod->year + 1 : $accountingPeriod->year;

            $nextPeriod = AccountingPeriod::firstOrCreate([
                'month' => $nextMonth,
                'year' => $nextYear,
            ]);

            // Generate opening balances untuk periode berikutnya
            if ($nextPeriod->status === 'open') {
                $accounts = \App\Models\Account::all();
                $incomeService = app(\App\Services\IncomeStatementService::class);
                $incomeData = $incomeService->generate($accountingPeriod->id);
                $netIncome = $incomeData['net_income'];

                foreach ($accounts as $account) {
                    // Akun Laba Rugi tidak carry forward — mulai dari 0
                    if ($account->report_type === 'income_statement') {
                        continue;
                    }

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

                    // Tambah laba bersih ke akun "Laba Periode Berjalan"
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
