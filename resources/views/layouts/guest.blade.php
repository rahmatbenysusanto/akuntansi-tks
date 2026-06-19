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
        .login-gradient { background: linear-gradient(135deg, #1e1b4b 0%, #312e81 50%, #1e40af 100%); }
        .login-card { background: rgba(255,255,255,0.95); backdrop-filter: blur(20px); }
        .input-modern { transition: all 0.2s; border: 1.5px solid #e2e8f0; }
        .input-modern:focus { border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99,102,241,0.15); outline: none; }
        .btn-primary { background: linear-gradient(135deg, #6366f1, #4f46e5); transition: all 0.2s; }
        .btn-primary:hover { transform: translateY(-1px); box-shadow: 0 8px 20px rgba(99,102,241,0.4); }
    </style>
</head>
<body class="font-['Inter'] antialiased">
    <div class="min-h-screen flex login-gradient">
        {{-- Left Panel --}}
        <div class="hidden lg:flex lg:w-1/2 flex-col justify-center px-16 text-white">
            <div class="max-w-lg">
                <div class="w-14 h-14 rounded-2xl bg-white/10 flex items-center justify-center mb-8">
                    <svg class="w-7 h-7 text-indigo-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <h1 class="text-3xl font-bold mb-4">Sistem Akuntansi</h1>
                <p class="text-indigo-200/80 text-sm leading-relaxed mb-8">
                    PT. Transkargo Solusindo — Kelola keuangan perusahaan dengan sistem akuntansi digital yang terintegrasi, akurat, dan profesional.
                </p>
                <div class="space-y-4">
                    <div class="flex items-center gap-3 text-sm text-indigo-200/70">
                        <svg class="w-5 h-5 text-indigo-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Double-entry accounting — semua transaksi balance otomatis</span>
                    </div>
                    <div class="flex items-center gap-3 text-sm text-indigo-200/70">
                        <svg class="w-5 h-5 text-indigo-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Laporan keuangan real-time: Laba Rugi, Neraca, Arus Kas</span>
                    </div>
                    <div class="flex items-center gap-3 text-sm text-indigo-200/70">
                        <svg class="w-5 h-5 text-indigo-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Multi-perusahaan, multi-mata uang, audit trail lengkap</span>
                    </div>
                </div>
                <div class="mt-12 pt-8 border-t border-white/10">
                    <p class="text-xs text-indigo-300/50">© {{ date('Y') }} PT. Transkargo Solusindo. All rights reserved.</p>
                </div>
            </div>
        </div>

        {{-- Right Panel --}}
        <div class="w-full lg:w-1/2 flex items-center justify-center px-6">
            <div class="w-full max-w-md">
                <div class="login-card rounded-2xl shadow-2xl p-8 sm:p-10">
                    <div class="text-center mb-8">
                        <div class="w-12 h-12 rounded-xl bg-indigo-50 flex items-center justify-center mx-auto mb-4">
                            <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <h2 class="text-xl font-bold text-slate-800">Selamat Datang</h2>
                        <p class="text-sm text-slate-500 mt-1">Masuk ke sistem akuntansi Transkargo</p>
                    </div>

                    {{ $slot }}

                    <p class="mt-6 text-center text-[11px] text-slate-400 lg:hidden">
                        © {{ date('Y') }} PT. Transkargo Solusindo
                    </p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
