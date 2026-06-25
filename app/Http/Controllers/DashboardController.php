<?php

namespace App\Http\Controllers;

use App\Models\AccountingPeriod;
use App\Models\Account;
use App\Models\JournalEntry;
use App\Models\SalesInvoice;
use App\Models\PurchaseInvoice;
use App\Models\Customer;
use App\Models\Vendor;
use App\Services\BalanceSheetService;
use App\Services\IncomeStatementService;
use App\Services\FinancialRatioService;
use App\Services\ARAPService;
use App\Services\LedgerService;

class DashboardController extends Controller
{
    public function index(
        IncomeStatementService $incomeStatementService,
        BalanceSheetService $balanceSheetService,
        FinancialRatioService $financialRatioService,
        ARAPService $arapService,
        LedgerService $ledgerService
    ) {
        $activePeriod = AccountingPeriod::where('status', 'open')
            ->orderBy('year', 'desc')->orderBy('month', 'desc')->first();

        // Default kosong jika belum ada periode
        $netIncome = 0;
        $totalRevenue = 0;
        $totalExpenses = 0;
        $grossProfit = 0;
        $totalAssets = 0;
        $totalLiabilitiesEquity = 0;
        $incomeSummary = null;
        $balanceSummary = null;
        $ratios = [];

        if ($activePeriod) {
            // Income Statement & Balance Sheet (each cached within request)
            $incomeSummary = $incomeStatementService->generate($activePeriod->id);
            $balanceSummary = $balanceSheetService->generate($activePeriod->id);

            $netIncome = $incomeSummary['net_income'] ?? 0;
            $totalRevenue = $incomeSummary['total_revenue'] ?? 0;
            $totalExpenses = ($incomeSummary['total_hpp'] ?? 0)
                + ($incomeSummary['total_operating_expenses'] ?? 0)
                + ($incomeSummary['total_interest'] ?? 0)
                + ($incomeSummary['total_tax'] ?? 0);
            $grossProfit = $incomeSummary['gross_profit'] ?? 0;
            $totalAssets = $balanceSummary['total_aktiva'] ?? 0;
            $totalLiabilitiesEquity = $balanceSummary['total_kewajiban_modal'] ?? 0;

            // Rasio keuangan — pass pre-computed data to avoid redundant service calls
            try {
                $ratios = $financialRatioService->generate(
                    $activePeriod->id,
                    income: $incomeSummary,
                    balance: $balanceSummary
                );
            } catch (\Exception $e) {
                $ratios = [];
            }
        }

        // ==========================================
        // KAS & BANK — use batch query (2 queries instead of 2N)
        // ==========================================
        $cashBalance = 0;
        $cashBreakdown = [];
        if ($activePeriod) {
            $cashAccounts = Account::where(function ($q) {
                $q->where('code', 'LIKE', '1.1.01.%')
                  ->orWhere('code', 'LIKE', '1.1.02.%');
            })->where('is_header', false)->get();

            if ($cashAccounts->isNotEmpty()) {
                $balances = $ledgerService->batchEndingBalances(
                    $cashAccounts->pluck('id')->toArray(),
                    $activePeriod->id
                );

                foreach ($cashAccounts as $acc) {
                    $ending = $balances[$acc->id] ?? 0;
                    $cashBalance += $ending;
                    $cashBreakdown[] = [
                        'account' => $acc,
                        'balance' => $ending,
                    ];
                }
                // Urutkan dari saldo terbesar
                usort($cashBreakdown, fn($a, $b) => $b['balance'] <=> $a['balance']);
            }
        }

        // ==========================================
        // AR / AP TOTALS
        // ==========================================
        $arTotal = 0;
        $apTotal = 0;
        $arAging = [];
        $apAging = [];
        try {
            $arAging = $arapService->agingPiutang();
            $apAging = $arapService->agingHutang();
            $arTotal = collect($arAging)->sum('total');
            $apTotal = collect($apAging)->sum('total');
        } catch (\Exception $e) {}

        // ==========================================
        // GRAFIK BULANAN — use lightweight batch summary (1 query, not 6× generate())
        // ==========================================
        $monthlyLabels = [];
        $monthlyRevenue = [];
        $monthlyExpense = [];
        $allPeriods = AccountingPeriod::orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->take(6)->get();

        if ($allPeriods->isNotEmpty()) {
            $periodIds = $allPeriods->pluck('id')->toArray();
            $summaries = $incomeStatementService->batchMonthlySummary($periodIds);

            $allPeriods = $allPeriods->reverse();
            foreach ($allPeriods as $p) {
                $summary = $summaries[$p->id] ?? null;
                $monthlyLabels[] = $p->label;
                $monthlyRevenue[] = $summary ? $summary['revenue'] : 0;
                $monthlyExpense[] = $summary ? $summary['expense'] : 0;
            }
        }

        // ==========================================
        // TRANSAKSI TERBARU (5 terakhir, eager-loaded)
        // ==========================================
        $recentEntries = JournalEntry::with(['accountingPeriod', 'createdBy', 'lines'])
            ->latest()->limit(5)->get();

        // ==========================================
        // QUICK STATS
        // ==========================================
        $totalAccounts = Account::count();
        $draftEntries = JournalEntry::where('status', 'draft')->count();
        $postedEntries = JournalEntry::where('status', 'posted')->count();
        $totalSalesInvoices = SalesInvoice::whereIn('status', ['posted', 'paid', 'partial'])->count();
        $totalPurchaseInvoices = PurchaseInvoice::whereIn('status', ['posted', 'paid', 'partial'])->count();
        $totalCustomers = Customer::where('is_active', true)->count();
        $totalVendors = Vendor::where('is_active', true)->count();

        // ==========================================
        // INVOICE JATUH TEMPO (7 hari ke depan)
        // ==========================================
        $dueSales = SalesInvoice::whereIn('status', ['posted', 'partial'])
            ->where('due_date', '>=', now())
            ->where('due_date', '<=', now()->addDays(7))
            ->count();
        $duePurchases = PurchaseInvoice::whereIn('status', ['posted', 'partial'])
            ->where('due_date', '>=', now())
            ->where('due_date', '<=', now()->addDays(7))
            ->count();

        return view('dashboard', compact(
            'activePeriod',
            'totalAccounts', 'draftEntries', 'postedEntries',
            'netIncome', 'totalRevenue', 'totalExpenses',
            'grossProfit', 'totalAssets', 'totalLiabilitiesEquity',
            'recentEntries', 'incomeSummary', 'balanceSummary',
            'cashBalance', 'cashBreakdown',
            'arTotal', 'apTotal', 'arAging', 'apAging',
            'ratios',
            'monthlyLabels', 'monthlyRevenue', 'monthlyExpense',
            'totalSalesInvoices', 'totalPurchaseInvoices',
            'totalCustomers', 'totalVendors',
            'dueSales', 'duePurchases',
        ));
    }
}
