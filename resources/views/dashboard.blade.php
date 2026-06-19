<x-app-layout>
    <x-slot name="header">Dashboard</x-slot>

    {{-- Gradient Stat Cards Row 1 --}}
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-5 mb-6">
        <div class="rounded-xl p-5 text-white" style="background: linear-gradient(135deg, #6366f1, #8b5cf6);">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-indigo-200 text-xs font-medium uppercase tracking-wider">Total Aset</p>
                    <p class="text-2xl font-bold mt-1">{{ number_format($totalAssets, 0, ',', '.') }}</p>
                </div>
                <div class="p-2.5 rounded-lg bg-white/15">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 2v20"/></svg>
                </div>
            </div>
            <div class="mt-2 text-indigo-200 text-xs">Total kekayaan perusahaan</div>
        </div>

        <div class="rounded-xl p-5 text-white" style="background: linear-gradient(135deg, #10b981, #059669);">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-emerald-200 text-xs font-medium uppercase tracking-wider">Laba Bersih</p>
                    <p class="text-2xl font-bold mt-1">{{ number_format($netIncome, 0, ',', '.') }}</p>
                </div>
                <div class="p-2.5 rounded-lg bg-white/15">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                </div>
            </div>
            <div class="mt-2 text-emerald-200 text-xs">Periode {{ $activePeriod->label ?? '-' }}</div>
        </div>

        <div class="rounded-xl p-5 text-white" style="background: linear-gradient(135deg, #f59e0b, #d97706);">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-amber-200 text-xs font-medium uppercase tracking-wider">Pendapatan</p>
                    <p class="text-2xl font-bold mt-1">{{ number_format($totalRevenue, 0, ',', '.') }}</p>
                </div>
                <div class="p-2.5 rounded-lg bg-white/15">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2"/></svg>
                </div>
            </div>
            <div class="mt-2 text-amber-200 text-xs">Laba Kotor: {{ number_format($grossProfit, 0, ',', '.') }}</div>
        </div>

        <div class="rounded-xl p-5 text-white" style="background: linear-gradient(135deg, #ef4444, #dc2626);">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-red-200 text-xs font-medium uppercase tracking-wider">Biaya</p>
                    <p class="text-2xl font-bold mt-1">{{ number_format($totalExpenses, 0, ',', '.') }}</p>
                </div>
                <div class="p-2.5 rounded-lg bg-white/15">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"/></svg>
                </div>
            </div>
            <div class="mt-2 text-red-200 text-xs">HPP + Biaya Operasional</div>
        </div>
    </div>

    {{-- Row 2: Kas, AR, AP, Jurnal --}}
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-5 mb-6">
        <div class="rounded-xl border border-slate-200 bg-white p-5 card-hover shadow-sm">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-emerald-50 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                </div>
                <div class="min-w-0"><p class="text-xs text-slate-400 font-medium uppercase">Kas & Bank</p><p class="text-lg font-bold text-slate-800 truncate">{{ number_format($cashBalance, 0, ',', '.') }}</p></div>
            </div>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-5 card-hover shadow-sm">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-orange-50 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div class="min-w-0"><p class="text-xs text-slate-400 font-medium uppercase">Piutang (AR)</p><p class="text-lg font-bold text-slate-800 truncate">{{ number_format($arTotal, 0, ',', '.') }}</p></div>
            </div>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-5 card-hover shadow-sm">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-blue-50 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2"/></svg>
                </div>
                <div class="min-w-0"><p class="text-xs text-slate-400 font-medium uppercase">Hutang (AP)</p><p class="text-lg font-bold text-slate-800 truncate">{{ number_format($apTotal, 0, ',', '.') }}</p></div>
            </div>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-5 card-hover shadow-sm">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-purple-50 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                </div>
                <div class="min-w-0"><p class="text-xs text-slate-400 font-medium uppercase">Jurnal (Draft/Posted)</p><p class="text-lg font-bold text-slate-800"><span class="text-amber-500">{{ $draftEntries }}</span><span class="text-slate-300">/</span><span class="text-emerald-600">{{ $postedEntries }}</span></p></div>
            </div>
        </div>
    </div>

    {{-- Row 3: Chart + Ringkasan --}}
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-5 mb-6">
        {{-- Chart --}}
        <div class="xl:col-span-2 rounded-xl border border-slate-200 bg-white p-5 card-hover shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="font-semibold text-slate-800">Tren Pendapatan & Biaya</h3>
                    <p class="text-xs text-slate-400 mt-0.5">6 bulan terakhir</p>
                </div>
                <div class="flex items-center gap-3 text-xs">
                    <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-indigo-500"></span>Pendapatan</span>
                    <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-rose-400"></span>Biaya</span>
                </div>
            </div>
            <canvas id="revenueChart" height="220"></canvas>
        </div>

        {{-- Ringkasan Sidebar --}}
        <div class="space-y-4">
            <div class="rounded-xl border border-slate-200 bg-white p-5 card-hover shadow-sm">
                <h4 class="font-semibold text-slate-800 text-sm mb-3">Ringkasan Keuangan</h4>
                <div class="space-y-2.5">
                    @foreach([
                        ['Total Aset', number_format($totalAssets, 0, ',', '.'), 'text-slate-800'],
                        ['Total Kewajiban & Modal', number_format($totalLiabilitiesEquity, 0, ',', '.'), 'text-slate-800'],
                        ['Piutang (AR)', number_format($arTotal, 0, ',', '.'), 'text-orange-600'],
                        ['Hutang (AP)', number_format($apTotal, 0, ',', '.'), 'text-blue-600'],
                        ['Kas & Bank', number_format($cashBalance, 0, ',', '.'), 'text-emerald-600'],
                    ] as $item)
                    <div class="flex items-center justify-between py-1.5 border-b border-slate-100 last:border-0">
                        <span class="text-sm text-slate-600">{{ $item[0] }}</span>
                        <span class="text-sm font-semibold {{ $item[2] }}">{{ $item[1] }}</span>
                    </div>
                    @endforeach
                </div>
            </div>

            @if($balanceSummary)
            <div class="rounded-xl border border-slate-200 bg-white p-5 card-hover shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <h4 class="font-semibold text-slate-800 text-sm">Cek Balance</h4>
                    <span class="badge {{ $balanceSummary['is_balanced'] ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-700' }}">
                        {{ $balanceSummary['is_balanced'] ? '✓ Balance' : '✗ Error' }}
                    </span>
                </div>
                @if($balanceSummary['is_balanced'])
                <p class="text-xs text-emerald-600">✅ Aktiva = Kewajiban & Modal</p>
                @else
                <p class="text-xs text-red-600">⚠️ Selisih: {{ number_format($balanceSummary['difference'], 0, ',', '.') }}</p>
                @endif
            </div>
            @endif
        </div>
    </div>

    {{-- Row 4: Mini Stats --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        @foreach([
            ['Total Akun', number_format($totalAccounts, 0, ',', '.'), 'bg-indigo-50 text-indigo-600', 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'],
            ['Customer', $totalCustomers, 'bg-emerald-50 text-emerald-600', 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2'],
            ['Vendor', $totalVendors, 'bg-amber-50 text-amber-600', 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3'],
            ['Invoice (Sales/Purch)', "$totalSalesInvoices/$totalPurchaseInvoices", 'bg-blue-50 text-blue-600', 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586']
        ] as $stat)
        <div class="rounded-xl border border-slate-200 bg-white p-4 card-hover shadow-sm">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-lg {{ explode(' ', $stat[2])[0] }} flex items-center justify-center">
                    <svg class="w-4 h-4 {{ $stat[2] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $stat[3] }}"/></svg>
                </div>
                <div class="min-w-0"><p class="text-xs text-slate-400">{{ $stat[0] }}</p><p class="text-lg font-bold text-slate-800">{{ $stat[1] }}</p></div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Recent Journals --}}
    <div class="rounded-xl border border-slate-200 bg-white card-hover shadow-sm mb-6">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <h3 class="font-semibold text-slate-800">Transaksi Terbaru</h3>
            <a href="{{ route('journal-entries.index') }}" class="text-xs font-medium text-indigo-600 hover:text-indigo-700">Lihat Semua →</a>
        </div>
        @if($recentEntries->isEmpty())
            <div class="p-8 text-center text-slate-400 text-sm">Belum ada transaksi.</div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm table-modern">
                <thead><tr class="bg-slate-50/50">
                    <th class="px-6 py-3 text-left">Tanggal</th>
                    <th class="px-6 py-3 text-left">No. Bukti</th>
                    <th class="px-6 py-3 text-left">Keterangan</th>
                    <th class="px-6 py-3 text-left">Periode</th>
                    <th class="px-6 py-3 text-left">Status</th>
                </tr></thead>
                <tbody>
                    @foreach($recentEntries as $entry)
                    <tr class="hover:bg-slate-50/80 transition-colors">
                        <td class="px-6 py-3 text-slate-700">{{ $entry->entry_date->format('d/m/Y') }}</td>
                        <td class="px-6 py-3 font-medium text-slate-800">{{ $entry->reference_no }}</td>
                        <td class="px-6 py-3 text-slate-600 max-w-xs truncate">{{ $entry->description }}</td>
                        <td class="px-6 py-3 text-slate-600">{{ $entry->accountingPeriod->label }}</td>
                        <td class="px-6 py-3">
                            @if($entry->isDraft())<span class="badge bg-amber-50 text-amber-700">Draft</span>
                            @else<span class="badge bg-emerald-50 text-emerald-700">Posted</span>@endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</x-app-layout>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('revenueChart')?.getContext('2d');
    if (!ctx) return;
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: @json($monthlyLabels),
            datasets: [
                { label: 'Pendapatan', data: @json($monthlyRevenue), backgroundColor: 'rgba(99,102,241,0.85)', borderColor: '#6366f1', borderWidth: 1, borderRadius: 4 },
                { label: 'Biaya', data: @json($monthlyExpense), backgroundColor: 'rgba(244,63,94,0.75)', borderColor: '#f43f5e', borderWidth: 1, borderRadius: 4 }
            ]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: { callbacks: { label: (ctx) => ctx.dataset.label + ': Rp ' + Number(ctx.raw).toLocaleString('id-ID') } }
            },
            scales: {
                y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.04)' }, ticks: { callback: (v) => 'Rp' + (v/1000000).toFixed(0) + 'jt' } },
                x: { grid: { display: false } }
            }
        }
    });
});
</script>
@endpush
