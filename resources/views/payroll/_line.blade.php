@php
    $empId = $line?->employee_id ?? $emp?->id ?? '';
    $sel   = $line ?? null;
@endphp
<tr class="border-b border-slate-100 hover:bg-slate-50/30">
    <input type="hidden" name="lines[{{ $i }}][employee_id]" value="{{ $empId }}">
    <td class="px-3 py-1.5">
        <select name="lines[{{ $i }}][employee_id]" onchange="onEmployeeChange(this)"
            class="w-full rounded input-modern text-xs" required>
            <option value="">-- Pilih --</option>
            @foreach($employees as $e)
                <option value="{{ $e->id }}" {{ $empId == $e->id ? 'selected' : '' }}>
                    {{ $e->name }}
                </option>
            @endforeach
        </select>
    </td>
    <td class="px-3 py-1.5">
        <input type="number" name="lines[{{ $i }}][base_salary]" step="1000" min="0"
            value="{{ $sel?->base_salary ?? $emp?->salary?->base_salary ?? 0 }}"
            class="w-full rounded input-modern text-xs text-right" required>
    </td>
    <td class="px-3 py-1.5">
        <input type="number" name="lines[{{ $i }}][allowance_transport]" step="1000" min="0"
            value="{{ $sel?->allowance_transport ?? $emp?->salary?->allowance_transport ?? 0 }}"
            class="w-full rounded input-modern text-xs text-right">
    </td>
    <td class="px-3 py-1.5">
        <input type="number" name="lines[{{ $i }}][allowance_meal]" step="1000" min="0"
            value="{{ $sel?->allowance_meal ?? $emp?->salary?->allowance_meal ?? 0 }}"
            class="w-full rounded input-modern text-xs text-right">
    </td>
    <td class="px-3 py-1.5">
        <input type="number" name="lines[{{ $i }}][allowance_other]" step="1000" min="0"
            value="{{ $sel?->allowance_other ?? $emp?->salary?->allowance_other ?? 0 }}"
            class="w-full rounded input-modern text-xs text-right">
    </td>
    <td class="px-3 py-1.5">
        <input type="number" name="lines[{{ $i }}][overtime]" step="1000" min="0"
            value="{{ $sel?->overtime ?? 0 }}"
            class="w-full rounded input-modern text-xs text-right">
    </td>
    <td class="px-3 py-1.5 text-right font-mono font-semibold text-emerald-700 row-gross bg-emerald-50/30">0</td>
    <td class="px-3 py-1.5">
        <input type="number" name="lines[{{ $i }}][bpjs_kesehatan]" step="100" min="0"
            value="{{ $sel?->bpjs_kesehatan ?? 0 }}"
            class="w-full rounded input-modern text-xs text-right">
    </td>
    <td class="px-3 py-1.5">
        <input type="number" name="lines[{{ $i }}][bpjs_tk]" step="100" min="0"
            value="{{ $sel?->bpjs_tk ?? 0 }}"
            class="w-full rounded input-modern text-xs text-right">
    </td>
    <td class="px-3 py-1.5">
        <input type="number" name="lines[{{ $i }}][pph21]" step="100" min="0"
            value="{{ $sel?->pph21 ?? 0 }}"
            class="w-full rounded input-modern text-xs text-right">
    </td>
    <td class="px-3 py-1.5">
        <select name="lines[{{ $i }}][cash_advance_id]" class="w-full rounded input-modern text-xs">
            <option value="">— Tidak Ada —</option>
            @foreach($advances as $adv)
                <option value="{{ $adv->id }}"
                    {{ ($sel?->cash_advance_id == $adv->id) ? 'selected' : '' }}>
                    {{ $adv->employee?->name }} (sisa: {{ number_format($adv->amount - $adv->settlements->sum('amount'), 0, ',', '.') }})
                </option>
            @endforeach
        </select>
    </td>
    <td class="px-3 py-1.5">
        <input type="number" name="lines[{{ $i }}][kasbon_deduction]" step="1000" min="0"
            value="{{ $sel?->kasbon_deduction ?? 0 }}"
            class="w-full rounded input-modern text-xs text-right">
    </td>
    <td class="px-3 py-1.5 text-right font-mono font-semibold text-blue-700 row-net bg-blue-50/30">0</td>
    <td class="px-3 py-1.5 text-center">
        <button type="button" onclick="removeLine(this)" class="text-red-400 hover:text-red-600 text-xs">✕</button>
    </td>
</tr>
