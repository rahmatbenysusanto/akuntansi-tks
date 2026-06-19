<x-app-layout><x-slot name="header">Kasbon Karyawan</x-slot>
<div class="flex justify-end mb-4"><a href="{{ route('cash-advances.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm">+ Kasbon Baru</a></div>
<div class="bg-white rounded-lg shadow-sm border overflow-x-auto">
    <table class="w-full text-sm">
        <thead><tr class="text-left text-gray-500 border-b bg-gray-50">
            <th class="px-4 py-3">No</th><th class="px-4 py-3">Karyawan</th><th class="px-4 py-3">Tanggal</th>
            <th class="px-4 py-3 text-right">Jumlah</th><th class="px-4 py-3">Status</th><th class="px-4 py-3">Metode</th>
        </tr></thead>
        <tbody>
        @forelse($advances as $a)
            <tr class="border-b hover:bg-gray-50">
                <td class="px-4 py-2">{{ $a->advance_no }}</td>
                <td class="px-4 py-2">{{ $a->employee?->name ?? '-' }}</td>
                <td class="px-4 py-2">{{ $a->advance_date->format('d/m/Y') }}</td>
                <td class="px-4 py-2 text-right">{{ number_format($a->amount, 0, ',', '.') }}</td>
                <td class="px-4 py-2"><span class="px-2 py-0.5 rounded-full text-xs
                    {{ $a->status == 'settled' ? 'bg-green-100 text-green-700' : ($a->status == 'partial' ? 'bg-amber-100 text-amber-700' : 'bg-red-100 text-red-700') }}">
                    {{ ucfirst($a->status) }}</span></td>
                <td class="px-4 py-2">{{ str_replace('_', ' ', $a->settlement_method) }}</td>
            </tr>
        @empty<tr><td colspan="6" class="px-4 py-8 text-center text-gray-400">Belum ada kasbon.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
</x-app-layout>
