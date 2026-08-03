<x-app-layout>
<x-slot name="header">{{ isset($request) ? 'Edit Request Overtime' : 'Request Overtime Baru' }}</x-slot>
<div class="max-w-3xl bg-white rounded-xl border border-slate-200 p-6 shadow-sm">
    <form method="POST"
        action="{{ isset($request) ? route('overtime-requests.update', $request) : route('overtime-requests.store') }}"
        data-confirm="Yakin ingin menyimpan request overtime ini?">
        @csrf
        @if(isset($request))
            @method('PUT')
        @endif

        {{-- Info Request --}}
        <h3 class="text-sm font-semibold text-slate-800 mb-4 border-b border-slate-100 pb-2">
            📋 Informasi Request
        </h3>
        <div class="grid grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">No. Request</label>
                <input type="text" name="request_no" value="{{ old('request_no', $requestNo ?? ($request->request_no ?? '')) }}"
                    class="w-full rounded-lg input-modern text-sm" required
                    {{ isset($request) ? 'readonly' : '' }}>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Tanggal Request</label>
                <input type="date" name="request_date"
                    value="{{ old('request_date', isset($request) ? $request->request_date->format('Y-m-d') : now()->format('Y-m-d')) }}"
                    class="w-full rounded-lg input-modern text-sm" required>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Tgl. Overtime</label>
                <input type="date" name="overtime_date"
                    value="{{ old('overtime_date', isset($request) ? $request->overtime_date->format('Y-m-d') : now()->format('Y-m-d')) }}"
                    class="w-full rounded-lg input-modern text-sm" required>
            </div>
        </div>

        {{-- Data Client --}}
        <h3 class="text-sm font-semibold text-slate-800 mb-4 border-b border-slate-100 pb-2 mt-6">
            🏢 Data Client
        </h3>
        <div class="space-y-3">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Nama Client <span class="text-red-500">*</span></label>
                <input type="text" name="client_name"
                    value="{{ old('client_name', $request->client_name ?? '') }}"
                    class="w-full rounded-lg input-modern text-sm" required
                    placeholder="Nama perusahaan / instansi client">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Alamat Client</label>
                <textarea name="client_address" rows="2" class="w-full rounded-lg input-modern text-sm"
                    placeholder="Alamat lengkap client">{{ old('client_address', $request->client_address ?? '') }}</textarea>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">No. Telepon Client</label>
                <input type="text" name="client_phone"
                    value="{{ old('client_phone', $request->client_phone ?? '') }}"
                    class="w-full rounded-lg input-modern text-sm"
                    placeholder="Contoh: 021-1234567 / 0812-3456-7890">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Nama PIC</label>
                    <input type="text" name="pic_name"
                        value="{{ old('pic_name', $request->pic_name ?? '') }}"
                        class="w-full rounded-lg input-modern text-sm"
                        placeholder="Nama PIC / kontak person client">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">No. Telepon PIC</label>
                    <input type="text" name="pic_phone"
                        value="{{ old('pic_phone', $request->pic_phone ?? '') }}"
                        class="w-full rounded-lg input-modern text-sm"
                        placeholder="Contoh: 0812-3456-7890">
                </div>
            </div>
        </div>

        {{-- Detail Overtime --}}
        <h3 class="text-sm font-semibold text-slate-800 mb-4 border-b border-slate-100 pb-2 mt-6">
            ⚙️ Detail Kegiatan Overtime
        </h3>
        <div class="space-y-3">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">
                    Jenis Kegiatan <span class="text-red-500">*</span>
                </label>
                <select name="activity_type" id="activity_type" class="w-full rounded-lg input-modern text-sm" required
                    onchange="toggleOtherDescription()">
                    <option value="">-- Pilih Jenis Kegiatan --</option>
                    <option value="staging_perangkat" {{ old('activity_type', $request->activity_type ?? '') === 'staging_perangkat' ? 'selected' : '' }}>
                        Staging Perangkat
                    </option>
                    <option value="lab_testing" {{ old('activity_type', $request->activity_type ?? '') === 'lab_testing' ? 'selected' : '' }}>
                        Lab Testing
                    </option>
                    <option value="software_upgrade" {{ old('activity_type', $request->activity_type ?? '') === 'software_upgrade' ? 'selected' : '' }}>
                        Software Upgrade
                    </option>
                    <option value="other" {{ old('activity_type', $request->activity_type ?? '') === 'other' ? 'selected' : '' }}>
                        Other (Lainnya)
                    </option>
                </select>
            </div>
            <div id="other_description_wrapper" style="{{ old('activity_type', $request->activity_type ?? '') === 'other' ? '' : 'display: none;' }}">
                <label class="block text-sm font-medium text-slate-700 mb-1.5">
                    Deskripsi Jenis Kegiatan (untuk "Other")
                </label>
                <input type="text" name="activity_description"
                    value="{{ old('activity_description', $request->activity_description ?? '') }}"
                    class="w-full rounded-lg input-modern text-sm"
                    placeholder="Tuliskan jenis kegiatan jika memilih Other...">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">
                        Jam Mulai <span class="text-red-500">*</span>
                    </label>
                    <input type="time" name="overtime_start_time"
                        value="{{ old('overtime_start_time', $request->overtime_start_time ?? '18:00') }}"
                        class="w-full rounded-lg input-modern text-sm" required>
                    <p class="text-xs text-slate-400 mt-1">Overtime mulai dari jam 18:00</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Jam Selesai</label>
                    <input type="time" name="overtime_end_time"
                        value="{{ old('overtime_end_time', $request->overtime_end_time ?? '') }}"
                        class="w-full rounded-lg input-modern text-sm">
                    <p class="text-xs text-slate-400 mt-1">Kosongkan jika belum tahu</p>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Deskripsi Kegiatan</label>
                <textarea name="description" rows="4" class="w-full rounded-lg input-modern text-sm"
                    placeholder="Deskripsikan detail kegiatan overtime yang akan dilakukan...">{{ old('description', $request->description ?? '') }}</textarea>
                <p class="text-xs text-slate-400 mt-1">Free text — jelaskan detail pekerjaan yang akan dilakukan saat overtime.</p>
            </div>
        </div>

        {{-- Biaya Overtime --}}
        <h3 class="text-sm font-semibold text-slate-800 mb-4 border-b border-slate-100 pb-2 mt-6">
            💰 Biaya Overtime
        </h3>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Tarif Per Jam (Rp)</label>
                <input type="number" name="hourly_rate"
                    value="{{ old('hourly_rate', $request->hourly_rate ?? 120000) }}"
                    class="w-full rounded-lg input-modern text-sm"
                    placeholder="120.000" min="0" step="1000">
                <p class="text-xs text-slate-400 mt-1">Default Rp 120.000 per jam</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Total Biaya (Rp)</label>
                <input type="number" name="total_cost"
                    value="{{ old('total_cost', $request->total_cost ?? '') }}"
                    class="w-full rounded-lg input-modern text-sm"
                    placeholder="Biaya total (input manual / free text)" min="0" step="1000">
                <p class="text-xs text-slate-400 mt-1">Bisa dihitung manual atau diisi bebas</p>
            </div>
        </div>

        {{-- Tombol --}}
        <div class="flex gap-3 mt-6 pt-4 border-t border-slate-100">
            <button type="submit" class="px-5 py-2.5 rounded-lg text-white text-sm font-semibold btn-primary">
                💾 Simpan Request
            </button>
            <a href="{{ route('overtime-requests.index') }}"
                class="px-5 py-2.5 rounded-lg border border-slate-200 text-slate-700 text-sm font-semibold hover:bg-slate-50 transition">
                Batal
            </a>
        </div>
    </form>
</div>

<script>
function toggleOtherDescription() {
    const val = document.getElementById('activity_type').value;
    document.getElementById('other_description_wrapper').style.display = val === 'other' ? '' : 'none';
}
</script>
</x-app-layout>
