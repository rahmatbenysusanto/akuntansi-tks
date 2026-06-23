<x-app-layout>
<x-slot name="header">Purchase Invoice Baru</x-slot>
<div class="max-w-4xl bg-white rounded-xl border border-slate-200 p-6 shadow-sm">
    <form method="POST" action="{{ route('purchases.store') }}">@csrf
        <div class="grid grid-cols-3 gap-4">
            <div><label class="block text-sm font-medium text-slate-700 mb-1.5">Vendor</label>
                <select name="vendor_id" class="w-full rounded-lg input-modern text-sm" required>@foreach($vendors as $v)<option value="{{ $v->id }}">{{ $v->name }}</option>@endforeach</select></div>
            <div><label class="block text-sm font-medium text-slate-700 mb-1.5">Invoice#</label><input type="text" name="invoice_no" class="w-full rounded-lg input-modern text-sm" required></div>
            <div><label class="block text-sm font-medium text-slate-700 mb-1.5">Tanggal</label><input type="date" name="invoice_date" value="{{ now()->format('Y-m-d') }}" class="w-full rounded-lg input-modern text-sm" required></div>
            <div><label class="block text-sm font-medium text-slate-700 mb-1.5">Jatuh Tempo</label><input type="date" name="due_date" value="{{ now()->addDays(30)->format('Y-m-d') }}" class="w-full rounded-lg input-modern text-sm" required></div>
        </div>
        <div class="mt-6">
            <label class="block text-sm font-medium text-slate-700 mb-2">Line Items</label>
            <div id="lines-container">
                <div class="grid grid-cols-12 gap-2 mb-2 text-xs text-slate-500"><div class="col-span-5">Deskripsi</div><div class="col-span-2">Qty</div><div class="col-span-2">Harga</div><div class="col-span-1">Diskon</div><div class="col-span-2">Pajak %</div></div>
                <div class="line-item grid grid-cols-12 gap-2 mb-2">
                    <input type="text" name="lines[0][description]" class="col-span-5 rounded-lg input-modern text-sm" required>
                    <input type="number" name="lines[0][qty]" value="1" class="col-span-2 rounded-lg input-modern text-sm">
                    <input type="number" step="0.01" name="lines[0][unit_price]" class="col-span-2 rounded-lg input-modern text-sm">
                    <input type="number" step="0.01" name="lines[0][discount]" value="0" class="col-span-1 rounded-lg input-modern text-sm">
                    <input type="number" step="0.01" name="lines[0][tax_rate]" value="11" class="col-span-2 rounded-lg input-modern text-sm">
                </div>
            </div>
            <button type="button" onclick="addLine()" class="text-sm text-indigo-600 hover:underline">+ Tambah Baris</button>
        </div>
        <div class="flex gap-3 mt-6">
            <button type="submit" name="action" value="draft" class="px-5 py-2.5 rounded-lg text-white text-sm font-semibold bg-slate-600 hover:bg-slate-700">Simpan Draft</button>
            <button type="button" onclick="confirmAndSubmit(this, 'Posting invoice pembelian ini?')" data-action-value="post" class="px-5 py-2.5 rounded-lg text-white text-sm font-semibold btn-primary">Posting</button>
            <a href="{{ route('purchases.index') }}" class="px-5 py-2.5 rounded-lg border border-slate-200 text-slate-700 text-sm font-semibold hover:bg-slate-50 transition">Batal</a>
        </div>
    </form>
</div>
<script>let lineIdx=1;function addLine(){const html=`<div class="line-item grid grid-cols-12 gap-2 mb-2">
    <input type="text" name="lines[${lineIdx}][description]" class="col-span-5 rounded-lg input-modern text-sm" required>
    <input type="number" name="lines[${lineIdx}][qty]" value="1" class="col-span-2 rounded-lg input-modern text-sm">
    <input type="number" step="0.01" name="lines[${lineIdx}][unit_price]" class="col-span-2 rounded-lg input-modern text-sm">
    <input type="number" step="0.01" name="lines[${lineIdx}][discount]" value="0" class="col-span-1 rounded-lg input-modern text-sm">
    <input type="number" step="0.01" name="lines[${lineIdx}][tax_rate]" value="11" class="col-span-2 rounded-lg input-modern text-sm">
</div>`;document.getElementById('lines-container').insertAdjacentHTML('beforeend',html);lineIdx++}</script>
</x-app-layout>
