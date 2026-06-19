<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Vendor;
use App\Services\ARAPService;
use Illuminate\Http\Request;

class ARAPController extends Controller
{
    public function kartuPiutang(Request $request, ARAPService $service)
    {
        $customers = Customer::where('is_active', true)->orderBy('name')->get();
        $data = null;
        $selectedCustomer = null;

        if ($request->filled('customer_id')) {
            $selectedCustomer = Customer::find($request->customer_id);
            $data = $service->kartuPiutang($request->customer_id);
        }

        return view('arap.kartu-piutang', compact('customers', 'data', 'selectedCustomer'));
    }

    public function kartuHutang(Request $request, ARAPService $service)
    {
        $vendors = Vendor::where('is_active', true)->orderBy('name')->get();
        $data = null;
        $selectedVendor = null;

        if ($request->filled('vendor_id')) {
            $selectedVendor = Vendor::find($request->vendor_id);
            $data = $service->kartuHutang($request->vendor_id);
        }

        return view('arap.kartu-hutang', compact('vendors', 'data', 'selectedVendor'));
    }

    public function agingPiutang(ARAPService $service)
    {
        $aging = $service->agingPiutang();
        $totalAll = collect($aging)->sum('total');
        return view('arap.aging-piutang', compact('aging', 'totalAll'));
    }

    public function agingHutang(ARAPService $service)
    {
        $aging = $service->agingHutang();
        $totalAll = collect($aging)->sum('total');
        return view('arap.aging-hutang', compact('aging', 'totalAll'));
    }
}
