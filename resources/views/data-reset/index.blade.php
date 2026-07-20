<x-app-layout>
    <x-slot name="header">Reset Data</x-slot>

    <div class="max-w-3xl mx-auto space-y-8">
        {{-- PERINGATAN --}}
        <div class="bg-red-50 border-l-4 border-red-400 rounded-xl p-5 shadow-sm">
            <div class="flex items-start gap-3">
                <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center shrink-0 mt-0.5">
                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-base font-semibold text-red-800">Peringatan! Tindakan Ini Tidak Bisa Dibalikkan</h3>
                    <p class="mt-1.5 text-sm text-red-700 leading-relaxed">
                        Fitur ini akan <strong>menghapus seluruh data transaksional</strong> dari sistem, termasuk jurnal,
                        saldo awal, invoice, aset tetap, inventory, payroll, absensi, customer, vendor, dan data lainnya.
                    </p>
                </div>
            </div>
        </div>

        {{-- DATA YANG AKAN DISELAMATKAN --}}
        <div class="bg-emerald-50 border border-emerald-200/60 rounded-xl p-5 shadow-sm">
            <div class="flex items-start gap-3">
                <div class="w-10 h-10 rounded-full bg-emerald-100 flex items-center justify-center shrink-0 mt-0.5">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-base font-semibold text-emerald-800">Data yang TETAP Aman</h3>
                    <ul class="mt-1.5 text-sm text-emerald-700 space-y-1">
                        <li>✓ <strong>User / Akun Login</strong> — semua pengguna tetap ada</li>
                        <li>✓ <strong>Chart of Account (COA)</strong> — kode akun dan hierarkinya utuh</li>
                        <li>✓ <strong>Data Perusahaan</strong> — profil perusahaan tetap tersimpan</li>
                        <li>✓ <strong>Riwayat Aktivitas</strong> — log aktivitas (termasuk reset ini) tetap tercatat</li>
                    </ul>
                </div>
            </div>
        </div>

        {{-- RINCIAN DATA YANG AKAN DIHAPUS --}}
        <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50">
                <h3 class="text-sm font-semibold text-slate-700 flex items-center gap-2">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    Rincian Data yang Akan Dihapus
                    @if($totalRecords > 0)
                        <span class="ml-auto text-xs font-normal text-slate-400">{{ number_format($totalRecords, 0, ',', '.') }} total record</span>
                    @endif
                </h3>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse($recordCounts as $table => $count)
                    <div class="px-6 py-3 flex items-center justify-between text-sm hover:bg-slate-50/50 transition-colors">
                        <span class="text-slate-600 font-mono text-xs">{{ $table }}</span>
                        <span class="ml-4 text-slate-500">{{ number_format($count, 0, ',', '.') }} record</span>
                    </div>
                @empty
                    <div class="px-6 py-8 text-center text-sm text-slate-400">
                        <svg class="w-10 h-10 mx-auto mb-2 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                        </svg>
                        Tidak ada data transaksional yang ditemukan. Sistem sudah bersih.
                    </div>
                @endforelse
            </div>
        </div>

        {{-- FORM KONFIRMASI --}}
        <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-6">
            <h3 class="text-base font-semibold text-slate-800 mb-1">Konfirmasi Reset Data</h3>
            <p class="text-sm text-slate-500 mb-5">
                Untuk melanjutkan, ketik <code class="bg-slate-100 px-2 py-0.5 rounded text-red-600 font-semibold text-xs">reset data</code>
                pada kolom di bawah ini, lalu klik tombol <strong>"Reset Data"</strong>.
            </p>

            <form method="POST" action="{{ route('data-reset.reset') }}" id="resetForm"
                  onsubmit="return validateResetForm(event)">
                @csrf

                <div class="mb-5">
                    <label for="confirmation" class="block text-sm font-medium text-slate-700 mb-1.5">
                        Ketik "reset data" untuk konfirmasi
                    </label>
                    <input type="text"
                           id="confirmation"
                           name="confirmation"
                           class="w-full px-4 py-2.5 rounded-xl border text-sm transition-all @error('confirmation') border-red-300 bg-red-50 ring-2 ring-red-200 @else border-slate-200 input-modern @enderror"
                           placeholder="Ketik 'reset data' di sini..."
                           autocomplete="off"
                           oninput="toggleResetButton()"
                           @error('confirmation') value="{{ old('confirmation') }}" @enderror>
                    @error('confirmation')
                        <p class="mt-1.5 text-xs text-red-600 flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            {{ $message }}
                        </p>
                    @enderror
                    <p class="mt-1.5 text-xs text-slate-400 flex items-center gap-1" id="statusText">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Menunggu konfirmasi...
                    </p>
                </div>

                <div class="flex items-center gap-3 pt-2 border-t border-slate-100">
                    <a href="{{ route('dashboard') }}" class="px-5 py-2.5 rounded-xl border border-slate-200 text-sm font-medium text-slate-600 hover:bg-slate-50 transition-all">
                        Batal
                    </a>
                    <button type="submit"
                            id="resetBtn"
                            disabled
                            class="px-5 py-2.5 rounded-xl text-white text-sm font-semibold transition-all bg-red-400 cursor-not-allowed opacity-60"
                            onclick="return confirmResetSubmit(event)">
                        <span class="flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            Reset Data
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
    function toggleResetButton() {
        const input = document.getElementById('confirmation');
        const btn = document.getElementById('resetBtn');
        const statusText = document.getElementById('statusText');
        const val = input.value.trim().toLowerCase();

        if (val === 'reset data') {
            btn.disabled = false;
            btn.className = 'px-5 py-2.5 rounded-xl text-white text-sm font-semibold transition-all bg-red-600 hover:bg-red-700 shadow-lg shadow-red-500/25';
            statusText.innerHTML = `
                <svg class="w-3.5 h-3.5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span class="text-emerald-600">Konfirmasi sesuai. Tombol reset sudah aktif.</span>`;
        } else {
            btn.disabled = true;
            btn.className = 'px-5 py-2.5 rounded-xl text-white text-sm font-semibold transition-all bg-red-400 cursor-not-allowed opacity-60';
            statusText.innerHTML = `
                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span class="text-slate-400">Menunggu konfirmasi...</span>`;
        }
    }

    // Validasi client-side sebelum submit
    function validateResetForm(event) {
        const input = document.getElementById('confirmation');
        const val = input.value.trim().toLowerCase();
        if (val !== 'reset data') {
            event.preventDefault();
            return false;
        }
        return true;
    }

    // Konfirmasi ganda dengan dialog
    function confirmResetSubmit(event) {
        event.preventDefault();
        const form = document.getElementById('resetForm');

        showConfirm(
            'Apakah Anda yakin ingin mereset SEMUA data? Tindakan ini TIDAK bisa dibatalkan!',
            'Reset Data?'
        ).then(confirmed => {
            if (confirmed) {
                // Disable button dan ubah teks
                const btn = document.getElementById('resetBtn');
                btn.disabled = true;
                btn.innerHTML = '<span class="flex items-center gap-2"><svg class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>Mereset data...</span>';
                btn.className = 'px-5 py-2.5 rounded-xl text-white text-sm font-semibold bg-slate-400 cursor-wait';
                form.submit();
            }
        });
        return false;
    }
    </script>
    @endpush
</x-app-layout>
