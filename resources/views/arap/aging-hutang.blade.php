<x-app-layout>
<x-slot name="header">Aging Hutang</x-slot>
<div class="bg-white rounded-lg shadow-sm border">
    <table class="w-full text-sm">
        <thead><tr class="text-left text-gray-500 border-b bg-gray-50">
            <th class="px-4 py-3">Vendor</th><th class="px-4 py-3 text-right">0-30 Hari</th>
            <th class="px-4 py-3 text-right">31-60 Hari</th><th class="px-4 py-3 text-right">61-90 Hari</th>
            <th class="px-4 py-3 text-right">&gt;90 Hari</th><th class="px-4 py-3 text-right">Total</th>
        </tr></thead>
        <tbody>
        @forelse($aging as $a)
            <tr class="border-b hover:bg-gray-50">
                <td class="px-4 py-2">{{ $a['vendor']->name }}</td>
                <td class="px-4 py-2 text-right">{{ number_format($a['aging']['0-30'], 0, ',', '.') }}</td>
                <td class="px-4 py-2 text-right">{{ number_format($a['aging']['31-60'], 0, ',', '.') }}</td>
                <td class="px-4 py-2 text-right">{{ number_format($a['aging']['61-90'], 0, ',', '.') }}</td>
                <td class="px-4 py-2 text-right font-semibold {{ $a['aging']['>90'] > 0 ? 'text-red-600' : '' }}">{{ number_format($a['aging']['>90'], 0, ',', '.') }}</td>
                <td class="px-4 py-2 text-right font-semibold">{{ number_format($a['total'], 0, ',', '.') }}</td>
            </tr>
        @empty<tr><td colspan="6" class="px-4 py-8 text-center text-gray-400">Tidak ada hutang outstanding.</td></tr>
        @endforelse
        </tbody>
        <tfoot><tr class="border-t-2 font-bold bg-gray-50">
            <td class="px-4 py-2">TOTAL</td>
            <td class="px-4 py-2 text-right">{{ number_format(collect($aging)->sum(fn($a) => $a['aging']['0-30']), 0, ',', '.') }}</td>
            <td class="px-4 py-2 text-right">{{ number_format(collect($aging)->sum(fn($a) => $a['aging']['31-60']), 0, ',', '.') }}</td>
            <td class="px-4 py-2 text-right">{{ number_format(collect($aging)->sum(fn($a) => $a['aging']['61-90']), 0, ',', '.') }}</td>
            <td class="px-4 py-2 text-right">{{ number_format(collect($aging)->sum(fn($a) => $a['aging']['>90']), 0, ',', '.') }}</td>
            <td class="px-4 py-2 text-right">{{ number_format($totalAll, 0, ',', '.') }}</td>
        </tr></tfoot>
    </table>
</div>
</x-app-layout>
