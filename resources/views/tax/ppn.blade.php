<x-app-layout><x-slot name="header">Rekap PPN</x-slot>
<div class="bg-white rounded-lg shadow-sm border p-5 mb-4">
    <form method="GET" class="flex gap-3 items-end">
        <div><label class="text-sm text-gray-600">Bulan</label>
            <select name="month" class="rounded-lg border-gray-300 text-sm">
                @foreach(range(1,12) as $m)<option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>{{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}</option>@endforeach
            </select></div>
        <div><label class="text-sm text-gray-600">Tahun</label>
            <select name="year" class="rounded-lg border-gray-300 text-sm">
                @foreach(range(now()->year - 2, now()->year) as $y)<option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>@endforeach
            </select></div>
        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm">Tampilkan</button>
    </form>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
    <div class="bg-white rounded-lg shadow-sm border p-4">
        <h4 class="font-semibold text-sm mb-2">PPN Keluaran</h4>
        <p class="text-2xl font-bold text-blue-600">{{ number_format($totalKeluaran, 0, ',', '.') }}</p>
        <p class="text-xs text-gray-500">{{ $keluaran->count() }} transaksi</p>
    </div>
    <div class="bg-white rounded-lg shadow-sm border p-4">
        <h4 class="font-semibold text-sm mb-2">PPN Masukan</h4>
        <p class="text-2xl font-bold text-green-600">{{ number_format($totalMasukan, 0, ',', '.') }}</p>
        <p class="text-xs text-gray-500">{{ $masukan->count() }} transaksi</p>
    </div>
</div>

<div class="bg-white rounded-lg shadow-sm border p-5 mb-4">
    <div class="flex justify-between items-center">
        <div><h4 class="font-semibold">PPN Harus Disetor / (Lebih Bayar)</h4></div>
        <p class="text-2xl font-bold {{ $netto >= 0 ? 'text-red-600' : 'text-green-600' }}">
            {{ number_format(abs($netto), 0, ',', '.') }}
            <span class="text-sm">{{ $netto >= 0 ? '(Setor)' : '(Lebih Bayar)' }}</span>
        </p>
    </div>
</div>

<div class="bg-white rounded-lg shadow-sm border">
    <div class="px-4 py-3 border-b font-semibold text-sm">Detail PPN Keluaran</div>
    <table class="w-full text-sm">
        <thead><tr class="text-left text-gray-500 border-b bg-gray-50">
            <th class="px-4 py-2">Tgl</th><th class="px-4 py-2">Counterparty</th><th class="px-4 py-2">NPWP</th>
            <th class="px-4 py-2 text-right">DPP</th><th class="px-4 py-2 text-right">PPN</th><th class="px-4 py-2">Dokumen</th>
        </tr></thead>
        <tbody>
        @foreach($keluaran as $k)
            <tr class="border-b hover:bg-gray-50">
                <td class="px-4 py-1.5">{{ \Carbon\Carbon::parse($k->transaction_date)->format('d/m/Y') }}</td>
                <td class="px-4 py-1.5">{{ $k->counterparty_name }}</td>
                <td class="px-4 py-1.5">{{ $k->counterparty_npwp ?? '-' }}</td>
                <td class="px-4 py-1.5 text-right">{{ number_format($k->dpp, 0, ',', '.') }}</td>
                <td class="px-4 py-1.5 text-right">{{ number_format($k->tax_amount, 0, ',', '.') }}</td>
                <td class="px-4 py-1.5">{{ $k->document_no ?? '-' }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
</x-app-layout>
