<x-app-layout>
<x-slot name="header">{{ isset($employee) ? 'Edit Karyawan' : 'Tambah Karyawan' }}</x-slot>
<div class="max-w-2xl bg-white rounded-xl border border-slate-200 p-6 shadow-sm">
    <form action="{{ isset($employee) ? route('employees.update', $employee) : route('employees.store') }}" method="POST" data-confirm="Yakin ingin menyimpan data karyawan ini?">
        @csrf @if(isset($employee)) @method('PUT') @endif
        <div class="grid grid-cols-2 gap-4">
            <div><label class="block text-sm font-medium text-slate-700 mb-1.5">NIK / No. Karyawan</label>
                <input type="text" name="employee_no" value="{{ old('employee_no', $employee->employee_no ?? '') }}" class="w-full rounded-lg input-modern text-sm" required></div>
            <div><label class="block text-sm font-medium text-slate-700 mb-1.5">Nama Lengkap</label>
                <input type="text" name="name" value="{{ old('name', $employee->name ?? '') }}" class="w-full rounded-lg input-modern text-sm" required></div>
            <div><label class="block text-sm font-medium text-slate-700 mb-1.5">Departemen</label>
                <input type="text" name="department" value="{{ old('department', $employee->department ?? '') }}" class="w-full rounded-lg input-modern text-sm"></div>
            <div><label class="block text-sm font-medium text-slate-700 mb-1.5">Jabatan</label>
                <input type="text" name="position" value="{{ old('position', $employee->position ?? '') }}" class="w-full rounded-lg input-modern text-sm"></div>
            <div class="col-span-2"><label class="block text-sm font-medium text-slate-700 mb-1.5">No. Rekening Bank</label>
                <input type="text" name="bank_account_no" value="{{ old('bank_account_no', $employee->bank_account_no ?? '') }}" class="w-full rounded-lg input-modern text-sm" placeholder="Contoh: 1234567890 (BCA)"></div>
            @if(isset($employee))
            <div class="flex items-center gap-2">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $employee->is_active ?? true) ? 'checked' : '' }} class="rounded border-slate-300 text-indigo-600">
                <span class="text-sm text-slate-600">Aktif</span>
            </div>
            @endif
        </div>
        <div class="flex gap-3 mt-6">
            <button type="submit" class="px-5 py-2.5 rounded-lg text-white text-sm font-semibold btn-primary">Simpan</button>
            <a href="{{ route('employees.index') }}" class="px-5 py-2.5 rounded-lg border border-slate-200 text-slate-700 text-sm font-semibold hover:bg-slate-50 transition">Batal</a>
        </div>
    </form>
</div>
</x-app-layout>
