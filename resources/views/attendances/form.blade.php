<x-app-layout>
<x-slot name="header">{{ isset($attendance) ? 'Edit Absensi' : 'Tambah Absensi' }}</x-slot>

<div class="max-w-2xl bg-white rounded-xl border border-slate-200 p-6 shadow-sm">

    @if($errors->any())
        <div class="mb-4 px-4 py-3 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm">
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form
        action="{{ isset($attendance) ? route('attendances.update', $attendance) : route('attendances.store') }}"
        method="POST"
        data-confirm="Yakin ingin menyimpan data absensi ini?">
        @csrf
        @if(isset($attendance)) @method('PUT') @endif

        <div class="grid grid-cols-2 gap-4">

            {{-- Karyawan --}}
            <div class="col-span-2">
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Karyawan <span class="text-red-500">*</span></label>
                <select name="employee_id" class="w-full rounded-lg input-modern text-sm account-select" required>
                    <option value="">-- Pilih Karyawan --</option>
                    @foreach($employees as $e)
                        <option value="{{ $e->id }}"
                            {{ old('employee_id', $attendance->employee_id ?? '') == $e->id ? 'selected' : '' }}>
                            {{ $e->name }} ({{ $e->employee_no }}){{ $e->department ? ' — ' . $e->department : '' }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Tanggal --}}
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Tanggal <span class="text-red-500">*</span></label>
                <input
                    type="date"
                    name="date"
                    value="{{ old('date', isset($attendance) ? $attendance->date->format('Y-m-d') : now()->format('Y-m-d')) }}"
                    class="w-full rounded-lg input-modern text-sm"
                    required>
            </div>

            {{-- Status --}}
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Status Kehadiran <span class="text-red-500">*</span></label>
                <select name="status" class="w-full rounded-lg input-modern text-sm" required>
                    @foreach(['hadir' => 'Hadir', 'sakit' => 'Sakit', 'izin' => 'Izin', 'cuti' => 'Cuti', 'dinas_luar' => 'Dinas Luar', 'alpha' => 'Alpha'] as $val => $label)
                        <option value="{{ $val }}"
                            {{ old('status', $attendance->status ?? 'hadir') == $val ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Jam Masuk --}}
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Jam Masuk</label>
                <input
                    type="time"
                    name="clock_in"
                    value="{{ old('clock_in', $attendance->clock_in ?? '') }}"
                    class="w-full rounded-lg input-modern text-sm">
                <p class="mt-1 text-xs text-slate-400">Kosongkan jika tidak ada jam masuk (mis. sakit/cuti).</p>
            </div>

            {{-- Jam Keluar --}}
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Jam Keluar</label>
                <input
                    type="time"
                    name="clock_out"
                    value="{{ old('clock_out', $attendance->clock_out ?? '') }}"
                    class="w-full rounded-lg input-modern text-sm">
                <p class="mt-1 text-xs text-slate-400">Kosongkan jika karyawan belum/tidak clock out.</p>
            </div>

            {{-- Keterangan --}}
            <div class="col-span-2">
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Keterangan</label>
                <textarea
                    name="notes"
                    rows="2"
                    class="w-full rounded-lg input-modern text-sm"
                    placeholder="Catatan tambahan (opsional)...">{{ old('notes', $attendance->notes ?? '') }}</textarea>
            </div>
        </div>

        <div class="flex gap-3 mt-6">
            <button type="submit" class="px-5 py-2.5 rounded-lg text-white text-sm font-semibold btn-primary">
                Simpan
            </button>
            <a href="{{ route('attendances.index') }}" class="px-5 py-2.5 rounded-lg border border-slate-200 text-slate-700 text-sm font-semibold hover:bg-slate-50 transition">
                Batal
            </a>
            @if(isset($attendance))
                <form method="POST" action="{{ route('attendances.destroy', $attendance) }}" class="ml-auto"
                    onsubmit="return confirm('Yakin ingin menghapus data absensi ini?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="px-5 py-2.5 rounded-lg border border-red-200 text-red-600 text-sm font-semibold hover:bg-red-50 transition">
                        Hapus
                    </button>
                </form>
            @endif
        </div>
    </form>
</div>
</x-app-layout>
