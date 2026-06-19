<x-app-layout>
<x-slot name="header">Jadwal Depresiasi: {{ $fixedAsset->name }}</x-slot>
<div class="bg-white rounded-lg shadow-sm border">
    <div class="px-5 py-4 border-b grid grid-cols-3 gap-4 text-sm">
        <div><span class="text-gray-500">Kode:</span> {{ $fixedAsset->asset_code }}</div>
        <div><span class="text-gray-500">Harga Perolehan:</span> {{ number_format($fixedAsset->acquisition_cost, 0, ',', '.') }}</div>
        <div><span class="text-gray-500">Nilai Residu:</span> {{ number_format($fixedAsset->salvage_value, 0, ',', '.') }}</div>
        <div><span class="text-gray-500">Masa Manfaat:</span> {{ $fixedAsset->useful_life_months }} bulan</div>
        <div><span class="text-gray-500">Metode:</span> Straight Line</div>
        <div><span class="text-gray-500">Status:</span> {{ ucfirst($fixedAsset->status) }}</div>
    </div>
    <table class="w-full text-sm">
        <thead><tr class="text-left text-gray-500 border-b bg-gray-50">
            <th class="px-4 py-3">#</th><th class="px-4 py-3">Tanggal</th>
            <th class="px-4 py-3 text-right">Beban Penyusutan</th><th class="px-4 py-3 text-right">Akumulasi</th>
            <th class="px-4 py-3 text-right">Nilai Buku</th><th class="px-4 py-3">Status</th>
        </tr></thead>
        <tbody>
        @foreach($schedules as $s)
            <tr class="border-b hover:bg-gray-50">
                <td class="px-4 py-1.5">{{ $s->period_no }}</td>
                <td class="px-4 py-1.5">{{ $s->schedule_date->format('d/m/Y') }}</td>
                <td class="px-4 py-1.5 text-right">{{ number_format($s->depreciation_amount, 0, ',', '.') }}</td>
                <td class="px-4 py-1.5 text-right">{{ number_format($s->accumulated_amount, 0, ',', '.') }}</td>
                <td class="px-4 py-1.5 text-right">{{ number_format($s->book_value, 0, ',', '.') }}</td>
                <td class="px-4 py-1.5">{{ $s->is_posted ? '✅ Diposting' : '⏳ Belum' }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
</x-app-layout>
