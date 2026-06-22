<?php

namespace App\Http\Controllers;

use App\Models\ExchangeRate;
use Illuminate\Http\Request;

class ExchangeRateController extends Controller
{
    public function index()
    {
        $rates = ExchangeRate::orderBy('rate_date', 'desc')->paginate(20);
        return view('currency.index', compact('rates'));
    }

    public function store(Request $r)
    {
        $validated = $r->validate([
            'currency_code' => 'required|string|in:USD,EUR,SGD,JPY,GBP,AUD,MYR,CNY,THB',
            'rate_date' => 'required|date',
            'rate_to_idr' => 'required|numeric|min:0',
        ]);

        ExchangeRate::create($validated);

        return redirect()->route('exchange-rates.index')
            ->with('success', 'Kurs berhasil ditambahkan.');
    }
}
