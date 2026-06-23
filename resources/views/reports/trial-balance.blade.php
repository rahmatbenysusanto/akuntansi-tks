<x-app-layout>
    <x-slot name="header">Neraca Lajur</x-slot>

    <div class="space-y-4">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-5">
            <form method="GET" class="flex gap-3 items-end">
                <div>
                    <label class="block text-sm text-gray-600 mb-1">Periode</label>
                    <select name="period_id" class="rounded-lg border-gray-300 text-sm" onchange="this.form.submit()">
                        @foreach($periods as $p)
                            <option value="{{ $p->id }}" {{ ($selectedPeriod?->id ?? '') == $p->id ? 'selected' : '' }}>{{ $p->label }}</option>
                        @endforeach
                    </select>
                </div>
            </form>
        </div>

        @if($data)
        <div class="flex justify-end gap-2 mb-3">
            <a href="{{ route('reports.trial-balance.pdf', ['period_id' => $selectedPeriod->id]) }}" class="px-3 py-1.5 bg-red-600 text-white rounded-lg text-xs hover:bg-red-700">PDF</a>
            <a href="{{ route('reports.trial-balance.excel', ['period_id' => $selectedPeriod->id]) }}" class="px-3 py-1.5 bg-green-600 text-white rounded-lg text-xs hover:bg-green-700">Excel</a>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-x-auto">
            <table class="w-full text-xs">
                <thead>
                    <tr class="text-center text-gray-600 border-b border-gray-200 bg-gray-50">
                        <th class="px-2 py-3 w-20" rowspan="2">Kode</th>
                        <th class="px-2 py-3" rowspan="2">Nama Akun</th>
                        <th class="px-2 py-2 border-l border-gray-200" colspan="2">Saldo Awal</th>
                        <th class="px-2 py-2 border-l border-gray-200" colspan="2">Mutasi</th>
                        <th class="px-2 py-2 border-l border-gray-200" colspan="2">Saldo Akhir</th>
                        <th class="px-2 py-2 border-l border-gray-200" colspan="2">Laba Rugi</th>
                        <th class="px-2 py-2 border-l border-gray-200" colspan="2">Neraca</th>
                    </tr>
                    <tr class="text-center text-gray-500 border-b border-gray-200 bg-gray-50">
                        <th class="px-2 py-1 border-l border-gray-200">Debet</th>
                        <th class="px-2 py-1">Kredit</th>
                        <th class="px-2 py-1 border-l border-gray-200">Debet</th>
                        <th class="px-2 py-1">Kredit</th>
                        <th class="px-2 py-1 border-l border-gray-200">Debet</th>
                        <th class="px-2 py-1">Kredit</th>
                        <th class="px-2 py-1 border-l border-gray-200">Debet</th>
                        <th class="px-2 py-1">Kredit</th>
                        <th class="px-2 py-1 border-l border-gray-200">Debet</th>
                        <th class="px-2 py-1">Kredit</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data['lines'] as $line)
                    <tr class="border-b border-gray-100 hover:bg-gray-50 {{ $line['account']->is_header ? 'bg-gray-50 font-semibold' : '' }}">
                        <td class="px-2 py-1.5 font-mono text-gray-400">{{ $line['account']->code }}</td>
                        <td class="px-2 py-1.5" style="padding-left: {{ 8 + ($line['account']->level - 1) * 15 }}px">
                            {{ $line['account']->name }}
                        </td>
                        <td class="px-2 py-1.5 text-right font-mono">{{ $line['opening_debit'] > 0 ? number_format($line['opening_debit'], 0, ',', '.') : '-' }}</td>
                        <td class="px-2 py-1.5 text-right font-mono">{{ $line['opening_credit'] > 0 ? number_format($line['opening_credit'], 0, ',', '.') : '-' }}</td>
                        <td class="px-2 py-1.5 text-right font-mono border-l border-gray-100">{{ $line['mutation_debit'] > 0 ? number_format($line['mutation_debit'], 0, ',', '.') : '-' }}</td>
                        <td class="px-2 py-1.5 text-right font-mono">{{ $line['mutation_credit'] > 0 ? number_format($line['mutation_credit'], 0, ',', '.') : '-' }}</td>
                        <td class="px-2 py-1.5 text-right font-mono border-l border-gray-100">{{ $line['ending_debit'] > 0 ? number_format($line['ending_debit'], 0, ',', '.') : '-' }}</td>
                        <td class="px-2 py-1.5 text-right font-mono">{{ $line['ending_credit'] > 0 ? number_format($line['ending_credit'], 0, ',', '.') : '-' }}</td>
                        <td class="px-2 py-1.5 text-right font-mono border-l border-gray-100">{{ $line['income_statement_debit'] > 0 ? number_format($line['income_statement_debit'], 0, ',', '.') : '-' }}</td>
                        <td class="px-2 py-1.5 text-right font-mono">{{ $line['income_statement_credit'] > 0 ? number_format($line['income_statement_credit'], 0, ',', '.') : '-' }}</td>
                        <td class="px-2 py-1.5 text-right font-mono border-l border-gray-100">{{ $line['balance_sheet_debit'] > 0 ? number_format($line['balance_sheet_debit'], 0, ',', '.') : '-' }}</td>
                        <td class="px-2 py-1.5 text-right font-mono">{{ $line['balance_sheet_credit'] > 0 ? number_format($line['balance_sheet_credit'], 0, ',', '.') : '-' }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="border-t-2 border-gray-300 font-bold bg-gray-100">
                        <td colspan="2" class="px-2 py-2 text-right">TOTAL</td>
                        <td class="px-2 py-2 text-right font-mono">{{ number_format($data['totals']['opening_debit'], 0, ',', '.') }}</td>
                        <td class="px-2 py-2 text-right font-mono">{{ number_format($data['totals']['opening_credit'], 0, ',', '.') }}</td>
                        <td class="px-2 py-2 text-right font-mono border-l border-gray-200">{{ number_format($data['totals']['mutation_debit'], 0, ',', '.') }}</td>
                        <td class="px-2 py-2 text-right font-mono">{{ number_format($data['totals']['mutation_credit'], 0, ',', '.') }}</td>
                        <td class="px-2 py-2 text-right font-mono border-l border-gray-200">{{ number_format($data['totals']['ending_debit'], 0, ',', '.') }}</td>
                        <td class="px-2 py-2 text-right font-mono">{{ number_format($data['totals']['ending_credit'], 0, ',', '.') }}</td>
                        <td class="px-2 py-2 text-right font-mono border-l border-gray-200">{{ number_format($data['totals']['income_statement_debit'], 0, ',', '.') }}</td>
                        <td class="px-2 py-2 text-right font-mono">{{ number_format($data['totals']['income_statement_credit'], 0, ',', '.') }}</td>
                        <td class="px-2 py-2 text-right font-mono border-l border-gray-200">{{ number_format($data['totals']['balance_sheet_debit'], 0, ',', '.') }}</td>
                        <td class="px-2 py-2 text-right font-mono">{{ number_format($data['totals']['balance_sheet_credit'], 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 text-sm">
            <div class="flex items-center gap-2">
                <span class="text-gray-600">Cek Balance:</span>
                @if($data['totals']['opening_debit'] == $data['totals']['opening_credit'])
                    <span class="text-green-600 font-semibold">✓ Saldo Awal Balance</span>
                @else
                    <span class="text-red-600 font-semibold">✗ Saldo Awal Tidak Balance</span>
                @endif
                <span class="text-gray-300 mx-2">|</span>
                @if($data['totals']['mutation_debit'] == $data['totals']['mutation_credit'])
                    <span class="text-green-600 font-semibold">✓ Mutasi Balance</span>
                @else
                    <span class="text-red-600 font-semibold">✗ Mutasi Tidak Balance</span>
                @endif
                <span class="text-gray-300 mx-2">|</span>
                @if($data['totals']['ending_debit'] == $data['totals']['ending_credit'])
                    <span class="text-green-600 font-semibold">✓ Saldo Akhir Balance</span>
                @else
                    <span class="text-red-600 font-semibold">✗ Saldo Akhir Tidak Balance</span>
                @endif
            </div>
        </div>
        @endif
    </div>
</x-app-layout>
