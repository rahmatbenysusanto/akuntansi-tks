<x-app-layout><x-slot name="header">Jadwal Angsuran: {{ $loan->name }}</x-slot>
<div class="bg-white rounded-lg shadow-sm border">
    <table class="w-full text-sm">
        <thead><tr class="text-left text-gray-500 border-b bg-gray-50">
            <th class="px-4 py-3">#</th><th class="px-4 py-3">Jatuh Tempo</th><th class="px-4 py-3 text-right">Pokok</th>
            <th class="px-4 py-3 text-right">Bunga</th><th class="px-4 py-3 text-right">Total</th><th class="px-4 py-3">Status</th><th class="px-4 py-3"></th>
        </tr></thead>
        <tbody>
        @foreach($schedules as $s)
            <tr class="border-b hover:bg-gray-50">
                <td class="px-4 py-2">{{ $s->installment_no }}</td>
                <td class="px-4 py-2">{{ $s->due_date->format('d/m/Y') }}</td>
                <td class="px-4 py-2 text-right">{{ number_format($s->principal_amount, 0, ',', '.') }}</td>
                <td class="px-4 py-2 text-right">{{ number_format($s->interest_amount, 0, ',', '.') }}</td>
                <td class="px-4 py-2 text-right">{{ number_format($s->total_amount, 0, ',', '.') }}</td>
                <td class="px-4 py-2">
                    <span class="px-2 py-0.5 rounded-full text-xs
                        {{ $s->status == 'paid' ? 'bg-green-100 text-green-700' : ($s->status == 'overdue' ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700') }}">
                        {{ ucfirst($s->status) }}
                    </span>
                </td>
                <td class="px-4 py-2">
                    @if($s->status == 'unpaid' || $s->status == 'overdue')
                        <form method="POST" action="{{ route('loans.pay-installment', $loan) }}">
                            @csrf
                            <input type="hidden" name="schedule_id" value="{{ $s->id }}">
                            <button type="button" onclick="confirmAndSubmit(this, 'Bayar cicilan ini? Jurnal akan otomatis terbentuk.')" class="text-xs text-blue-600 hover:underline">Bayar</button>
                        </form>
                    @endif
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
</x-app-layout>
