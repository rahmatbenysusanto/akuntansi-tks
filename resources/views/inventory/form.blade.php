<x-app-layout><x-slot name="header">{{ isset($item) ? 'Edit Item' : 'Tambah Item' }}</x-slot>
<div class="max-w-2xl bg-white rounded-xl border border-slate-200 p-6 shadow-sm">
    <form method="POST" data-confirm="Yakin ingin menyimpan item ini?">@csrf @if(isset($item)) @method('PUT') @endif
        <div class="grid grid-cols-2 gap-4">
            <div><label class="block text-sm font-medium text-slate-700 mb-1.5">SKU</label><input type="text" name="sku" value="{{ old('sku', $item->sku ?? '') }}" class="w-full rounded-lg input-modern text-sm" required></div>
            <div><label class="block text-sm font-medium text-slate-700 mb-1.5">Nama</label><input type="text" name="name" value="{{ old('name', $item->name ?? '') }}" class="w-full rounded-lg input-modern text-sm" required></div>
            <div><label class="block text-sm font-medium text-slate-700 mb-1.5">Unit</label><input type="text" name="unit" value="{{ old('unit', $item->unit ?? 'pcs') }}" class="w-full rounded-lg input-modern text-sm"></div>
            <div><label class="block text-sm font-medium text-slate-700 mb-1.5">Kategori</label><input type="text" name="category" value="{{ old('category', $item->category ?? '') }}" class="w-full rounded-lg input-modern text-sm"></div>
            <div><label class="block text-sm font-medium text-slate-700 mb-1.5">Costing Method</label>
                <select name="costing_method" class="w-full rounded-lg input-modern text-sm"><option value="average">Average</option><option value="fifo">FIFO</option></select></div>
            <div><label class="block text-sm font-medium text-slate-700 mb-1.5">Min Stock</label><input type="number" name="min_stock" value="{{ old('min_stock', $item->min_stock ?? 0) }}" class="w-full rounded-lg input-modern text-sm"></div>
        </div>
        <div class="grid grid-cols-3 gap-4 mt-4">
            <div><label class="block text-sm font-medium text-slate-700 mb-1.5">Akun Persediaan</label>
                <select name="inventory_account_id" class="w-full rounded-lg input-modern text-sm account-select" required>@foreach($accounts as $a)<option value="{{ $a->id }}">{{ $a->code }} {{ $a->name }}</option>@endforeach</select></div>
            <div><label class="block text-sm font-medium text-slate-700 mb-1.5">Akun COGS</label>
                <select name="cogs_account_id" class="w-full rounded-lg input-modern text-sm account-select" required>@foreach($accounts as $a)<option value="{{ $a->id }}">{{ $a->code }} {{ $a->name }}</option>@endforeach</select></div>
            <div><label class="block text-sm font-medium text-slate-700 mb-1.5">Akun Sales</label>
                <select name="sales_account_id" class="w-full rounded-lg input-modern text-sm account-select" required>@foreach($accounts as $a)<option value="{{ $a->id }}">{{ $a->code }} {{ $a->name }}</option>@endforeach</select></div>
        </div>
        <button type="submit" class="mt-6 px-5 py-2.5 rounded-lg text-white text-sm font-semibold btn-primary">Simpan</button>
    </form>
</div>
</x-app-layout>
