<?php

namespace App\Http\Controllers;

use App\Models\AccountingPeriod;
use App\Models\Account;
use App\Services\BalanceSheetService;
use App\Services\FinancialRatioService;
use App\Services\IncomeStatementService;
use App\Services\LedgerService;
use App\Services\TrialBalanceService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function generalLedger(Request $request, LedgerService $ledgerService)
    {
        $periods = AccountingPeriod::orderBy('year', 'desc')->orderBy('month', 'desc')->get();
        $accounts = Account::orderBy('code')->get();
        $selectedPeriod = AccountingPeriod::find($request->get('period_id', $periods->first()?->id));
        $selectedAccount = Account::find($request->get('account_id'));

        $data = null;
        if ($selectedPeriod && $selectedAccount) {
            $data = $ledgerService->generate($selectedAccount->id, $selectedPeriod->id);
        }

        return view('reports.general-ledger', compact('periods', 'accounts', 'selectedPeriod', 'selectedAccount', 'data'));
    }

    public function trialBalance(Request $request, TrialBalanceService $trialBalanceService)
    {
        $periods = AccountingPeriod::orderBy('year', 'desc')->orderBy('month', 'desc')->get();
        $selectedPeriod = AccountingPeriod::find($request->get('period_id', $periods->first()?->id));

        $data = null;
        if ($selectedPeriod) {
            $data = $trialBalanceService->generate($selectedPeriod->id);
        }

        return view('reports.trial-balance', compact('periods', 'selectedPeriod', 'data'));
    }

    public function incomeStatement(Request $request, IncomeStatementService $incomeStatementService)
    {
        $periods = AccountingPeriod::orderBy('year', 'desc')->orderBy('month', 'desc')->get();
        $selectedPeriod = AccountingPeriod::find($request->get('period_id', $periods->first()?->id));

        $data = null;
        $startPeriod = null;
        $endPeriod = null;

        if ($request->filled('period_from') && $request->filled('period_to')) {
            $startPeriod = AccountingPeriod::find($request->period_from);
            $endPeriod = AccountingPeriod::find($request->period_to);
        } elseif ($selectedPeriod) {
            $startPeriod = $selectedPeriod;
            $endPeriod = $selectedPeriod;
        }

        if ($startPeriod && $endPeriod) {
            // For cumulative income statement, use the end period
            $data = $incomeStatementService->generate($endPeriod->id);
        }

        return view('reports.income-statement', compact('periods', 'selectedPeriod', 'data', 'startPeriod', 'endPeriod'));
    }

    public function balanceSheet(Request $request, BalanceSheetService $balanceSheetService)
    {
        $periods = AccountingPeriod::orderBy('year', 'desc')->orderBy('month', 'desc')->get();
        $selectedPeriod = AccountingPeriod::find($request->get('period_id', $periods->first()?->id));

        $data = null;
        if ($selectedPeriod) {
            $data = $balanceSheetService->generate($selectedPeriod->id);
        }

        return view('reports.balance-sheet', compact('periods', 'selectedPeriod', 'data'));
    }

    public function financialNotes(Request $request, BalanceSheetService $balanceSheetService, IncomeStatementService $incomeStatementService)
    {
        $periods = AccountingPeriod::orderBy('year', 'desc')->orderBy('month', 'desc')->get();
        $selectedPeriod = AccountingPeriod::find($request->get('period_id', $periods->first()?->id));

        $balanceData = null;
        $incomeData = null;

        if ($selectedPeriod) {
            $balanceData = $balanceSheetService->generate($selectedPeriod->id);
            $incomeData = $incomeStatementService->generate($selectedPeriod->id);
        }

        return view('reports.financial-notes', compact('periods', 'selectedPeriod', 'balanceData', 'incomeData'));
    }

    public function financialHighlight(Request $request, FinancialRatioService $ratioService)
    {
        $periods = AccountingPeriod::orderBy('year', 'desc')->orderBy('month', 'desc')->get();
        $selectedPeriod = AccountingPeriod::find($request->get('period_id', $periods->first()?->id));

        $ratios = null;
        if ($selectedPeriod) {
            $ratios = $ratioService->generate($selectedPeriod->id);
        }

        return view('reports.financial-highlight', compact('periods', 'selectedPeriod', 'ratios'));
    }

    public function incomeStatementPdf(Request $request, IncomeStatementService $incomeStatementService)
    {
        $period = AccountingPeriod::findOrFail($request->period_id);
        $data = $incomeStatementService->generate($period->id);

        $pdf = Pdf::loadView('reports.pdf.income-statement', compact('period', 'data'));
        return $pdf->download('Laba-Rugi-' . $period->label . '.pdf');
    }

    public function balanceSheetPdf(Request $request, BalanceSheetService $balanceSheetService)
    {
        $period = AccountingPeriod::findOrFail($request->period_id);
        $data = $balanceSheetService->generate($period->id);

        $pdf = Pdf::loadView('reports.pdf.balance-sheet', compact('period', 'data'));
        return $pdf->download('Neraca-' . $period->label . '.pdf');
    }

    public function incomeStatementExcel(Request $request, IncomeStatementService $incomeStatementService)
    {
        $period = AccountingPeriod::findOrFail($request->period_id);
        $data = $incomeStatementService->generate($period->id);

        return Excel::download(new \App\Exports\IncomeStatementExport($period, $data), 'Laba-Rugi-' . $period->label . '.xlsx');
    }

    public function balanceSheetExcel(Request $request, BalanceSheetService $balanceSheetService)
    {
        $period = AccountingPeriod::findOrFail($request->period_id);
        $data = $balanceSheetService->generate($period->id);

        return Excel::download(new \App\Exports\BalanceSheetExport($period, $data), 'Neraca-' . $period->label . '.xlsx');
    }
}
