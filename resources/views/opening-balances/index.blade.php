<x-app-layout>
    <x-slot name="header">Saldo Awal</x-slot>

    <div class="space-y-4">
        <!-- Period Filter -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-5">
            <form method="GET" class="flex gap-3 items-end">
                <div>
                    <label class="block text-sm text-gray-600 mb-1">Pilih Periode</label>
                    <select name="period_id" class="rounded-lg border-gray-300 text-sm" onchange="this.form.submit()">
                        @foreach($periods as $p)
                            <option value="{{ $p->id }}" {{ $selectedPeriod?->id == $p->id ? 'selected' : '' }}>{{ $p->label }}</option>
                        @endforeach
                    </select>
                </div>
            </form>
        </div>

        @if($selectedPeriod)
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <form action="{{ route('opening-balances.store') }}" method="POST">
                @csrf
                <input type="hidden" name="period_id" value="{{ $selectedPeriod->id }}">

                <div class="overflow-x-auto max-h-[70vh] overflow-y-auto">
                    <table class="w-full text-sm">
                        <thead class="sticky top-0 bg-gray-50">
                            <tr class="text-left text-gray-500 border-b border-gray-200">
                                <th class="px-4 py-3 w-24">Kode</th>
                                <th class="px-4 py-3">Nama Akun</th>
                                <th class="px-4 py-3 w-40 text-right">Debet (Rp)</th>
                                <th class="px-4 py-3 w-40 text-right">Kredit (Rp)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($accounts as $account)
                                @php
                                    $bal = $balances->get($account->id);
                                    $isIncomeStmt = $account->report_type === 'income_statement';
                                @endphp
                                <tr class="border-b border-gray-100 hover:bg-gray-50 {{ $account->is_header ? 'bg-gray-50 font-semibold' : '' }} {{ $isIncomeStmt && !$account->is_header ? 'text-gray-300' : '' }}">
                                    <td class="px-4 py-2 font-mono text-xs text-gray-400">{{ $account->code }}</td>
                                    <td class="px-4 py-2 {{ $account->is_header ? 'text-gray-800' : 'text-gray-700' }}" style="padding-left: {{ 16 + ($account->level - 1) * 20 }}px">
                                        {{ $account->name }}
                                        @if($isIncomeStmt && !$account->is_header)
                                            <span class="text-[10px] text-gray-400 ml-1 italic">(akun laba rugi, mulai dari 0)</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-2">
                                        @if(!$account->is_header && !$isIncomeStmt)
                                            <input type="number" step="0.01" name="balances[{{ $account->id }}][debit]"
                                                value="{{ old("balances.{$account->id}.debit", $bal?->debit ?? 0) }}"
                                                class="w-full text-right rounded border-gray-200 text-sm"
                                                @if($account->normal_balance === 'credit') readonly style="background:#f9fafb" @endif>
                                        @endif
                                        <input type="hidden" name="balances[{{ $account->id }}][account_id]" value="{{ $account->id }}">
                                    </td>
                                    <td class="px-4 py-2">
                                        @if(!$account->is_header && !$isIncomeStmt)
                                            <input type="number" step="0.01" name="balances[{{ $account->id }}][credit]"
                                                value="{{ old("balances.{$account->id}.credit", $bal?->credit ?? 0) }}"
                                                class="w-full text-right rounded border-gray-200 text-sm"
                                                @if($account->normal_balance === 'debit') readonly style="background:#f9fafb" @endif>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="px-5 py-4 border-t border-gray-200 flex items-center justify-between">
                    @if($selectedPeriod->status === 'closed')
                        <p class="text-sm text-red-500">Periode ini sudah ditutup, tidak bisa mengubah saldo awal.</p>
                    @endif
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed"
                        {{ $selectedPeriod->status === 'closed' ? 'disabled' : '' }}>
                        Simpan Saldo Awal
                    </button>
                </div>
            </form>
        </div>
        @endif
    </div>
</x-app-layout>
