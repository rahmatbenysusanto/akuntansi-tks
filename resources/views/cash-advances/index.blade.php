<x-app-layout>
<x-slot name="header">Kasbon Karyawan</x-slot>
<div class="space-y-4">

    {{-- Modal Pelunasan --}}
    <div id="settle-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40">
        <div class="bg-white rounded-xl shadow-xl p-6 w-full max-w-md mx-4">
            <h3 class="text-base font-semibold text-slate-800 mb-4">Lunasi Kasbon</h3>
            <form id="settle-form" method="POST" action="">
                @csrf
                <div class="space-y-3">
                    <div>
                        <p class="text-xs text-slate-500 mb-1">Karyawan</p>
                        <p id="settle-employee" class="font-medium text-slate-800 text-sm">—</p>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <p class="text-xs text-slate-500">Total Kasbon</p>
                            <p id="settle-total" class="font-semibold text-slate-800 text-sm font-mono">—</p>
                        </div>
                        <div>
                            <p class="text-xs text-slate-500">Sisa Belum Lunas</p>
                            <p id="settle-remaining" class="font-semibold text-red-600 text-sm font-mono">—</p>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Tanggal Pelunasan</label>
                        <input type="date" name="settlement_date" value="{{ now()->format('Y-m-d') }}"
                            class="w-full rounded-lg input-modern text-sm" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Jumlah Dilunasi</label>
                        <input type="number" name="amount" id="settle-amount-input"
                            class="w-full rounded-lg input-modern text-sm" min="1" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Metode Pelunasan</label>
                        <select name="method" class="w-full rounded-lg input-modern text-sm">
                            <option value="kembali_tunai">Kembali Tunai (Kredit Kas)</option>
                            <option value="potong_gaji">Potong Gaji (Kredit Beban Gaji)</option>
                        </select>
                    </div>
                </div>
                <div class="flex gap-3 mt-5">
                    <button type="submit" class="px-5 py-2.5 rounded-lg text-white text-sm font-semibold btn-primary">
                        Simpan & Jurnal Otomatis
                    </button>
                    <button type="button" onclick="closeSettleModal()"
                        class="px-5 py-2.5 rounded-lg border border-slate-200 text-slate-700 text-sm font-semibold hover:bg-slate-50 transition">
                        Batal
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="flex justify-end">
        <a href="{{ route('cash-advances.create') }}"
            class="px-5 py-2.5 rounded-lg text-white text-sm font-semibold btn-primary">
            + Kasbon Baru
        </a>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-x-auto">
        <table class="w-full text-sm table-modern">
            <thead>
                <tr class="bg-slate-50/50">
                    <th class="px-5 py-3 text-left">No. Kasbon</th>
                    <th class="px-5 py-3 text-left">Karyawan</th>
                    <th class="px-5 py-3 text-left">Tanggal</th>
                    <th class="px-5 py-3 text-right">Jumlah</th>
                    <th class="px-5 py-3 text-right">Terlunasi</th>
                    <th class="px-5 py-3 text-right">Sisa</th>
                    <th class="px-5 py-3 text-center">Status</th>
                    <th class="px-5 py-3 text-left">Metode</th>
                    <th class="px-5 py-3 text-left">Aksi</th>
                </tr>
            </thead>
            <tbody>
            @forelse($advances as $a)
                @php
                    $settled   = $a->settlements->sum('amount');
                    $remaining = $a->amount - $settled;
                @endphp
                <tr class="border-b border-slate-100 hover:bg-slate-50/80">
                    <td class="px-5 py-3 font-medium text-slate-800">{{ $a->advance_no }}</td>
                    <td class="px-5 py-3">{{ $a->employee?->name ?? '-' }}</td>
                    <td class="px-5 py-3 text-slate-600">{{ $a->advance_date->format('d/m/Y') }}</td>
                    <td class="px-5 py-3 text-right font-mono">@rupiah($a->amount)</td>
                    <td class="px-5 py-3 text-right font-mono text-emerald-700">@rupiah($settled)</td>
                    <td class="px-5 py-3 text-right font-mono {{ $remaining > 0 ? 'text-red-600 font-semibold' : 'text-slate-400' }}">
                        @rupiah($remaining)
                    </td>
                    <td class="px-5 py-3 text-center">
                        <span class="badge {{ $a->status == 'settled' ? 'bg-emerald-50 text-emerald-700' : ($a->status == 'partial' ? 'bg-amber-50 text-amber-700' : 'bg-red-50 text-red-700') }}">
                            {{ match($a->status) { 'settled' => 'Lunas', 'partial' => 'Sebagian', default => 'Belum Lunas' } }}
                        </span>
                    </td>
                    <td class="px-5 py-3 text-slate-600">{{ str_replace('_', ' ', ucfirst($a->settlement_method)) }}</td>
                    <td class="px-5 py-3">
                        @if($a->status !== 'settled')
                            <button onclick="openSettleModal(
                                '{{ route('cash-advances.settle', $a) }}',
                                '{{ $a->employee?->name }}',
                                {{ $a->amount }},
                                {{ $remaining }}
                            )" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">
                                Lunasi
                            </button>
                        @else
                            <span class="text-slate-300 text-sm">Lunas</span>
                        @endif
                    </td>
                </tr>
                {{-- Riwayat settlement --}}
                @if($a->settlements->count())
                    <tr class="border-b border-slate-100 bg-slate-50/30">
                        <td colspan="9" class="px-8 py-2">
                            <div class="flex flex-wrap gap-3">
                                @foreach($a->settlements as $s)
                                    <span class="text-xs text-slate-500">
                                        📌 {{ \Carbon\Carbon::parse($s->settlement_date)->format('d/m/Y') }}
                                        — @rupiah($s->amount)
                                        ({{ str_replace('_', ' ', $s->method) }})
                                    </span>
                                @endforeach
                            </div>
                        </td>
                    </tr>
                @endif
            @empty
                <tr><td colspan="9" class="px-5 py-10 text-center text-slate-400">Belum ada data kasbon.</td></tr>
            @endforelse
            </tbody>
        </table>
        @if($advances->hasPages())
            <div class="px-5 py-3 border-t border-slate-100">{{ $advances->links() }}</div>
        @endif
    </div>
</div>

<script>
function openSettleModal(action, employee, total, remaining) {
    document.getElementById('settle-form').action = action;
    document.getElementById('settle-employee').textContent = employee;
    document.getElementById('settle-total').textContent = new Intl.NumberFormat('id-ID').format(total);
    document.getElementById('settle-remaining').textContent = new Intl.NumberFormat('id-ID').format(remaining);
    document.getElementById('settle-amount-input').max = remaining;
    document.getElementById('settle-amount-input').value = remaining;
    document.getElementById('settle-modal').classList.remove('hidden');
}
function closeSettleModal() {
    document.getElementById('settle-modal').classList.add('hidden');
}
document.getElementById('settle-modal').addEventListener('click', function(e) {
    if (e.target === this) closeSettleModal();
});
</script>
</x-app-layout>
