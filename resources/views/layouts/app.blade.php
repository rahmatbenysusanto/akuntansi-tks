<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Transkargo Accounting') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        [x-cloak] { display: none !important; }
        .scrollbar-thin::-webkit-scrollbar { width: 6px; }
        .sidebar-nav::-webkit-scrollbar { width: 6px; }
        .sidebar-nav::-webkit-scrollbar-track { background: rgba(255,255,255,0.05); }
        .sidebar-nav::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.2); border-radius: 3px; }
        .sidebar-nav::-webkit-scrollbar-thumb:hover { background: rgba(255,255,255,0.35); }
        .scrollbar-thin::-webkit-scrollbar-track { background: transparent; }
        .scrollbar-thin::-webkit-scrollbar-thumb { background: #94a3b8; border-radius: 2px; }
        .sidebar-gradient { background: linear-gradient(180deg, #1e1b4b 0%, #312e81 30%, #1e40af 70%, #1e3a5f 100%); }
        .card-hover { transition: all 0.2s ease; }
        .card-hover:hover { transform: translateY(-1px); box-shadow: 0 8px 25px rgba(0,0,0,0.08); }
        .nav-link-active { background: rgba(255,255,255,0.12) !important; border-left: 3px solid #818cf8; }
        .nav-link { transition: all 0.15s ease; border-left: 3px solid transparent; }
        .nav-link:hover { background: rgba(255,255,255,0.08); }
        .btn-primary { background: linear-gradient(135deg, #6366f1, #4f46e5); transition: all 0.2s; }
        .btn-primary:hover { transform: translateY(-0.5px); box-shadow: 0 4px 12px rgba(99,102,241,0.4); }
        .btn-success { background: linear-gradient(135deg, #22c55e, #16a34a); }
        .btn-success:hover { transform: translateY(-0.5px); box-shadow: 0 4px 12px rgba(34,197,94,0.4); }
        .btn-danger { background: linear-gradient(135deg, #ef4444, #dc2626); }
        .btn-danger:hover { transform: translateY(-0.5px); box-shadow: 0 4px 12px rgba(239,68,68,0.4); }
        .input-modern { transition: all 0.2s; border: 1.5px solid #e2e8f0; }
        .input-modern:focus { border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99,102,241,0.1); outline: none; }
        .badge { display: inline-flex; align-items: center; padding: 0.125rem 0.625rem; border-radius: 9999px; font-size: 0.7rem; font-weight: 600; }
        .glass { background: rgba(255,255,255,0.8); backdrop-filter: blur(12px); }
    </style>
</head>
<body class="font-['Inter'] antialiased bg-slate-50 h-screen overflow-hidden flex">
    {{-- SIDEBAR --}}
    <aside class="w-64 sidebar-gradient text-white flex flex-col shrink-0 shadow-xl shadow-indigo-500/10 h-full">
        <div class="px-5 py-5 border-b border-white/10 shrink-0 flex items-center gap-3">
            <div class="w-9 h-9 rounded-lg bg-indigo-400/20 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-indigo-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div class="min-w-0">
                <span class="font-semibold text-sm text-white block truncate">Transkargo</span>
                <span class="text-[10px] text-indigo-300/70 block -mt-0.5 truncate">Accounting System</span>
            </div>
        </div>
        <nav class="px-3 py-4 space-y-0.5 text-sm sidebar-nav" style="overflow-y: scroll; height: calc(100vh - 128px);">
            <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" class="nav-link text-white/80 hover:text-white"><svg class="w-4 h-4 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>Dashboard</x-nav-link>
            <div class="text-[10px] uppercase tracking-widest text-indigo-300/50 font-semibold px-3 pt-5 pb-1.5">Master Data</div>
            <x-nav-link :href="route('customers.index')" class="nav-link text-white/80 hover:text-white"><svg class="w-4 h-4 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>Customer</x-nav-link>
            <x-nav-link :href="route('vendors.index')" class="nav-link text-white/80 hover:text-white"><svg class="w-4 h-4 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>Vendor</x-nav-link>
            <x-nav-link :href="route('accounts.index')" :active="request()->routeIs('accounts.*')" class="nav-link text-white/80 hover:text-white"><svg class="w-4 h-4 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>Chart of Account</x-nav-link>
            <x-nav-link :href="route('accounting-periods.index')" :active="request()->routeIs('accounting-periods.*')" class="nav-link text-white/80 hover:text-white"><svg class="w-4 h-4 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>Periode Akuntansi</x-nav-link>
            <x-nav-link :href="route('opening-balances.index')" :active="request()->routeIs('opening-balances.*')" class="nav-link text-white/80 hover:text-white"><svg class="w-4 h-4 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>Saldo Awal</x-nav-link>
            <div class="text-[10px] uppercase tracking-widest text-indigo-300/50 font-semibold px-3 pt-5 pb-1.5">Transaksi</div>
            <x-nav-link :href="route('journal-entries.index')" class="nav-link text-white/80 hover:text-white"><svg class="w-4 h-4 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>Jurnal Umum</x-nav-link>
            <x-nav-link :href="route('sales.index')" class="nav-link text-white/80 hover:text-white"><svg class="w-4 h-4 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>Sales Invoice</x-nav-link>
            <x-nav-link :href="route('purchases.index')" class="nav-link text-white/80 hover:text-white"><svg class="w-4 h-4 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/></svg>Purchase Invoice</x-nav-link>
            <div class="text-[10px] uppercase tracking-widest text-indigo-300/50 font-semibold px-3 pt-5 pb-1.5">Lainnya</div>
            <x-nav-link :href="route('fixed-assets.index')" class="nav-link text-white/80 hover:text-white"><svg class="w-4 h-4 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>Aset Tetap</x-nav-link>
            <x-nav-link :href="route('arap.kartu-piutang')" class="nav-link text-white/80 hover:text-white"><svg class="w-4 h-4 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>Kartu Piutang</x-nav-link>
            <x-nav-link :href="route('arap.kartu-hutang')" class="nav-link text-white/80 hover:text-white"><svg class="w-4 h-4 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2"/></svg>Kartu Hutang</x-nav-link>
            <x-nav-link :href="route('items.index')" class="nav-link text-white/80 hover:text-white"><svg class="w-4 h-4 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>Inventory</x-nav-link>
            <x-nav-link :href="route('loans.index')" class="nav-link text-white/80 hover:text-white"><svg class="w-4 h-4 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>Cicilan</x-nav-link>
            <x-nav-link :href="route('employees.index')" class="nav-link text-white/80 hover:text-white"><svg class="w-4 h-4 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>Data Karyawan</x-nav-link>
            <x-nav-link :href="route('cash-advances.index')" class="nav-link text-white/80 hover:text-white"><svg class="w-4 h-4 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2"/></svg>Kasbon</x-nav-link>
            <div class="text-[10px] uppercase tracking-widest text-indigo-300/50 font-semibold px-3 pt-5 pb-1.5">Pajak</div>
            <x-nav-link :href="route('tax.index')" class="nav-link text-white/80 hover:text-white"><svg class="w-4 h-4 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01"/></svg>Transaksi Pajak</x-nav-link>
            <x-nav-link :href="route('tax.ppn')" class="nav-link text-white/80 hover:text-white"><svg class="w-4 h-4 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>Rekap PPN</x-nav-link>
            <x-nav-link :href="route('exchange-rates.index')" class="nav-link text-white/80 hover:text-white"><svg class="w-4 h-4 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>Kurs Valas</x-nav-link>
            <div class="text-[10px] uppercase tracking-widest text-indigo-300/50 font-semibold px-3 pt-5 pb-1.5">Laporan</div>
            <x-nav-link :href="route('reports.general-ledger')" class="nav-link text-white/80 hover:text-white"><svg class="w-4 h-4 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>Buku Besar</x-nav-link>
            <x-nav-link :href="route('reports.trial-balance')" class="nav-link text-white/80 hover:text-white"><svg class="w-4 h-4 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"/></svg>Neraca Lajur</x-nav-link>
            <x-nav-link :href="route('reports.income-statement')" class="nav-link text-white/80 hover:text-white"><svg class="w-4 h-4 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>Laba Rugi</x-nav-link>
            <x-nav-link :href="route('reports.balance-sheet')" class="nav-link text-white/80 hover:text-white"><svg class="w-4 h-4 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>Neraca</x-nav-link>
            <x-nav-link :href="route('reports.financial-highlight')" class="nav-link text-white/80 hover:text-white"><svg class="w-4 h-4 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"/></svg>Financial Highlight</x-nav-link>
            @if(auth()->user()?->isAdmin())
                <div class="text-[10px] uppercase tracking-widest text-indigo-300/50 font-semibold px-3 pt-5 pb-1.5">Pengaturan</div>
                <x-nav-link :href="route('users.index')" class="nav-link text-white/80 hover:text-white"><svg class="w-4 h-4 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>User Management</x-nav-link>
                <x-nav-link :href="route('activity-logs.index')" class="nav-link text-white/80 hover:text-white"><svg class="w-4 h-4 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>Riwayat Aktivitas</x-nav-link>
            @endif
        </nav>
        <div class="border-t border-white/10 p-4 shrink-0">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-indigo-400/20 flex items-center justify-center text-xs font-semibold text-indigo-300 shrink-0">{{ substr(auth()->user()?->name ?? 'U', 0, 1) }}</div>
                <div class="flex-1 min-w-0"><p class="text-sm font-medium text-white truncate">{{ auth()->user()?->name }}</p><span class="text-[10px] text-indigo-300/60 uppercase">{{ auth()->user()?->role }}</span></div>
                <form method="POST" action="{{ route('logout') }}">@csrf<button class="text-indigo-300/60 hover:text-white shrink-0"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg></button></form>
            </div>
        </div>
    </aside>

    {{-- MAIN --}}
    <div class="flex-1 flex flex-col">
        <header class="bg-white/80 glass border-b border-slate-200/60 shrink-0">
            <div class="px-6 py-3 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-1.5 h-1.5 rounded-full bg-emerald-400"></div>
                    <h1 class="text-lg font-semibold text-slate-800">{{ $header ?? 'Dashboard' }}</h1>
                </div>
            </div>
        </header>
        <main class="p-6 lg:p-8" style="overflow-y: auto; height: calc(100vh - 57px);">
            @if (session('success'))
                <div class="mb-6 px-5 py-3.5 bg-emerald-50 border border-emerald-200/60 text-emerald-700 rounded-xl text-sm flex items-center gap-2.5 shadow-sm">
                    <svg class="w-5 h-5 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    {{ session('success') }}
                </div>
            @endif
            @if (session('error'))
                <div class="mb-6 px-5 py-3.5 bg-red-50 border border-red-200/60 text-red-700 rounded-xl text-sm flex items-center gap-2.5 shadow-sm">
                    <svg class="w-5 h-5 text-red-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    {{ session('error') }}
                </div>
            @endif
            {{ $slot }}
        </main>
    </div>

    @stack('scripts')
</body>
</html>
