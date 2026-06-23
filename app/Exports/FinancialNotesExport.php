<?php

namespace App\Exports;

use App\Models\AccountingPeriod;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class FinancialNotesExport implements FromArray, WithHeadings, WithTitle
{
    protected AccountingPeriod $period;
    protected array $balanceData;
    protected array $incomeData;

    public function __construct(AccountingPeriod $period, array $balanceData, array $incomeData)
    {
        $this->period = $period;
        $this->balanceData = $balanceData;
        $this->incomeData = $incomeData;
    }

    public function array(): array
    {
        $rows = [];

        $rows[] = ['PT. TRANSKARGO SOLUSINDO'];
        $rows[] = ['CATATAN ATAS LAPORAN KEUANGAN'];
        $rows[] = ['Periode: ' . $this->period->label];
        $rows[] = [];

        $rows[] = ['1. UMUM'];
        $rows[] = ['PT. Transkargo Solusindo adalah perusahaan yang bergerak di bidang jasa cargo dan logistik.'];
        $rows[] = [];

        // Assets breakdown
        $rows[] = ['3.1 AKTIVA', 'Jumlah (Rp)'];
        foreach ($this->balanceData['aktiva']['details'] as $a) {
            $rows[] = [str_repeat('  ', $a['account']->level) . $a['account']->name,
                $a['balance'] > 0 ? number_format($a['balance'], 0, ',', '.') : '-'];
        }
        $rows[] = ['TOTAL AKTIVA', number_format($this->balanceData['total_aktiva'], 0, ',', '.')];
        $rows[] = [];

        // Liabilities
        $rows[] = ['3.2 KEWAJIBAN', 'Jumlah (Rp)'];
        foreach ($this->balanceData['kewajiban']['details'] as $k) {
            $rows[] = [str_repeat('  ', $k['account']->level) . $k['account']->name,
                $k['balance'] > 0 ? number_format($k['balance'], 0, ',', '.') : '-'];
        }
        $rows[] = ['TOTAL KEWAJIBAN', number_format($this->balanceData['total_kewajiban'], 0, ',', '.')];
        $rows[] = [];

        // Equity
        $rows[] = ['3.3 MODAL', 'Jumlah (Rp)'];
        foreach ($this->balanceData['modal']['details'] as $m) {
            $rows[] = [str_repeat('  ', $m['account']->level) . $m['account']->name,
                $m['balance'] > 0 ? number_format($m['balance'], 0, ',', '.') : '-'];
        }
        $rows[] = ['TOTAL MODAL', number_format($this->balanceData['total_modal'], 0, ',', '.')];
        $rows[] = [];

        // Income Statement
        $rows[] = ['4. LABA RUGI', 'Jumlah (Rp)'];
        $rows[] = ['PENDAPATAN USAHA', number_format($this->incomeData['total_revenue'], 0, ',', '.')];
        $rows[] = ['BEBAN POKOK PENDAPATAN', '(' . number_format($this->incomeData['total_hpp'], 0, ',', '.') . ')'];
        $rows[] = ['LABA KOTOR', number_format($this->incomeData['gross_profit'], 0, ',', '.')];
        $rows[] = ['LABA BERSIH', number_format($this->incomeData['net_income'], 0, ',', '.')];

        return $rows;
    }

    public function headings(): array
    {
        return [];
    }

    public function title(): string
    {
        return 'Catatan LK';
    }
}
