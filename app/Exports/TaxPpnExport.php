<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class TaxPpnExport implements FromCollection, WithHeadings, WithTitle
{
    protected Collection $keluaran;
    protected Collection $masukan;
    protected float $totalKeluaran;
    protected float $totalMasukan;
    protected float $netto;
    protected int $month;
    protected int $year;

    public function __construct(Collection $keluaran, Collection $masukan, float $totalKeluaran, float $totalMasukan, float $netto, int $month, int $year)
    {
        $this->keluaran = $keluaran;
        $this->masukan = $masukan;
        $this->totalKeluaran = $totalKeluaran;
        $this->totalMasukan = $totalMasukan;
        $this->netto = $netto;
        $this->month = $month;
        $this->year = $year;
    }

    public function collection(): Collection
    {
        $rows = collect();

        $rows->push(['PT. TRANSKARGO SOLUSINDO']);
        $rows->push(['REKAP PPN']);
        $rows->push(['Periode: ' . \Carbon\Carbon::create($this->year, $this->month, 1)->translatedFormat('F Y')]);
        $rows->push([]);

        $rows->push(['PPN Keluaran', number_format($this->totalKeluaran, 0, ',', '.')]);
        $rows->push(['PPN Masukan', number_format($this->totalMasukan, 0, ',', '.')]);
        $rows->push([$this->netto >= 0 ? 'PPN Harus Disetor' : 'PPN Lebih Bayar', number_format(abs($this->netto), 0, ',', '.')]);
        $rows->push([]);

        // Keluaran detail
        $rows->push(['DETAIL PPN KELUARAN']);
        $rows->push(['Tanggal', 'Counterparty', 'NPWP', 'DPP', 'PPN', 'Dokumen']);
        if ($this->keluaran->count() > 0) {
            foreach ($this->keluaran as $k) {
                $rows->push([
                    \Carbon\Carbon::parse($k->transaction_date)->format('d/m/Y'),
                    $k->counterparty_name,
                    $k->counterparty_npwp,
                    number_format($k->dpp, 0, ',', '.'),
                    number_format($k->tax_amount, 0, ',', '.'),
                    $k->document_no,
                ]);
            }
        } else {
            $rows->push(['Tidak ada transaksi PPN Keluaran.']);
        }

        $rows->push([]);
        $rows->push(['DETAIL PPN MASUKAN']);
        $rows->push(['Tanggal', 'Counterparty', 'NPWP', 'DPP', 'PPN', 'Dokumen']);
        if ($this->masukan->count() > 0) {
            foreach ($this->masukan as $m) {
                $rows->push([
                    \Carbon\Carbon::parse($m->transaction_date)->format('d/m/Y'),
                    $m->counterparty_name,
                    $m->counterparty_npwp,
                    number_format($m->dpp, 0, ',', '.'),
                    number_format($m->tax_amount, 0, ',', '.'),
                    $m->document_no,
                ]);
            }
        } else {
            $rows->push(['Tidak ada transaksi PPN Masukan.']);
        }

        return $rows;
    }

    public function headings(): array
    {
        return [];
    }

    public function title(): string
    {
        return 'Rekap PPN';
    }
}
