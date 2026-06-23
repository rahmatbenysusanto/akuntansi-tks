<?php

namespace App\Exports;

use App\Models\AccountingPeriod;
use App\Models\Account;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class GeneralLedgerExport implements FromArray, WithHeadings, WithTitle
{
    protected AccountingPeriod $period;
    protected Account $account;
    protected array $data;

    public function __construct(AccountingPeriod $period, Account $account, array $data)
    {
        $this->period = $period;
        $this->account = $account;
        $this->data = $data;
    }

    public function array(): array
    {
        $rows = [];

        $rows[] = ['PT. TRANSKARGO SOLUSINDO'];
        $rows[] = ['BUKU BESAR'];
        $rows[] = [$this->account->code . ' - ' . $this->account->name];
        $rows[] = ['Periode: ' . $this->period->label];
        $rows[] = [];

        $rows[] = ['Tanggal', 'No. Bukti', 'Keterangan', 'Debet', 'Kredit', 'Saldo'];
        $rows[] = ['Saldo Awal', '', '',
            number_format($this->data['opening_balance_debit'], 0, ',', '.'),
            number_format($this->data['opening_balance_credit'], 0, ',', '.'),
            number_format($this->data['opening_balance'], 0, ',', '.')];

        foreach ($this->data['mutations'] as $m) {
            $rows[] = [
                $m['date']->format('d/m/Y'),
                $m['reference_no'],
                $m['description'],
                $m['debit'] > 0 ? number_format($m['debit'], 0, ',', '.') : '-',
                $m['credit'] > 0 ? number_format($m['credit'], 0, ',', '.') : '-',
                number_format($m['balance'], 0, ',', '.'),
            ];
        }

        $rows[] = ['Saldo Akhir', '', '',
            number_format($this->data['total_debit'], 0, ',', '.'),
            number_format($this->data['total_credit'], 0, ',', '.'),
            number_format($this->data['ending_balance'], 0, ',', '.')];

        return $rows;
    }

    public function headings(): array
    {
        return [];
    }

    public function title(): string
    {
        return 'Buku Besar';
    }
}
