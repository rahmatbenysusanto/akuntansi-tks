<x-app-layout>
<x-slot name="header">Setup Gaji — {{ $employee->name }}</x-slot>
<div class="max-w-2xl bg-white rounded-xl border border-slate-200 p-6 shadow-sm">
    <div class="mb-5 pb-4 border-b border-slate-100">
        <p class="text-sm text-slate-500">NIK: <span class="font-medium text-slate-700">{{ $employee->employee_no }}</span></p>
        <p class="text-sm text-slate-500">Departemen: <span class="font-medium text-slate-700">{{ $employee->department ?? '-' }}</span></p>
    </div>

    @if($errors->any())
        <div class="mb-4 px-4 py-3 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm">
            <ul class="list-disc list-inside">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    <form action="{{ route('employee-salaries.update', $employee) }}" method="POST">
        @csrf @method('PUT')

        <div class="grid grid-cols-2 gap-4">
            <div class="col-span-2">
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Gaji Pokok <span class="text-red-500">*</span></label>
                <input type="number" name="base_salary" step="1000"
                    value="{{ old('base_salary', $salary->base_salary ?? 0) }}"
                    class="w-full rounded-lg input-modern text-sm" required>
            </div>

            <div class="col-span-2"><p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mt-2">Tunjangan Tetap</p></div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Tunjangan Transport</label>
                <input type="number" name="allowance_transport" step="1000"
                    value="{{ old('allowance_transport', $salary->allowance_transport ?? 0) }}"
                    class="w-full rounded-lg input-modern text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Tunjangan Makan</label>
                <input type="number" name="allowance_meal" step="1000"
                    value="{{ old('allowance_meal', $salary->allowance_meal ?? 0) }}"
                    class="w-full rounded-lg input-modern text-sm">
            </div>
            <div class="col-span-2">
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Tunjangan Lainnya</label>
                <input type="number" name="allowance_other" step="1000"
                    value="{{ old('allowance_other', $salary->allowance_other ?? 0) }}"
                    class="w-full rounded-lg input-modern text-sm">
            </div>

            <div class="col-span-2"><p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mt-2">% Potongan BPJS (Bagian Karyawan)</p></div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">BPJS Kesehatan (%)</label>
                <input type="number" name="bpjs_kesehatan_pct" step="0.01" min="0" max="100"
                    value="{{ old('bpjs_kesehatan_pct', $salary->bpjs_kesehatan_pct ?? 1) }}"
                    class="w-full rounded-lg input-modern text-sm">
                <p class="mt-1 text-xs text-slate-400">Default: 1% dari gaji pokok</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">BPJS Ketenagakerjaan (%)</label>
                <input type="number" name="bpjs_tk_pct" step="0.01" min="0" max="100"
                    value="{{ old('bpjs_tk_pct', $salary->bpjs_tk_pct ?? 2) }}"
                    class="w-full rounded-lg input-modern text-sm">
                <p class="mt-1 text-xs text-slate-400">Default: 2% dari gaji pokok</p>
            </div>
        </div>

        <div class="flex gap-3 mt-6">
            <button type="submit" class="px-5 py-2.5 rounded-lg text-white text-sm font-semibold btn-primary">Simpan</button>
            <a href="{{ route('employee-salaries.index') }}"
                class="px-5 py-2.5 rounded-lg border border-slate-200 text-slate-700 text-sm font-semibold hover:bg-slate-50 transition">Batal</a>
        </div>
    </form>
</div>
</x-app-layout>
