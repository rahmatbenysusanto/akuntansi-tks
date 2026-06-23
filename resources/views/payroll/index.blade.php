<x-app-layout>
<x-slot name="header">Penggajian (Payroll)</x-slot>
<div class="space-y-4">
    <div class="flex justify-end">
        <a href="{{ route('payroll.create') }}" class="px-5 py-2.5 rounded-lg text-white text-sm font-semibold btn-primary">
            + Buat Payroll
        </a>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-x-auto">
        <table class="w-full text-sm table-modern">
            <thead>
                <tr class="bg-slate-50/50">
                    <th class="px-5 py-3 text-left">No. Referensi</th>
                    <th class="px-5 py-3 text-left">Periode</th>
                    <th class="px-5 py-3 text-center">Karyawan</th>
                    <th class="px-5 py-3 text-right">Total Gross</th>
                    <th class="px-5 py-3 text-right">Total Net</th>
                    <th class="px-5 py-3 text-center">Status</th>
                    <th class="px-5 py-3 text-left">Aksi</th>
                </tr>
            </thead>
            <tbody>
            @forelse($payrolls as $p)
                <tr class="border-b border-slate-100 hover:bg-slate-50/80">
                    <td class="px-5 py-3 font-medium text-slate-800">{{ $p->reference_no }}</td>
                    <td class="px-5 py-3 text-slate-600">{{ $p->accountingPeriod?->label ?? '-' }}</td>
                    <td class="px-5 py-3 text-center text-slate-600">{{ $p->lines()->count() }} orang</td>
                    <td class="px-5 py-3 text-right font-mono">@rupiah($p->totalGross())</td>
                    <td class="px-5 py-3 text-right font-mono font-semibold">@rupiah($p->totalNet())</td>
                    <td class="px-5 py-3 text-center">
                        <span class="badge {{ $p->status === 'posted' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
                            {{ $p->status === 'posted' ? 'Posted' : 'Draft' }}
                        </span>
                    </td>
                    <td class="px-5 py-3 flex items-center gap-3">
                        <a href="{{ route('payroll.show', $p) }}" class="text-slate-600 hover:text-slate-800 text-sm">Detail</a>
                        @if($p->isDraft())
                            <a href="{{ route('payroll.edit', $p) }}" class="text-indigo-600 hover:text-indigo-800 text-sm">Edit</a>
                            <form method="POST" action="{{ route('payroll.post', $p) }}"
                                onsubmit="return confirm('Posting payroll ini? Jurnal akuntansi akan otomatis dibuat.')">
                                @csrf
                                <button type="submit" class="text-emerald-600 hover:text-emerald-800 text-sm font-medium">Posting</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="px-5 py-10 text-center text-slate-400">Belum ada data payroll.</td></tr>
            @endforelse
            </tbody>
        </table>
        @if($payrolls->hasPages())
            <div class="px-5 py-3 border-t border-slate-100">{{ $payrolls->links() }}</div>
        @endif
    </div>
</div>
</x-app-layout>
