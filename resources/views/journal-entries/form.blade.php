<x-app-layout>
    <x-slot name="header">{{ isset($readonly) && $readonly ? 'Detail Jurnal' : (isset($journalEntry) ? 'Edit Jurnal' : 'Jurnal Baru') }}</x-slot>
    @php $entry = $journalEntry ?? null; @endphp
    <div class="max-w-4xl bg-white rounded-xl border border-slate-200 p-6 shadow-sm">

        {{-- Validation Errors --}}
        @if ($errors->any())
        <div class="mb-5 px-4 py-3 bg-red-50 border border-red-200 rounded-lg">
            <p class="text-sm font-semibold text-red-700 mb-1">⚠️ Ada kesalahan:</p>
            <ul class="list-disc list-inside text-sm text-red-600 space-y-0.5">
                @foreach ($errors->all() as $msg)
                    <li>{{ $msg }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form id="journal-form" action="{{ isset($entry) ? route('journal-entries.update', $entry) : route('journal-entries.store') }}" method="POST" enctype="multipart/form-data">
            @csrf @if(isset($entry)) @method('PUT') @endif
            <div class="grid grid-cols-3 gap-4">
                <div><label class="block text-sm font-medium text-slate-700 mb-1.5">Periode</label>
                    <select name="accounting_period_id" class="w-full rounded-lg input-modern text-sm" {{ isset($readonly) && $readonly ? 'disabled' : '' }} required>
                        @foreach($periods as $p)<option value="{{ $p->id }}" {{ old('accounting_period_id', $entry->accounting_period_id ?? '') == $p->id ? 'selected' : '' }}>{{ $p->label }}</option>@endforeach
                    </select></div>
                <div><label class="block text-sm font-medium text-slate-700 mb-1.5">Tanggal</label>
                    <input type="date" name="entry_date" value="{{ old('entry_date', $entry?->entry_date?->format('Y-m-d') ?? now()->format('Y-m-d')) }}" class="w-full rounded-lg input-modern text-sm" {{ isset($readonly) && $readonly ? 'disabled' : '' }} required></div>
                <div><label class="block text-sm font-medium text-slate-700 mb-1.5">No. Bukti</label>
                    <input type="text" name="reference_no" value="{{ old('reference_no', $entry->reference_no ?? '') }}" class="w-full rounded-lg input-modern text-sm" placeholder="{{ (isset($readonly) && $readonly) ? '-' : 'BKM-001 (opsional)' }}" {{ isset($readonly) && $readonly ? 'disabled' : '' }}></div>
            </div>
            <div class="mt-4"><label class="block text-sm font-medium text-slate-700 mb-1.5">Keterangan</label>
                <textarea name="description" rows="2" class="w-full rounded-lg input-modern text-sm" {{ isset($readonly) && $readonly ? 'disabled' : '' }} required>{{ old('description', $entry->description ?? '') }}</textarea></div>
            <!-- File Upload Section -->
            @if(!isset($readonly) || !$readonly)
            <div class="mt-4">
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Lampiran Dokumen <span class="text-slate-400 text-xs font-normal">(Opsional — PNG, JPG, PDF, max 10MB)</span></label>

                {{-- Existing attachments (edit mode) --}}
                @if(isset($entry) && $entry->attachments->count() > 0)
                <div id="existing-attachments" class="mb-3 space-y-1.5">
                    @foreach($entry->attachments as $att)
                    <div class="flex items-center justify-between bg-slate-50 border border-slate-200 rounded-lg px-3 py-2 text-sm" data-attachment-id="{{ $att->id }}">
                        <div class="flex items-center gap-2 min-w-0">
                            @if($att->isImage())
                                <svg class="w-4 h-4 flex-shrink-0 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            @else
                                <svg class="w-4 h-4 flex-shrink-0 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                            @endif
                            <span class="truncate text-slate-700" title="{{ $att->original_name }}">{{ $att->original_name }}</span>
                            <span class="text-slate-400 flex-shrink-0">({{ $att->sizeForHumans() }})</span>
                        </div>
                        <button type="button" onclick="removeExistingAttachment(this, {{ $att->id }})" class="text-red-400 hover:text-red-600 flex-shrink-0 ml-2" title="Hapus lampiran">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    @endforeach
                </div>
                @endif

                {{-- New file previews --}}
                <div id="file-previews" class="mb-3 space-y-1.5"></div>

                {{-- Upload dropzone --}}
                <label for="file-upload" class="flex flex-col items-center justify-center w-full h-24 border-2 border-dashed border-slate-300 rounded-xl cursor-pointer bg-slate-50 hover:bg-slate-100 transition" id="upload-dropzone">
                    <div class="flex flex-col items-center justify-center pt-2 pb-2">
                        <svg class="w-7 h-7 text-slate-400 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                        <p class="text-xs text-slate-500"><span class="font-medium text-indigo-600">Klik untuk memilih</span> atau drag & drop file di sini</p>
                    </div>
                    <input type="file" id="file-upload" name="attachments[]" multiple accept="image/png,image/jpeg,image/jpg,application/pdf" class="hidden" onchange="handleFiles(this.files)">
                </label>
            </div>
            @endif
            {{-- End upload section --}}

            {{-- Show attachments in readonly mode --}}
            @if(isset($readonly) && $readonly && isset($entry) && $entry->attachments->count() > 0)
            <div class="mt-4">
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Lampiran Dokumen</label>
                <div class="space-y-1.5">
                    @foreach($entry->attachments as $att)
                    <div class="flex items-center gap-2 bg-slate-50 border border-slate-200 rounded-lg px-3 py-2 text-sm">
                        @if($att->isImage())
                            <svg class="w-4 h-4 flex-shrink-0 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        @else
                            <svg class="w-4 h-4 flex-shrink-0 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                        @endif
                        <span class="truncate text-slate-700">{{ $att->original_name }}</span>
                        <span class="text-slate-400">({{ $att->sizeForHumans() }})</span>
                        <a href="{{ $att->url() }}" target="_blank" class="ml-auto text-xs text-indigo-600 hover:underline">Lihat</a>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <div class="mt-6">
                <div class="flex justify-between items-center mb-2"><label class="block text-sm font-medium text-slate-700">Baris Jurnal</label>
                    @if(!isset($readonly) || !$readonly)
                    <button type="button" onclick="addLine()" class="text-xs px-3 py-1.5 bg-slate-100 text-slate-700 rounded-lg hover:bg-slate-200">+ Tambah Baris</button>
                    @endif
                </div>
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
                @if(!isset($readonly) || !$readonly)
                <input type="hidden" name="status" id="status-input" value="draft">
                <button type="button" onclick="submitForm('draft')" class="px-5 py-2.5 rounded-lg text-white text-sm font-semibold bg-slate-600 hover:bg-slate-700">Simpan Draft</button>
                <button type="button" onclick="submitForm('posted')" class="px-5 py-2.5 rounded-lg text-white text-sm font-semibold btn-primary">Posting</button>
                @endif
                <a href="{{ route('journal-entries.index') }}" class="px-5 py-2.5 rounded-lg border border-slate-200 text-slate-700 text-sm font-semibold hover:bg-slate-50 transition">{{ (isset($readonly) && $readonly) ? 'Kembali' : 'Batal' }}</a>
            </div>
        </form>
    </div>
    @push('scripts')
    <script>
        let lineIndex = {{ old('lines') ? count(old('lines')) : ($entry ? $entry->lines->count() : 2) }};

        // --- File upload handling ---
        const uploadedFiles = new DataTransfer();
        const removeAttachmentIds = [];

        function removeExistingAttachment(btn, attId) {
            removeAttachmentIds.push(attId);
            const hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = 'remove_attachment_ids[]';
            hiddenInput.value = attId;
            document.getElementById('journal-form').appendChild(hiddenInput);
            btn.closest('[data-attachment-id]').remove();
        }

        function handleFiles(files) {
            const previews = document.getElementById('file-previews');
            for (const file of files) {
                if (!['image/png', 'image/jpeg', 'image/jpg', 'application/pdf'].includes(file.type)) {
                    showAlert('File ' + file.name + ' tidak didukung. Gunakan PNG, JPG, atau PDF.');
                    continue;
                }
                if (file.size > 10 * 1024 * 1024) {
                    showAlert('File ' + file.name + ' terlalu besar. Maksimal 10MB.');
                    continue;
                }
                uploadedFiles.items.add(file);
                const div = document.createElement('div');
                div.className = 'flex items-center justify-between bg-indigo-50 border border-indigo-200 rounded-lg px-3 py-2 text-sm';
                div.dataset.filename = file.name;
                div.innerHTML = `
                    <div class="flex items-center gap-2 min-w-0">
                        ${file.type.startsWith('image/')
                            ? '<svg class="w-4 h-4 flex-shrink-0 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>'
                            : '<svg class="w-4 h-4 flex-shrink-0 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>'}
                        <span class="truncate text-slate-700" title="${file.name}">${file.name}</span>
                        <span class="text-slate-400 flex-shrink-0">(${(file.size / 1024).toFixed(1)} KB)</span>
                    </div>
                    <button type="button" onclick="removeNewFile(this, '${file.name.replace(/'/g, "\\'")}')" class="text-red-400 hover:text-red-600 flex-shrink-0 ml-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>`;
                previews.appendChild(div);
            }
            syncFileInput();
        }

        function removeNewFile(btn, fileName) {
            btn.closest('[data-filename]').remove();
            rebuildUploadedFiles();
            syncFileInput();
        }

        function rebuildUploadedFiles() {
            const dt = new DataTransfer();
            const previews = document.getElementById('file-previews').children;
            for (const div of previews) {
                const fname = div.dataset.filename;
                for (const file of uploadedFiles.files) {
                    if (file.name === fname) { dt.items.add(file); break; }
                }
            }
            uploadedFiles.items.clear();
            for (const file of dt.files) {
                uploadedFiles.items.add(file);
            }
        }

        function syncFileInput() {
            const input = document.getElementById('file-upload');
            input.files = uploadedFiles.files;
        }

        // Drag & drop support
        const dropzone = document.getElementById('upload-dropzone');
        if (dropzone) {
            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(evt => {
                dropzone.addEventListener(evt, e => { e.preventDefault(); e.stopPropagation(); });
            });
            ['dragenter', 'dragover'].forEach(evt => {
                dropzone.addEventListener(evt, () => dropzone.classList.add('border-indigo-400', 'bg-indigo-50'));
            });
            ['dragleave', 'drop'].forEach(evt => {
                dropzone.addEventListener(evt, () => dropzone.classList.remove('border-indigo-400', 'bg-indigo-50'));
            });
            dropzone.addEventListener('drop', e => {
                handleFiles(e.dataTransfer.files);
            });
        }

        function addLine() {
            const tbody = document.getElementById('journal-lines-body');
            const html = `<tr class="border-b border-slate-100">
                <td class="py-1.5 pr-2"><select name="lines[${lineIndex}][account_id]" class="w-full text-sm account-select" required>
                    <option value="">-- Pilih Akun --</option>
                    @foreach($accounts as $acc)<option value="{{ $acc->id }}">{{ $acc->code }} - {{ $acc->name }}</option>@endforeach
                </select></td>
                <td class="py-1.5 px-1"><input type="number" step="0.01" name="lines[${lineIndex}][debit]" value="0" class="w-full text-right rounded-lg input-modern text-sm debit-input" oninput="calculateTotals()"></td>
                <td class="py-1.5 px-1"><input type="number" step="0.01" name="lines[${lineIndex}][credit]" value="0" class="w-full text-right rounded-lg input-modern text-sm credit-input" oninput="calculateTotals()"></td>
                <td class="py-1.5 pl-2 text-center"><button type="button" onclick="removeLine(this)" class="text-red-400 hover:text-red-600"><svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button></td>
            </tr>`;
            tbody.insertAdjacentHTML('beforeend', html);
            const newSelect = tbody.querySelector(`select[name="lines[${lineIndex}][account_id]"]`);
            if (typeof $ !== 'undefined') initSelect2(newSelect);
            lineIndex++; calculateTotals();
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
        async function submitForm(status) {
            const totals = getTotals();
            if(totals.debit === 0) { await showAlert('Jurnal tidak boleh kosong.'); return; }
            if(status === 'posted') {
                if(Math.abs(totals.debit - totals.credit) > 0.01) { await showAlert('Total Debet harus sama dengan Total Kredit.'); return; }
                const ok = await showConfirm('Posting jurnal ini?', 'Konfirmasi Posting');
                if(!ok) return;
            }
            document.getElementById('status-input').value = status;
            document.getElementById('journal-form').submit();
        }
        document.addEventListener('DOMContentLoaded', function() {
            calculateTotals();
        });
    </script>
    @endpush
</x-app-layout>
