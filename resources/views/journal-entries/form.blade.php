<x-app-layout>
    <x-slot name="header">{{ isset($journalEntry) ? 'Edit Jurnal' : 'Jurnal Baru' }}</x-slot>
    @php $entry = $journalEntry ?? null; @endphp
    <div class="max-w-4xl bg-white rounded-xl border border-slate-200 p-6 shadow-sm">
        <form action="{{ isset($entry) ? route('journal-entries.update', $entry) : route('journal-entries.store') }}" method="POST">
            @csrf @if(isset($entry)) @method('PUT') @endif
            <div class="grid grid-cols-3 gap-4">
                <div><label class="block text-sm font-medium text-slate-700 mb-1.5">Periode</label>
                    <select name="accounting_period_id" class="w-full rounded-lg input-modern text-sm" required>
                        @foreach($periods as $p)<option value="{{ $p->id }}" {{ old('accounting_period_id', $entry->accounting_period_id ?? '') == $p->id ? 'selected' : '' }}>{{ $p->label }}</option>@endforeach
                    </select></div>
                <div><label class="block text-sm font-medium text-slate-700 mb-1.5">Tanggal</label>
                    <input type="date" name="entry_date" value="{{ old('entry_date', $entry?->entry_date?->format('Y-m-d') ?? now()->format('Y-m-d')) }}" class="w-full rounded-lg input-modern text-sm" required></div>
                <div><label class="block text-sm font-medium text-slate-700 mb-1.5">No. Bukti</label>
                    <input type="text" name="reference_no" value="{{ old('reference_no', $entry->reference_no ?? '') }}" class="w-full rounded-lg input-modern text-sm" required placeholder="BKM-001"></div>
            </div>
            <div class="mt-4"><label class="block text-sm font-medium text-slate-700 mb-1.5">Keterangan</label>
                <textarea name="description" rows="2" class="w-full rounded-lg input-modern text-sm" required>{{ old('description', $entry->description ?? '') }}</textarea></div>
            <div class="mt-6">
                <div class="flex justify-between items-center mb-2"><label class="block text-sm font-medium text-slate-700">Baris Jurnal</label>
                    <button type="button" onclick="addLine()" class="text-xs px-3 py-1.5 bg-slate-100 text-slate-700 rounded-lg hover:bg-slate-200">+ Tambah Baris</button></div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead><tr class="text-left text-slate-500 border-b border-slate-200">
                            <th class="pb-2 w-1/2">Akun</th><th class="pb-2 w-48 text-right">Debet (Rp)</th><th class="pb-2 w-48 text-right">Kredit (Rp)</th><th class="pb-2 w-16"></th>
                        </tr></thead>
                        <tbody id="journal-lines-body">
                            @if(old('lines')) @foreach(old('lines') as $i => $line) @include('journal-entries._line', ['index' => $i, 'line' => $line]) @endforeach
                            @elseif($entry) @foreach($entry->lines as $i => $line) @include('journal-entries._line', ['index' => $i, 'line' => $line]) @endforeach
                            @else @include('journal-entries._line', ['index' => 0, 'line' => null]) @include('journal-entries._line', ['index' => 1, 'line' => null])
                            @endif
                        </tbody>
                        <tfoot>
                            <tr class="border-t border-slate-200 font-semibold"><td class="pt-2 text-right">Total</td><td class="pt-2 text-right" id="total-debit">0</td><td class="pt-2 text-right" id="total-credit">0</td><td></td></tr>
                            <tr id="balance-warning" class="hidden"><td colspan="4" class="pt-1 text-red-600 text-xs text-right">Total Debet tidak sama dengan Total Kredit</td></tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            <div class="flex gap-3 mt-6">
                <input type="hidden" name="status" id="status-input" value="draft">
                <button type="button" onclick="submitForm('draft')" class="px-5 py-2.5 rounded-lg text-white text-sm font-semibold bg-slate-600 hover:bg-slate-700">Simpan Draft</button>
                <button type="button" onclick="submitForm('posted')" class="px-5 py-2.5 rounded-lg text-white text-sm font-semibold btn-primary">Posting</button>
                <a href="{{ route('journal-entries.index') }}" class="px-5 py-2.5 rounded-lg border border-slate-200 text-slate-700 text-sm font-semibold hover:bg-slate-50 transition">Batal</a>
            </div>
        </form>
    </div>
    @push('scripts')
    <script>
        let lineIndex = {{ old('lines') ? count(old('lines')) : ($entry ? $entry->lines->count() : 2) }};
        function addLine() {
            const tbody = document.getElementById('journal-lines-body');
            const html = `<tr class="border-b border-slate-100">
                <td class="py-1.5 pr-2"><select name="lines[${lineIndex}][account_id]" class="w-full rounded-lg input-modern text-sm account-select" required>
                    <option value="">-- Pilih Akun --</option>
                    @foreach($accounts as $acc)<option value="{{ $acc->id }}">{{ $acc->code }} - {{ $acc->name }}</option>@endforeach
                </select></td>
                <td class="py-1.5 px-1"><input type="number" step="0.01" name="lines[${lineIndex}][debit]" value="0" class="w-full text-right rounded-lg input-modern text-sm debit-input" oninput="calculateTotals()"></td>
                <td class="py-1.5 px-1"><input type="number" step="0.01" name="lines[${lineIndex}][credit]" value="0" class="w-full text-right rounded-lg input-modern text-sm credit-input" oninput="calculateTotals()"></td>
                <td class="py-1.5 pl-2 text-center"><button type="button" onclick="this.closest('tr').remove(); calculateTotals();" class="text-red-400 hover:text-red-600"><svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button></td>
            </tr>`;
            tbody.insertAdjacentHTML('beforeend', html); lineIndex++; calculateTotals();
        }
        function calculateTotals() {
            let d = 0, c = 0;
            document.querySelectorAll('.debit-input').forEach(e => d += parseFloat(e.value) || 0);
            document.querySelectorAll('.credit-input').forEach(e => c += parseFloat(e.value) || 0);
            document.getElementById('total-debit').textContent = d.toLocaleString('id-ID', {minimumFractionDigits:2});
            document.getElementById('total-credit').textContent = c.toLocaleString('id-ID', {minimumFractionDigits:2});
            document.getElementById('balance-warning').classList.toggle('hidden', Math.abs(d-c) < 0.01);
        }
        function getTotals() {
            const d = Array.from(document.querySelectorAll('.debit-input')).reduce((s,e) => s+(parseFloat(e.value)||0),0);
            const c = Array.from(document.querySelectorAll('.credit-input')).reduce((s,e) => s+(parseFloat(e.value)||0),0);
            return { debit: d, credit: c };
        }
        function submitForm(status) {
            const totals = getTotals();
            if(totals.debit === 0) { alert('Jurnal tidak boleh kosong.'); return; }
            if(status === 'posted') {
                if(Math.abs(totals.debit - totals.credit) > 0.01) { alert('Total Debet harus sama dengan Total Kredit.'); return; }
                if(!confirm('Posting jurnal ini?')) return;
            }
            document.getElementById('status-input').value = status;
            document.querySelector('form').submit();
        }
        document.addEventListener('DOMContentLoaded', calculateTotals);
    </script>
    @endpush
</x-app-layout>
