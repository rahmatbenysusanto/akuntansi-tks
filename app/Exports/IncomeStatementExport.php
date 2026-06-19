<?php

namespace App\Exports;

use App\Models\AccountingPeriod;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class IncomeStatementExport implements FromArray, WithHeadings, WithTitle
{
    protected AccountingPeriod $period;
    protected array $data;

    public function __construct(AccountingPeriod $period, array $data)
    {
        $this->period = $period;
        $this->data = $data;
    }

    public function array(): array
    {
        $rows = [];

        $rows[] = ['PT. TRANSKARGO SOLUSINDO', ''];
        $rows[] = ['LAPORAN LABA RUGI', ''];
        $rows[] = ['Periode: ' . $this->period->label, ''];
        $rows[] = [];

        $rows[] = ['PENDAPATAN USAHA', number_format($this->data['total_revenue'], 0, ',', '.')];
        foreach ($this->data['revenues'] as $rev) {
            $rows[] = ['  ' . $rev['account']->name, $rev['balance'] > 0 ? number_format($rev['balance'], 0, ',', '.') : '-'];
        }

        $rows[] = ['BEBAN POKOK PENDAPATAN (HPP)', '(' . number_format($this->data['total_hpp'], 0, ',', '.') . ')'];
        $rows[] = ['LABA KOTOR', number_format($this->data['gross_profit'], 0, ',', '.')];
        $rows[] = ['BIAYA OPERASIONAL', '(' . number_format($this->data['total_operating_expenses'], 0, ',', '.') . ')'];
        $rows[] = ['LABA USAHA', number_format($this->data['operating_profit'], 0, ',', '.')];
        $rows[] = ['PENDAPATAN / BIAYA LAIN-LAIN', number_format($this->data['total_other'], 0, ',', '.')];
        $rows[] = ['BIAYA BUNGA', '(' . number_format($this->data['total_interest'], 0, ',', '.') . ')'];
        $rows[] = ['LABA SEBELUM PAJAK', number_format($this->data['profit_before_tax'], 0, ',', '.')];
        $rows[] = ['PAJAK PENGHASILAN', '(' . number_format($this->data['total_tax'], 0, ',', '.') . ')'];
        $rows[] = ['LABA BERSIH', number_format($this->data['net_income'], 0, ',', '.')];

        return $rows;
    }

    public function headings(): array
    {
        return ['Akun', 'Jumlah (Rp)'];
    }

    public function title(): string
    {
        return 'Laba Rugi';
    }
}
