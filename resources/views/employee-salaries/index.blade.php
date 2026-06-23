<x-app-layout>
<x-slot name="header">Setup Gaji Karyawan</x-slot>
<div class="space-y-4">
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-x-auto">
        <table class="w-full text-sm table-modern">
            <thead>
                <tr class="bg-slate-50/50">
                    <th class="px-5 py-3 text-left">Karyawan</th>
                    <th class="px-5 py-3 text-left">Departemen</th>
                    <th class="px-5 py-3 text-right">Gaji Pokok</th>
                    <th class="px-5 py-3 text-right">Tunjangan</th>
                    <th class="px-5 py-3 text-center">BPJS Kes %</th>
                    <th class="px-5 py-3 text-center">BPJS TK %</th>
                    <th class="px-5 py-3 text-left">Aksi</th>
                </tr>
            </thead>
            <tbody>
            @forelse($employees as $emp)
                @php $sal = $emp->salary; @endphp
                <tr class="border-b border-slate-100 hover:bg-slate-50/80">
                    <td class="px-5 py-3 font-medium text-slate-800">
                        {{ $emp->name }}
                        <div class="text-xs text-slate-400 font-normal">{{ $emp->employee_no }}</div>
                    </td>
                    <td class="px-5 py-3 text-slate-500">{{ $emp->department ?? '-' }}</td>
                    <td class="px-5 py-3 text-right font-mono">
                        @if($sal) @rupiah($sal->base_salary) @else <span class="text-slate-300">Belum diset</span> @endif
                    </td>
                    <td class="px-5 py-3 text-right font-mono text-slate-600">
                        @if($sal) @rupiah($sal->allowance_transport + $sal->allowance_meal + $sal->allowance_other) @else <span class="text-slate-300">-</span> @endif
                    </td>
                    <td class="px-5 py-3 text-center text-slate-600">
                        {{ $sal ? $sal->bpjs_kesehatan_pct . '%' : '-' }}
                    </td>
                    <td class="px-5 py-3 text-center text-slate-600">
                        {{ $sal ? $sal->bpjs_tk_pct . '%' : '-' }}
                    </td>
                    <td class="px-5 py-3">
                        <a href="{{ route('employee-salaries.edit', $emp) }}"
                            class="text-indigo-600 hover:text-indigo-800 text-sm">
                            {{ $sal ? 'Edit' : 'Setup' }}
                        </a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="px-5 py-10 text-center text-slate-400">Belum ada karyawan aktif.</td></tr>
            @endforelse
            </tbody>
        </table>
        @if($employees->hasPages())
            <div class="px-5 py-3 border-t border-slate-100">{{ $employees->links() }}</div>
        @endif
    </div>
</div>
</x-app-layout>
