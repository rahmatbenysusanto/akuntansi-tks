<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index()
    {
        $customers = Customer::with('arAccount')->orderBy('name')->paginate(20);
        return view('customers.index', compact('customers'));
    }

    public function create()
    {
        $accounts = Account::where('is_header', false)->orderBy('code')->get();
        return view('customers.form', compact('accounts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:20',
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:30',
            'npwp' => 'nullable|string|max:30',
            'payment_term_days' => 'nullable|integer',
            'credit_limit' => 'nullable|numeric|min:0',
            'ar_account_id' => 'nullable|exists:accounts,id',
            'is_active' => 'nullable|boolean',
        ]);

        // Set default values for optional fields
        $validated['payment_term_days'] ??= 30;
        $validated['credit_limit'] ??= 0;
        $validated['is_active'] ??= true;

        Customer::create($validated);

        return redirect()->route('customers.index')
            ->with('success', 'Customer berhasil ditambahkan.');
    }

    public function edit(Customer $customer)
    {
        $accounts = Account::where('is_header', false)->orderBy('code')->get();
        return view('customers.form', compact('customer', 'accounts'));
    }

    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:20',
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:30',
            'npwp' => 'nullable|string|max:30',
            'payment_term_days' => 'nullable|integer',
            'credit_limit' => 'nullable|numeric|min:0',
            'ar_account_id' => 'nullable|exists:accounts,id',
            'is_active' => 'nullable|boolean',
        ]);

        // Set default values for optional fields
        $validated['payment_term_days'] ??= 30;
        $validated['credit_limit'] ??= 0;
        $validated['is_active'] ??= true;

        $customer->update($validated);

        return redirect()->route('customers.index')
            ->with('success', 'Customer berhasil diperbarui.');
    }

    public function destroy(Customer $customer)
    {
        $customer->update(['is_active' => false]);
        return redirect()->route('customers.index')
            ->with('success', 'Customer dinonaktifkan.');
    }
}
