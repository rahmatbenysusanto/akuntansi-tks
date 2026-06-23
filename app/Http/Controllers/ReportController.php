<?php

namespace App\Http\Controllers;

use App\Models\AccountingPeriod;
use App\Models\Account;
use App\Services\BalanceSheetService;
use App\Services\FinancialRatioService;
use App\Services\IncomeStatementService;
use App\Services\LedgerService;
use App\Services\TrialBalanceService;
use App\Exports\FinancialHighlightExport;
use App\Exports\FinancialNotesExport;
use App\Exports\GeneralLedgerExport;
use App\Exports\TrialBalanceExport;
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
            // Generate cumulative data across period range
            $data = $incomeStatementService->generate($endPeriod->id, $startPeriod->id);
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
        if (!$request->filled('period_id')) {
            return redirect()->route('reports.income-statement')->with('error', 'Pilih periode terlebih dahulu.');
        }

        try {
            $period = AccountingPeriod::findOrFail($request->period_id);
            $data = $incomeStatementService->generate($period->id);

            $pdf = Pdf::loadView('reports.pdf.income-statement', compact('period', 'data'));
            return $pdf->download('Laba-Rugi-' . $period->label . '.pdf');
        } catch (\Exception $e) {
            return redirect()->route('reports.income-statement')->with('error', 'Gagal generate PDF: ' . $e->getMessage());
        }
    }

    public function balanceSheetPdf(Request $request, BalanceSheetService $balanceSheetService)
    {
        if (!$request->filled('period_id')) {
            return redirect()->route('reports.balance-sheet')->with('error', 'Pilih periode terlebih dahulu.');
        }

        try {
            $period = AccountingPeriod::findOrFail($request->period_id);
            $data = $balanceSheetService->generate($period->id);

            $pdf = Pdf::loadView('reports.pdf.balance-sheet', compact('period', 'data'));
            return $pdf->download('Neraca-' . $period->label . '.pdf');
        } catch (\Exception $e) {
            return redirect()->route('reports.balance-sheet')->with('error', 'Gagal generate PDF: ' . $e->getMessage());
        }
    }

    public function incomeStatementExcel(Request $request, IncomeStatementService $incomeStatementService)
    {
        if (!$request->filled('period_id')) {
            return redirect()->route('reports.income-statement')->with('error', 'Pilih periode terlebih dahulu.');
        }

        try {
            $period = AccountingPeriod::findOrFail($request->period_id);
            $data = $incomeStatementService->generate($period->id);

            return Excel::download(new \App\Exports\IncomeStatementExport($period, $data), 'Laba-Rugi-' . $period->label . '.xlsx');
        } catch (\Exception $e) {
            return redirect()->route('reports.income-statement')->with('error', 'Gagal export Excel: ' . $e->getMessage());
        }
    }

    public function balanceSheetExcel(Request $request, BalanceSheetService $balanceSheetService)
    {
        if (!$request->filled('period_id')) {
            return redirect()->route('reports.balance-sheet')->with('error', 'Pilih periode terlebih dahulu.');
        }

        try {
            $period = AccountingPeriod::findOrFail($request->period_id);
            $data = $balanceSheetService->generate($period->id);

            return Excel::download(new \App\Exports\BalanceSheetExport($period, $data), 'Neraca-' . $period->label . '.xlsx');
        } catch (\Exception $e) {
            return redirect()->route('reports.balance-sheet')->with('error', 'Gagal export Excel: ' . $e->getMessage());
        }
    }

    // ========================================================================
    // GENERAL LEDGER EXPORTS
    // ========================================================================
    public function generalLedgerPdf(Request $request, LedgerService $ledgerService)
    {
        if (!$request->filled('period_id') || !$request->filled('account_id')) {
            return redirect()->route('reports.general-ledger')->with('error', 'Pilih periode dan akun terlebih dahulu.');
        }
        try {
            $selectedPeriod = AccountingPeriod::findOrFail($request->period_id);
            $selectedAccount = Account::findOrFail($request->account_id);
            $data = $ledgerService->generate($selectedAccount->id, $selectedPeriod->id);
            $pdf = Pdf::loadView('reports.pdf.general-ledger', compact('selectedPeriod', 'selectedAccount', 'data'));
            return $pdf->download('Buku-Besar-' . $selectedAccount->code . '-' . $selectedPeriod->label . '.pdf');
        } catch (\Exception $e) {
            return redirect()->route('reports.general-ledger')->with('error', 'Gagal generate PDF: ' . $e->getMessage());
        }
    }

    public function generalLedgerExcel(Request $request, LedgerService $ledgerService)
    {
        if (!$request->filled('period_id') || !$request->filled('account_id')) {
            return redirect()->route('reports.general-ledger')->with('error', 'Pilih periode dan akun terlebih dahulu.');
        }
        try {
            $period = AccountingPeriod::findOrFail($request->period_id);
            $account = Account::findOrFail($request->account_id);
            $data = $ledgerService->generate($account->id, $period->id);
            return Excel::download(new GeneralLedgerExport($period, $account, $data), 'Buku-Besar-' . $account->code . '-' . $period->label . '.xlsx');
        } catch (\Exception $e) {
            return redirect()->route('reports.general-ledger')->with('error', 'Gagal export Excel: ' . $e->getMessage());
        }
    }

    // ========================================================================
    // TRIAL BALANCE EXPORTS
    // ========================================================================
    public function trialBalancePdf(Request $request, TrialBalanceService $trialBalanceService)
    {
        if (!$request->filled('period_id')) {
            return redirect()->route('reports.trial-balance')->with('error', 'Pilih periode terlebih dahulu.');
        }
        try {
            $selectedPeriod = AccountingPeriod::findOrFail($request->period_id);
            $data = $trialBalanceService->generate($selectedPeriod->id);
            $pdf = Pdf::loadView('reports.pdf.trial-balance', compact('selectedPeriod', 'data'));
            return $pdf->download('Neraca-Lajur-' . $selectedPeriod->label . '.pdf');
        } catch (\Exception $e) {
            return redirect()->route('reports.trial-balance')->with('error', 'Gagal generate PDF: ' . $e->getMessage());
        }
    }

    public function trialBalanceExcel(Request $request, TrialBalanceService $trialBalanceService)
    {
        if (!$request->filled('period_id')) {
            return redirect()->route('reports.trial-balance')->with('error', 'Pilih periode terlebih dahulu.');
        }
        try {
            $period = AccountingPeriod::findOrFail($request->period_id);
            $data = $trialBalanceService->generate($period->id);
            return Excel::download(new TrialBalanceExport($period, $data), 'Neraca-Lajur-' . $period->label . '.xlsx');
        } catch (\Exception $e) {
            return redirect()->route('reports.trial-balance')->with('error', 'Gagal export Excel: ' . $e->getMessage());
        }
    }

    // ========================================================================
    // FINANCIAL NOTES EXPORTS
    // ========================================================================
    public function financialNotesPdf(Request $request, BalanceSheetService $balanceSheetService, IncomeStatementService $incomeStatementService)
    {
        if (!$request->filled('period_id')) {
            return redirect()->route('reports.financial-notes')->with('error', 'Pilih periode terlebih dahulu.');
        }
        try {
            $selectedPeriod = AccountingPeriod::findOrFail($request->period_id);
            $balanceData = $balanceSheetService->generate($selectedPeriod->id);
            $incomeData = $incomeStatementService->generate($selectedPeriod->id);
            $pdf = Pdf::loadView('reports.pdf.financial-notes', compact('selectedPeriod', 'balanceData', 'incomeData'));
            return $pdf->download('Catatan-LK-' . $selectedPeriod->label . '.pdf');
        } catch (\Exception $e) {
            return redirect()->route('reports.financial-notes')->with('error', 'Gagal generate PDF: ' . $e->getMessage());
        }
    }

    public function financialNotesExcel(Request $request, BalanceSheetService $balanceSheetService, IncomeStatementService $incomeStatementService)
    {
        if (!$request->filled('period_id')) {
            return redirect()->route('reports.financial-notes')->with('error', 'Pilih periode terlebih dahulu.');
        }
        try {
            $period = AccountingPeriod::findOrFail($request->period_id);
            $balanceData = $balanceSheetService->generate($period->id);
            $incomeData = $incomeStatementService->generate($period->id);
            return Excel::download(new FinancialNotesExport($period, $balanceData, $incomeData), 'Catatan-LK-' . $period->label . '.xlsx');
        } catch (\Exception $e) {
            return redirect()->route('reports.financial-notes')->with('error', 'Gagal export Excel: ' . $e->getMessage());
        }
    }

    // ========================================================================
    // FINANCIAL HIGHLIGHT EXPORTS
    // ========================================================================
    public function financialHighlightPdf(Request $request, FinancialRatioService $ratioService)
    {
        if (!$request->filled('period_id')) {
            return redirect()->route('reports.financial-highlight')->with('error', 'Pilih periode terlebih dahulu.');
        }
        try {
            $selectedPeriod = AccountingPeriod::findOrFail($request->period_id);
            $ratios = $ratioService->generate($selectedPeriod->id);
            $pdf = Pdf::loadView('reports.pdf.financial-highlight', compact('selectedPeriod', 'ratios'));
            return $pdf->download('Financial-Highlight-' . $selectedPeriod->label . '.pdf');
        } catch (\Exception $e) {
            return redirect()->route('reports.financial-highlight')->with('error', 'Gagal generate PDF: ' . $e->getMessage());
        }
    }

    public function financialHighlightExcel(Request $request, FinancialRatioService $ratioService)
    {
        if (!$request->filled('period_id')) {
            return redirect()->route('reports.financial-highlight')->with('error', 'Pilih periode terlebih dahulu.');
        }
        try {
            $period = AccountingPeriod::findOrFail($request->period_id);
            $ratios = $ratioService->generate($period->id);
            return Excel::download(new FinancialHighlightExport($period, $ratios), 'Financial-Highlight-' . $period->label . '.xlsx');
        } catch (\Exception $e) {
            return redirect()->route('reports.financial-highlight')->with('error', 'Gagal export Excel: ' . $e->getMessage());
        }
    }
}
