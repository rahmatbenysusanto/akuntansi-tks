<?php

namespace App\Exports;

use App\Models\AccountingPeriod;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class TrialBalanceExport implements FromArray, WithHeadings, WithTitle
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

        $rows[] = ['PT. TRANSKARGO SOLUSINDO'];
        $rows[] = ['NERACA LAJUR'];
        $rows[] = ['Periode: ' . $this->period->label];
        $rows[] = [];

        $header1 = ['Kode', 'Nama Akun', 'Saldo Awal', '', 'Mutasi', '', 'Saldo Akhir', '', 'Laba Rugi', '', 'Neraca', ''];
        $header2 = ['', '', 'Debet', 'Kredit', 'Debet', 'Kredit', 'Debet', 'Kredit', 'Debet', 'Kredit', 'Debet', 'Kredit'];
        $rows[] = $header1;
        $rows[] = $header2;

        foreach ($this->data['lines'] as $line) {
            $rows[] = [
                $line['account']->code,
                $line['account']->name,
                $line['opening_debit'] > 0 ? number_format($line['opening_debit'], 0, ',', '.') : '-',
                $line['opening_credit'] > 0 ? number_format($line['opening_credit'], 0, ',', '.') : '-',
                $line['mutation_debit'] > 0 ? number_format($line['mutation_debit'], 0, ',', '.') : '-',
                $line['mutation_credit'] > 0 ? number_format($line['mutation_credit'], 0, ',', '.') : '-',
                $line['ending_debit'] > 0 ? number_format($line['ending_debit'], 0, ',', '.') : '-',
                $line['ending_credit'] > 0 ? number_format($line['ending_credit'], 0, ',', '.') : '-',
                $line['income_statement_debit'] > 0 ? number_format($line['income_statement_debit'], 0, ',', '.') : '-',
                $line['income_statement_credit'] > 0 ? number_format($line['income_statement_credit'], 0, ',', '.') : '-',
                $line['balance_sheet_debit'] > 0 ? number_format($line['balance_sheet_debit'], 0, ',', '.') : '-',
                $line['balance_sheet_credit'] > 0 ? number_format($line['balance_sheet_credit'], 0, ',', '.') : '-',
            ];
        }

        $t = $this->data['totals'];
        $rows[] = [
            '', 'TOTAL',
            number_format($t['opening_debit'], 0, ',', '.'),
            number_format($t['opening_credit'], 0, ',', '.'),
            number_format($t['mutation_debit'], 0, ',', '.'),
            number_format($t['mutation_credit'], 0, ',', '.'),
            number_format($t['ending_debit'], 0, ',', '.'),
            number_format($t['ending_credit'], 0, ',', '.'),
            number_format($t['income_statement_debit'], 0, ',', '.'),
            number_format($t['income_statement_credit'], 0, ',', '.'),
            number_format($t['balance_sheet_debit'], 0, ',', '.'),
            number_format($t['balance_sheet_credit'], 0, ',', '.'),
        ];

        return $rows;
    }

    public function headings(): array
    {
        return [];
    }

    public function title(): string
    {
        return 'Neraca Lajur';
    }
}
