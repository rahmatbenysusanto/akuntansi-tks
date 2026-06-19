<x-app-layout>
    <x-slot name="header">{{ isset($vendor) ? 'Edit Vendor' : 'Tambah Vendor' }}</x-slot>

    <div class="max-w-2xl bg-white rounded-xl border border-slate-200 p-6 shadow-sm">
        <form action="{{ isset($vendor) ? route('vendors.update', $vendor) : route('vendors.store') }}" method="POST">
            @csrf
            @if(isset($vendor)) @method('PUT') @endif

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Kode Vendor</label>
                    <input type="text" name="code" value="{{ old('code', $vendor->code ?? '') }}" class="w-full rounded-lg input-modern text-sm" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Nama Vendor</label>
                    <input type="text" name="name" value="{{ old('name', $vendor->name ?? '') }}" class="w-full rounded-lg input-modern text-sm" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Telepon</label>
                    <input type="text" name="phone" value="{{ old('phone', $vendor->phone ?? '') }}" class="w-full rounded-lg input-modern text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">NPWP</label>
                    <input type="text" name="npwp" value="{{ old('npwp', $vendor->npwp ?? '') }}" class="w-full rounded-lg input-modern text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Term Pembayaran (hari)</label>
                    <input type="number" name="payment_term_days" value="{{ old('payment_term_days', $vendor->payment_term_days ?? 30) }}" class="w-full rounded-lg input-modern text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Akun Hutang (AP)</label>
                    <select name="ap_account_id" class="w-full rounded-lg input-modern text-sm">
                        <option value="">-- Default --</option>
                        @foreach($accounts as $a)
                            <option value="{{ $a->id }}" {{ old('ap_account_id', $vendor->ap_account_id ?? '') == $a->id ? 'selected' : '' }}>{{ $a->code }} - {{ $a->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="mt-4">
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Alamat</label>
                <textarea name="address" rows="2" class="w-full rounded-lg input-modern text-sm">{{ old('address', $vendor->address ?? '') }}</textarea>
            </div>

            <div class="mt-4 flex items-center gap-2">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $vendor->is_active ?? true) ? 'checked' : '' }} class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                <span class="text-sm text-slate-600">Aktif</span>
            </div>

            <div class="flex gap-3 mt-6">
                <button type="submit" class="px-5 py-2.5 rounded-lg text-white text-sm font-semibold btn-primary">Simpan</button>
                <a href="{{ route('vendors.index') }}" class="px-5 py-2.5 rounded-lg border border-slate-200 text-slate-700 text-sm font-semibold hover:bg-slate-50 transition">Batal</a>
            </div>
        </form>
    </div>
</x-app-layout>
