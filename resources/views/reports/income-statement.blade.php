<x-app-layout>
    <x-slot name="header">Laporan Laba Rugi</x-slot>

    <div class="space-y-4">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-5">
            <form method="GET" class="flex flex-wrap gap-3 items-end">
                <div>
                    <label class="block text-sm text-gray-600 mb-1">Periode</label>
                    <select name="period_id" class="rounded-lg border-gray-300 text-sm" onchange="this.form.submit()">
                        @foreach($periods as $p)
                            <option value="{{ $p->id }}" {{ ($selectedPeriod?->id ?? '') == $p->id ? 'selected' : '' }}>{{ $p->label }}</option>
                        @endforeach
                    </select>
                </div>
                @if($data)
                <div class="flex gap-2">
                    <a href="{{ route('reports.income-statement.pdf', ['period_id' => $endPeriod?->id ?? $selectedPeriod?->id]) }}" class="px-3 py-2 bg-red-600 text-white rounded-lg text-sm hover:bg-red-700">
                        PDF
                    </a>
                    <a href="{{ route('reports.income-statement.excel', ['period_id' => $endPeriod?->id ?? $selectedPeriod?->id]) }}" class="px-3 py-2 bg-green-600 text-white rounded-lg text-sm hover:bg-green-700">
                        Excel
                    </a>
                </div>
                @endif
            </form>
        </div>

        @if($data)
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="px-5 py-4 border-b border-gray-200 text-center">
                <h3 class="font-bold text-gray-800 text-lg">PT. TRANSKARGO SOLUSINDO</h3>
                <p class="text-sm text-gray-500">LAPORAN LABA RUGI</p>
                <p class="text-sm text-gray-500">Periode {{ $selectedPeriod?->label }}</p>
            </div>
            <div class="p-5">
                <table class="w-full text-sm">
                    <!-- PENDAPATAN -->
                    <tr class="border-b border-gray-200">
                        <td class="py-2 font-bold text-gray-800" colspan="2">PENDAPATAN USAHA</td>
                        <td class="py-2 text-right font-bold font-mono">{{ number_format($data['total_revenue'], 0, ',', '.') }}</td>
                    </tr>
                    @foreach($data['revenues'] as $rev)
                    <tr class="border-b border-gray-100">
                        <td class="py-1.5 w-6"></td>
                        <td class="py-1.5 text-gray-700">{{ $rev['account']->name }}</td>
                        <td class="py-1.5 text-right font-mono">{{ $rev['balance'] > 0 ? number_format($rev['balance'], 0, ',', '.') : '-' }}</td>
                    </tr>
                    @endforeach

                    <!-- HPP -->
                    <tr class="border-b border-gray-200">
                        <td class="py-2 font-bold text-gray-800" colspan="2">BEBAN POKOK PENDAPATAN (HPP)</td>
                        <td class="py-2 text-right font-bold font-mono">({{ number_format($data['total_hpp'], 0, ',', '.') }})</td>
                    </tr>
                    @foreach($data['hpp'] as $h)
                    <tr class="border-b border-gray-100">
                        <td class="py-1.5 w-6"></td>
                        <td class="py-1.5 text-gray-700">{{ $h['account']->name }}</td>
                        <td class="py-1.5 text-right font-mono">{{ $h['balance'] > 0 ? number_format($h['balance'], 0, ',', '.') : '-' }}</td>
                    </tr>
                    @endforeach

                    <!-- GROSS PROFIT -->
                    <tr class="border-b-2 border-gray-300 bg-gray-50 font-bold">
                        <td class="py-2 font-bold text-gray-800" colspan="2">LABA KOTOR</td>
                        <td class="py-2 text-right font-bold font-mono {{ $data['gross_profit'] >= 0 ? 'text-green-700' : 'text-red-700' }}">
                            {{ $data['gross_profit'] >= 0 ? '' : '(' }}{{ number_format(abs($data['gross_profit']), 0, ',', '.') }}{{ $data['gross_profit'] < 0 ? ')' : '' }}
                        </td>
                    </tr>

                    <!-- OPEX -->
                    <tr class="border-b border-gray-200">
                        <td class="py-2 font-bold text-gray-800" colspan="2">BIAYA OPERASIONAL</td>
                        <td class="py-2 text-right font-bold font-mono">({{ number_format($data['total_operating_expenses'], 0, ',', '.') }})</td>
                    </tr>
                    @foreach($data['operating_expenses'] as $oe)
                    <tr class="border-b border-gray-100">
                        <td class="py-1.5 w-6"></td>
                        <td class="py-1.5 text-gray-700">{{ $oe['account']->name }}</td>
                        <td class="py-1.5 text-right font-mono">{{ $oe['balance'] > 0 ? number_format($oe['balance'], 0, ',', '.') : '-' }}</td>
                    </tr>
                    @endforeach

                    <!-- OPERATING PROFIT -->
                    <tr class="border-b-2 border-gray-300 bg-gray-50 font-bold">
                        <td class="py-2 font-bold text-gray-800" colspan="2">LABA USAHA</td>
                        <td class="py-2 text-right font-bold font-mono {{ $data['operating_profit'] >= 0 ? 'text-green-700' : 'text-red-700' }}">
                            {{ $data['operating_profit'] >= 0 ? '' : '(' }}{{ number_format(abs($data['operating_profit']), 0, ',', '.') }}{{ $data['operating_profit'] < 0 ? ')' : '' }}
                        </td>
                    </tr>

                    <!-- OTHER INCOME/EXPENSES -->
                    <tr class="border-b border-gray-200">
                        <td class="py-2 font-bold text-gray-800" colspan="2">PENDAPATAN / BIAYA LAIN-LAIN</td>
                        <td class="py-2 text-right font-bold font-mono">{{ number_format($data['total_other'], 0, ',', '.') }}</td>
                    </tr>
                    @foreach($data['other_income_expenses'] as $oi)
                    <tr class="border-b border-gray-100">
                        <td class="py-1.5 w-6"></td>
                        <td class="py-1.5 text-gray-700">{{ $oi['account']->name }}</td>
                        <td class="py-1.5 text-right font-mono">{{ $oi['balance'] > 0 ? number_format($oi['balance'], 0, ',', '.') : '-' }}</td>
                    </tr>
                    @endforeach

                    <!-- INTEREST -->
                    <tr class="border-b border-gray-200">
                        <td class="py-2 font-bold text-gray-800" colspan="2">BIAYA BUNGA</td>
                        <td class="py-2 text-right font-bold font-mono">({{ number_format($data['total_interest'], 0, ',', '.') }})</td>
                    </tr>

                    <!-- PROFIT BEFORE TAX -->
                    <tr class="border-b-2 border-gray-300 bg-gray-50 font-bold">
                        <td class="py-2 font-bold text-gray-800" colspan="2">LABA SEBELUM PAJAK</td>
                        <td class="py-2 text-right font-bold font-mono {{ $data['profit_before_tax'] >= 0 ? 'text-green-700' : 'text-red-700' }}">
                            {{ $data['profit_before_tax'] >= 0 ? '' : '(' }}{{ number_format(abs($data['profit_before_tax']), 0, ',', '.') }}{{ $data['profit_before_tax'] < 0 ? ')' : '' }}
                        </td>
                    </tr>

                    <!-- TAX -->
                    <tr class="border-b border-gray-200">
                        <td class="py-2 font-bold text-gray-800" colspan="2">PAJAK PENGHASILAN</td>
                        <td class="py-2 text-right font-bold font-mono">({{ number_format($data['total_tax'], 0, ',', '.') }})</td>
                    </tr>

                    <!-- NET INCOME -->
                    <tr class="border-b-2 border-gray-300 bg-blue-50 font-bold text-base">
                        <td class="py-3 font-bold text-gray-900" colspan="2">LABA BERSIH TAHUN BERJALAN</td>
                        <td class="py-3 text-right font-bold font-mono text-lg {{ $data['net_income'] >= 0 ? 'text-green-700' : 'text-red-700' }}">
                            {{ $data['net_income'] >= 0 ? '' : '(' }}{{ number_format(abs($data['net_income']), 0, ',', '.') }}{{ $data['net_income'] < 0 ? ')' : '' }}
                        </td>
                    </tr>
                </table>
            </div>
        </div>
        @else
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8 text-center text-gray-400">
            Pilih periode untuk menampilkan Laporan Laba Rugi.
        </div>
        @endif
    </div>
</x-app-layout>
