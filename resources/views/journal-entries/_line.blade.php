<tr class="border-b border-slate-100">
    <td class="py-1.5 pr-2">
        <select name="lines[{{ $index }}][account_id]" class="w-full rounded-lg input-modern text-sm account-select" required>
            <option value="">-- Pilih Akun --</option>
            @foreach($accounts as $acc)
                <option value="{{ $acc->id }}" {{ (old("lines.{$index}.account_id", $line['account_id'] ?? ($line->account_id ?? '')) == $acc->id) ? 'selected' : '' }}>{{ $acc->code }} - {{ $acc->name }}</option>
            @endforeach
        </select>
    </td>
    <td class="py-1.5 px-1"><input type="number" step="0.01" name="lines[{{ $index }}][debit]" value="{{ old("lines.{$index}.debit", $line['debit'] ?? ($line->debit ?? 0)) }}" class="w-full text-right rounded-lg input-modern text-sm debit-input" oninput="calculateTotals()"></td>
    <td class="py-1.5 px-1"><input type="number" step="0.01" name="lines[{{ $index }}][credit]" value="{{ old("lines.{$index}.credit", $line['credit'] ?? ($line->credit ?? 0)) }}" class="w-full text-right rounded-lg input-modern text-sm credit-input" oninput="calculateTotals()"></td>
    <td class="py-1.5 pl-2 text-center">
        @if($index > 1 || !isset($entry))
        <button type="button" onclick="this.closest('tr').remove(); calculateTotals();" class="text-red-400 hover:text-red-600">
            <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
        </button>
        @endif
    </td>
</tr>
