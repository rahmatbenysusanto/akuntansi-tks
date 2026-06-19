<x-app-layout><x-slot name="header">Kurs Valuta Asing</x-slot>
<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div class="bg-white rounded-lg shadow-sm border p-5">
        <h3 class="font-semibold mb-3">Tambah Kurs Baru</h3>
        <form method="POST">@csrf
            <div class="grid grid-cols-3 gap-3">
                <div><label class="text-sm text-gray-600">Mata Uang</label>
                    <select name="currency_code" class="w-full rounded-lg border-gray-300 text-sm">
                        <option value="USD">USD</option><option value="EUR">EUR</option><option value="SGD">SGD</option><option value="JPY">JPY</option>
                    </select></div>
                <div><label class="text-sm text-gray-600">Tanggal</label>
                    <input type="date" name="rate_date" value="{{ now()->format('Y-m-d') }}" class="w-full rounded-lg border-gray-300 text-sm"></div>
                <div><label class="text-sm text-gray-600">Rate ke IDR</label>
                    <input type="number" step="0.01" name="rate_to_idr" class="w-full rounded-lg border-gray-300 text-sm" required></div>
            </div>
            <button type="submit" class="mt-3 px-4 py-2 bg-blue-600 text-white rounded-lg text-sm">Simpan</button>
        </form>
    </div>
    <div class="bg-white rounded-lg shadow-sm border p-5">
        <h3 class="font-semibold mb-3">Riwayat Kurs</h3>
        <div class="max-h-60 overflow-y-auto">
            <table class="w-full text-sm">
                <thead><tr class="text-left text-gray-500 border-b"><th class="py-1">Mata Uang</th><th class="py-1">Tanggal</th><th class="py-1 text-right">Rate</th></tr></thead>
                <tbody>
                @foreach($rates as $r)
                    <tr class="border-b">
                        <td class="py-1 font-semibold">{{ $r->currency_code }}</td>
                        <td class="py-1">{{ $r->rate_date->format('d/m/Y') }}</td>
                        <td class="py-1 text-right">{{ number_format($r->rate_to_idr, 2, ',', '.') }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
</x-app-layout>
