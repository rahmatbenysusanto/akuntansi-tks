<x-app-layout><x-slot name="header">Cicilan / Angsuran</x-slot>
<div class="flex justify-end mb-4"><a href="{{ route('loans.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm">+ Fasilitas Baru</a></div>
<div class="bg-white rounded-lg shadow-sm border overflow-x-auto">
    <table class="w-full text-sm">
        <thead><tr class="text-left text-gray-500 border-b bg-gray-50">
            <th class="px-4 py-3">Nama</th><th class="px-4 py-3">Tipe</th><th class="px-4 py-3">Pokok</th><th class="px-4 py-3">Bunga/th</th>
            <th class="px-4 py-3">Tenor</th><th class="px-4 py-3">Mulai</th><th class="px-4 py-3">Status</th><th class="px-4 py-3"></th>
        </tr></thead>
        <tbody>
        @forelse($loans as $l)
            <tr class="border-b hover:bg-gray-50">
                <td class="px-4 py-2">{{ $l->name }}</td><td class="px-4 py-2">{{ str_replace('_', ' ', ucfirst($l->type)) }}</td>
                <td class="px-4 py-2 text-right">{{ number_format($l->principal_amount, 0, ',', '.') }}</td>
                <td class="px-4 py-2 text-right">{{ $l->interest_rate_per_year }}%</td>
                <td class="px-4 py-2 text-right">{{ $l->tenor_months }} bln</td>
                <td class="px-4 py-2">{{ $l->start_date->format('d/m/Y') }}</td>
                <td class="px-4 py-2"><span class="px-2 py-0.5 rounded-full text-xs {{ $l->status == 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-100' }}">{{ ucfirst($l->status) }}</span></td>
                <td class="px-4 py-2"><a href="{{ route('loans.show', $l) }}" class="text-blue-600 hover:underline">Jadwal</a></td>
            </tr>
        @empty<tr><td colspan="8" class="px-4 py-8 text-center text-gray-400">Belum ada fasilitas.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
</x-app-layout>
