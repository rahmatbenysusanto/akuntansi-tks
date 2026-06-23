<x-app-layout>
<x-slot name="header">Aset Tetap</x-slot>
<div class="space-y-4">
    <div class="flex justify-between items-center">
        <a href="{{ route('fixed-assets.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm hover:bg-blue-700">+ Aset Baru</a>
        <form method="POST" action="{{ route('fixed-assets.post-depreciation') }}" class="inline">
            @csrf
            <button type="button" onclick="confirmAndSubmit(this, 'Posting depresiasi bulan ini?')" class="px-4 py-2 bg-green-600 text-white rounded-lg text-sm hover:bg-green-700">Posting Depresiasi Bulan Ini</button>
        </form>
    </div>
    <div class="bg-white rounded-lg shadow-sm border overflow-x-auto">
        <table class="w-full text-sm">
            <thead><tr class="text-left text-gray-500 border-b bg-gray-50">
                <th class="px-4 py-3">Kode</th><th class="px-4 py-3">Nama</th><th class="px-4 py-3">Tanggal Peroleh</th>
                <th class="px-4 py-3 text-right">Harga Peroleh</th><th class="px-4 py-3 text-right">Masa Manfaat</th><th class="px-4 py-3">Status</th><th class="px-4 py-3"></th>
            </tr></thead>
            <tbody>
            @forelse($assets as $a)
                <tr class="border-b hover:bg-gray-50">
                    <td class="px-4 py-2">{{ $a->asset_code }}</td>
                    <td class="px-4 py-2">{{ $a->name }}</td>
                    <td class="px-4 py-2">{{ $a->acquisition_date->format('d/m/Y') }}</td>
                    <td class="px-4 py-2 text-right">{{ number_format($a->acquisition_cost, 0, ',', '.') }}</td>
                    <td class="px-4 py-2 text-right">{{ $a->useful_life_months }} bln</td>
                    <td class="px-4 py-2"><span class="px-2 py-0.5 rounded-full text-xs {{ $a->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-100' }}">{{ ucfirst($a->status) }}</span></td>
                    <td class="px-4 py-2"><a href="{{ route('fixed-assets.show', $a) }}" class="text-blue-600 hover:underline">Jadwal</a></td>
                </tr>
            @empty<tr><td colspan="7" class="px-4 py-8 text-center text-gray-400">Belum ada aset tetap.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
</x-app-layout>
