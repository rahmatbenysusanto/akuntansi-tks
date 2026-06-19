<x-app-layout>
    <x-slot name="header">{{ isset($account) ? 'Edit Akun' : 'Tambah Akun' }}</x-slot>
    <div class="max-w-2xl bg-white rounded-xl border border-slate-200 p-6 shadow-sm">
        <form action="{{ isset($account) ? route('accounts.update', $account) : route('accounts.store') }}" method="POST">
            @csrf @if(isset($account)) @method('PUT') @endif
            <div class="grid grid-cols-2 gap-4">
                <div><label class="block text-sm font-medium text-slate-700 mb-1.5">Kode Akun</label><input type="text" name="code" value="{{ old('code', $account->code ?? '') }}" class="w-full rounded-lg input-modern text-sm" required pattern="[0-9.]+" placeholder="1.1.01.01.01"></div>
                <div><label class="block text-sm font-medium text-slate-700 mb-1.5">Level</label><input type="number" name="level" value="{{ old('level', $account->level ?? 1) }}" class="w-full rounded-lg input-modern text-sm" min="1" max="5" required></div>
            </div>
            <div class="mt-4"><label class="block text-sm font-medium text-slate-700 mb-1.5">Nama Akun</label><input type="text" name="name" value="{{ old('name', $account->name ?? '') }}" class="w-full rounded-lg input-modern text-sm" required></div>
            <div class="grid grid-cols-2 gap-4 mt-4">
                <div><label class="block text-sm font-medium text-slate-700 mb-1.5">Induk Akun</label>
                    <select name="parent_code" class="w-full rounded-lg input-modern text-sm">
                        <option value="">-- Tidak Ada --</option>
                        @foreach($parentAccounts as $p)<option value="{{ $p->code }}" {{ old('parent_code', $account->parent_code ?? '') == $p->code ? 'selected' : '' }}>{{ $p->code }} - {{ $p->name }}</option>@endforeach
                    </select></div>
                <div><label class="block text-sm font-medium text-slate-700 mb-1.5">Kategori</label>
                    <select name="category" class="w-full rounded-lg input-modern text-sm" required>
                        @foreach(['aktiva','kewajiban','modal','pendapatan','hpp','biaya_operasional','pendapatan_biaya_lain','biaya_bunga','pajak_penghasilan'] as $cat)
                            <option value="{{ $cat }}" {{ old('category', $account->category ?? '') == $cat ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $cat)) }}</option>
                        @endforeach
                    </select></div>
            </div>
            <div class="grid grid-cols-2 gap-4 mt-4">
                <div><label class="block text-sm font-medium text-slate-700 mb-1.5">Saldo Normal</label>
                    <select name="normal_balance" class="w-full rounded-lg input-modern text-sm" required>
                        <option value="debit" {{ old('normal_balance', $account->normal_balance ?? '') == 'debit' ? 'selected' : '' }}>Debet</option><option value="credit" {{ old('normal_balance', $account->normal_balance ?? '') == 'credit' ? 'selected' : '' }}>Kredit</option>
                    </select></div>
                <div><label class="block text-sm font-medium text-slate-700 mb-1.5">Posisi Laporan</label>
                    <select name="report_type" class="w-full rounded-lg input-modern text-sm" required>
                        <option value="balance_sheet" {{ old('report_type', $account->report_type ?? '') == 'balance_sheet' ? 'selected' : '' }}>Neraca</option>
                        <option value="income_statement" {{ old('report_type', $account->report_type ?? '') == 'income_statement' ? 'selected' : '' }}>Laba Rugi</option>
                    </select></div>
            </div>
            <div class="flex items-center gap-4 mt-4">
                <label class="flex items-center gap-2"><input type="checkbox" name="is_header" value="1" {{ old('is_header', $account->is_header ?? false) ? 'checked' : '' }} class="rounded border-slate-300 text-indigo-600"> <span class="text-sm text-slate-600">Akun Header</span></label>
                @if(isset($account))<label class="flex items-center gap-2"><input type="checkbox" name="is_active" value="1" {{ old('is_active', $account->is_active ?? true) ? 'checked' : '' }} class="rounded border-slate-300 text-indigo-600"> <span class="text-sm text-slate-600">Aktif</span></label>@endif
            </div>
            <div class="flex gap-3 mt-6">
                <button type="submit" class="px-5 py-2.5 rounded-lg text-white text-sm font-semibold btn-primary">{{ isset($account) ? 'Simpan Perubahan' : 'Simpan' }}</button>
                <a href="{{ route('accounts.index') }}" class="px-5 py-2.5 rounded-lg border border-slate-200 text-slate-700 text-sm font-semibold hover:bg-slate-50 transition">Batal</a>
            </div>
        </form>
    </div>
</x-app-layout>
