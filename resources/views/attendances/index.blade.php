<x-app-layout>
<x-slot name="header">Absensi Karyawan</x-slot>

<div class="space-y-4">

    {{-- Flash messages --}}
    @if(session('error'))
        <div class="px-4 py-3 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm">
            {{ session('error') }}
        </div>
    @endif

    {{-- Kartu Clock In / Clock Out --}}
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
        <h3 class="text-sm font-semibold text-slate-700 mb-3">Absen Hari Ini — {{ now()->isoFormat('dddd, D MMMM Y') }}</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

            {{-- Clock In --}}
            <form method="POST" action="{{ route('attendances.clock-in') }}">
                @csrf
                <div class="flex items-end gap-3">
                    <div class="flex-1">
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Pilih Karyawan (Clock In)</label>
                        <select name="employee_id" class="w-full rounded-lg input-modern text-sm account-select" required>
                            <option value="">-- Pilih Karyawan --</option>
                            @foreach($employees as $e)
                                <option value="{{ $e->id }}"
                                    {{ $todayAttendances->has($e->id) ? 'disabled' : '' }}>
                                    {{ $e->name }} ({{ $e->employee_no }})
                                    {{ $todayAttendances->has($e->id) ? '— sudah absen' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit"
                        class="px-4 py-2.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold transition whitespace-nowrap">
                        ⏰ Clock In
                    </button>
                </div>
            </form>

            {{-- Clock Out --}}
            <form method="POST" action="{{ route('attendances.clock-out') }}">
                @csrf
                <div class="flex items-end gap-3">
                    <div class="flex-1">
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Pilih Karyawan (Clock Out)</label>
                        <select name="employee_id" class="w-full rounded-lg input-modern text-sm account-select" required>
                            <option value="">-- Pilih Karyawan --</option>
                            @foreach($employees as $e)
                                @php $todayAtt = $todayAttendances->get($e->id); @endphp
                                @if($todayAtt && !$todayAtt->clock_out)
                                    <option value="{{ $e->id }}">
                                        {{ $e->name }} — masuk {{ $todayAtt->clock_in ?? '-' }}
                                    </option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                    <button type="submit"
                        class="px-4 py-2.5 rounded-lg bg-orange-500 hover:bg-orange-600 text-white text-sm font-semibold transition whitespace-nowrap">
                        🏠 Clock Out
                    </button>
                </div>
            </form>
        </div>

        {{-- Rekap hari ini --}}
        @if($todayAttendances->count() > 0)
        <div class="mt-4 pt-4 border-t border-slate-100">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Sudah Absen Hari Ini</p>
            <div class="flex flex-wrap gap-2">
                @foreach($todayAttendances as $ta)
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium {{ $ta->statusColor() }}">
                        <span>{{ $ta->employee?->name }}</span>
                        <span class="text-[10px] opacity-70">
                            {{ $ta->clock_in ?? '?' }} → {{ $ta->clock_out ?? '...' }}
                        </span>
                    </span>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    {{-- Filter & List --}}
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm">
        <div class="p-4 border-b border-slate-100 flex flex-wrap gap-3 items-end justify-between">
            <form method="GET" action="{{ route('attendances.index') }}" class="flex flex-wrap gap-3 items-end">
                {{-- Filter Bulan --}}
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Bulan</label>
                    <select name="month" class="rounded-lg input-modern text-sm py-2">
                        @for($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::create()->month($m)->isoFormat('MMMM') }}
                            </option>
                        @endfor
                    </select>
                </div>
                {{-- Filter Tahun --}}
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Tahun</label>
                    <select name="year" class="rounded-lg input-modern text-sm py-2">
                        @for($y = now()->year; $y >= now()->year - 3; $y--)
                            <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>
                {{-- Filter Karyawan --}}
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Karyawan</label>
                    <select name="employee_id" class="rounded-lg input-modern text-sm py-2 account-select">
                        <option value="">Semua Karyawan</option>
                        @foreach($employees as $e)
                            <option value="{{ $e->id }}" {{ request('employee_id') == $e->id ? 'selected' : '' }}>
                                {{ $e->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                {{-- Filter Status --}}
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Status</label>
                    <select name="status" class="rounded-lg input-modern text-sm py-2">
                        <option value="">Semua Status</option>
                        <option value="hadir"      {{ request('status') == 'hadir'      ? 'selected' : '' }}>Hadir</option>
                        <option value="sakit"      {{ request('status') == 'sakit'      ? 'selected' : '' }}>Sakit</option>
                        <option value="izin"       {{ request('status') == 'izin'       ? 'selected' : '' }}>Izin</option>
                        <option value="cuti"       {{ request('status') == 'cuti'       ? 'selected' : '' }}>Cuti</option>
                        <option value="dinas_luar" {{ request('status') == 'dinas_luar' ? 'selected' : '' }}>Dinas Luar</option>
                        <option value="alpha"      {{ request('status') == 'alpha'      ? 'selected' : '' }}>Alpha</option>
                    </select>
                </div>
                <button type="submit" class="px-4 py-2.5 rounded-lg btn-primary text-white text-sm font-semibold">
                    Filter
                </button>
                <a href="{{ route('attendances.index') }}" class="px-4 py-2.5 rounded-lg border border-slate-200 text-slate-600 text-sm hover:bg-slate-50 transition">
                    Reset
                </a>
            </form>

            <a href="{{ route('attendances.create') }}" class="px-4 py-2.5 rounded-lg text-white text-sm font-semibold btn-primary whitespace-nowrap">
                + Tambah Absensi
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm table-modern">
                <thead>
                    <tr class="bg-slate-50/50">
                        <th class="px-5 py-3 text-left">Tanggal</th>
                        <th class="px-5 py-3 text-left">Karyawan</th>
                        <th class="px-5 py-3 text-left">Departemen</th>
                        <th class="px-5 py-3 text-center">Jam Masuk</th>
                        <th class="px-5 py-3 text-center">Jam Keluar</th>
                        <th class="px-5 py-3 text-center">Durasi</th>
                        <th class="px-5 py-3 text-center">Status</th>
                        <th class="px-5 py-3 text-left">Keterangan</th>
                        <th class="px-5 py-3 text-left">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($attendances as $a)
                    @php
                        $duration = '-';
                        if ($a->clock_in && $a->clock_out) {
                            $in  = \Carbon\Carbon::parse($a->clock_in);
                            $out = \Carbon\Carbon::parse($a->clock_out);
                            $diff = $in->diff($out);
                            $duration = $diff->h . 'j ' . $diff->i . 'm';
                        }
                    @endphp
                    <tr class="border-b border-slate-100 hover:bg-slate-50/80">
                        <td class="px-5 py-3 font-medium text-slate-800">
                            {{ $a->date->format('d/m/Y') }}
                            <div class="text-xs text-slate-400 font-normal">{{ $a->date->isoFormat('ddd') }}</div>
                        </td>
                        <td class="px-5 py-3 font-medium text-slate-800">{{ $a->employee?->name ?? '-' }}</td>
                        <td class="px-5 py-3 text-slate-500 text-xs">{{ $a->employee?->department ?? '-' }}</td>
                        <td class="px-5 py-3 text-center font-mono text-sm">
                            @if($a->clock_in)
                                <span class="text-emerald-700 font-semibold">{{ $a->clock_in }}</span>
                            @else
                                <span class="text-slate-300">—</span>
                            @endif
                        </td>
                        <td class="px-5 py-3 text-center font-mono text-sm">
                            @if($a->clock_out)
                                <span class="text-orange-600 font-semibold">{{ $a->clock_out }}</span>
                            @else
                                <span class="text-slate-300">—</span>
                            @endif
                        </td>
                        <td class="px-5 py-3 text-center text-xs text-slate-500">{{ $duration }}</td>
                        <td class="px-5 py-3 text-center">
                            <span class="badge {{ $a->statusColor() }}">{{ $a->statusLabel() }}</span>
                        </td>
                        <td class="px-5 py-3 text-slate-500 text-xs max-w-[150px] truncate">{{ $a->notes ?? '-' }}</td>
                        <td class="px-5 py-3">
                            <a href="{{ route('attendances.edit', $a) }}" class="text-indigo-600 hover:text-indigo-800 text-sm">Edit</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="px-5 py-10 text-center text-slate-400">
                            Belum ada data absensi untuk periode ini.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        @if($attendances->hasPages())
            <div class="px-5 py-3 border-t border-slate-100">{{ $attendances->links() }}</div>
        @endif
    </div>
</div>
</x-app-layout>
