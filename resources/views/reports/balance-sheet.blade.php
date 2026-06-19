<x-app-layout>
    <x-slot name="header">Neraca</x-slot>

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
                    <a href="{{ route('reports.balance-sheet.pdf', ['period_id' => $selectedPeriod?->id]) }}" class="px-3 py-2 bg-red-600 text-white rounded-lg text-sm hover:bg-red-700">PDF</a>
                    <a href="{{ route('reports.balance-sheet.excel', ['period_id' => $selectedPeriod?->id]) }}" class="px-3 py-2 bg-green-600 text-white rounded-lg text-sm hover:bg-green-700">Excel</a>
                </div>
                @endif
            </form>
        </div>

        @if($data)
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="px-5 py-4 border-b border-gray-200 text-center">
                <h3 class="font-bold text-gray-800 text-lg">PT. TRANSKARGO SOLUSINDO</h3>
                <p class="text-sm text-gray-500">NERACA</p>
                <p class="text-sm text-gray-500">Per {{ $selectedPeriod->label }}</p>
            </div>

            <div class="p-5 grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- LEFT: ASSETS -->
                <div>
                    <h4 class="font-bold text-gray-800 border-b-2 border-gray-300 pb-2 mb-3">AKTIVA</h4>

                    <table class="w-full text-sm">
                        @foreach($data['aktiva']['details'] as $aktiva)
                        <tr class="border-b border-gray-100 {{ $aktiva['account']->is_header ? 'bg-gray-50 font-semibold' : '' }}">
                            <td class="py-1.5 text-gray-700" style="padding-left: {{ $aktiva['account']->level * 12 }}px">
                                {{ $aktiva['account']->name }}
                            </td>
                            <td class="py-1.5 text-right font-mono w-32">
                                {{ $aktiva['balance'] > 0 ? number_format($aktiva['balance'], 0, ',', '.') : '-' }}
                            </td>
                        </tr>
                        @endforeach
                        <tr class="border-t-2 border-gray-300 font-bold bg-gray-50">
                            <td class="py-2 text-gray-800">TOTAL AKTIVA</td>
                            <td class="py-2 text-right font-mono">{{ number_format($data['total_aktiva'], 0, ',', '.') }}</td>
                        </tr>
                    </table>
                </div>

                <!-- RIGHT: LIABILITIES + EQUITY -->
                <div>
                    <h4 class="font-bold text-gray-800 border-b-2 border-gray-300 pb-2 mb-3">KEWAJIBAN DAN MODAL</h4>

                    <table class="w-full text-sm">
                        <tr class="border-b border-gray-200">
                            <td class="py-2 font-bold text-gray-800" colspan="2">KEWAJIBAN</td>
                        </tr>
                        @foreach($data['kewajiban']['details'] as $kew)
                        <tr class="border-b border-gray-100 {{ $kew['account']->is_header ? 'bg-gray-50 font-semibold' : '' }}">
                            <td class="py-1.5 text-gray-700" style="padding-left: {{ $kew['account']->level * 12 }}px">
                                {{ $kew['account']->name }}
                            </td>
                            <td class="py-1.5 text-right font-mono w-32">
                                {{ $kew['balance'] > 0 ? number_format($kew['balance'], 0, ',', '.') : '-' }}
                            </td>
                        </tr>
                        @endforeach
                        <tr class="border-t border-gray-300 font-semibold bg-gray-50">
                            <td class="py-2 text-gray-800">TOTAL KEWAJIBAN</td>
                            <td class="py-2 text-right font-mono">{{ number_format($data['total_kewajiban'], 0, ',', '.') }}</td>
                        </tr>

                        <tr class="border-b border-gray-200">
                            <td class="py-2 font-bold text-gray-800 pt-4" colspan="2">MODAL</td>
                        </tr>
                        @foreach($data['modal']['details'] as $m)
                        <tr class="border-b border-gray-100 {{ $m['account']->is_header ? 'bg-gray-50 font-semibold' : '' }}">
                            <td class="py-1.5 text-gray-700" style="padding-left: {{ $m['account']->level * 12 }}px">
                                {{ $m['account']->name }}
                            </td>
                            <td class="py-1.5 text-right font-mono">
                                {{ $m['balance'] > 0 ? number_format($m['balance'], 0, ',', '.') : '-' }}
                            </td>
                        </tr>
                        @endforeach
                        <tr class="border-t border-gray-300 font-semibold bg-gray-50">
                            <td class="py-2 text-gray-800">TOTAL MODAL</td>
                            <td class="py-2 text-right font-mono">{{ number_format($data['total_modal'], 0, ',', '.') }}</td>
                        </tr>

                        <tr class="border-t-2 border-gray-300 font-bold bg-blue-50">
                            <td class="py-2 text-gray-800">TOTAL KEWAJIBAN & MODAL</td>
                            <td class="py-2 text-right font-mono">{{ number_format($data['total_kewajiban_modal'], 0, ',', '.') }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Balance check -->
            <div class="px-5 py-3 border-t border-gray-200 bg-gray-50">
                <div class="flex items-center gap-2 text-sm">
                    <span class="font-semibold">Cek Keseimbangan:</span>
                    @if($data['is_balanced'])
                        <span class="text-green-600 font-bold">✓ BALANCE</span>
                        <span class="text-gray-500">(Total Aktiva = Total Kewajiban & Modal)</span>
                    @else
                        <span class="text-red-600 font-bold">✗ TIDAK BALANCE</span>
                        <span class="text-gray-500">(Selisih: {{ number_format($data['difference'], 0, ',', '.') }})</span>
                    @endif
                </div>
            </div>
        </div>
        @else
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8 text-center text-gray-400">
            Pilih periode untuk menampilkan Neraca.
        </div>
        @endif
    </div>
</x-app-layout>
