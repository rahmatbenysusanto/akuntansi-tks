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

        {{-- AI Journal Generator -- hanya di mode create --}}
        @if(!isset($entry))
        <div class="mb-6 p-5 bg-gradient-to-br from-indigo-50 via-white to-blue-50 border border-indigo-200 rounded-xl shadow-sm" id="ai-generator-section">
            <div class="flex items-center gap-2 mb-3">
                <div class="flex items-center justify-center w-7 h-7 rounded-lg bg-indigo-100">
                    <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.455 2.456L21.75 6l-1.036.259a3.375 3.375 0 00-2.455 2.456z"/>
                    </svg>
                </div>
                <span class="text-sm font-semibold text-indigo-800">✨ Generate Jurnal dengan AI</span>
                <span class="text-[11px] text-indigo-400 font-medium bg-indigo-50 px-2 py-0.5 rounded-full">DeepSeek</span>
            </div>
            <p class="text-xs text-slate-500 mb-3 leading-relaxed">
                Tulis deskripsi transaksi, AI akan otomatis memilih akun COA yang sesuai.
                <span class="text-slate-400">Contoh: <em>"Bayar listrik kantor Juni 2026 Rp 1.500.000 dari Bank Danamon"</em></span>
            </p>

            {{-- Textarea full width --}}
            <div class="relative mb-3">
                <textarea id="ai-prompt" rows="3"
                    class="w-full rounded-xl border-slate-300 bg-white text-sm placeholder:text-slate-400 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition pr-12 resize-none"
                    placeholder="Ketik deskripsi transaksi di sini...&#10;Contoh: Bayar listrik kantor bulan Juni 2026 Rp 1.500.000 dari Bank Danamon"></textarea>
                <button type="button" onclick="clearAiPrompt()"
                    class="absolute right-2.5 top-2.5 text-slate-400 hover:text-red-500 hover:bg-red-50 p-1 rounded-lg transition hidden"
                    id="clear-prompt-btn" title="Hapus">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            {{-- Generate button full width --}}
            <button type="button" id="ai-generate-btn" onclick="generateAiJournal()"
                class="w-full group relative overflow-hidden px-5 py-3 bg-gradient-to-r from-indigo-600 to-indigo-700 text-white rounded-xl text-sm font-semibold hover:from-indigo-700 hover:to-indigo-800 disabled:from-indigo-400 disabled:to-indigo-400 disabled:cursor-not-allowed transition-all duration-300 shadow-md shadow-indigo-200 hover:shadow-lg hover:shadow-indigo-300 disabled:shadow-none flex items-center justify-center gap-3">
                {{-- Default state --}}
                <span id="ai-btn-default" class="flex items-center gap-2.5">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                    <span class="text-base">Generate dengan AI</span>
                </span>
                {{-- Loading state --}}
                <span id="ai-btn-loading" class="hidden flex items-center gap-3">
                    <svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span>AI sedang menganalisis transaksi...</span>
                    <span class="flex gap-0.5 ml-1">
                        <span class="w-1.5 h-1.5 bg-white rounded-full animate-bounce [animation-delay:0ms]"></span>
                        <span class="w-1.5 h-1.5 bg-white rounded-full animate-bounce [animation-delay:150ms]"></span>
                        <span class="w-1.5 h-1.5 bg-white rounded-full animate-bounce [animation-delay:300ms]"></span>
                    </span>
                </span>
            </button>

            {{-- Shortcut hint --}}
            <p class="text-center text-[11px] text-slate-400 mt-2">Tekan <kbd class="px-1.5 py-0.5 bg-slate-100 border border-slate-200 rounded text-[10px] text-slate-500 font-mono">Enter</kbd> untuk langsung generate</p>

            <div id="ai-error" class="hidden mt-3 px-4 py-2.5 bg-red-50 border border-red-200 rounded-xl text-sm text-red-700"></div>
            <div id="ai-success" class="hidden mt-3 px-4 py-2.5 bg-emerald-50 border border-emerald-200 rounded-xl text-sm text-emerald-700 flex items-start gap-2.5">
                <svg class="w-4 h-4 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span id="ai-success-msg"></span>
            </div>
        </div>

        {{-- Loading shimmer animation styles --}}
        <style>
            @keyframes ai-pulse {
                0%, 100% { box-shadow: 0 0 0 0 rgba(99,102,241,0.3); }
                50% { box-shadow: 0 0 0 8px rgba(99,102,241,0); }
            }
            #ai-generate-btn:disabled {
                animation: ai-pulse 1.8s ease-in-out infinite;
            }
            #ai-generate-btn .animate-bounce { animation-duration: 0.8s; }
        </style>
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

        // Cache account options HTML untuk dipakai addLine() dan addAiLine()
        const accountOptionsHtml = `@foreach($accounts as $acc)<option value="{{ $acc->id }}">{{ $acc->code }} - {{ $acc->name }}</option>@endforeach`;
        function getAccountOptionsHtml() { return accountOptionsHtml; }

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
                    ${getAccountOptionsHtml()}
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

            // AI prompt textarea: show/hide clear button
            const aiPrompt = document.getElementById('ai-prompt');
            const clearBtn = document.getElementById('clear-prompt-btn');
            if (aiPrompt && clearBtn) {
                aiPrompt.addEventListener('input', function() {
                    clearBtn.classList.toggle('hidden', !this.value);
                });
                // Allow Enter to submit (without Shift)
                aiPrompt.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter' && !e.shiftKey) {
                        e.preventDefault();
                        generateAiJournal();
                    }
                });
            }
        });

        // ========== AI Journal Generator ==========

        async function generateAiJournal() {
            const promptEl = document.getElementById('ai-prompt');
            const btn = document.getElementById('ai-generate-btn');
            const btnDefault = document.getElementById('ai-btn-default');
            const btnLoading = document.getElementById('ai-btn-loading');
            const errorEl = document.getElementById('ai-error');
            const successEl = document.getElementById('ai-success');
            const successMsg = document.getElementById('ai-success-msg');

            const prompt = promptEl?.value?.trim();
            if (!prompt || prompt.length < 10) {
                showAiError('Deskripsi transaksi terlalu pendek. Minimal 10 karakter.');
                promptEl?.focus();
                return;
            }

            // Show loading state
            btn.disabled = true;
            btnDefault.classList.add('hidden');
            btnLoading.classList.remove('hidden');
            btnLoading.classList.add('flex');
            errorEl.classList.add('hidden');
            successEl.classList.add('hidden');

            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content
                    || document.querySelector('input[name="_token"]')?.value;

                const response = await fetch('{{ route('journal-entries.ai-suggest') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ prompt: prompt }),
                });

                const result = await response.json();

                if (!response.ok || !result.success) {
                    showAiError(result.error || 'Gagal generate jurnal dari AI.');
                    return;
                }

                // Populate form with AI result
                populateFormFromAi(result.data);

                // Show success
                successMsg.textContent = '✅ Jurnal berhasil digenerate! Silakan review sebelum simpan. — Total: Debit Rp ' +
                    formatNumber(result.data.total_debit) + ' = Kredit Rp ' + formatNumber(result.data.total_credit);
                successEl.classList.remove('hidden');

                // Highlight AI-generated fields briefly
                highlightAiFields();

                // Scroll to journal lines
                document.getElementById('journal-lines-body')?.scrollIntoView({ behavior: 'smooth', block: 'center' });

            } catch (err) {
                showAiError('Gagal menghubungi server: ' + err.message);
            } finally {
                btn.disabled = false;
                btnDefault.classList.remove('hidden');
                btnLoading.classList.add('hidden');
                btnLoading.classList.remove('flex');
            }
        }

        function populateFormFromAi(data) {
            // Set description
            if (data.description) {
                const descEl = document.querySelector('textarea[name="description"]');
                if (descEl) descEl.value = data.description;
            }

            // Set reference number
            if (data.reference_no) {
                const refEl = document.querySelector('input[name="reference_no"]');
                if (refEl) refEl.value = data.reference_no;
            }

            // Set entry date if provided
            if (data.entry_date) {
                const dateEl = document.querySelector('input[name="entry_date"]');
                if (dateEl) dateEl.value = data.entry_date;
            }

            // Clear existing lines & populate with AI results
            const tbody = document.getElementById('journal-lines-body');
            if (!tbody) return;

            // Remove all existing rows
            tbody.innerHTML = '';

            // Reset lineIndex
            lineIndex = 0;

            // Add AI-generated lines
            if (data.lines && data.lines.length > 0) {
                data.lines.forEach((line, i) => {
                    addAiLine(line, i);
                    lineIndex = i + 1;
                });
            }

            calculateTotals();
        }

        function addAiLine(line, index) {
            const tbody = document.getElementById('journal-lines-body');
            if (!tbody) return;

            const debitVal = line.debit > 0 ? line.debit : 0;
            const creditVal = line.credit > 0 ? line.credit : 0;

            const tr = document.createElement('tr');
            tr.className = 'border-b border-slate-100 ai-generated-row';
            tr.innerHTML = `
                <td class="py-1.5 pr-2">
                    <select name="lines[${index}][account_id]" class="w-full text-sm account-select" required>
                        <option value="">-- Pilih Akun --</option>
                        ${getAccountOptionsHtml()}
                    </select>
                </td>
                <td class="py-1.5 px-1">
                    <input type="number" step="0.01" name="lines[${index}][debit]" value="${debitVal}" class="w-full text-right rounded-lg input-modern text-sm debit-input" oninput="calculateTotals()">
                </td>
                <td class="py-1.5 px-1">
                    <input type="number" step="0.01" name="lines[${index}][credit]" value="${creditVal}" class="w-full text-right rounded-lg input-modern text-sm credit-input" oninput="calculateTotals()">
                </td>
                <td class="py-1.5 pl-2 text-center">
                    <button type="button" onclick="removeLine(this)" class="text-red-400 hover:text-red-600">
                        <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                </td>`;

            tbody.appendChild(tr);

            // Select the correct account
            const select = tr.querySelector('select');
            if (select && line.account_id) {
                select.value = String(line.account_id);
            }

            // Add AI suggestion badge if explanation exists
            if (line.explanation) {
                const td = tr.querySelector('td');
                const badge = document.createElement('span');
                badge.className = 'ml-2 text-xs text-indigo-500 italic ai-explanation';
                badge.textContent = '💡 ' + line.explanation;
                badge.title = line.explanation;
                td.appendChild(badge);
            }
        }

        function showAiError(msg) {
            const errorEl = document.getElementById('ai-error');
            const successEl = document.getElementById('ai-success');
            if (errorEl) {
                errorEl.textContent = '⚠️ ' + msg;
                errorEl.classList.remove('hidden');
            }
            if (successEl) successEl.classList.add('hidden');
        }

        function highlightAiFields() {
            // Brief highlight animation on AI-populated fields
            const fields = document.querySelectorAll('textarea[name="description"], input[name="reference_no"], input[name="entry_date"]');
            fields.forEach(f => {
                f.classList.add('ring-2', 'ring-indigo-300');
                setTimeout(() => f.classList.remove('ring-2', 'ring-indigo-300'), 2000);
            });

            // Highlight rows
            document.querySelectorAll('.ai-generated-row').forEach(row => {
                row.classList.add('bg-indigo-50');
                setTimeout(() => row.classList.remove('bg-indigo-50'), 3000);
            });
        }

        function clearAiPrompt() {
            const promptEl = document.getElementById('ai-prompt');
            const clearBtn = document.getElementById('clear-prompt-btn');
            const errorEl = document.getElementById('ai-error');
            const successEl = document.getElementById('ai-success');
            if (promptEl) promptEl.value = '';
            if (clearBtn) clearBtn.classList.add('hidden');
            if (errorEl) errorEl.classList.add('hidden');
            if (successEl) successEl.classList.add('hidden');
        }

        function formatNumber(num) {
            return Number(num).toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }

        function removeLine(btn) {
            const tbody = document.getElementById('journal-lines-body');
            if (tbody && tbody.children.length <= 2) return; // Minimal 2 baris
            btn.closest('tr').remove();
            calculateTotals();
        }
    </script>
    @endpush
</x-app-layout>
