<?php

namespace App\Http\Controllers;

use App\Models\Account;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AccountController extends Controller
{
    public function index()
    {
        $accounts = Account::orderBy('code')->get();
        $tree = $this->buildTree($accounts);
        return view('accounts.index', compact('accounts', 'tree'));
    }

    public function create()
    {
        $parentAccounts = Account::where('is_header', true)->orWhere('level', '<', 5)->orderBy('code')->get();
        return view('accounts.form', compact('parentAccounts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:20|unique:accounts,code',
            'name' => 'required|string|max:255',
            'parent_code' => 'nullable|string|max:20|exists:accounts,code',
            'level' => 'required|integer|min:1|max:5',
            'category' => 'required|in:aktiva,kewajiban,modal,pendapatan,hpp,biaya_operasional,pendapatan_biaya_lain,biaya_bunga,pajak_penghasilan',
            'normal_balance' => 'required|in:debit,credit',
            'report_type' => 'required|in:balance_sheet,income_statement',
            'is_header' => 'boolean',
        ]);

        $validated['is_header'] = $request->boolean('is_header', false);

        Account::create($validated);

        return redirect()->route('accounts.index')
            ->with('success', 'Akun berhasil ditambahkan.');
    }

    public function edit(Account $account)
    {
        if ($account->journalEntryLines()->exists()) {
            return redirect()->route('accounts.index')
                ->with('error', 'Akun yang sudah dipakai transaksi tidak bisa diedit. Nonaktifkan saja.');
        }

        $parentAccounts = Account::where('is_header', true)
            ->where('code', '!=', $account->code)
            ->orderBy('code')
            ->get();

        return view('accounts.form', compact('account', 'parentAccounts'));
    }

    public function update(Request $request, Account $account)
    {
        if ($account->journalEntryLines()->exists()) {
            return redirect()->route('accounts.index')
                ->with('error', 'Akun yang sudah dipakai transaksi tidak bisa diedit.');
        }

        $validated = $request->validate([
            'code' => 'required|string|max:20|unique:accounts,code,' . $account->id,
            'name' => 'required|string|max:255',
            'parent_code' => 'nullable|string|max:20|exists:accounts,code',
            'level' => 'required|integer|min:1|max:5',
            'category' => 'required|in:aktiva,kewajiban,modal,pendapatan,hpp,biaya_operasional,pendapatan_biaya_lain,biaya_bunga,pajak_penghasilan',
            'normal_balance' => 'required|in:debit,credit',
            'report_type' => 'required|in:balance_sheet,income_statement',
            'is_header' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $validated['is_header'] = $request->boolean('is_header', false);
        $validated['is_active'] = $request->boolean('is_active', true);

        $account->update($validated);

        return redirect()->route('accounts.index')
            ->with('success', 'Akun berhasil diperbarui.');
    }

    public function destroy(Account $account)
    {
        if ($account->journalEntryLines()->exists() || $account->openingBalances()->exists()) {
            $account->update(['is_active' => false]);
            return redirect()->route('accounts.index')
                ->with('success', 'Akun dinonaktifkan karena sudah memiliki transaksi.');
        }

        $account->delete();
        return redirect()->route('accounts.index')
            ->with('success', 'Akun berhasil dihapus.');
    }

    private function buildTree($accounts, $parentCode = null)
    {
        $branch = [];
        foreach ($accounts as $account) {
            $accountParentCode = $account->parent_code ?: null;
            $cmpParentCode = $parentCode ?: null;
            if ($accountParentCode === $cmpParentCode) {
                $children = $this->buildTree($accounts, $account->code);
                $branch[] = [
                    'account' => $account,
                    'children' => $children,
                    'has_children' => count($children) > 0,
                ];
            }
        }
        return $branch;
    }
}
