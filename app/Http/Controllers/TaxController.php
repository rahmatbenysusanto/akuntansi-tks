<?php

namespace App\Http\Controllers;

use App\Models\TaxTransaction;
use App\Exports\TaxPpnExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class TaxController extends Controller
{
    public function index(Request $r)
    {
        $taxes = TaxTransaction::orderBy('period_year', 'desc')->orderBy('period_month', 'desc')->paginate(50);
        return view('tax.index', compact('taxes'));
    }

    public function reportPpn(Request $r)
    {
        $month = $r->month ?? now()->month;
        $year = $r->year ?? now()->year;

        $keluaran = TaxTransaction::where('tax_type', 'ppn_keluaran')
            ->where('period_month', $month)->where('period_year', $year)->get();
        $masukan = TaxTransaction::where('tax_type', 'ppn_masukan')
            ->where('period_month', $month)->where('period_year', $year)->get();

        $totalKeluaran = $keluaran->sum('tax_amount');
        $totalMasukan = $masukan->sum('tax_amount');
        $netto = $totalKeluaran - $totalMasukan;

        return view('tax.ppn', compact('keluaran', 'masukan', 'totalKeluaran', 'totalMasukan', 'netto', 'month', 'year'));
    }

    public function ppnPdf(Request $r)
    {
        $month = $r->month ?? now()->month;
        $year = $r->year ?? now()->year;

        $keluaran = TaxTransaction::where('tax_type', 'ppn_keluaran')
            ->where('period_month', $month)->where('period_year', $year)->get();
        $masukan = TaxTransaction::where('tax_type', 'ppn_masukan')
            ->where('period_month', $month)->where('period_year', $year)->get();

        $totalKeluaran = $keluaran->sum('tax_amount');
        $totalMasukan = $masukan->sum('tax_amount');
        $netto = $totalKeluaran - $totalMasukan;

        try {
            $pdf = Pdf::loadView('reports.pdf.tax-ppn', compact('keluaran', 'masukan', 'totalKeluaran', 'totalMasukan', 'netto', 'month', 'year'));
            return $pdf->download('Rekap-PPN-' . $month . '-' . $year . '.pdf');
        } catch (\Exception $e) {
            return redirect()->route('tax.ppn', compact('month', 'year'))->with('error', 'Gagal generate PDF: ' . $e->getMessage());
        }
    }

    public function ppnExcel(Request $r)
    {
        $month = $r->month ?? now()->month;
        $year = $r->year ?? now()->year;

        $keluaran = TaxTransaction::where('tax_type', 'ppn_keluaran')
            ->where('period_month', $month)->where('period_year', $year)->get();
        $masukan = TaxTransaction::where('tax_type', 'ppn_masukan')
            ->where('period_month', $month)->where('period_year', $year)->get();

        $totalKeluaran = $keluaran->sum('tax_amount');
        $totalMasukan = $masukan->sum('tax_amount');
        $netto = $totalKeluaran - $totalMasukan;

        try {
            return Excel::download(new TaxPpnExport($keluaran, $masukan, $totalKeluaran, $totalMasukan, $netto, $month, $year), 'Rekap-PPN-' . $month . '-' . $year . '.xlsx');
        } catch (\Exception $e) {
            return redirect()->route('tax.ppn', compact('month', 'year'))->with('error', 'Gagal export Excel: ' . $e->getMessage());
        }
    }
}
