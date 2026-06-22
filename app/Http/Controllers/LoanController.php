<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\LoanFacility;
use App\Models\LoanInstallmentSchedule;
use App\Models\JournalEntry;
use App\Models\AccountingPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class LoanController extends Controller
{
    public function index() { $loans = LoanFacility::latest()->paginate(20); return view('loans.index', compact('loans')); }
    public function create() { $accounts = Account::where('is_header', false)->orderBy('code')->get(); return view('loans.form', compact('accounts')); }

    public function store(Request $r)
    {
        $companyId = auth()->user()->current_company_id;

        $validated = $r->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:bank_loan,leasing,kpr,kredit_investasi',
            'liability_account_id' => [
                'required',
                Rule::exists('accounts', 'id')->where('company_id', $companyId),
            ],
            'interest_expense_account_id' => [
                'required',
                Rule::exists('accounts', 'id')->where('company_id', $companyId),
            ],
            'principal_amount' => 'required|numeric|min:0',
            'interest_rate_per_year' => 'required|numeric|min:0|max:100',
            'tenor_months' => 'required|integer|min:1',
            'start_date' => 'required|date',
            'calculation_method' => 'nullable|in:flat,anuitas,efektif',
            'counterparty' => 'nullable|string|max:255',
        ]);

        $validated['calculation_method'] ??= 'flat';
        $validated['status'] = 'active';

        $loan = LoanFacility::create($validated);

        // Generate flat installment schedule
        $monthlyPrincipal = $loan->principal_amount / $loan->tenor_months;
        $monthlyInterest = $loan->principal_amount * ($loan->interest_rate_per_year / 100) / 12;
        for ($i = 1; $i <= $loan->tenor_months; $i++) {
            LoanInstallmentSchedule::create([
                'loan_facility_id' => $loan->id,
                'installment_no' => $i,
                'due_date' => $loan->start_date->addMonths($i),
                'principal_amount' => $monthlyPrincipal,
                'interest_amount' => $monthlyInterest,
                'total_amount' => $monthlyPrincipal + $monthlyInterest,
                'status' => 'unpaid',
            ]);
        }
        return redirect()->route('loans.index')->with('success', 'Loan created with ' . $loan->tenor_months . ' installments.');
    }

    public function show(LoanFacility $loan)
    {
        $schedules = $loan->schedules()->orderBy('installment_no')->get();
        return view('loans.show', compact('loan', 'schedules'));
    }

    public function payInstallment(Request $r, LoanFacility $loan)
    {
        $schedule = LoanInstallmentSchedule::findOrFail($r->schedule_id);

        if ($schedule->status === 'paid') {
            return back()->with('error', 'Cicilan ini sudah dibayar.');
        }

        DB::transaction(function () use ($schedule, $loan) {
            $period = AccountingPeriod::where('year', now()->year)->where('month', now()->month)->first();
            if (!$period) {
                throw new \Exception('Periode akuntansi bulan ini belum dibuat. Buat periode terlebih dahulu.');
            }

            $kasAccount = Account::where('code', '1.1.01.01.01')->first();
            if (!$kasAccount) {
                throw new \Exception('Akun Kas/Bank (1.1.01.01.01) tidak ditemukan.');
            }

            $entry = JournalEntry::create([
                'company_id' => auth()->user()->current_company_id,
                'accounting_period_id' => $period->id,
                'entry_date' => now(),
                'reference_no' => 'LOAN-' . $loan->id . '-' . $schedule->installment_no,
                'description' => 'Bayar cicilan ' . $loan->name . ' ke-' . $schedule->installment_no,
                'status' => 'posted',
                'created_by' => auth()->id(),
                'posted_at' => now(),
            ]);

            $entry->lines()->createMany([
                ['account_id' => $loan->liability_account_id, 'debit' => $schedule->principal_amount, 'credit' => 0, 'line_order' => 1],
                ['account_id' => $loan->interest_expense_account_id, 'debit' => $schedule->interest_amount, 'credit' => 0, 'line_order' => 2],
                ['account_id' => $kasAccount->id, 'debit' => 0, 'credit' => $schedule->total_amount, 'line_order' => 3],
            ]);

            $schedule->update(['status' => 'paid', 'paid_date' => now(), 'journal_entry_id' => $entry->id]);
        });

        return redirect()->route('loans.show', $loan)->with('success', 'Cicilan berhasil dibayar.');
    }
}
