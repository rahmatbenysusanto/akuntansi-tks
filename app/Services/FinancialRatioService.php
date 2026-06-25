<?php

namespace App\Services;

use App\Models\Account;
use App\Models\JournalEntryLine;
use Illuminate\Support\Facades\DB;

class FinancialRatioService
{
    /** @var array<string, array> Request-level cache */
    private static array $cache = [];

    /**
     * Generate financial ratios for a period.
     * Optionally accepts pre-computed data to avoid redundant service calls.
     *
     * @param array|null $income Pre-computed IncomeStatementService::generate() result
     * @param array|null $balance Pre-computed BalanceSheetService::generate() result
     */
    public function generate(int $periodId, ?array $income = null, ?array $balance = null): array
    {
        $cacheKey = "fr:{$periodId}";

        if (isset(self::$cache[$cacheKey])) {
            return self::$cache[$cacheKey];
        }

        // Use provided data or fetch from services (which have their own cache)
        if ($income === null) {
            $incomeService = app(IncomeStatementService::class);
            $income = $incomeService->generate($periodId);
        }
        if ($balance === null) {
            $balanceService = app(BalanceSheetService::class);
            $balance = $balanceService->generate($periodId);
        }

        $totalRevenue = $income['total_revenue'];
        $netIncome = $income['net_income'];
        $totalAssets = $balance['total_aktiva'];
        $totalLiabilities = $balance['total_kewajiban'];
        $totalEquity = $balance['total_modal'];

        // Calculate current assets & liabilities from balance data (NO extra service calls)
        $aktivaLancar = $this->sumBalancesByCodePrefix($balance['aktiva']['details'] ?? [], '1.1.');
        $kewajibanLancar = $this->sumBalancesByCodePrefix($balance['kewajiban']['details'] ?? [], '2.1.');

        // Calculate persediaan & kas/bank from balance data (NO extra service calls)
        $persediaan = $this->sumBalancesByNameKeyword($balance['aktiva']['details'] ?? [], 'persediaan');
        $kasBank = $this->sumBalancesByNameKeyword($balance['aktiva']['details'] ?? [], ['kas', 'bank']);

        $ratios = [];

        // Profitability Ratios
        $ratios['net_profit_margin'] = $totalRevenue > 0 ? [
            'label' => 'Net Profit Margin',
            'value' => round(($netIncome / $totalRevenue) * 100, 2),
            'unit' => '%',
            'formula' => 'Laba Bersih / Pendapatan × 100%',
        ] : ['label' => 'Net Profit Margin', 'value' => 0, 'unit' => '%', 'formula' => 'Laba Bersih / Pendapatan × 100%'];

        $ratios['roi'] = $totalAssets > 0 ? [
            'label' => 'Return on Investment (ROI)',
            'value' => round(($netIncome / $totalAssets) * 100, 2),
            'unit' => '%',
            'formula' => 'Laba Bersih / Total Aktiva × 100%',
        ] : ['label' => 'ROI', 'value' => 0, 'unit' => '%', 'formula' => 'Laba Bersih / Total Aktiva × 100%'];

        $ratios['roe'] = $totalEquity > 0 ? [
            'label' => 'Return on Equity (ROE)',
            'value' => round(($netIncome / $totalEquity) * 100, 2),
            'unit' => '%',
            'formula' => 'Laba Bersih / Modal × 100%',
        ] : ['label' => 'ROE', 'value' => 0, 'unit' => '%', 'formula' => 'Laba Bersih / Modal × 100%'];

        $ratios['roce'] = ($totalAssets - $kewajibanLancar) > 0 ? [
            'label' => 'Return on Capital Employed (ROCE)',
            'value' => round(($netIncome / ($totalAssets - $kewajibanLancar)) * 100, 2),
            'unit' => '%',
            'formula' => 'Laba Bersih / (Total Aktiva - Kewajiban Lancar) × 100%',
        ] : ['label' => 'ROCE', 'value' => 0, 'unit' => '%', 'formula' => 'Laba Bersih / (Total Aktiva - Kewajiban Lancar) × 100%'];

        // Liquidity Ratios
        $ratios['current_ratio'] = $kewajibanLancar > 0 ? [
            'label' => 'Current Ratio',
            'value' => round($aktivaLancar / $kewajibanLancar, 2),
            'unit' => 'x',
            'formula' => 'Aktiva Lancar / Kewajiban Lancar',
        ] : ['label' => 'Current Ratio', 'value' => 0, 'unit' => 'x', 'formula' => 'Aktiva Lancar / Kewajiban Lancar'];

        $quickAssets = $aktivaLancar - $persediaan;
        $ratios['quick_ratio'] = $kewajibanLancar > 0 ? [
            'label' => 'Quick Ratio',
            'value' => round($quickAssets / $kewajibanLancar, 2),
            'unit' => 'x',
            'formula' => '(Aktiva Lancar - Persediaan) / Kewajiban Lancar',
        ] : ['label' => 'Quick Ratio', 'value' => 0, 'unit' => 'x', 'formula' => '(Aktiva Lancar - Persediaan) / Kewajiban Lancar'];

        $ratios['absolute_liquidity_ratio'] = $kewajibanLancar > 0 ? [
            'label' => 'Absolute Liquidity Ratio',
            'value' => round($kasBank / $kewajibanLancar, 2),
            'unit' => 'x',
            'formula' => '(Kas + Bank) / Kewajiban Lancar',
        ] : ['label' => 'Absolute Liquidity Ratio', 'value' => 0, 'unit' => 'x', 'formula' => '(Kas + Bank) / Kewajiban Lancar'];

        // Efficiency Ratios
        $ratios['sales_to_liquid_assets'] = $aktivaLancar > 0 ? [
            'label' => 'Sales to Liquid Assets',
            'value' => round($totalRevenue / $aktivaLancar, 2),
            'unit' => 'x',
            'formula' => 'Pendapatan / Aktiva Lancar',
        ] : ['label' => 'Sales to Liquid Assets', 'value' => 0, 'unit' => 'x', 'formula' => 'Pendapatan / Aktiva Lancar'];

        $ratios['debt_to_equity'] = $totalEquity > 0 ? [
            'label' => 'Debt to Equity Ratio (DER)',
            'value' => round(($totalLiabilities / $totalEquity) * 100, 2),
            'unit' => '%',
            'formula' => 'Total Kewajiban / Modal × 100%',
        ] : ['label' => 'DER', 'value' => 0, 'unit' => '%', 'formula' => 'Total Kewajiban / Modal × 100%'];

        return self::$cache[$cacheKey] = $ratios;
    }

    /**
     * Sum balances for accounts whose code starts with the given prefix.
     */
    private function sumBalancesByCodePrefix(array $details, string $prefix): float
    {
        $total = 0.0;
        foreach ($details as $detail) {
            if (strpos($detail['account']->code, $prefix) === 0) {
                $total += $detail['balance'];
            }
        }
        return $total;
    }

    /**
     * Sum balances for accounts whose name contains any of the given keywords.
     */
    private function sumBalancesByNameKeyword(array $details, array|string $keywords): float
    {
        $keywords = is_array($keywords) ? $keywords : [$keywords];
        $total = 0.0;
        foreach ($details as $detail) {
            foreach ($keywords as $kw) {
                if (stripos($detail['account']->name, $kw) !== false) {
                    $total += $detail['balance'];
                    break;
                }
            }
        }
        return $total;
    }
}
