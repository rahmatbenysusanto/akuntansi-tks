<x-app-layout>
<x-slot name="header">Data Karyawan</x-slot>
<div class="space-y-4">
    <div class="flex justify-end">
        <a href="{{ route('employees.create') }}" class="px-5 py-2.5 rounded-lg text-white text-sm font-semibold btn-primary">+ Tambah Karyawan</a>
    </div>
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-x-auto">
        <table class="w-full text-sm table-modern">
            <thead><tr class="bg-slate-50/50">
                <th class="px-5 py-3 text-left">NIK</th><th class="px-5 py-3 text-left">Nama</th>
                <th class="px-5 py-3 text-left">Departemen</th><th class="px-5 py-3 text-left">Jabatan</th>
                <th class="px-5 py-3 text-left">No. Rekening</th><th class="px-5 py-3 text-left">Status</th><th class="px-5 py-3 text-left">Aksi</th>
            </tr></thead>
            <tbody>
            @forelse($employees as $e)
                <tr class="border-b border-slate-100 hover:bg-slate-50/80">
                    <td class="px-5 py-3">{{ $e->employee_no }}</td>
                    <td class="px-5 py-3 font-medium text-slate-800">{{ $e->name }}</td>
                    <td class="px-5 py-3 text-slate-600">{{ $e->department ?? '-' }}</td>
                    <td class="px-5 py-3 text-slate-600">{{ $e->position ?? '-' }}</td>
                    <td class="px-5 py-3 text-slate-600">{{ $e->bank_account_no ?? '-' }}</td>
                    <td class="px-5 py-3">@if($e->is_active)<span class="badge bg-emerald-50 text-emerald-700">Aktif</span>@else<span class="badge bg-slate-100 text-slate-500">Nonaktif</span>@endif</td>
                    <td class="px-5 py-3"><a href="{{ route('employees.edit', $e) }}" class="text-indigo-600 hover:text-indigo-800 text-sm">Edit</a></td>
                </tr>
            @empty
                <tr><td colspan="7" class="px-5 py-8 text-center text-slate-400">Belum ada data karyawan.</td></tr>
            @endforelse
            </tbody>
        </table>
        @if($employees->hasPages())<div class="px-5 py-3 border-t border-slate-100">{{ $employees->links() }}</div>@endif
    </div>
</div>
</x-app-layout>
