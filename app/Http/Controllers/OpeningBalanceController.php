<?php

namespace App\Http\Controllers;

use App\Models\AccountingPeriod;
use App\Models\Account;
use App\Models\OpeningBalance;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OpeningBalanceController extends Controller
{
    public function index(Request $request)
    {
        $periodId = $request->get('period_id');
        $periods = AccountingPeriod::orderBy('year', 'desc')->orderBy('month', 'desc')->get();

        if (!$periodId && $periods->isNotEmpty()) {
            $period = $periods->firstWhere('status', 'open') ?? $periods->first();
            $periodId = $period->id;
        }

        $selectedPeriod = AccountingPeriod::find($periodId);
        $companyId = auth()->user()->current_company_id;

        $accounts = Account::orderBy('code')->get();
        $balances = OpeningBalance::where('accounting_period_id', $periodId)->get()->keyBy('account_id');

        return view('opening-balances.index', compact('periods', 'selectedPeriod', 'accounts', 'balances', 'companyId'));
    }

    public function store(Request $request)
    {
        $companyId = auth()->user()->current_company_id;

        $validated = $request->validate([
            'period_id' => [
                'required',
                Rule::exists('accounting_periods', 'id')
                    ->where('company_id', $companyId),
            ],
            'balances' => 'required|array',
            'balances.*.account_id' => [
                'required',
                Rule::exists('accounts', 'id')
                    ->where('company_id', $companyId),
            ],
            'balances.*.debit' => 'nullable|numeric|min:0',
            'balances.*.credit' => 'nullable|numeric|min:0',
        ]);

        $period = AccountingPeriod::findOrFail($validated['period_id']);

        if ($period->status === 'closed') {
            return back()->with('error', 'Tidak bisa mengubah saldo awal periode yang sudah ditutup.');
        }

        // Validasi total debit = total credit
        $totalDebit = 0;
        $totalCredit = 0;

        foreach ($validated['balances'] as $balance) {
            $account = Account::find($balance['account_id']);
            // Skip akun laba rugi — tidak punya saldo awal
            if ($account && $account->report_type === 'income_statement') {
                continue;
            }
            $totalDebit += (float)($balance['debit'] ?? 0);
            $totalCredit += (float)($balance['credit'] ?? 0);
        }

        if (abs($totalDebit - $totalCredit) > 0.01) {
            return back()->with(
                'error',
                'Total Debet (' . number_format($totalDebit, 0, ',', '.') .
                ') tidak sama dengan Total Kredit (' . number_format($totalCredit, 0, ',', '.') . ').'
            )->withInput();
        }

        foreach ($validated['balances'] as $balance) {
            $account = Account::find($balance['account_id']);
            // Skip akun laba rugi
            if ($account && $account->report_type === 'income_statement') {
                continue;
            }

            $debit = (float)($balance['debit'] ?? 0);
            $credit = (float)($balance['credit'] ?? 0);

            if ($debit > 0 || $credit > 0) {
                OpeningBalance::updateOrCreate(
                    [
                        'accounting_period_id' => $period->id,
                        'account_id' => $balance['account_id'],
                    ],
                    ['debit' => $debit, 'credit' => $credit]
                );
            } else {
                OpeningBalance::where('accounting_period_id', $period->id)
                    ->where('account_id', $balance['account_id'])
                    ->delete();
            }
        }

        return redirect()->route('opening-balances.index', ['period_id' => $period->id])
            ->with('success', 'Saldo awal berhasil disimpan.');
    }
}
