<?php

namespace App\Http\Controllers;

use App\Models\ExchangeRate;
use Illuminate\Http\Request;

class ExchangeRateController extends Controller
{
    public function index() { $rates = ExchangeRate::orderBy('rate_date', 'desc')->paginate(20); return view('currency.index', compact('rates')); }
    public function store(Request $r) { ExchangeRate::create($r->all()); return redirect()->route('exchange-rates.index')->with('success', 'Rate added.'); }
}
