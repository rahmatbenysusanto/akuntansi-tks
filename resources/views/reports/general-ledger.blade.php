<x-app-layout>
    <x-slot name="header">Buku Besar</x-slot>

    <div class="space-y-4">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-5">
            <form method="GET" class="flex flex-wrap gap-3 items-end">
                <div>
                    <label class="block text-sm text-gray-600 mb-1">Periode</label>
                    <select name="period_id" class="rounded-lg border-gray-300 text-sm">
                        @foreach($periods as $p)
                            <option value="{{ $p->id }}" {{ ($selectedPeriod?->id ?? '') == $p->id ? 'selected' : '' }}>{{ $p->label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm text-gray-600 mb-1">Akun</label>
                    <select name="account_id" class="rounded-lg border-gray-300 text-sm w-64 account-select">
                        <option value="">-- Pilih Akun --</option>
                        @foreach($accounts as $acc)
                            <option value="{{ $acc->id }}" {{ ($selectedAccount?->id ?? '') == $acc->id ? 'selected' : '' }}>
                                {{ $acc->code }} - {{ $acc->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm hover:bg-blue-700">Tampilkan</button>
            </form>
        </div>

        @if($data)
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="px-5 py-4 border-b border-gray-200 flex items-center justify-between">
                <div>
                    <h3 class="font-semibold text-gray-800">{{ $data['account']->code }} - {{ $data['account']->name }}</h3>
                    <p class="text-sm text-gray-500">Periode: {{ $selectedPeriod->label }}</p>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('reports.general-ledger.pdf', ['period_id' => $selectedPeriod->id, 'account_id' => $selectedAccount->id]) }}" class="px-3 py-1.5 bg-red-600 text-white rounded-lg text-xs hover:bg-red-700">PDF</a>
                    <a href="{{ route('reports.general-ledger.excel', ['period_id' => $selectedPeriod->id, 'account_id' => $selectedAccount->id]) }}" class="px-3 py-1.5 bg-green-600 text-white rounded-lg text-xs hover:bg-green-700">Excel</a>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-gray-500 border-b border-gray-200 bg-gray-50">
                            <th class="px-4 py-3">Tanggal</th>
                            <th class="px-4 py-3">No. Bukti</th>
                            <th class="px-4 py-3">Keterangan</th>
                            <th class="px-4 py-3 text-right">Debet</th>
                            <th class="px-4 py-3 text-right">Kredit</th>
                            <th class="px-4 py-3 text-right">Saldo</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="border-b border-gray-100 font-semibold text-gray-600">
                            <td class="px-4 py-2" colspan="3">Saldo Awal</td>
                            <td class="px-4 py-2 text-right font-mono">{{ number_format($data['opening_balance_debit'], 0, ',', '.') }}</td>
                            <td class="px-4 py-2 text-right font-mono">{{ number_format($data['opening_balance_credit'], 0, ',', '.') }}</td>
                            <td class="px-4 py-2 text-right font-mono">{{ number_format($data['opening_balance'], 0, ',', '.') }}</td>
                        </tr>
                        @forelse($data['mutations'] as $mutation)
                        <tr class="border-b border-gray-100 hover:bg-gray-50">
                            <td class="px-4 py-2">{{ $mutation['date']->format('d/m/Y') }}</td>
                            <td class="px-4 py-2">{{ $mutation['reference_no'] }}</td>
                            <td class="px-4 py-2">{{ $mutation['description'] }}</td>
                            <td class="px-4 py-2 text-right font-mono">{{ $mutation['debit'] > 0 ? number_format($mutation['debit'], 0, ',', '.') : '-' }}</td>
                            <td class="px-4 py-2 text-right font-mono">{{ $mutation['credit'] > 0 ? number_format($mutation['credit'], 0, ',', '.') : '-' }}</td>
                            <td class="px-4 py-2 text-right font-mono">{{ number_format($mutation['balance'], 0, ',', '.') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-4 py-4 text-center text-gray-400">Tidak ada transaksi.</td>
                        </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr class="border-t border-gray-200 font-semibold bg-gray-50">
                            <td colspan="3" class="px-4 py-3 text-right">Saldo Akhir</td>
                            <td class="px-4 py-3 text-right font-mono">{{ number_format($data['total_debit'], 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right font-mono">{{ number_format($data['total_credit'], 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right font-mono">{{ number_format($data['ending_balance'], 0, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        @else
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8 text-center text-gray-400">
            Pilih akun dan periode untuk menampilkan Buku Besar.
        </div>
        @endif
    </div>
</x-app-layout>
