<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Employee;
use App\Models\CashAdvance;
use App\Models\CashAdvanceSettlement;
use App\Models\JournalEntry;
use App\Models\AccountingPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CashAdvanceController extends Controller
{
    public function index() {
        $advances = CashAdvance::with('employee')->latest()->paginate(20);
        return view('cash-advances.index', compact('advances'));
    }
    public function create() {
        $employees = Employee::where('is_active', true)->orderBy('name')->get();
        $accounts = Account::where('code', 'LIKE', '1.1.50.%')->orWhere('name', 'LIKE', '%UANG MUKA%')->where('is_header', false)->orderBy('code')->get();
        return view('cash-advances.form', compact('employees', 'accounts'));
    }
    public function store(Request $r)
    {
        DB::transaction(function () use ($r) {
            $advance = CashAdvance::create($r->all());
            $period = AccountingPeriod::where('year', now()->year)->where('month', now()->month)->first();
            $kasAccount = Account::where('code', '1.1.01.01.01')->first();

            $entry = JournalEntry::create([
                'company_id' => auth()->user()->current_company_id,
                'accounting_period_id' => $period?->id,
                'entry_date' => $r->advance_date,
                'reference_no' => $r->advance_no,
                'description' => 'Kasbon: ' . $advance->employee->name . ' - ' . $r->reason,
                'status' => 'posted',
                'created_by' => auth()->id(),
                'posted_at' => now(),
            ]);
            $entry->lines()->createMany([
                ['account_id' => $r->account_id, 'debit' => $r->amount, 'credit' => 0, 'line_order' => 1],
                ['account_id' => $kasAccount?->id, 'debit' => 0, 'credit' => $r->amount, 'line_order' => 2],
            ]);
            $advance->update(['journal_entry_id' => $entry->id]);
        });
        return redirect()->route('cash-advances.index')->with('success', 'Kasbon created.');
    }
}
