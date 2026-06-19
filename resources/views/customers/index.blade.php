<x-app-layout>
    <x-slot name="header">Data Customer</x-slot>
    <div class="space-y-4">
        <div class="flex justify-end">
            <a href="{{ route('customers.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm hover:bg-blue-700">+ Tambah Customer</a>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-x-auto">
            <table class="w-full text-sm">
                <thead><tr class="text-left text-gray-500 border-b bg-gray-50">
                    <th class="px-4 py-3">Kode</th><th class="px-4 py-3">Nama</th><th class="px-4 py-3">Telepon</th>
                    <th class="px-4 py-3">NPWP</th><th class="px-4 py-3">Term (hari)</th><th class="px-4 py-3">Status</th><th class="px-4 py-3">Aksi</th>
                </tr></thead>
                <tbody>
                @forelse($customers as $c)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="px-4 py-2">{{ $c->code }}</td>
                        <td class="px-4 py-2">{{ $c->name }}</td>
                        <td class="px-4 py-2">{{ $c->phone ?? '-' }}</td>
                        <td class="px-4 py-2">{{ $c->npwp ?? '-' }}</td>
                        <td class="px-4 py-2">{{ $c->payment_term_days }}</td>
                        <td class="px-4 py-2">{{ $c->is_active ? 'Aktif' : 'Nonaktif' }}</td>
                        <td class="px-4 py-2"><a href="{{ route('customers.edit', $c) }}" class="text-blue-600 hover:underline">Edit</a></td>
                    </tr>
                @empty<tr><td colspan="7" class="px-4 py-8 text-center text-gray-400">Belum ada customer.</td></tr>
                @endforelse
                </tbody>
            </table>
            @if($customers->hasPages())<div class="px-4 py-3 border-t">{{ $customers->links() }}</div>@endif
        </div>
    </div>
</x-app-layout>
