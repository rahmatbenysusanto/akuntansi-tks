<x-app-layout><x-slot name="header">Kasbon Baru</x-slot>
<div class="max-w-2xl bg-white rounded-xl border border-slate-200 p-6 shadow-sm">
    <form method="POST" data-confirm="Yakin ingin menyimpan kasbon ini? Jurnal akan otomatis terposting.">@csrf
        <div class="grid grid-cols-2 gap-4">
            <div><label class="block text-sm font-medium text-slate-700 mb-1.5">Karyawan</label>
                <select name="employee_id" class="w-full rounded-lg input-modern text-sm" required>@foreach($employees as $e)<option value="{{ $e->id }}">{{ $e->name }} ({{ $e->employee_no }})</option>@endforeach</select></div>
            <div><label class="block text-sm font-medium text-slate-700 mb-1.5">No Kasbon</label><input type="text" name="advance_no" class="w-full rounded-lg input-modern text-sm" required></div>
            <div><label class="block text-sm font-medium text-slate-700 mb-1.5">Tanggal</label><input type="date" name="advance_date" value="{{ now()->format('Y-m-d') }}" class="w-full rounded-lg input-modern text-sm"></div>
            <div><label class="block text-sm font-medium text-slate-700 mb-1.5">Jumlah</label><input type="number" step="0.01" name="amount" class="w-full rounded-lg input-modern text-sm" required></div>
            <div><label class="block text-sm font-medium text-slate-700 mb-1.5">Metode Pelunasan</label>
                <select name="settlement_method" class="w-full rounded-lg input-modern text-sm"><option value="kembali_tunai">Kembali Tunai</option><option value="potong_gaji">Potong Gaji</option><option value="campuran">Campuran</option></select></div>
            <div><label class="block text-sm font-medium text-slate-700 mb-1.5">Akun Uang Muka</label>
                <select name="account_id" class="w-full rounded-lg input-modern text-sm account-select" required>@foreach($accounts as $a)<option value="{{ $a->id }}">{{ $a->code }} - {{ $a->name }}</option>@endforeach</select></div>
        </div>
        <div class="mt-4"><textarea name="reason" rows="2" class="w-full rounded-lg input-modern text-sm" placeholder="Alasan kasbon..."></textarea></div>
        <button type="submit" class="mt-4 px-5 py-2.5 rounded-lg text-white text-sm font-semibold btn-primary">Simpan & Posting Jurnal</button>
    </form>
</div>
</x-app-layout>
