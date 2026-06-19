<x-app-layout><x-slot name="header">Tambah Aset Tetap</x-slot>
<div class="max-w-2xl bg-white rounded-xl border border-slate-200 p-6 shadow-sm">
    <form method="POST">@csrf
        <div class="grid grid-cols-2 gap-4">
            <div><label class="block text-sm font-medium text-slate-700 mb-1.5">Kode Aset</label><input type="text" name="asset_code" class="w-full rounded-lg input-modern text-sm" required></div>
            <div><label class="block text-sm font-medium text-slate-700 mb-1.5">Nama Aset</label><input type="text" name="name" class="w-full rounded-lg input-modern text-sm" required></div>
            <div><label class="block text-sm font-medium text-slate-700 mb-1.5">Tanggal Perolehan</label><input type="date" name="acquisition_date" class="w-full rounded-lg input-modern text-sm" required></div>
            <div><label class="block text-sm font-medium text-slate-700 mb-1.5">Harga Perolehan</label><input type="number" step="0.01" name="acquisition_cost" class="w-full rounded-lg input-modern text-sm" required></div>
            <div><label class="block text-sm font-medium text-slate-700 mb-1.5">Masa Manfaat (bulan)</label><input type="number" name="useful_life_months" class="w-full rounded-lg input-modern text-sm" required></div>
            <div><label class="block text-sm font-medium text-slate-700 mb-1.5">Nilai Residu</label><input type="number" step="0.01" name="salvage_value" value="0" class="w-full rounded-lg input-modern text-sm"></div>
        </div>
        <div class="grid grid-cols-3 gap-4 mt-4">
            <div><label class="block text-sm font-medium text-slate-700 mb-1.5">Akun Aset</label>
                <select name="account_id" class="w-full rounded-lg input-modern text-sm" required>@foreach($assetAccounts as $a)<option value="{{ $a->id }}">{{ $a->code }} - {{ $a->name }}</option>@endforeach</select></div>
            <div><label class="block text-sm font-medium text-slate-700 mb-1.5">Akun Akum. Penyusutan</label>
                <select name="accumulated_depreciation_account_id" class="w-full rounded-lg input-modern text-sm" required>@foreach($assetAccounts as $a)<option value="{{ $a->id }}">{{ $a->code }} - {{ $a->name }}</option>@endforeach</select></div>
            <div><label class="block text-sm font-medium text-slate-700 mb-1.5">Akun Beban Penyusutan</label>
                <select name="depreciation_expense_account_id" class="w-full rounded-lg input-modern text-sm" required>@foreach($assetAccounts as $a)<option value="{{ $a->id }}">{{ $a->code }} - {{ $a->name }}</option>@endforeach</select></div>
        </div>
        <div class="flex gap-3 mt-6">
            <button type="submit" class="px-5 py-2.5 rounded-lg text-white text-sm font-semibold btn-primary">Simpan</button>
            <a href="{{ route('fixed-assets.index') }}" class="px-5 py-2.5 rounded-lg border border-slate-200 text-slate-700 text-sm font-semibold hover:bg-slate-50 transition">Batal</a>
        </div>
    </form>
</div>
</x-app-layout>
