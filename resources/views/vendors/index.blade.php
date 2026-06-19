<x-app-layout>
    <x-slot name="header">Data Vendor</x-slot>
    <div class="space-y-4">
        <div class="flex justify-end">
            <a href="{{ route('vendors.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm hover:bg-blue-700">+ Tambah Vendor</a>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-x-auto">
            <table class="w-full text-sm">
                <thead><tr class="text-left text-gray-500 border-b bg-gray-50">
                    <th class="px-4 py-3">Kode</th><th class="px-4 py-3">Nama</th><th class="px-4 py-3">Telepon</th>
                    <th class="px-4 py-3">NPWP</th><th class="px-4 py-3">Term (hari)</th><th class="px-4 py-3">Status</th><th class="px-4 py-3">Aksi</th>
                </tr></thead>
                <tbody>
                @forelse($vendors as $v)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="px-4 py-2">{{ $v->code }}</td>
                        <td class="px-4 py-2">{{ $v->name }}</td>
                        <td class="px-4 py-2">{{ $v->phone ?? '-' }}</td>
                        <td class="px-4 py-2">{{ $v->npwp ?? '-' }}</td>
                        <td class="px-4 py-2">{{ $v->payment_term_days }}</td>
                        <td class="px-4 py-2">{{ $v->is_active ? 'Aktif' : 'Nonaktif' }}</td>
                        <td class="px-4 py-2"><a href="{{ route('vendors.edit', $v) }}" class="text-blue-600 hover:underline">Edit</a></td>
                    </tr>
                @empty<tr><td colspan="7" class="px-4 py-8 text-center text-gray-400">Belum ada vendor.</td></tr>
                @endforelse
                </tbody>
            </table>
            @if($vendors->hasPages())<div class="px-4 py-3 border-t">{{ $vendors->links() }}</div>@endif
        </div>
    </div>
</x-app-layout>
