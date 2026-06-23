<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\AccountingPeriod;
use App\Models\CashAdvance;
use App\Models\CashAdvanceSettlement;
use App\Models\Employee;
use App\Models\JournalEntry;
use App\Models\Payroll;
use App\Models\PayrollLine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PayrollController extends Controller
{
    public function index()
    {
        $payrolls = Payroll::with('accountingPeriod')
            ->latest()->paginate(15);
        return view('payroll.index', compact('payrolls'));
    }

    public function create()
    {
        $periods   = AccountingPeriod::where('status', 'open')->orderByDesc('year')->orderByDesc('month')->get();
        $employees = Employee::with('salary')->where('is_active', true)->orderBy('name')->get();
        $advances  = CashAdvance::with('employee')
            ->whereIn('status', ['outstanding', 'partial'])->get();

        // Akun untuk jurnal payroll
        $expenseAccounts = Account::where('is_header', false)
            ->where('is_active', true)
            ->where(fn($q) => $q->where('name', 'LIKE', '%GAJI%')->orWhere('name', 'LIKE', '%UPAH%'))
            ->orderBy('code')->get();

        $liabilityAccounts = Account::where('is_header', false)
            ->where('is_active', true)
            ->where(fn($q) => $q
                ->where('name', 'LIKE', '%HUTANG GAJI%')
                ->orWhere('name', 'LIKE', '%GAJI YANG MASIH HARUS%')
                ->orWhere('code', 'LIKE', '2.%'))
            ->orderBy('code')->get();

        return view('payroll.form', compact(
            'periods', 'employees', 'advances',
            'expenseAccounts', 'liabilityAccounts'
        ));
    }

    public function store(Request $request)
    {
        $companyId = auth()->user()->current_company_id;

        $request->validate([
            'accounting_period_id'     => 'required|exists:accounting_periods,id',
            'reference_no'             => 'required|string|max:50',
            'salary_expense_account_id' => 'required|exists:accounts,id',
            'salary_payable_account_id' => 'required|exists:accounts,id',
            'lines'                    => 'required|array|min:1',
            'lines.*.employee_id'      => 'required|exists:employees,id',
            'lines.*.base_salary'      => 'required|numeric|min:0',
        ]);

        $postNow = $request->has('post_now');

        DB::transaction(function () use ($request, $companyId, $postNow) {
            $payroll = Payroll::create([
                'company_id'                => $companyId,
                'accounting_period_id'      => $request->accounting_period_id,
                'reference_no'              => $request->reference_no,
                'description'               => $request->description,
                'status'                    => 'draft',
                'created_by'                => auth()->id(),
                'salary_expense_account_id' => $request->salary_expense_account_id,
                'salary_payable_account_id' => $request->salary_payable_account_id,
                'bpjs_payable_account_id'   => $request->bpjs_payable_account_id,
                'pph21_payable_account_id'  => $request->pph21_payable_account_id,
            ]);

            foreach ($request->lines as $line) {
                $gross = (float)($line['base_salary'] ?? 0)
                       + (float)($line['allowance_transport'] ?? 0)
                       + (float)($line['allowance_meal'] ?? 0)
                       + (float)($line['allowance_other'] ?? 0)
                       + (float)($line['overtime'] ?? 0);

                $totalDeduction = (float)($line['bpjs_kesehatan'] ?? 0)
                                + (float)($line['bpjs_tk'] ?? 0)
                                + (float)($line['pph21'] ?? 0)
                                + (float)($line['kasbon_deduction'] ?? 0);

                $payroll->lines()->create([
                    'employee_id'         => $line['employee_id'],
                    'base_salary'         => $line['base_salary'] ?? 0,
                    'allowance_transport' => $line['allowance_transport'] ?? 0,
                    'allowance_meal'      => $line['allowance_meal'] ?? 0,
                    'allowance_other'     => $line['allowance_other'] ?? 0,
                    'overtime'            => $line['overtime'] ?? 0,
                    'gross_salary'        => $gross,
                    'bpjs_kesehatan'      => $line['bpjs_kesehatan'] ?? 0,
                    'bpjs_tk'             => $line['bpjs_tk'] ?? 0,
                    'pph21'               => $line['pph21'] ?? 0,
                    'kasbon_deduction'    => $line['kasbon_deduction'] ?? 0,
                    'cash_advance_id'     => !empty($line['cash_advance_id']) ? $line['cash_advance_id'] : null,
                    'total_deduction'     => $totalDeduction,
                    'net_salary'          => max(0, $gross - $totalDeduction),
                ]);
            }

            if ($postNow) {
                $this->doPost($payroll);
            }
        });

        return redirect()->route('payroll.index')
            ->with('success', $postNow ? 'Payroll berhasil dibuat dan diposting.' : 'Payroll berhasil disimpan sebagai draft.');
    }

    public function show(Payroll $payroll)
    {
        $payroll->load(['accountingPeriod', 'lines.employee', 'lines.cashAdvance', 'journalEntry']);
        return view('payroll.show', compact('payroll'));
    }

    public function edit(Payroll $payroll)
    {
        if ($payroll->isPosted()) {
            return back()->with('error', 'Payroll yang sudah diposting tidak bisa diedit.');
        }

        $payroll->load('lines.employee');
        $periods   = AccountingPeriod::where('status', 'open')->orderByDesc('year')->orderByDesc('month')->get();
        $employees = Employee::with('salary')->where('is_active', true)->orderBy('name')->get();
        $advances  = CashAdvance::with('employee')->whereIn('status', ['outstanding', 'partial'])->get();

        $expenseAccounts = Account::where('is_header', false)->where('is_active', true)
            ->where(fn($q) => $q->where('name', 'LIKE', '%GAJI%')->orWhere('name', 'LIKE', '%UPAH%'))
            ->orderBy('code')->get();

        $liabilityAccounts = Account::where('is_header', false)->where('is_active', true)
            ->where(fn($q) => $q->where('name', 'LIKE', '%HUTANG GAJI%')->orWhere('code', 'LIKE', '2.%'))
            ->orderBy('code')->get();

        return view('payroll.form', compact(
            'payroll', 'periods', 'employees', 'advances',
            'expenseAccounts', 'liabilityAccounts'
        ));
    }

    public function update(Request $request, Payroll $payroll)
    {
        if ($payroll->isPosted()) {
            return back()->with('error', 'Payroll yang sudah diposting tidak bisa diedit.');
        }

        $postNow = $request->has('post_now');

        DB::transaction(function () use ($request, $payroll, $postNow) {
            $payroll->update([
                'accounting_period_id'      => $request->accounting_period_id,
                'reference_no'              => $request->reference_no,
                'description'               => $request->description,
                'salary_expense_account_id' => $request->salary_expense_account_id,
                'salary_payable_account_id' => $request->salary_payable_account_id,
                'bpjs_payable_account_id'   => $request->bpjs_payable_account_id,
                'pph21_payable_account_id'  => $request->pph21_payable_account_id,
            ]);

            $payroll->lines()->delete();

            foreach ($request->lines as $line) {
                $gross = (float)($line['base_salary'] ?? 0)
                       + (float)($line['allowance_transport'] ?? 0)
                       + (float)($line['allowance_meal'] ?? 0)
                       + (float)($line['allowance_other'] ?? 0)
                       + (float)($line['overtime'] ?? 0);

                $totalDeduction = (float)($line['bpjs_kesehatan'] ?? 0)
                                + (float)($line['bpjs_tk'] ?? 0)
                                + (float)($line['pph21'] ?? 0)
                                + (float)($line['kasbon_deduction'] ?? 0);

                $payroll->lines()->create([
                    'employee_id'         => $line['employee_id'],
                    'base_salary'         => $line['base_salary'] ?? 0,
                    'allowance_transport' => $line['allowance_transport'] ?? 0,
                    'allowance_meal'      => $line['allowance_meal'] ?? 0,
                    'allowance_other'     => $line['allowance_other'] ?? 0,
                    'overtime'            => $line['overtime'] ?? 0,
                    'gross_salary'        => $gross,
                    'bpjs_kesehatan'      => $line['bpjs_kesehatan'] ?? 0,
                    'bpjs_tk'             => $line['bpjs_tk'] ?? 0,
                    'pph21'               => $line['pph21'] ?? 0,
                    'kasbon_deduction'    => $line['kasbon_deduction'] ?? 0,
                    'cash_advance_id'     => !empty($line['cash_advance_id']) ? $line['cash_advance_id'] : null,
                    'total_deduction'     => $totalDeduction,
                    'net_salary'          => max(0, $gross - $totalDeduction),
                ]);
            }

            if ($postNow) {
                $this->doPost($payroll->fresh());
            }
        });

        return redirect()->route('payroll.index')
            ->with('success', $postNow ? 'Payroll berhasil diperbarui dan diposting.' : 'Payroll berhasil diperbarui.');
    }

    public function post(Payroll $payroll)
    {
        if ($payroll->isPosted()) {
            return back()->with('error', 'Payroll ini sudah diposting.');
        }

        DB::transaction(fn() => $this->doPost($payroll));

        return back()->with('success', 'Payroll berhasil diposting. Jurnal akuntansi otomatis terbuat.');
    }

    /** Logic posting — buat jurnal + settlement kasbon */
    private function doPost(Payroll $payroll): void
    {
        $payroll->load('lines.employee');
        $companyId = auth()->user()->current_company_id;

        $period = AccountingPeriod::find($payroll->accounting_period_id);
        if (!$period || !$period->isOpen()) {
            throw new \Exception('Periode akuntansi sudah ditutup atau tidak ditemukan.');
        }

        $totalGross     = (float) $payroll->lines->sum('gross_salary');
        $totalNet       = (float) $payroll->lines->sum('net_salary');
        $totalBpjsKes   = (float) $payroll->lines->sum('bpjs_kesehatan');
        $totalBpjsTk    = (float) $payroll->lines->sum('bpjs_tk');
        $totalPph21     = (float) $payroll->lines->sum('pph21');
        $totalKasbon    = (float) $payroll->lines->sum('kasbon_deduction');

        // Buat JournalEntry
        $entry = JournalEntry::create([
            'company_id'           => $companyId,
            'accounting_period_id' => $payroll->accounting_period_id,
            'entry_date'           => now()->startOfMonth(),
            'reference_no'         => $payroll->reference_no,
            'description'          => 'Penggajian ' . ($payroll->accountingPeriod->label ?? ''),
            'status'               => 'posted',
            'created_by'           => auth()->id(),
            'posted_at'            => now(),
        ]);

        $lines = [];
        $order = 1;

        // Debit: Beban Gaji (gross)
        $lines[] = ['account_id' => $payroll->salary_expense_account_id, 'debit' => $totalGross, 'credit' => 0, 'line_order' => $order++];

        // Kredit: Hutang Gaji / Kas (net)
        $lines[] = ['account_id' => $payroll->salary_payable_account_id, 'debit' => 0, 'credit' => $totalNet, 'line_order' => $order++];

        // Kredit: Hutang BPJS
        if (($totalBpjsKes + $totalBpjsTk) > 0 && $payroll->bpjs_payable_account_id) {
            $lines[] = ['account_id' => $payroll->bpjs_payable_account_id, 'debit' => 0, 'credit' => $totalBpjsKes + $totalBpjsTk, 'line_order' => $order++];
        }

        // Kredit: Hutang PPh 21
        if ($totalPph21 > 0 && $payroll->pph21_payable_account_id) {
            $lines[] = ['account_id' => $payroll->pph21_payable_account_id, 'debit' => 0, 'credit' => $totalPph21, 'line_order' => $order++];
        }

        // Kredit: Uang Muka per karyawan yang kena potongan kasbon
        foreach ($payroll->lines as $line) {
            if ($line->kasbon_deduction > 0 && $line->cash_advance_id) {
                $advance = CashAdvance::find($line->cash_advance_id);
                if ($advance) {
                    $lines[] = ['account_id' => $advance->account_id, 'debit' => 0, 'credit' => $line->kasbon_deduction, 'line_order' => $order++];

                    // Buat settlement record
                    CashAdvanceSettlement::create([
                        'cash_advance_id'  => $advance->id,
                        'settlement_date'  => now()->startOfMonth(),
                        'amount'           => $line->kasbon_deduction,
                        'method'           => 'potong_gaji',
                        'journal_entry_id' => $entry->id,
                    ]);

                    $totalSettled = (float) $advance->settlements()->sum('amount');
                    $advance->update([
                        'status' => $totalSettled >= (float) $advance->amount ? 'settled' : 'partial',
                    ]);
                }
            }
        }

        $entry->lines()->createMany($lines);

        $payroll->update([
            'status'           => 'posted',
            'posted_at'        => now(),
            'journal_entry_id' => $entry->id,
        ]);
    }
}
