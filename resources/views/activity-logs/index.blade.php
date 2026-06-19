<x-app-layout>
    <x-slot name="header">Riwayat Aktivitas</x-slot>

    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-500 border-b border-gray-200 bg-gray-50">
                        <th class="px-4 py-3">Waktu</th>
                        <th class="px-4 py-3">User</th>
                        <th class="px-4 py-3">Aksi</th>
                        <th class="px-4 py-3">Deskripsi</th>
                        <th class="px-4 py-3">Detail</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($activities as $log)
                    <tr class="border-b border-gray-100 hover:bg-gray-50">
                        <td class="px-4 py-3 text-xs">{{ $log->created_at->format('d/m/Y H:i') }}</td>
                        <td class="px-4 py-3">{{ $log->causer?->name ?? 'System' }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-0.5 rounded-full text-xs {{
                                $log->event === 'created' ? 'bg-green-100 text-green-700' :
                                ($log->event === 'updated' ? 'bg-blue-100 text-blue-700' :
                                'bg-red-100 text-red-700')
                            }}">
                                {{ $log->event ?? 'unknown' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 max-w-xs truncate">{{ $log->description }}</td>
                        <td class="px-4 py-3 text-xs max-w-xs truncate text-gray-400">
                            {{ class_basename($log->subject_type) }} #{{ $log->subject_id }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-gray-400">Belum ada aktivitas.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($activities->hasPages())
            <div class="px-4 py-3 border-t border-gray-200">
                {{ $activities->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
