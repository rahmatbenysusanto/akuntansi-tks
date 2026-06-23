<x-app-layout>
<x-slot name="header">{{ isset($payroll) ? 'Edit Payroll' : 'Buat Payroll Baru' }}</x-slot>

<div class="space-y-4">

    @if($errors->any())
        <div class="px-4 py-3 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm">
            <ul class="list-disc list-inside">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    <form action="{{ isset($payroll) ? route('payroll.update', $payroll) : route('payroll.store') }}" method="POST" id="payroll-form">
        @csrf
        @if(isset($payroll)) @method('PUT') @endif

        {{-- Header --}}
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5 mb-4">
            <h3 class="text-sm font-semibold text-slate-700 mb-4">Informasi Payroll</h3>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Periode Akuntansi <span class="text-red-500">*</span></label>
                    <select name="accounting_period_id" class="w-full rounded-lg input-modern text-sm" required>
                        <option value="">-- Pilih Periode --</option>
                        @foreach($periods as $p)
                            <option value="{{ $p->id }}"
                                {{ old('accounting_period_id', $payroll->accounting_period_id ?? '') == $p->id ? 'selected' : '' }}>
                                {{ $p->label }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">No. Referensi <span class="text-red-500">*</span></label>
                    <input type="text" name="reference_no"
                        value="{{ old('reference_no', $payroll->reference_no ?? 'PAY-' . date('Ym')) }}"
                        class="w-full rounded-lg input-modern text-sm" required>
                </div>
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Keterangan</label>
                    <input type="text" name="description"
                        value="{{ old('description', $payroll->description ?? 'Penggajian ' . now()->isoFormat('MMMM YYYY')) }}"
                        class="w-full rounded-lg input-modern text-sm">
                </div>
            </div>

            {{-- Akun Jurnal --}}
            <div class="mt-4 pt-4 border-t border-slate-100">
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-3">Akun untuk Jurnal Otomatis</p>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Debit: Beban Gaji <span class="text-red-500">*</span></label>
                        <select name="salary_expense_account_id" class="w-full rounded-lg input-modern text-sm account-select" required>
                            <option value="">-- Pilih Akun --</option>
                            @foreach($expenseAccounts as $a)
                                <option value="{{ $a->id }}"
                                    {{ old('salary_expense_account_id', $payroll->salary_expense_account_id ?? '') == $a->id ? 'selected' : '' }}>
                                    {{ $a->code }} - {{ $a->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Kredit: Hutang Gaji / Kas <span class="text-red-500">*</span></label>
                        <select name="salary_payable_account_id" class="w-full rounded-lg input-modern text-sm account-select" required>
                            <option value="">-- Pilih Akun --</option>
                            @foreach($liabilityAccounts as $a)
                                <option value="{{ $a->id }}"
                                    {{ old('salary_payable_account_id', $payroll->salary_payable_account_id ?? '') == $a->id ? 'selected' : '' }}>
                                    {{ $a->code }} - {{ $a->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Kredit: Hutang BPJS <small class="text-slate-400">(opsional)</small></label>
                        <select name="bpjs_payable_account_id" class="w-full rounded-lg input-modern text-sm account-select">
                            <option value="">-- Tidak Ada / Sama dgn Hutang Gaji --</option>
                            @foreach($liabilityAccounts as $a)
                                <option value="{{ $a->id }}"
                                    {{ old('bpjs_payable_account_id', $payroll->bpjs_payable_account_id ?? '') == $a->id ? 'selected' : '' }}>
                                    {{ $a->code }} - {{ $a->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Kredit: Hutang PPh 21 <small class="text-slate-400">(opsional)</small></label>
                        <select name="pph21_payable_account_id" class="w-full rounded-lg input-modern text-sm account-select">
                            <option value="">-- Tidak Ada / Sama dgn Hutang Gaji --</option>
                            @foreach($liabilityAccounts as $a)
                                <option value="{{ $a->id }}"
                                    {{ old('pph21_payable_account_id', $payroll->pph21_payable_account_id ?? '') == $a->id ? 'selected' : '' }}>
                                    {{ $a->code }} - {{ $a->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tabel baris per karyawan --}}
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-x-auto">
            <div class="px-5 py-3 border-b border-slate-100 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-slate-700">Detail Gaji Per Karyawan</h3>
                <button type="button" onclick="addLine()"
                    class="px-3 py-1.5 text-xs rounded-lg border border-indigo-300 text-indigo-600 hover:bg-indigo-50 transition">
                    + Tambah Baris
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-xs" id="payroll-table">
                    <thead>
                        <tr class="bg-slate-50/50 text-slate-500">
                            <th class="px-3 py-2 text-left min-w-[160px]">Karyawan</th>
                            <th class="px-3 py-2 text-right min-w-[110px]">Gaji Pokok</th>
                            <th class="px-3 py-2 text-right min-w-[100px]">Transport</th>
                            <th class="px-3 py-2 text-right min-w-[100px]">Makan</th>
                            <th class="px-3 py-2 text-right min-w-[100px]">Lainnya</th>
                            <th class="px-3 py-2 text-right min-w-[100px]">Lembur</th>
                            <th class="px-3 py-2 text-right min-w-[110px] bg-emerald-50/50">Gross</th>
                            <th class="px-3 py-2 text-right min-w-[90px]">BPJS Kes</th>
                            <th class="px-3 py-2 text-right min-w-[90px]">BPJS TK</th>
                            <th class="px-3 py-2 text-right min-w-[90px]">PPh 21</th>
                            <th class="px-3 py-2 text-left min-w-[150px]">Kasbon</th>
                            <th class="px-3 py-2 text-right min-w-[100px]">Pot. Kasbon</th>
                            <th class="px-3 py-2 text-right min-w-[110px] bg-blue-50/50">Net Gaji</th>
                            <th class="px-3 py-2 text-center min-w-[50px]"></th>
                        </tr>
                    </thead>
                    <tbody id="payroll-lines">
                        @php
                            $existingLines = isset($payroll) ? $payroll->lines : collect();
                        @endphp
                        @if($existingLines->count())
                            @foreach($existingLines as $i => $line)
                                @include('payroll._line', ['i' => $i, 'line' => $line, 'employees' => $employees, 'advances' => $advances])
                            @endforeach
                        @else
                            @foreach($employees as $i => $emp)
                                @include('payroll._line', ['i' => $i, 'line' => null, 'emp' => $emp, 'employees' => $employees, 'advances' => $advances])
                            @endforeach
                        @endif
                    </tbody>
                    <tfoot>
                        <tr class="bg-slate-50 font-semibold text-xs border-t-2 border-slate-200">
                            <td class="px-3 py-2 text-slate-600">TOTAL</td>
                            <td colspan="5"></td>
                            <td class="px-3 py-2 text-right font-mono text-emerald-700" id="total-gross">0</td>
                            <td colspan="3"></td>
                            <td class="px-3 py-2 text-right font-mono text-red-600" id="total-kasbon">0</td>
                            <td></td>
                            <td class="px-3 py-2 text-right font-mono text-blue-700" id="total-net">0</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        {{-- Tombol --}}
        <div class="flex gap-3 mt-4">
            <button type="submit" name="save_draft" class="px-5 py-2.5 rounded-lg border border-slate-300 text-slate-700 text-sm font-semibold hover:bg-slate-50 transition">
                💾 Simpan Draft
            </button>
            <button type="submit" name="post_now" value="1" onclick="return confirm('Posting payroll sekarang? Jurnal akuntansi akan langsung dibuat.')"
                class="px-5 py-2.5 rounded-lg text-white text-sm font-semibold btn-primary">
                ✅ Posting Langsung
            </button>
            <a href="{{ route('payroll.index') }}"
                class="px-5 py-2.5 rounded-lg border border-slate-200 text-slate-600 text-sm font-semibold hover:bg-slate-50 transition">
                Batal
            </a>
        </div>
    </form>
</div>

{{-- Template baris baru (hidden) --}}
<template id="line-template">
    @include('payroll._line', ['i' => '__IDX__', 'line' => null, 'emp' => null, 'employees' => $employees, 'advances' => $advances])
</template>

<script>
let lineIndex = {{ isset($payroll) ? $existingLines->count() : $employees->count() }};

// Data karyawan untuk auto-fill
const employeeSalaries = {
    @foreach($employees as $emp)
    {{ $emp->id }}: {
        base_salary: {{ $emp->salary?->base_salary ?? 0 }},
        allowance_transport: {{ $emp->salary?->allowance_transport ?? 0 }},
        allowance_meal: {{ $emp->salary?->allowance_meal ?? 0 }},
        allowance_other: {{ $emp->salary?->allowance_other ?? 0 }},
        bpjs_kesehatan_pct: {{ $emp->salary?->bpjs_kesehatan_pct ?? 1 }},
        bpjs_tk_pct: {{ $emp->salary?->bpjs_tk_pct ?? 2 }},
    },
    @endforeach
};

function addLine() {
    const template = document.getElementById('line-template').innerHTML
        .replace(/__IDX__/g, lineIndex);
    document.getElementById('payroll-lines').insertAdjacentHTML('beforeend', template);
    recalcAll();
    lineIndex++;
}

function removeLine(btn) {
    btn.closest('tr').remove();
    recalcAll();
}

function onEmployeeChange(sel) {
    const row = sel.closest('tr');
    const empId = parseInt(sel.value);
    const sal = employeeSalaries[empId];
    if (!sal) return;

    const base = sal.base_salary;
    row.querySelector('[name$="[base_salary]"]').value = base;
    row.querySelector('[name$="[allowance_transport]"]').value = sal.allowance_transport;
    row.querySelector('[name$="[allowance_meal]"]').value = sal.allowance_meal;
    row.querySelector('[name$="[allowance_other]"]').value = sal.allowance_other;
    // BPJS auto-calculate
    row.querySelector('[name$="[bpjs_kesehatan]"]').value = Math.round(base * sal.bpjs_kesehatan_pct / 100);
    row.querySelector('[name$="[bpjs_tk]"]').value = Math.round(base * sal.bpjs_tk_pct / 100);
    recalcRow(row);
}

function recalcRow(row) {
    const val = name => parseFloat(row.querySelector('[name$="[' + name + ']"]')?.value) || 0;
    const gross = val('base_salary') + val('allowance_transport') + val('allowance_meal') + val('allowance_other') + val('overtime');
    const deduction = val('bpjs_kesehatan') + val('bpjs_tk') + val('pph21') + val('kasbon_deduction');
    const net = Math.max(0, gross - deduction);
    row.querySelector('.row-gross').textContent = fmt(gross);
    row.querySelector('.row-net').textContent = fmt(net);
    recalcAll();
}

function recalcAll() {
    let totalGross = 0, totalNet = 0, totalKasbon = 0;
    document.querySelectorAll('#payroll-lines tr').forEach(row => {
        const val = name => parseFloat(row.querySelector('[name$="[' + name + ']"]')?.value) || 0;
        const gross = val('base_salary') + val('allowance_transport') + val('allowance_meal') + val('allowance_other') + val('overtime');
        const deduction = val('bpjs_kesehatan') + val('bpjs_tk') + val('pph21') + val('kasbon_deduction');
        totalGross += gross;
        totalNet += Math.max(0, gross - deduction);
        totalKasbon += val('kasbon_deduction');
    });
    document.getElementById('total-gross').textContent = fmt(totalGross);
    document.getElementById('total-net').textContent = fmt(totalNet);
    document.getElementById('total-kasbon').textContent = fmt(totalKasbon);
}

function fmt(n) {
    return new Intl.NumberFormat('id-ID').format(Math.round(n));
}

// Init
document.addEventListener('DOMContentLoaded', recalcAll);
document.getElementById('payroll-lines').addEventListener('input', e => {
    if (e.target.tagName === 'INPUT') recalcRow(e.target.closest('tr'));
});
</script>
</x-app-layout>
