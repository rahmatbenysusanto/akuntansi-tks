<x-app-layout>
    <x-slot name="header">Periode Akuntansi</x-slot>

    <div class="space-y-4">
        <!-- Add Period Form -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-5">
            <h3 class="font-semibold text-gray-800 mb-3">Tambah Periode Baru</h3>
            <form action="{{ route('accounting-periods.store') }}" method="POST" class="flex gap-3 items-end">
                @csrf
                <div>
                    <label class="block text-sm text-gray-600 mb-1">Bulan</label>
                    <select name="month" class="rounded-lg border-gray-300 text-sm" required>
                        @foreach(['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'] as $i => $m)
                            <option value="{{ $i + 1 }}" {{ now()->month == $i + 1 ? 'selected' : '' }}>{{ $m }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm text-gray-600 mb-1">Tahun</label>
                    <input type="number" name="year" value="{{ now()->year }}" class="rounded-lg border-gray-300 text-sm w-24" min="2020" max="2099" required>
                </div>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm hover:bg-blue-700">Tambah</button>
            </form>
        </div>

        <!-- Periods List -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-gray-500 border-b border-gray-200 bg-gray-50">
                            <th class="px-5 py-3">Periode</th>
                            <th class="px-5 py-3">Status</th>
                            <th class="px-5 py-3">Ditutup Oleh</th>
                            <th class="px-5 py-3">Ditutup Pada</th>
                            <th class="px-5 py-3">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($periods as $period)
                        <tr class="border-b border-gray-100 hover:bg-gray-50">
                            <td class="px-5 py-3 font-medium">{{ $period->label }}</td>
                            <td class="px-5 py-3">
                                @if($period->isOpen())
                                    <span class="px-2 py-0.5 bg-green-100 text-green-700 rounded-full text-xs">Open</span>
                                @else
                                    <span class="px-2 py-0.5 bg-gray-100 text-gray-600 rounded-full text-xs">Closed</span>
                                @endif
                            </td>
                            <td class="px-5 py-3">{{ $period->closedBy?->name ?? '-' }}</td>
                            <td class="px-5 py-3">{{ $period->closed_at ? $period->closed_at->format('d/m/Y H:i') : '-' }}</td>
                            <td class="px-5 py-3">
                                @if($period->isOpen())
                                    <form action="{{ route('accounting-periods.close', $period) }}" method="POST" onsubmit="return confirm('Tutup periode {{ $period->label }}? Pastikan semua jurnal sudah diposting.')">
                                        @csrf @method('PATCH')
                                        <button class="text-xs text-red-600 hover:underline">Tutup Periode</button>
                                    </form>
                                @else
                                    <span class="text-xs text-gray-400">Selesai</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
