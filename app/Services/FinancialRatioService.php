<?php

namespace App\Services;

use App\Models\Account;

class FinancialRatioService
{
    public function generate(int $periodId): array
    {
        $incomeService = app(IncomeStatementService::class);
        $balanceService = app(BalanceSheetService::class);

        $income = $incomeService->generate($periodId);
        $balance = $balanceService->generate($periodId);

        $totalRevenue = $income['total_revenue'];
        $netIncome = $income['net_income'];
        $totalAssets = $balance['total_aktiva'];
        $totalLiabilities = $balance['total_kewajiban'];
        $totalEquity = $balance['total_modal'];

        // Find specific account groups for ratios
        $aktivaLancar = $this->getTotalByCategory('aktiva', $periodId, true);
        $kewajibanLancar = $this->getTotalByCategory('kewajiban', $periodId, true);
        $persediaan = $this->getTotalByName('persediaan', $periodId);
        $kasBank = $this->getTotalByName(['kas', 'bank'], $periodId);

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

        return $ratios;
    }

    private function getTotalByCategory(string $category, int $periodId, bool $currentOnly = false): float
    {
        $balanceService = app(BalanceSheetService::class);
        $balance = $balanceService->generate($periodId);

        if ($category === 'aktiva') {
            // For current assets (aktiva lancar) look at level 2 code 1.1
            $total = 0;
            foreach ($balance['aktiva']['details'] as $detail) {
                if ($currentOnly) {
                    // Only count accounts under 1.1.x.x.x (current assets)
                    if (strpos($detail['account']->code, '1.1.') === 0) {
                        $total += $detail['balance'];
                    }
                } else {
                    $total += $detail['balance'];
                }
            }
            return $total;
        }

        if ($category === 'kewajiban') {
            $total = 0;
            foreach ($balance['kewajiban']['details'] as $detail) {
                if ($currentOnly) {
                    // Only count accounts under 2.1.x.x.x (current liabilities)
                    if (strpos($detail['account']->code, '2.1.') === 0) {
                        $total += $detail['balance'];
                    }
                } else {
                    $total += $detail['balance'];
                }
            }
            return $total;
        }

        return 0;
    }

    private function getTotalByName(array|string $names, int $periodId): float
    {
        $ledgerService = app(LedgerService::class);
        $accounts = Account::where('report_type', 'balance_sheet')->get();
        $total = 0;

        $names = is_array($names) ? $names : [$names];

        foreach ($accounts as $account) {
            $match = false;
            foreach ($names as $name) {
                if (stripos($account->name, $name) !== false) {
                    $match = true;
                    break;
                }
            }
            if ($match) {
                $ledger = $ledgerService->generate($account->id, $periodId);
                $total += $ledger['ending_balance'];
            }
        }

        return $total;
    }
}
