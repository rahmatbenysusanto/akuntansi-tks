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
use Illuminate\Validation\Rule;

class CashAdvanceController extends Controller
{
    public function index() {
        $advances = CashAdvance::with(['employee', 'settlements'])->latest()->paginate(20);
        return view('cash-advances.index', compact('advances'));
    }

    public function settle(Request $request, CashAdvance $cashAdvance)
    {
        if ($cashAdvance->status === 'settled') {
            return back()->with('error', 'Kasbon ini sudah lunas.');
        }

        $settled = (float) $cashAdvance->settlements()->sum('amount');
        $remaining = (float) $cashAdvance->amount - $settled;

        $validated = $request->validate([
            'settlement_date' => 'required|date',
            'amount'          => "required|numeric|min:1|max:{$remaining}",
            'method'          => 'required|in:potong_gaji,kembali_tunai',
        ], [
            'amount.max' => "Jumlah pelunasan tidak boleh melebihi sisa kasbon (" . number_format($remaining, 0, ',', '.') . ").",
        ]);

        DB::transaction(function () use ($validated, $cashAdvance) {
            $companyId = auth()->user()->current_company_id;

            $period = AccountingPeriod::where('year', now()->year)
                ->where('month', now()->month)->first();
            if (!$period) {
                throw new \Exception('Periode akuntansi bulan ini belum dibuat.');
            }

            // Akun kredit: Kas jika kembali tunai, Beban Gaji jika potong gaji
            if ($validated['method'] === 'kembali_tunai') {
                $creditAccount = Account::where('code', '1.1.01.01.01')->first();
                if (!$creditAccount) throw new \Exception('Akun Kas (1.1.01.01.01) tidak ditemukan.');
            } else {
                $creditAccount = Account::where('name', 'LIKE', '%BEBAN GAJI%')
                    ->orWhere('name', 'LIKE', '%BIAYA GAJI%')
                    ->where('is_header', false)->first();
                if (!$creditAccount) {
                    $creditAccount = Account::where('code', '1.1.01.01.01')->first();
                }
            }

            // Jurnal pelunasan: Debit akun Uang Muka, Kredit Kas/Beban Gaji
            $entry = JournalEntry::create([
                'company_id'           => $companyId,
                'accounting_period_id' => $period->id,
                'entry_date'           => $validated['settlement_date'],
                'reference_no'         => 'STL-' . $cashAdvance->advance_no,
                'description'          => 'Pelunasan Kasbon: ' . ($cashAdvance->employee->name ?? '-'),
                'status'               => 'posted',
                'created_by'           => auth()->id(),
                'posted_at'            => now(),
            ]);

            $entry->lines()->createMany([
                ['account_id' => $cashAdvance->account_id,   'debit' => 0,                       'credit' => $validated['amount'], 'line_order' => 1],
                ['account_id' => $creditAccount->id,          'debit' => $validated['amount'],    'credit' => 0,                    'line_order' => 2],
            ]);

            // Simpan settlement record
            CashAdvanceSettlement::create([
                'cash_advance_id'  => $cashAdvance->id,
                'settlement_date'  => $validated['settlement_date'],
                'amount'           => $validated['amount'],
                'method'           => $validated['method'],
                'journal_entry_id' => $entry->id,
            ]);

            // Update status kasbon
            $totalSettled = (float) $cashAdvance->settlements()->sum('amount') + (float) $validated['amount'];
            $newStatus = $totalSettled >= (float) $cashAdvance->amount ? 'settled' : 'partial';
            $cashAdvance->update(['status' => $newStatus]);
        });

        return back()->with('success', 'Pelunasan kasbon berhasil dicatat dan jurnal otomatis terbuat.');
    }
    public function create() {
        $employees = Employee::where('is_active', true)->orderBy('name')->get();
        $accounts = Account::where('code', 'LIKE', '1.1.50.%')->orWhere('name', 'LIKE', '%UANG MUKA%')->where('is_header', false)->orderBy('code')->get();
        return view('cash-advances.form', compact('employees', 'accounts'));
    }
    public function store(Request $r)
    {
        $companyId = auth()->user()->current_company_id;

        $validated = $r->validate([
            'employee_id' => [
                'required',
                Rule::exists('employees', 'id'),
            ],
            'advance_no' => 'required|string|max:50',
            'advance_date' => 'required|date',
            'amount' => 'required|numeric|min:0',
            'reason' => 'required|string|max:255',
            'account_id' => [
                'required',
                Rule::exists('accounts', 'id')->where('company_id', $companyId),
            ],
            'settlement_method' => 'required|in:potong_gaji,kembali_tunai,campuran',
        ]);

        DB::transaction(function () use ($validated, $companyId) {
            $period = AccountingPeriod::where('year', now()->year)->where('month', now()->month)->first();
            if (!$period) {
                throw new \Exception('Periode akuntansi bulan ini belum dibuat. Buat periode terlebih dahulu.');
            }

            $kasAccount = Account::where('code', '1.1.01.01.01')->first();
            if (!$kasAccount) {
                throw new \Exception('Akun Kas/Bank (1.1.01.01.01) tidak ditemukan.');
            }

            $advance = CashAdvance::create($validated + ['status' => 'outstanding']);

            $entry = JournalEntry::create([
                'company_id' => $companyId,
                'accounting_period_id' => $period->id,
                'entry_date' => $validated['advance_date'],
                'reference_no' => $validated['advance_no'],
                'description' => 'Kasbon: ' . ($advance->employee->name ?? '-') . ' - ' . $validated['reason'],
                'status' => 'posted',
                'created_by' => auth()->id(),
                'posted_at' => now(),
            ]);

            $entry->lines()->createMany([
                ['account_id' => $validated['account_id'], 'debit' => $validated['amount'], 'credit' => 0, 'line_order' => 1],
                ['account_id' => $kasAccount->id, 'debit' => 0, 'credit' => $validated['amount'], 'line_order' => 2],
            ]);

            $advance->update(['journal_entry_id' => $entry->id]);
        });

        return redirect()->route('cash-advances.index')->with('success', 'Kasbon berhasil dibuat.');
    }
}
