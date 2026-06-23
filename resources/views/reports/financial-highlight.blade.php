<x-app-layout>
    <x-slot name="header">Financial Highlight</x-slot>

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

        @if($ratios)
        <div class="flex justify-end gap-2 mb-3">
            <a href="{{ route('reports.financial-highlight.pdf', ['period_id' => $selectedPeriod->id]) }}" class="px-3 py-1.5 bg-red-600 text-white rounded-lg text-xs hover:bg-red-700">PDF</a>
            <a href="{{ route('reports.financial-highlight.excel', ['period_id' => $selectedPeriod->id]) }}" class="px-3 py-1.5 bg-green-600 text-white rounded-lg text-xs hover:bg-green-700">Excel</a>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Profitability Ratios -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-5">
                <h3 class="font-bold text-gray-800 border-b border-gray-200 pb-2 mb-4">Profitability Ratios</h3>
                <div class="space-y-3">
                    @foreach(['net_profit_margin', 'roi', 'roe', 'roce'] as $key)
                        @if(isset($ratios[$key]))
                        <div class="flex justify-between items-center border-b border-gray-100 pb-2">
                            <div>
                                <p class="text-sm font-medium text-gray-700">{{ $ratios[$key]['label'] }}</p>
                                <p class="text-xs text-gray-400">{{ $ratios[$key]['formula'] }}</p>
                            </div>
                            <div class="text-right">
                                <span class="text-lg font-bold {{ $ratios[$key]['value'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                    {{ number_format($ratios[$key]['value'], 2) }}{{ $ratios[$key]['unit'] }}
                                </span>
                            </div>
                        </div>
                        @endif
                    @endforeach
                </div>
            </div>

            <!-- Liquidity Ratios -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-5">
                <h3 class="font-bold text-gray-800 border-b border-gray-200 pb-2 mb-4">Liquidity Ratios</h3>
                <div class="space-y-3">
                    @foreach(['current_ratio', 'quick_ratio', 'absolute_liquidity_ratio'] as $key)
                        @if(isset($ratios[$key]))
                        <div class="flex justify-between items-center border-b border-gray-100 pb-2">
                            <div>
                                <p class="text-sm font-medium text-gray-700">{{ $ratios[$key]['label'] }}</p>
                                <p class="text-xs text-gray-400">{{ $ratios[$key]['formula'] }}</p>
                            </div>
                            <div class="text-right">
                                <span class="text-lg font-bold {{ $ratios[$key]['value'] >= 1 ? 'text-green-600' : 'text-amber-600' }}">
                                    {{ number_format($ratios[$key]['value'], 2) }}{{ $ratios[$key]['unit'] }}
                                </span>
                            </div>
                        </div>
                        @endif
                    @endforeach
                </div>
            </div>

            <!-- Efficiency / Others -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-5 md:col-span-2">
                <h3 class="font-bold text-gray-800 border-b border-gray-200 pb-2 mb-4">Efficiency & Other Ratios</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach(['sales_to_liquid_assets', 'debt_to_equity'] as $key)
                        @if(isset($ratios[$key]))
                        <div class="flex justify-between items-center border-b border-gray-100 pb-2">
                            <div>
                                <p class="text-sm font-medium text-gray-700">{{ $ratios[$key]['label'] }}</p>
                                <p class="text-xs text-gray-400">{{ $ratios[$key]['formula'] }}</p>
                            </div>
                            <div class="text-right">
                                <span class="text-lg font-bold text-blue-600">
                                    {{ number_format($ratios[$key]['value'], 2) }}{{ $ratios[$key]['unit'] }}
                                </span>
                            </div>
                        </div>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-5 text-center text-sm text-gray-500">
            Rasio dihitung otomatis dari data laporan keuangan periode {{ $selectedPeriod->label }}.
        </div>
        @else
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8 text-center text-gray-400">
            Pilih periode untuk menampilkan Financial Highlight.
        </div>
        @endif
    </div>
</x-app-layout>
