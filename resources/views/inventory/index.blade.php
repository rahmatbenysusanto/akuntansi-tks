<x-app-layout><x-slot name="header">Items / Inventory</x-slot>
<div class="flex justify-end mb-4"><a href="{{ route('items.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm">+ Item Baru</a></div>
<div class="bg-white rounded-lg shadow-sm border overflow-x-auto">
    <table class="w-full text-sm">
        <thead><tr class="text-left text-gray-500 border-b bg-gray-50">
            <th class="px-4 py-3">SKU</th><th class="px-4 py-3">Nama</th><th class="px-4 py-3">Unit</th><th class="px-4 py-3">Kategori</th><th class="px-4 py-3">Method</th><th class="px-4 py-3">Min Stock</th>
        </tr></thead>
        <tbody>
        @forelse($items as $i)
            <tr class="border-b hover:bg-gray-50">
                <td class="px-4 py-2">{{ $i->sku }}</td><td class="px-4 py-2">{{ $i->name }}</td>
                <td class="px-4 py-2">{{ $i->unit }}</td><td class="px-4 py-2">{{ $i->category ?? '-' }}</td>
                <td class="px-4 py-2">{{ strtoupper($i->costing_method) }}</td><td class="px-4 py-2">{{ $i->min_stock }}</td>
            </tr>
        @empty<tr><td colspan="6" class="px-4 py-8 text-center text-gray-400">Belum ada item.</td></tr>
        @endforelse
        </tbody>
    </table>
    @if($items->hasPages())<div class="px-4 py-3 border-t">{{ $items->links() }}</div>@endif
</div>
</x-app-layout>
