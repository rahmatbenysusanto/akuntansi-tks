<?php

namespace App\Http\Controllers;

use App\Models\AccountingPeriod;
use App\Models\Account;
use App\Models\JournalEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class JournalEntryController extends Controller
{
    public function index(Request $request)
    {
        $query = JournalEntry::with(['accountingPeriod', 'createdBy', 'lines']);

        if ($request->filled('period_id')) {
            $query->where('accounting_period_id', $request->period_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->where('entry_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('entry_date', '<=', $request->date_to);
        }

        $entries = $query->latest()->paginate(20);
        $periods = AccountingPeriod::orderBy('year', 'desc')->orderBy('month', 'desc')->get();

        return view('journal-entries.index', compact('entries', 'periods'));
    }

    public function create()
    {
        $periods = AccountingPeriod::where('status', 'open')->orderBy('year', 'desc')->orderBy('month', 'desc')->get();
        $accounts = Account::where('is_header', false)->where('is_active', true)->orderBy('code')->get();

        if ($periods->isEmpty()) {
            return redirect()->route('journal-entries.index')
                ->with('error', 'Tidak ada periode aktif. Buat periode terlebih dahulu.');
        }

        return view('journal-entries.form', compact('periods', 'accounts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'accounting_period_id' => 'required|exists:accounting_periods,id',
            'entry_date' => 'required|date',
            'reference_no' => 'required|string|max:50',
            'description' => 'required|string',
            'lines' => 'required|array|min:2',
            'lines.*.account_id' => 'required|exists:accounts,id',
            'lines.*.debit' => 'nullable|numeric|min:0',
            'lines.*.credit' => 'nullable|numeric|min:0',
            'status' => 'required|in:draft,posted',
        ]);

        $period = AccountingPeriod::findOrFail($validated['accounting_period_id']);
        if ($period->status === 'closed') {
            return back()->with('error', 'Tidak bisa menambah jurnal ke periode yang sudah ditutup.')->withInput();
        }

        // Validate accounts are not headers
        $accountIds = collect($validated['lines'])->pluck('account_id');
        $headerAccounts = Account::whereIn('id', $accountIds)->where('is_header', true)->count();
        if ($headerAccounts > 0) {
            return back()->with('error', 'Akun header tidak boleh dipilih di jurnal.')->withInput();
        }

        // Calculate totals
        $totalDebit = 0;
        $totalCredit = 0;
        foreach ($validated['lines'] as $line) {
            $totalDebit += $line['debit'] ?? 0;
            $totalCredit += $line['credit'] ?? 0;
        }

        if ($totalDebit != $totalCredit) {
            return back()->with('error', 'Total debit harus sama dengan total kredit.')->withInput();
        }

        if ($totalDebit == 0) {
            return back()->with('error', 'Jurnal tidak boleh kosong.')->withInput();
        }

        DB::transaction(function () use ($validated, $totalDebit) {
            $entry = JournalEntry::create([
                'accounting_period_id' => $validated['accounting_period_id'],
                'entry_date' => $validated['entry_date'],
                'reference_no' => $validated['reference_no'],
                'description' => $validated['description'],
                'status' => $validated['status'],
                'created_by' => auth()->id(),
                'posted_at' => $validated['status'] === 'posted' ? now() : null,
            ]);

            $order = 1;
            foreach ($validated['lines'] as $line) {
                $entry->lines()->create([
                    'account_id' => $line['account_id'],
                    'debit' => $line['debit'] ?? 0,
                    'credit' => $line['credit'] ?? 0,
                    'line_order' => $order++,
                ]);
            }
        });

        return redirect()->route('journal-entries.index')
            ->with('success', 'Jurnal berhasil ' . ($validated['status'] === 'posted' ? 'di-posting.' : 'disimpan sebagai draft.'));
    }

    public function edit(JournalEntry $journalEntry)
    {
        if ($journalEntry->status === 'posted') {
            return redirect()->route('journal-entries.index')
                ->with('error', 'Jurnal yang sudah di-posting tidak bisa diedit.');
        }

        if ($journalEntry->accountingPeriod->status === 'closed') {
            return redirect()->route('journal-entries.index')
                ->with('error', 'Tidak bisa mengedit jurnal di periode yang sudah ditutup.');
        }

        $periods = AccountingPeriod::where('status', 'open')->orderBy('year', 'desc')->orderBy('month', 'desc')->get();
        $accounts = Account::where('is_header', false)->where('is_active', true)->orderBy('code')->get();

        return view('journal-entries.form', compact('journalEntry', 'periods', 'accounts'));
    }

    public function update(Request $request, JournalEntry $journalEntry)
    {
        if ($journalEntry->status === 'posted') {
            return back()->with('error', 'Jurnal yang sudah di-posting tidak bisa diedit.');
        }

        if ($journalEntry->accountingPeriod->status === 'closed') {
            return back()->with('error', 'Tidak bisa mengedit jurnal di periode yang sudah ditutup.');
        }

        $validated = $request->validate([
            'accounting_period_id' => 'required|exists:accounting_periods,id',
            'entry_date' => 'required|date',
            'reference_no' => 'required|string|max:50',
            'description' => 'required|string',
            'lines' => 'required|array|min:2',
            'lines.*.account_id' => 'required|exists:accounts,id',
            'lines.*.debit' => 'nullable|numeric|min:0',
            'lines.*.credit' => 'nullable|numeric|min:0',
            'status' => 'required|in:draft,posted',
        ]);

        // Validate balance
        $totalDebit = 0;
        $totalCredit = 0;
        foreach ($validated['lines'] as $line) {
            $totalDebit += $line['debit'] ?? 0;
            $totalCredit += $line['credit'] ?? 0;
        }

        if ($totalDebit != $totalCredit) {
            return back()->with('error', 'Total debit harus sama dengan total kredit.')->withInput();
        }

        DB::transaction(function () use ($journalEntry, $validated) {
            $journalEntry->update([
                'accounting_period_id' => $validated['accounting_period_id'],
                'entry_date' => $validated['entry_date'],
                'reference_no' => $validated['reference_no'],
                'description' => $validated['description'],
                'status' => $validated['status'],
                'posted_at' => ($validated['status'] === 'posted' && !$journalEntry->posted_at) ? now() : $journalEntry->posted_at,
            ]);

            $journalEntry->lines()->delete();

            $order = 1;
            foreach ($validated['lines'] as $line) {
                $journalEntry->lines()->create([
                    'account_id' => $line['account_id'],
                    'debit' => $line['debit'] ?? 0,
                    'credit' => $line['credit'] ?? 0,
                    'line_order' => $order++,
                ]);
            }
        });

        return redirect()->route('journal-entries.index')
            ->with('success', 'Jurnal berhasil diperbarui.');
    }

    public function destroy(JournalEntry $journalEntry)
    {
        if ($journalEntry->status === 'posted') {
            return back()->with('error', 'Jurnal yang sudah di-posting tidak bisa dihapus.');
        }

        if ($journalEntry->accountingPeriod->status === 'closed') {
            return back()->with('error', 'Tidak bisa menghapus jurnal di periode yang sudah ditutup.');
        }

        $journalEntry->lines()->delete();
        $journalEntry->delete();

        return redirect()->route('journal-entries.index')
            ->with('success', 'Jurnal berhasil dihapus.');
    }

    public function post(JournalEntry $journalEntry)
    {
        try {
            $journalEntry->post();
            return redirect()->route('journal-entries.index')
                ->with('success', 'Jurnal berhasil di-posting.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
