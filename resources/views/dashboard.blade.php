<x-app-layout>
    <x-slot name="header">Dashboard</x-slot>

    {{-- ROW 1: BIG STAT CARDS --}}
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-5 mb-6">
        <div class="rounded-xl p-5 text-white shadow-lg relative overflow-hidden group" style="background: linear-gradient(135deg, #6366f1, #8b5cf6);">
            <div class="absolute inset-0 bg-white/5 translate-x-full group-hover:translate-x-0 transition-transform duration-500 skew-x-12"></div>
            <div class="flex items-start justify-between relative">
                <div>
                    <p class="text-indigo-200 text-xs font-medium uppercase tracking-wider">Total Aset</p>
                    <p class="text-2xl font-bold mt-1 tracking-tight">@rupiah($totalAssets)</p>
                </div>
                <div class="p-2.5 rounded-lg bg-white/15">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                </div>
            </div>
            <div class="mt-3 flex items-center gap-2 text-indigo-200 text-xs">
                <span>Total kekayaan perusahaan</span>
                @if($balanceSummary && $balanceSummary['is_balanced'])
                <span class="inline-flex items-center gap-0.5 bg-emerald-400/20 text-emerald-200 px-1.5 py-0.5 rounded text-[10px] font-semibold">✓ Balance</span>
                @endif
            </div>
        </div>

        <div class="rounded-xl p-5 text-white shadow-lg relative overflow-hidden group" style="background: linear-gradient(135deg, #10b981, #059669);">
            <div class="absolute inset-0 bg-white/5 translate-x-full group-hover:translate-x-0 transition-transform duration-500 skew-x-12"></div>
            <div class="flex items-start justify-between relative">
                <div>
                    <p class="text-emerald-200 text-xs font-medium uppercase tracking-wider">Laba Bersih</p>
                    <p class="text-2xl font-bold mt-1 tracking-tight">@rupiah($netIncome)</p>
                </div>
                <div class="p-2.5 rounded-lg bg-white/15">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                </div>
            </div>
            <div class="mt-3 flex items-center gap-2 text-emerald-200 text-xs">
                <span>Periode {{ $activePeriod->label ?? '-' }}</span>
                @if($netIncome > 0)
                <span class="inline-flex items-center gap-0.5 bg-white/15 text-emerald-100 px-1.5 py-0.5 rounded text-[10px] font-semibold">▲ Profit</span>
                @elseif($netIncome < 0)
                <span class="inline-flex items-center gap-0.5 bg-red-400/20 text-red-200 px-1.5 py-0.5 rounded text-[10px] font-semibold">▼ Rugi</span>
                @endif
            </div>
        </div>

        <div class="rounded-xl p-5 text-white shadow-lg relative overflow-hidden group" style="background: linear-gradient(135deg, #f59e0b, #d97706);">
            <div class="absolute inset-0 bg-white/5 translate-x-full group-hover:translate-x-0 transition-transform duration-500 skew-x-12"></div>
            <div class="flex items-start justify-between relative">
                <div>
                    <p class="text-amber-200 text-xs font-medium uppercase tracking-wider">Pendapatan</p>
                    <p class="text-2xl font-bold mt-1 tracking-tight">@rupiah($totalRevenue)</p>
                </div>
                <div class="p-2.5 rounded-lg bg-white/15">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2"/></svg>
                </div>
            </div>
            <div class="mt-3 space-y-0.5 text-xs">
                <div class="flex items-center gap-2">
                    <span class="text-amber-200">Laba Kotor:</span>
                    <span class="text-white font-semibold">@rupiah($grossProfit)</span>
                </div>
                @if($totalRevenue > 0)
                <div class="w-full h-1.5 bg-white/20 rounded-full overflow-hidden mt-1">
                    <div class="h-full bg-white/40 rounded-full" style="width: {{ min(100, ($grossProfit / max(1, $totalRevenue)) * 100) }}%"></div>
                </div>
                @endif
            </div>
        </div>

        <div class="rounded-xl p-5 text-white shadow-lg relative overflow-hidden group" style="background: linear-gradient(135deg, #ef4444, #dc2626);">
            <div class="absolute inset-0 bg-white/5 translate-x-full group-hover:translate-x-0 transition-transform duration-500 skew-x-12"></div>
            <div class="flex items-start justify-between relative">
                <div>
                    <p class="text-red-200 text-xs font-medium uppercase tracking-wider">Total Biaya</p>
                    <p class="text-2xl font-bold mt-1 tracking-tight">@rupiah($totalExpenses)</p>
                </div>
                <div class="p-2.5 rounded-lg bg-white/15">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"/></svg>
                </div>
            </div>
            <div class="mt-3 flex items-center gap-2 text-red-200 text-xs">
                <span>Termasuk HPP, Operasi, Bunga & Pajak</span>
                @if($totalRevenue > 0)
                <span class="text-red-200 font-medium">{{ round(($totalExpenses / max(1, $totalRevenue)) * 100) }}% dari pendapatan</span>
                @endif
            </div>
        </div>
    </div>

    {{-- ROW 2: INFO CARDS + RINGKASAN KAS --}}
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-5 mb-6">
        <div class="rounded-xl border border-slate-200 bg-white p-5 card-hover shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-emerald-50 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-xs text-slate-400 font-medium uppercase">Kas & Bank</p>
                    <p class="text-lg font-bold text-slate-800 truncate">@rupiah($cashBalance)</p>
                </div>
            </div>
            @if(count($cashBreakdown) > 0)
            <div class="mt-2.5 pt-2.5 border-t border-slate-100 space-y-1">
                @foreach(array_slice($cashBreakdown, 0, 3) as $cb)
                <div class="flex justify-between text-xs">
                    <span class="text-slate-500 truncate max-w-[140px]">{{ $cb['account']->name }}</span>
                    <span class="font-medium text-slate-700">@rupiah($cb['balance'])</span>
                </div>
                @endforeach
            </div>
            @endif
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-5 card-hover shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-orange-50 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-xs text-slate-400 font-medium uppercase">Piutang (AR)</p>
                    <p class="text-lg font-bold text-slate-800 truncate">@rupiah($arTotal)</p>
                </div>
            </div>
            @if(count($arAging) > 0)
            <div class="mt-2.5 pt-2.5 border-t border-slate-100">
                @php
                    $arAgingSummary = ['0-30' => 0, '31-60' => 0, '61-90' => 0, '>90' => 0];
                    foreach ($arAging as $a) {
                        foreach (($a['aging'] ?? []) as $k => $v) {
                            if (isset($arAgingSummary[$k])) $arAgingSummary[$k] += $v;
                        }
                    }
                @endphp
                @foreach($arAgingSummary as $key => $amount)
                <div class="flex justify-between text-xs py-0.5">
                    <span class="text-slate-500">{{ $key }} Hari</span>
                    <span class="font-medium {{ $amount > 0 && $key == '>90' ? 'text-red-600' : 'text-slate-700' }}">@rupiah($amount)</span>
                </div>
                @endforeach
            </div>
            @endif
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-5 card-hover shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-blue-50 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2"/></svg>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-xs text-slate-400 font-medium uppercase">Hutang (AP)</p>
                    <p class="text-lg font-bold text-slate-800 truncate">@rupiah($apTotal)</p>
                </div>
            </div>
            @if(count($apAging) > 0)
            <div class="mt-2.5 pt-2.5 border-t border-slate-100">
                @php
                    $apAgingSummary = ['0-30' => 0, '31-60' => 0, '61-90' => 0, '>90' => 0];
                    foreach ($apAging as $a) {
                        foreach (($a['aging'] ?? []) as $k => $v) {
                            if (isset($apAgingSummary[$k])) $apAgingSummary[$k] += $v;
                        }
                    }
                @endphp
                @foreach($apAgingSummary as $key => $amount)
                <div class="flex justify-between text-xs py-0.5">
                    <span class="text-slate-500">{{ $key }} Hari</span>
                    <span class="font-medium {{ $amount > 0 && $key == '>90' ? 'text-red-600' : 'text-slate-700' }}">@rupiah($amount)</span>
                </div>
                @endforeach
            </div>
            @endif
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-5 card-hover shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-purple-50 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-xs text-slate-400 font-medium uppercase">Jurnal</p>
                    <p class="text-lg font-bold text-slate-800">
                        <span class="text-amber-500">{{ $draftEntries }}</span>
                        <span class="text-slate-300 font-normal">/</span>
                        <span class="text-emerald-600">{{ $postedEntries }}</span>
                    </p>
                </div>
            </div>
            <div class="mt-2.5 pt-2.5 border-t border-slate-100">
                <div class="flex justify-between text-xs">
                    <span class="text-slate-500">Draft / Posted</span>
                    <span class="text-slate-700 font-medium">
                        @if(($draftEntries + $postedEntries) > 0)
                        {{ round(($postedEntries / max(1, ($draftEntries + $postedEntries))) * 100) }}% posted
                        @else - @endif
                    </span>
                </div>
                @if($dueSales > 0 || $duePurchases > 0)
                <div class="mt-1.5 flex gap-2 text-[10px]">
                    @if($dueSales > 0)<span class="inline-flex items-center gap-0.5 bg-amber-50 text-amber-700 px-1.5 py-0.5 rounded">📄 {{ $dueSales }} invoice jatuh tempo</span>@endif
                    @if($duePurchases > 0)<span class="inline-flex items-center gap-0.5 bg-blue-50 text-blue-700 px-1.5 py-0.5 rounded">📄 {{ $duePurchases }} pembayaran jatuh tempo</span>@endif
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- ROW 3: FINANCIAL RATIOS --}}
    @if(count($ratios) > 0)
    <div class="mb-6">
        <div class="flex items-center gap-2 mb-4">
            <div class="w-1 h-5 bg-indigo-500 rounded-full"></div>
            <h3 class="font-semibold text-slate-800">Rasio Keuangan</h3>
            <span class="text-xs text-slate-400">({{ $activePeriod?->label ?? '-' }})</span>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-4 xl:grid-cols-7 gap-3">
            @foreach([
                'net_profit_margin' => ['NPM', 'bg-indigo-50 text-indigo-700'],
                'current_ratio' => ['Current Ratio', 'bg-emerald-50 text-emerald-700'],
                'quick_ratio' => ['Quick Ratio', 'bg-cyan-50 text-cyan-700'],
                'roe' => ['ROE', 'bg-violet-50 text-violet-700'],
                'debt_to_equity' => ['DER', 'bg-orange-50 text-orange-700'],
                'roi' => ['ROI', 'bg-blue-50 text-blue-700'],
                'absolute_liquidity_ratio' => ['Kas Ratio', 'bg-rose-50 text-rose-700'],
            ] as $key => [$label, $class])
            @if(isset($ratios[$key]))
            @php $r = $ratios[$key]; @endphp
            <div class="rounded-xl border border-slate-200 bg-white p-3.5 card-hover shadow-sm hover:shadow-md transition-shadow text-center">
                <p class="text-[10px] text-slate-400 font-medium uppercase tracking-wider mb-1">{{ $label }}</p>
                <p class="text-lg font-bold {{ explode(' ', $class)[1] }}">{{ number_format($r['value'], 2) }}<span class="text-xs font-normal text-slate-400">{{ $r['unit'] }}</span></p>
                <p class="text-[9px] text-slate-400 mt-0.5 truncate" title="{{ $r['formula'] }}">{{ $r['formula'] }}</p>
            </div>
            @endif
            @endforeach
        </div>
    </div>
    @endif

    {{-- ROW 4: CHART + FINANCIAL SUMMARY --}}
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-5 mb-6">
        {{-- CHART --}}
        <div class="xl:col-span-2 rounded-xl border border-slate-200 bg-white p-5 card-hover shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="font-semibold text-slate-800">Tren Pendapatan & Biaya</h3>
                    <p class="text-xs text-slate-400 mt-0.5">6 periode terakhir</p>
                </div>
                <div class="flex items-center gap-3 text-xs">
                    <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-indigo-500"></span>Pendapatan</span>
                    <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-rose-400"></span>Biaya</span>
                </div>
            </div>
            @if(count($monthlyLabels) > 0)
            <div class="relative" style="height: 240px;">
                <canvas id="revenueChart"></canvas>
            </div>
            @else
            <div class="flex items-center justify-center h-[240px] text-slate-300 text-sm">Belum ada data periode</div>
            @endif
        </div>

        {{-- SIDEBAR SUMMARY --}}
        <div class="space-y-4">
            <div class="rounded-xl border border-slate-200 bg-white p-5 card-hover shadow-sm">
                <h4 class="font-semibold text-slate-800 text-sm mb-3">Ringkasan Neraca</h4>
                <div class="space-y-2.5">
                    @foreach([
                        ['Total Aset', 'rupiah' => $totalAssets, 'color' => 'text-indigo-600'],
                        ['Total Kewajiban & Modal', 'rupiah' => $totalLiabilitiesEquity, 'color' => 'text-slate-800'],
                        ['Piutang (AR)', 'rupiah' => $arTotal, 'color' => 'text-orange-600'],
                        ['Hutang (AP)', 'rupiah' => $apTotal, 'color' => 'text-blue-600'],
                        ['Kas & Bank', 'rupiah' => $cashBalance, 'color' => 'text-emerald-600'],
                    ] as $item)
                    <div class="flex items-center justify-between py-1.5 border-b border-slate-100 last:border-0">
                        <span class="text-sm text-slate-600">{{ $item[0] }}</span>
                        <span class="text-sm font-semibold {{ $item['color'] }}">@rupiah($item['rupiah'])</span>
                    </div>
                    @endforeach
                </div>
            </div>

            @if($balanceSummary)
            <div class="rounded-xl border border-slate-200 bg-white p-5 card-hover shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <h4 class="font-semibold text-slate-800 text-sm">Cek Balance</h4>
                    <span class="badge {{ $balanceSummary['is_balanced'] ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-700' }}">
                        {{ $balanceSummary['is_balanced'] ? '✓ Balance' : '✗ Tidak Balance' }}
                    </span>
                </div>
                <div class="space-y-1.5 text-xs">
                    <div class="flex justify-between py-1 border-b border-slate-50">
                        <span class="text-slate-500">Total Aktiva</span>
                        <span class="font-semibold text-slate-700">@rupiah($balanceSummary['total_aktiva'])</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-slate-50">
                        <span class="text-slate-500">Total Kewajiban</span>
                        <span class="font-semibold text-slate-700">@rupiah($balanceSummary['total_kewajiban'])</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-slate-50">
                        <span class="text-slate-500">Total Modal</span>
                        <span class="font-semibold text-slate-700">@rupiah($balanceSummary['total_modal'])</span>
                    </div>
                    @if($balanceSummary['is_balanced'])
                    <p class="text-emerald-600 font-medium pt-1">✅ Aktiva = Kewajiban & Modal</p>
                    @else
                    <p class="text-red-600 font-medium pt-1">⚠️ Selisih: @rupiah($balanceSummary['difference'])</p>
                    @endif
                </div>
            </div>
            @endif
        </div>
    </div>

    {{-- ROW 5: QUICK STATS --}}
    <div class="mb-6">
        <div class="flex items-center gap-2 mb-4">
            <div class="w-1 h-5 bg-slate-400 rounded-full"></div>
            <h3 class="font-semibold text-slate-800">Ringkasan Data</h3>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            @foreach([
                ['Total Akun', number_format($totalAccounts, 0, ',', '.'), 'bg-indigo-50 text-indigo-600', 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'],
                ['Customer Aktif', $totalCustomers, 'bg-emerald-50 text-emerald-600', 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2'],
                ['Vendor Aktif', $totalVendors, 'bg-amber-50 text-amber-600', 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3'],
                ['Invoice Aktif', "$totalSalesInvoices Sales / $totalPurchaseInvoices Purch", 'bg-blue-50 text-blue-600', 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586'],
            ] as $stat)
            <div class="rounded-xl border border-slate-200 bg-white p-4 card-hover shadow-sm hover:shadow-md transition-shadow">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg {{ \Illuminate\Support\Str::before($stat[2], ' ') }} flex items-center justify-center">
                        <svg class="w-4 h-4 {{ $stat[2] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $stat[3] }}"/></svg>
                    </div>
                    <div class="min-w-0"><p class="text-xs text-slate-400">{{ $stat[0] }}</p><p class="text-lg font-bold text-slate-800">{{ $stat[1] }}</p></div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- ROW 6: RECENT TRANSACTIONS --}}
    <div class="rounded-xl border border-slate-200 bg-white shadow-sm mb-6 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <div class="w-1 h-5 bg-indigo-500 rounded-full"></div>
                <h3 class="font-semibold text-slate-800">Transaksi Terbaru</h3>
            </div>
            <a href="{{ route('journal-entries.index') }}" class="text-xs font-medium text-indigo-600 hover:text-indigo-700 transition-colors">Lihat Semua →</a>
        </div>
        @if($recentEntries->isEmpty())
            <div class="p-8 text-center text-slate-400 text-sm">Belum ada transaksi. Mulai dengan membuat jurnal baru.</div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead><tr class="bg-slate-50/50 border-b border-slate-100">
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Tanggal</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">No. Bukti</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Keterangan</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Periode</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-slate-500 uppercase tracking-wider">Total</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-slate-500 uppercase tracking-wider">Status</th>
                </tr></thead>
                <tbody class="divide-y divide-slate-50">
                    @foreach($recentEntries as $entry)
                    @php
                        $totalDebit = $entry->lines->sum('debit');
                    @endphp
                    <tr class="hover:bg-slate-50/80 transition-colors">
                        <td class="px-6 py-3.5 text-slate-700 whitespace-nowrap">{{ $entry->entry_date->format('d/m/Y') }}</td>
                        <td class="px-6 py-3.5 font-medium text-slate-800 whitespace-nowrap">{{ $entry->reference_no }}</td>
                        <td class="px-6 py-3.5 text-slate-600 max-w-[200px] truncate" title="{{ $entry->description }}">{{ $entry->description }}</td>
                        <td class="px-6 py-3.5 text-slate-600 whitespace-nowrap">{{ $entry->accountingPeriod->label ?? '-' }}</td>
                        <td class="px-6 py-3.5 text-right font-medium text-slate-700 whitespace-nowrap tabular-nums">@rupiah($totalDebit)</td>
                        <td class="px-6 py-3.5 text-center whitespace-nowrap">
                            @if($entry->isDraft())
                            <span class="badge bg-amber-50 text-amber-700 border border-amber-200/50">Draft</span>
                            @else
                            <span class="badge bg-emerald-50 text-emerald-700 border border-emerald-200/50">Posted</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('revenueChart')?.getContext('2d');
        if (!ctx) return;

        const revenueData = @json($monthlyRevenue);
        const expenseData = @json($monthlyExpense);
        const labels = @json($monthlyLabels);

        if (!revenueData.length && !expenseData.length) return;

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Pendapatan',
                        data: revenueData,
                        backgroundColor: 'rgba(99,102,241,0.75)',
                        borderColor: '#6366f1',
                        borderWidth: 1,
                        borderRadius: 4,
                    },
                    {
                        label: 'Biaya',
                        data: expenseData,
                        backgroundColor: 'rgba(244,63,94,0.7)',
                        borderColor: '#f43f5e',
                        borderWidth: 1,
                        borderRadius: 4,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#1e293b',
                        titleColor: '#f1f5f9',
                        bodyColor: '#e2e8f0',
                        padding: 10,
                        cornerRadius: 8,
                        callbacks: {
                            label: (ctx) => ctx.dataset.label + ': Rp ' + Number(ctx.raw).toLocaleString('id-ID')
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(0,0,0,0.04)' },
                        ticks: {
                            callback: (v) => {
                                if (v >= 1000000000) return 'Rp' + (v / 1000000000).toFixed(1) + 'M';
                                if (v >= 1000000) return 'Rp' + (v / 1000000).toFixed(0) + 'jt';
                                if (v >= 1000) return 'Rp' + (v / 1000).toFixed(0) + 'rb';
                                return 'Rp' + v;
                            }
                        }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { font: { size: 10 } }
                    }
                }
            }
        });
    });
    </script>
    @endpush
</x-app-layout>
