<?php

namespace App\Exports;

use App\Models\AccountingPeriod;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class FinancialHighlightExport implements FromArray, WithHeadings, WithTitle
{
    protected AccountingPeriod $period;
    protected array $ratios;

    public function __construct(AccountingPeriod $period, array $ratios)
    {
        $this->period = $period;
        $this->ratios = $ratios;
    }

    public function array(): array
    {
        $rows = [];

        $rows[] = ['PT. TRANSKARGO SOLUSINDO'];
        $rows[] = ['FINANCIAL HIGHLIGHT'];
        $rows[] = ['Periode: ' . $this->period->label];
        $rows[] = [];

        $rows[] = ['RASIO', 'NILAI', 'FORMULA'];
        $rows[] = ['PROFITABILITY RATIOS', '', ''];

        foreach (['net_profit_margin', 'roi', 'roe', 'roce'] as $key) {
            if (isset($this->ratios[$key])) {
                $r = $this->ratios[$key];
                $rows[] = [$r['label'], number_format($r['value'], 2) . $r['unit'], $r['formula']];
            }
        }

        $rows[] = ['', '', ''];
        $rows[] = ['LIQUIDITY RATIOS', '', ''];
        foreach (['current_ratio', 'quick_ratio', 'absolute_liquidity_ratio'] as $key) {
            if (isset($this->ratios[$key])) {
                $r = $this->ratios[$key];
                $rows[] = [$r['label'], number_format($r['value'], 2) . $r['unit'], $r['formula']];
            }
        }

        $rows[] = ['', '', ''];
        $rows[] = ['EFFICIENCY & LEVERAGE RATIOS', '', ''];
        foreach (['sales_to_liquid_assets', 'debt_to_equity'] as $key) {
            if (isset($this->ratios[$key])) {
                $r = $this->ratios[$key];
                $rows[] = [$r['label'], number_format($r['value'], 2) . $r['unit'], $r['formula']];
            }
        }

        return $rows;
    }

    public function headings(): array
    {
        return [];
    }

    public function title(): string
    {
        return 'Financial Highlight';
    }
}
