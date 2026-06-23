<x-app-layout><x-slot name="header">Fasilitas Cicilan Baru</x-slot>
<div class="max-w-2xl bg-white rounded-xl border border-slate-200 p-6 shadow-sm">
    <form method="POST" data-confirm="Yakin ingin menyimpan fasilitas cicilan ini?">@csrf
        <div class="grid grid-cols-2 gap-4">
            <div><label class="block text-sm font-medium text-slate-700 mb-1.5">Nama Fasilitas</label><input type="text" name="name" class="w-full rounded-lg input-modern text-sm" required></div>
            <div><label class="block text-sm font-medium text-slate-700 mb-1.5">Tipe</label>
                <select name="type" class="w-full rounded-lg input-modern text-sm"><option value="bank_loan">Bank Loan</option><option value="leasing">Leasing</option><option value="kpr">KPR</option><option value="kredit_investasi">Kredit Investasi</option></select></div>
            <div><label class="block text-sm font-medium text-slate-700 mb-1.5">Pokok Pinjaman</label><input type="number" step="0.01" name="principal_amount" class="w-full rounded-lg input-modern text-sm" required></div>
            <div><label class="block text-sm font-medium text-slate-700 mb-1.5">Bunga per Tahun (%)</label><input type="number" step="0.01" name="interest_rate_per_year" class="w-full rounded-lg input-modern text-sm" required></div>
            <div><label class="block text-sm font-medium text-slate-700 mb-1.5">Tenor (bulan)</label><input type="number" name="tenor_months" class="w-full rounded-lg input-modern text-sm" required></div>
            <div><label class="block text-sm font-medium text-slate-700 mb-1.5">Tanggal Mulai</label><input type="date" name="start_date" value="{{ now()->format('Y-m-d') }}" class="w-full rounded-lg input-modern text-sm" required></div>
            <div><label class="block text-sm font-medium text-slate-700 mb-1.5">Counterparty</label><input type="text" name="counterparty" class="w-full rounded-lg input-modern text-sm"></div>
        </div>
        <div class="grid grid-cols-2 gap-4 mt-4">
            <div><label class="block text-sm font-medium text-slate-700 mb-1.5">Akun Hutang</label>
                <select name="liability_account_id" class="w-full rounded-lg input-modern text-sm account-select" required>@foreach($accounts as $a)<option value="{{ $a->id }}">{{ $a->code }} - {{ $a->name }}</option>@endforeach</select></div>
            <div><label class="block text-sm font-medium text-slate-700 mb-1.5">Akun Bunga</label>
                <select name="interest_expense_account_id" class="w-full rounded-lg input-modern text-sm account-select" required>@foreach($accounts as $a)<option value="{{ $a->id }}">{{ $a->code }} - {{ $a->name }}</option>@endforeach</select></div>
        </div>
        <button type="submit" class="mt-6 px-5 py-2.5 rounded-lg text-white text-sm font-semibold btn-primary">Simpan & Generate Jadwal</button>
    </form>
</div>
</x-app-layout>
