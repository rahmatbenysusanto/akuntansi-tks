<?php

namespace App\Http\Controllers;

use App\Models\AccountingPeriod;
use App\Models\Account;
use App\Models\OpeningBalance;
use Illuminate\Http\Request;

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
        $accounts = Account::orderBy('code')->get();
        $balances = OpeningBalance::where('accounting_period_id', $periodId)->get()->keyBy('account_id');

        return view('opening-balances.index', compact('periods', 'selectedPeriod', 'accounts', 'balances'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'period_id' => 'required|exists:accounting_periods,id',
            'balances' => 'required|array',
            'balances.*.account_id' => 'required|exists:accounts,id',
            'balances.*.debit' => 'nullable|numeric|min:0',
            'balances.*.credit' => 'nullable|numeric|min:0',
        ]);

        $period = AccountingPeriod::findOrFail($validated['period_id']);

        if ($period->status === 'closed') {
            return back()->with('error', 'Tidak bisa mengubah saldo awal periode yang sudah ditutup.');
        }

        foreach ($validated['balances'] as $balance) {
            $debit = $balance['debit'] ?? 0;
            $credit = $balance['credit'] ?? 0;

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
