<?php

namespace App\Http\Controllers;

use App\Models\PurchaseInvoice;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PurchaseInvoiceController extends Controller
{
    public function index()
    {
        $invoices = PurchaseInvoice::with('vendor')->latest()->paginate(20);
        return view('purchases.index', compact('invoices'));
    }

    public function create()
    {
        $vendors = Vendor::where('is_active', true)->orderBy('name')->get();
        return view('purchases.form', compact('vendors'));
    }

    public function store(Request $request)
    {
        $companyId = auth()->user()->current_company_id;

        $validated = $request->validate([
            'vendor_id' => [
                'required',
                Rule::exists('vendors', 'id')
                    ->where('company_id', $companyId),
            ],
            'invoice_no' => 'required|string|max:50',
            'invoice_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:invoice_date',
            'lines' => 'required|array|min:1',
            'lines.*.description' => 'required|string',
            'lines.*.qty' => 'required|integer|min:1',
            'lines.*.unit_price' => 'required|numeric|min:0',
            'lines.*.discount' => 'numeric|min:0',
            'lines.*.tax_rate' => 'numeric|min:0|max:100',
        ]);

        $vendor = Vendor::findOrFail($validated['vendor_id']);

        DB::transaction(function () use ($validated, $vendor, $request) {
            $subtotal = 0;
            $tax = 0;
            $linesData = [];

            foreach ($validated['lines'] as $line) {
                $lineTotal = $line['qty'] * $line['unit_price'] - ($line['discount'] ?? 0);
                $lineTax = $lineTotal * ($line['tax_rate'] ?? 0) / 100;
                $subtotal += $lineTotal;
                $tax += $lineTax;
                $linesData[] = [
                    'description' => $line['description'],
                    'qty' => $line['qty'],
                    'unit_price' => $line['unit_price'],
                    'discount' => $line['discount'] ?? 0,
                    'tax_rate' => $line['tax_rate'] ?? 0,
                    'line_total' => $lineTotal + $lineTax,
                ];
            }

            $invoice = PurchaseInvoice::create([
                'company_id' => auth()->user()->current_company_id,
                'vendor_id' => $vendor->id,
                'invoice_no' => $validated['invoice_no'],
                'invoice_date' => $validated['invoice_date'],
                'due_date' => $validated['due_date'],
                'status' => $request->input('action', 'draft') === 'post' ? 'posted' : 'draft',
                'subtotal' => $subtotal,
                'tax_amount' => $tax,
                'total' => $subtotal + $tax,
                'created_by' => auth()->id(),
            ]);

            $invoice->lines()->createMany($linesData);

            if ($request->input('action') === 'post') {
                $invoice->post();
            }
        });

        return redirect()->route('purchases.index')
            ->with('success', 'Purchase invoice created successfully.');
    }
}
