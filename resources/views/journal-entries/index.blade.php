<x-app-layout>
    <x-slot name="header">Jurnal Umum</x-slot>

    <div class="space-y-4">
        <!-- Filter & Actions -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-5">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <form method="GET" class="flex flex-wrap items-end gap-3">
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Periode</label>
                        <select name="period_id" class="rounded-lg border-gray-300 text-sm" onchange="this.form.submit()">
                            <option value="">Semua</option>
                            @foreach($periods as $p)
                                <option value="{{ $p->id }}" {{ request('period_id') == $p->id ? 'selected' : '' }}>{{ $p->label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Status</label>
                        <select name="status" class="rounded-lg border-gray-300 text-sm" onchange="this.form.submit()">
                            <option value="">Semua</option>
                            <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                            <option value="posted" {{ request('status') == 'posted' ? 'selected' : '' }}>Posted</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Dari Tanggal</label>
                        <input type="date" name="date_from" value="{{ request('date_from') }}" class="rounded-lg border-gray-300 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Sampai Tanggal</label>
                        <input type="date" name="date_to" value="{{ request('date_to') }}" class="rounded-lg border-gray-300 text-sm">
                    </div>
                    <button type="submit" class="px-3 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm hover:bg-gray-200">Filter</button>
                </form>
                <a href="{{ route('journal-entries.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm hover:bg-blue-700">
                    + Jurnal Baru
                </a>
            </div>
        </div>

        <!-- Entries List -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-gray-500 border-b border-gray-200 bg-gray-50">
                            <th class="px-4 py-3">Tanggal</th>
                            <th class="px-4 py-3">No. Bukti</th>
                            <th class="px-4 py-3">Keterangan</th>
                            <th class="px-4 py-3">Periode</th>
                            <th class="px-4 py-3 text-right">Total</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($entries as $entry)
                        <tr class="border-b border-gray-100 hover:bg-gray-50">
                            <td class="px-4 py-3">{{ $entry->entry_date->format('d/m/Y') }}</td>
                            <td class="px-4 py-3">{{ $entry->reference_no }}</td>
                            <td class="px-4 py-3 max-w-xs truncate">{{ $entry->description }}</td>
                            <td class="px-4 py-3">{{ $entry->accountingPeriod->label }}</td>
                            <td class="px-4 py-3 text-right font-mono">
                                {{ number_format($entry->lines->sum('debit'), 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3">
                                @if($entry->isDraft())
                                    <span class="px-2 py-0.5 bg-amber-100 text-amber-700 rounded-full text-xs">Draft</span>
                                @else
                                    <span class="px-2 py-0.5 bg-green-100 text-green-700 rounded-full text-xs">Posted</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex gap-2">
                                    @if($entry->isDraft())
                                        <a href="{{ route('journal-entries.edit', $entry) }}" class="text-xs text-blue-600 hover:underline">Edit</a>
                                        <form action="{{ route('journal-entries.post', $entry) }}" method="POST" class="inline">
                                            @csrf @method('PATCH')
                                            <button type="button" onclick="confirmAndSubmit(this, 'Posting jurnal ini?')" class="text-xs text-green-600 hover:underline">Posting</button>
                                        </form>
                                        <form action="{{ route('journal-entries.destroy', $entry) }}" method="POST" class="inline">
                                            @csrf @method('DELETE')
                                            <button type="button" onclick="confirmAndSubmit(this, 'Hapus jurnal ini?')" class="text-xs text-red-600 hover:underline">Hapus</button>
                                        </form>
                                    @else
                                        <a href="{{ route('journal-entries.edit', $entry) }}" class="text-xs text-gray-400">Detail</a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-gray-400">Belum ada jurnal.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($entries->hasPages())
                <div class="px-4 py-3 border-t border-gray-200">
                    {{ $entries->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
