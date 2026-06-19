<x-app-layout>
<x-slot name="header">Kartu Piutang - Customer</x-slot>
<div class="space-y-4">
    <div class="bg-white rounded-lg shadow-sm border p-5">
        <form method="GET" class="flex gap-3 items-end">
            <div><label class="text-sm text-gray-600 mb-1 block">Pilih Customer</label>
                <select name="customer_id" class="rounded-lg border-gray-300 text-sm" onchange="this.form.submit()">
                    <option value="">-- Pilih --</option>
                    @foreach($customers as $c)
                        <option value="{{ $c->id }}" {{ ($selectedCustomer->id ?? '') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                    @endforeach
                </select></div>
        </form>
    </div>

    @if($data)
    <div class="bg-white rounded-lg shadow-sm border">
        <div class="px-5 py-4 border-b flex justify-between">
            <div><h3 class="font-semibold">{{ $data['customer']->name }}</h3><p class="text-sm text-gray-500">{{ $data['customer']->npwp ?? '-' }}</p></div>
            <div class="text-right"><p class="text-sm text-gray-500">Saldo Piutang</p><p class="text-xl font-bold text-red-600">{{ number_format($data['saldo'], 0, ',', '.') }}</p></div>
        </div>
        <table class="w-full text-sm">
            <thead><tr class="text-left text-gray-500 border-b bg-gray-50">
                <th class="px-4 py-2">Invoice#</th><th class="px-4 py-2">Tanggal</th><th class="px-4 py-2">Jatuh Tempo</th>
                <th class="px-4 py-2 text-right">Total</th><th class="px-4 py-2 text-right">Dibayar</th><th class="px-4 py-2 text-right">Outstanding</th>
            </tr></thead>
            <tbody>
            @foreach($data['invoices'] as $i)
                <tr class="border-b hover:bg-gray-50">
                    <td class="px-4 py-1.5">{{ $i['invoice']->invoice_no }}</td>
                    <td class="px-4 py-1.5">{{ $i['invoice']->invoice_date->format('d/m/Y') }}</td>
                    <td class="px-4 py-1.5">{{ $i['invoice']->due_date->format('d/m/Y') }}</td>
                    <td class="px-4 py-1.5 text-right">{{ number_format($i['invoice']->total, 0, ',', '.') }}</td>
                    <td class="px-4 py-1.5 text-right">{{ number_format($i['total_paid'], 0, ',', '.') }}</td>
                    <td class="px-4 py-1.5 text-right font-semibold {{ $i['outstanding'] > 0 ? 'text-red-600' : 'text-green-600' }}">{{ number_format($i['outstanding'], 0, ',', '.') }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>
</x-app-layout>
