<x-app-layout><x-slot name="header">Transaksi Pajak</x-slot>
<div class="bg-white rounded-lg shadow-sm border overflow-x-auto">
    <table class="w-full text-sm">
        <thead><tr class="text-left text-gray-500 border-b bg-gray-50">
            <th class="px-4 py-3">Tgl</th><th class="px-4 py-3">Tipe</th><th class="px-4 py-3">Counterparty</th>
            <th class="px-4 py-3 text-right">DPP</th><th class="px-4 py-3">Tarif</th><th class="px-4 py-3 text-right">Pajak</th>
            <th class="px-4 py-3">Periode</th><th class="px-4 py-3">Status</th>
        </tr></thead>
        <tbody>
        @forelse($taxes as $t)
            <tr class="border-b hover:bg-gray-50">
                <td class="px-4 py-2">{{ \Carbon\Carbon::parse($t->transaction_date)->format('d/m/Y') }}</td>
                <td class="px-4 py-2"><span class="px-2 py-0.5 rounded-full text-xs bg-purple-100 text-purple-700">{{ strtoupper($t->tax_type) }}</span></td>
                <td class="px-4 py-2">{{ $t->counterparty_name ?? '-' }}</td>
                <td class="px-4 py-2 text-right">{{ number_format($t->dpp, 0, ',', '.') }}</td>
                <td class="px-4 py-2">{{ $t->tax_rate }}%</td>
                <td class="px-4 py-2 text-right">{{ number_format($t->tax_amount, 0, ',', '.') }}</td>
                <td class="px-4 py-2">{{ $t->period_month }}/{{ $t->period_year }}</td>
                <td class="px-4 py-2"><span class="px-2 py-0.5 rounded-full text-xs {{ $t->status == 'sudah_lapor' ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700' }}">{{ str_replace('_', ' ', $t->status) }}</span></td>
            </tr>
        @empty<tr><td colspan="8" class="px-4 py-8 text-center text-gray-400">Belum ada transaksi pajak.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
</x-app-layout>
