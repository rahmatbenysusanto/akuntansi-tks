<?php

namespace App\Exports;

use App\Models\AccountingPeriod;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class BalanceSheetExport implements FromArray, WithHeadings, WithTitle
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

        $rows[] = ['PT. TRANSKARGO SOLUSINDO', '', ''];
        $rows[] = ['NERACA', ''];
        $rows[] = ['Per ' . $this->period->label, ''];
        $rows[] = [];

        // Assets
        $rows[] = ['AKTIVA', 'Jumlah (Rp)', ''];
        foreach ($this->data['aktiva']['details'] as $a) {
            $rows[] = [str_repeat('  ', $a['account']->level) . $a['account']->name,
                $a['balance'] > 0 ? number_format($a['balance'], 0, ',', '.') : '-', ''];
        }
        $rows[] = ['TOTAL AKTIVA', number_format($this->data['total_aktiva'], 0, ',', '.'), ''];

        $rows[] = [];
        $rows[] = ['KEWAJIBAN DAN MODAL', '', ''];
        $rows[] = ['KEWAJIBAN', '', ''];
        foreach ($this->data['kewajiban']['details'] as $k) {
            $rows[] = [str_repeat('  ', $k['account']->level) . $k['account']->name,
                '', $k['balance'] > 0 ? number_format($k['balance'], 0, ',', '.') : '-'];
        }
        $rows[] = ['TOTAL KEWAJIBAN', '', number_format($this->data['total_kewajiban'], 0, ',', '.')];

        $rows[] = ['MODAL', '', ''];
        foreach ($this->data['modal']['details'] as $m) {
            $rows[] = [str_repeat('  ', $m['account']->level) . $m['account']->name,
                '', $m['balance'] > 0 ? number_format($m['balance'], 0, ',', '.') : '-'];
        }
        $rows[] = ['TOTAL MODAL', '', number_format($this->data['total_modal'], 0, ',', '.')];
        $rows[] = ['TOTAL KEWAJIBAN & MODAL', '', number_format($this->data['total_kewajiban_modal'], 0, ',', '.')];

        return $rows;
    }

    public function headings(): array
    {
        return ['Akun', 'Aktiva (Rp)', 'Pasiva (Rp)'];
    }

    public function title(): string
    {
        return 'Neraca';
    }
}
