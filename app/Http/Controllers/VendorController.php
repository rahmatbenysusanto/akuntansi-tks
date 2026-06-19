<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Vendor;
use Illuminate\Http\Request;

class VendorController extends Controller
{
    public function index()
    {
        $vendors = Vendor::with('apAccount')->orderBy('name')->paginate(20);
        return view('vendors.index', compact('vendors'));
    }

    public function create()
    {
        $accounts = Account::where('is_header', false)->orderBy('code')->get();
        return view('vendors.form', compact('accounts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:20',
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:30',
            'npwp' => 'nullable|string|max:30',
            'payment_term_days' => 'integer|default:30',
            'ap_account_id' => 'nullable|exists:accounts,id',
            'is_active' => 'boolean',
        ]);

        Vendor::create($validated);

        return redirect()->route('vendors.index')
            ->with('success', 'Vendor berhasil ditambahkan.');
    }

    public function edit(Vendor $vendor)
    {
        $accounts = Account::where('is_header', false)->orderBy('code')->get();
        return view('vendors.form', compact('vendor', 'accounts'));
    }

    public function update(Request $request, Vendor $vendor)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:20',
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:30',
            'npwp' => 'nullable|string|max:30',
            'payment_term_days' => 'integer|default:30',
            'ap_account_id' => 'nullable|exists:accounts,id',
            'is_active' => 'boolean',
        ]);

        $vendor->update($validated);

        return redirect()->route('vendors.index')
            ->with('success', 'Vendor berhasil diperbarui.');
    }

    public function destroy(Vendor $vendor)
    {
        $vendor->update(['is_active' => false]);
        return redirect()->route('vendors.index')
            ->with('success', 'Vendor dinonaktifkan.');
    }
}
