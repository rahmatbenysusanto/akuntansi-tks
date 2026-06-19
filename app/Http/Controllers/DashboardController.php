<?php

namespace App\Http\Controllers;

use App\Models\AccountingPeriod;
use App\Models\Account;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\SalesInvoice;
use App\Models\PurchaseInvoice;
use App\Models\Customer;
use App\Models\Vendor;
use App\Services\BalanceSheetService;
use App\Services\IncomeStatementService;
use App\Services\ARAPService;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(
        IncomeStatementService $incomeStatementService,
        BalanceSheetService $balanceSheetService,
        ARAPService $arapService
    ) {
        $activePeriod = AccountingPeriod::where('status', 'open')
            ->orderBy('year', 'desc')->orderBy('month', 'desc')->first();

        $totalAccounts = Account::count();
        $draftEntries = JournalEntry::where('status', 'draft')->count();
        $postedEntries = JournalEntry::where('status', 'posted')->count();

        $incomeSummary = $activePeriod ? $incomeStatementService->generate($activePeriod->id) : null;
        $balanceSummary = $activePeriod ? $balanceSheetService->generate($activePeriod->id) : null;

        $netIncome = $incomeSummary['net_income'] ?? 0;
        $totalRevenue = $incomeSummary['total_revenue'] ?? 0;
        $totalExpenses = ($incomeSummary['total_hpp'] ?? 0) + ($incomeSummary['total_operating_expenses'] ?? 0);
        $totalRevenueFormatted = $totalRevenue;
        $grossProfit = $incomeSummary['gross_profit'] ?? 0;
        $totalAssets = $balanceSummary['total_aktiva'] ?? 0;
        $totalLiabilitiesEquity = $balanceSummary['total_kewajiban_modal'] ?? 0;

        // Kas & Bank balance
        $kasAccounts = Account::where('code', 'LIKE', '1.1.01.%')->where('is_header', false)->pluck('id');
        $bankAccountIds = Account::where('code', 'LIKE', '1.1.01.02.%')->where('is_header', false)->pluck('id');
        $cashBalance = 0;
        if ($activePeriod) {
            $ledgerService = app(\App\Services\LedgerService::class);
            foreach ($kasAccounts as $accId) {
                try {
                    $ledger = $ledgerService->generate($accId, $activePeriod->id);
                    $cashBalance += $ledger['ending_balance'];
                } catch (\Exception $e) {}
            }
        }

        // AR/AP Totals
        $arTotal = 0;
        $apTotal = 0;
        try {
            $arAging = $arapService->agingPiutang();
            $apAging = $arapService->agingHutang();
            $arTotal = collect($arAging)->sum('total');
            $apTotal = collect($apAging)->sum('total');
        } catch (\Exception $e) {}

        // Monthly revenue/expense for chart (last 6 months)
        $monthlyLabels = [];
        $monthlyRevenue = [];
        $monthlyExpense = [];
        $periods = AccountingPeriod::where('status', 'open')
            ->orderBy('year', 'desc')->orderBy('month', 'desc')->take(6)->get()->reverse();

        foreach ($periods as $p) {
            $monthlyLabels[] = $p->label;
            try {
                $stmt = $incomeStatementService->generate($p->id);
                $monthlyRevenue[] = (float)($stmt['total_revenue'] ?? 0);
                $monthlyExpense[] = (float)(($stmt['total_hpp'] ?? 0) + ($stmt['total_operating_expenses'] ?? 0));
            } catch (\Exception $e) {
                $monthlyRevenue[] = 0;
                $monthlyExpense[] = 0;
            }
        }

        // Recent journal entries
        $recentEntries = JournalEntry::with(['accountingPeriod', 'createdBy', 'lines'])
            ->latest()->take(5)->get();

        // Quick stats
        $totalSalesInvoices = SalesInvoice::whereIn('status', ['posted', 'paid', 'partial'])->count();
        $totalPurchaseInvoices = PurchaseInvoice::whereIn('status', ['posted', 'paid', 'partial'])->count();
        $totalCustomers = Customer::where('is_active', true)->count();
        $totalVendors = Vendor::where('is_active', true)->count();

        return view('dashboard', compact(
            'activePeriod', 'totalAccounts', 'draftEntries', 'postedEntries',
            'netIncome', 'totalRevenue', 'totalExpenses', 'totalRevenueFormatted',
            'grossProfit', 'totalAssets', 'totalLiabilitiesEquity', 'recentEntries',
            'incomeSummary', 'balanceSummary', 'cashBalance',
            'arTotal', 'apTotal', 'monthlyLabels', 'monthlyRevenue', 'monthlyExpense',
            'totalSalesInvoices', 'totalPurchaseInvoices', 'totalCustomers', 'totalVendors'
        ));
    }
}
