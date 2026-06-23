<x-app-layout>
<x-slot name="header">Detail Payroll — {{ $payroll->reference_no }}</x-slot>
<div class="space-y-4">
    <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm">
        <div class="grid grid-cols-3 gap-4 text-sm">
            <div><span class="text-slate-500">Periode:</span> <span class="font-medium">{{ $payroll->accountingPeriod?->label }}</span></div>
            <div><span class="text-slate-500">Status:</span>
                <span class="badge {{ $payroll->status === 'posted' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
                    {{ $payroll->status === 'posted' ? 'Posted' : 'Draft' }}
                </span>
            </div>
            <div><span class="text-slate-500">Karyawan:</span> <span class="font-medium">{{ $payroll->lines->count() }} orang</span></div>
            <div><span class="text-slate-500">Total Gross:</span> <span class="font-mono font-semibold">@rupiah($payroll->totalGross())</span></div>
            <div><span class="text-slate-500">Total Potongan:</span> <span class="font-mono text-red-600">@rupiah($payroll->totalDeduction())</span></div>
            <div><span class="text-slate-500 font-semibold">Total Net:</span> <span class="font-mono font-bold text-blue-700">@rupiah($payroll->totalNet())</span></div>
            @if($payroll->description)<div class="col-span-3"><span class="text-slate-500">Keterangan:</span> {{ $payroll->description }}</div>@endif
            @if($payroll->isPosted())
                <div class="col-span-3"><span class="text-slate-500">Jurnal:</span>
                    <a href="#" class="text-indigo-600">#{{ $payroll->journal_entry_id }}</a>
                </div>
            @endif
        </div>
        @if($payroll->isDraft())
            <form method="POST" action="{{ route('payroll.post', $payroll) }}" class="mt-4"
                onsubmit="return confirm('Posting payroll? Jurnal akuntansi akan dibuat.')">
                @csrf
                <button class="px-5 py-2.5 rounded-lg text-white text-sm font-semibold btn-primary">✅ Posting Sekarang</button>
                <a href="{{ route('payroll.edit', $payroll) }}" class="px-5 py-2.5 rounded-lg border text-slate-700 text-sm font-semibold">✏️ Edit</a>
            </form>
        @endif
    </div>

    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-x-auto">
        <table class="w-full text-sm table-modern">
            <thead><tr class="bg-slate-50/50">
                <th class="px-4 py-3 text-left">Karyawan</th>
                <th class="px-4 py-3 text-right">Gaji Pokok</th>
                <th class="px-4 py-3 text-right">Tunjangan</th>
                <th class="px-4 py-3 text-right">Lembur</th>
                <th class="px-4 py-3 text-right bg-emerald-50">Gross</th>
                <th class="px-4 py-3 text-right">BPJS</th>
                <th class="px-4 py-3 text-right">PPh 21</th>
                <th class="px-4 py-3 text-right">Pot. Kasbon</th>
                <th class="px-4 py-3 text-right bg-blue-50">Net</th>
            </tr></thead>
            <tbody>
            @foreach($payroll->lines as $l)
                <tr class="border-b border-slate-100">
                    <td class="px-4 py-2 font-medium">{{ $l->employee?->name }}</td>
                    <td class="px-4 py-2 text-right font-mono">@rupiah($l->base_salary)</td>
                    <td class="px-4 py-2 text-right font-mono">@rupiah($l->allowance_transport + $l->allowance_meal + $l->allowance_other)</td>
                    <td class="px-4 py-2 text-right font-mono">@rupiah($l->overtime)</td>
                    <td class="px-4 py-2 text-right font-mono font-semibold bg-emerald-50/50">@rupiah($l->gross_salary)</td>
                    <td class="px-4 py-2 text-right font-mono">@rupiah($l->bpjs_kesehatan + $l->bpjs_tk)</td>
                    <td class="px-4 py-2 text-right font-mono">@rupiah($l->pph21)</td>
                    <td class="px-4 py-2 text-right font-mono">@rupiah($l->kasbon_deduction)</td>
                    <td class="px-4 py-2 text-right font-mono font-bold bg-blue-50/50">@rupiah($l->net_salary)</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
</x-app-layout>
