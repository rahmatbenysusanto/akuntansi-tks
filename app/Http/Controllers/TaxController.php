<?php

namespace App\Http\Controllers;

use App\Models\TaxTransaction;
use Illuminate\Http\Request;

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
}
