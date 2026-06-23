<div id="confirm-dialog" class="fixed inset-0 z-[100] hidden items-center justify-center p-4">
    {{-- Overlay --}}
    <div class="fixed inset-0 bg-black/50 transition-opacity" onclick="closeConfirmDialog()"></div>
    {{-- Modal --}}
    <div class="relative bg-white rounded-2xl shadow-2xl max-w-sm w-full mx-auto p-6 transform transition-all scale-95" id="confirm-dialog-box">
        <div class="text-center">
            {{-- Icon --}}
            <div id="confirm-dialog-icon" class="mx-auto w-14 h-14 rounded-full flex items-center justify-center mb-4">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" id="confirm-dialog-svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01"/>
                </svg>
            </div>
            {{-- Title --}}
            <h3 id="confirm-dialog-title" class="text-lg font-semibold text-slate-800 mb-2"></h3>
            {{-- Message --}}
            <p id="confirm-dialog-message" class="text-sm text-slate-500 mb-6 leading-relaxed"></p>
            {{-- Buttons --}}
            <div id="confirm-dialog-actions" class="flex gap-3 justify-center">
                <button id="confirm-dialog-cancel" class="px-6 py-2.5 rounded-xl border border-slate-200 text-sm font-medium text-slate-600 hover:bg-slate-50 hover:border-slate-300 transition-all">Batal</button>
                <button id="confirm-dialog-ok" class="px-6 py-2.5 rounded-xl text-white text-sm font-semibold transition-all shadow-lg shadow-indigo-500/25">
                    Ya
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let confirmResolve = null;
let confirmDialogEl = null;
let confirmBoxEl = null;

document.addEventListener('DOMContentLoaded', function() {
    confirmDialogEl = document.getElementById('confirm-dialog');
    confirmBoxEl = document.getElementById('confirm-dialog-box');

    // Close on escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && confirmDialogEl && !confirmDialogEl.classList.contains('hidden')) {
            closeConfirmDialog(false);
        }
    });
});

function showAlert(message) {
    return new Promise((resolve) => {
        showConfirmDialog('info', 'Perhatian', message, 'Oke', function() {
            resolve();
        });
    });
}

function showConfirm(message, title = 'Konfirmasi') {
    return new Promise((resolve) => {
        showConfirmDialog('confirm', title, message, 'Ya, Lanjutkan', function(result) {
            resolve(result);
        });
    });
}

function showConfirmDialog(type, title, message, okText, callback) {
    if (!confirmDialogEl || !confirmBoxEl) return;

    const icon = document.getElementById('confirm-dialog-icon');
    const svg = document.getElementById('confirm-dialog-svg');
    const titleEl = document.getElementById('confirm-dialog-title');
    const msgEl = document.getElementById('confirm-dialog-message');
    const okBtn = document.getElementById('confirm-dialog-ok');
    const cancelBtn = document.getElementById('confirm-dialog-cancel');
    const actions = document.getElementById('confirm-dialog-actions');

    // Set content
    titleEl.textContent = title;
    msgEl.textContent = message;
    okBtn.textContent = okText;

    // Set icon & colors based on type
    if (type === 'confirm') {
        icon.className = 'mx-auto w-14 h-14 rounded-full flex items-center justify-center mb-4 bg-amber-50';
        svg.className = 'w-7 h-7 text-amber-500';
        svg.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>';
        okBtn.className = 'px-6 py-2.5 rounded-xl text-white text-sm font-semibold transition-all shadow-lg bg-amber-500 hover:bg-amber-600 shadow-amber-500/25';
        cancelBtn.classList.remove('hidden');
    } else if (type === 'danger') {
        icon.className = 'mx-auto w-14 h-14 rounded-full flex items-center justify-center mb-4 bg-red-50';
        svg.className = 'w-7 h-7 text-red-500';
        svg.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>';
        okBtn.className = 'px-6 py-2.5 rounded-xl text-white text-sm font-semibold transition-all shadow-lg bg-red-500 hover:bg-red-600 shadow-red-500/25';
        cancelBtn.classList.remove('hidden');
    } else {
        // info/alert
        icon.className = 'mx-auto w-14 h-14 rounded-full flex items-center justify-center mb-4 bg-indigo-50';
        svg.className = 'w-7 h-7 text-indigo-500';
        svg.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>';
        okBtn.className = 'px-6 py-2.5 rounded-xl text-white text-sm font-semibold transition-all shadow-lg bg-indigo-500 hover:bg-indigo-600 shadow-indigo-500/25';
        cancelBtn.classList.add('hidden');
    }

    // Show modal
    confirmDialogEl.classList.remove('hidden');
    confirmDialogEl.classList.add('flex');
    setTimeout(() => {
        confirmBoxEl.classList.remove('scale-95');
        confirmBoxEl.classList.add('scale-100');
    }, 10);

    // Button handlers (remove old ones first)
    const newOk = okBtn.cloneNode(true);
    okBtn.parentNode.replaceChild(newOk, okBtn);
    const newCancel = cancelBtn.cloneNode(true);
    cancelBtn.parentNode.replaceChild(newCancel, cancelBtn);

    if (type === 'info') {
        newOk.addEventListener('click', function() {
            closeConfirmDialog();
            if (callback) callback(true);
        });
    } else {
        newOk.addEventListener('click', function() {
            closeConfirmDialog();
            if (callback) callback(true);
        });
        newCancel.addEventListener('click', function() {
            closeConfirmDialog();
            if (callback) callback(false);
        });
    }
}

function closeConfirmDialog(result = false) {
    if (confirmDialogEl) {
        confirmDialogEl.classList.add('hidden');
        confirmDialogEl.classList.remove('flex');
    }
    if (confirmBoxEl) {
        confirmBoxEl.classList.add('scale-95');
        confirmBoxEl.classList.remove('scale-100');
    }
}
</script>
