<x-app-layout>
<x-slot name="header">Sales Invoice</x-slot>
<div class="space-y-4">
    <div class="flex justify-end">
        <a href="{{ route('sales.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm hover:bg-blue-700">+ Sales Invoice Baru</a>
    </div>
    <div class="bg-white rounded-lg shadow-sm border overflow-x-auto">
        <table class="w-full text-sm">
            <thead><tr class="text-left text-gray-500 border-b bg-gray-50">
                <th class="px-4 py-3">Invoice#</th><th class="px-4 py-3">Customer</th>
                <th class="px-4 py-3">Tanggal</th><th class="px-4 py-3">Jatuh Tempo</th>
                <th class="px-4 py-3 text-right">Total</th><th class="px-4 py-3">Status</th>
            </tr></thead>
            <tbody>
            @forelse($invoices as $inv)
                <tr class="border-b hover:bg-gray-50">
                    <td class="px-4 py-2">{{ $inv->invoice_no }}</td>
                    <td class="px-4 py-2">{{ $inv->customer->name ?? '-' }}</td>
                    <td class="px-4 py-2">{{ $inv->invoice_date->format('d/m/Y') }}</td>
                    <td class="px-4 py-2">{{ $inv->due_date->format('d/m/Y') }}</td>
                    <td class="px-4 py-2 text-right font-mono">{{ number_format($inv->total, 0, ',', '.') }}</td>
                    <td class="px-4 py-2"><span class="px-2 py-0.5 rounded-full text-xs
                        {{ $inv->status === 'posted' ? 'bg-green-100 text-green-700' : ($inv->status === 'paid' ? 'bg-blue-100 text-blue-700' : 'bg-amber-100 text-amber-700') }}">
                        {{ ucfirst($inv->status) }}</span></td>
                </tr>
            @empty<tr><td colspan="6" class="px-4 py-8 text-center text-gray-400">Belum ada invoice.</td></tr>
            @endforelse
            </tbody>
        </table>
        @if($invoices->hasPages())<div class="px-4 py-3 border-t">{{ $invoices->links() }}</div>@endif
    </div>
</div>
</x-app-layout>
