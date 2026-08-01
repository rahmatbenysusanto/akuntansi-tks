<x-app-layout>
<x-slot name="header">Request Overtime (WH)</x-slot>
<div class="space-y-4">

    <div class="flex justify-between items-center">
        <div class="text-sm text-slate-500">
            Daftar request overtime untuk disubmit ke client.
        </div>
        <a href="{{ route('overtime-requests.create') }}"
            class="px-5 py-2.5 rounded-lg text-white text-sm font-semibold btn-primary">
            + Request Baru
        </a>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-x-auto">
        <table class="w-full text-sm table-modern">
            <thead>
                <tr class="bg-slate-50/50">
                    <th class="px-5 py-3 text-left">No. Request</th>
                    <th class="px-5 py-3 text-left">Tanggal</th>
                    <th class="px-5 py-3 text-left">Client</th>
                    <th class="px-5 py-3 text-left">Jenis Kegiatan</th>
                    <th class="px-5 py-3 text-left">Tgl. Overtime</th>
                    <th class="px-5 py-3 text-left">Durasi</th>
                    <th class="px-5 py-3 text-center">Status</th>
                    <th class="px-5 py-3 text-left">Aksi</th>
                </tr>
            </thead>
            <tbody>
            @forelse($requests as $r)
                <tr class="border-b border-slate-100 hover:bg-slate-50/80">
                    <td class="px-5 py-3 font-medium text-slate-800">{{ $r->request_no }}</td>
                    <td class="px-5 py-3 text-slate-600">{{ $r->request_date->format('d/m/Y') }}</td>
                    <td class="px-5 py-3">{{ $r->client_name }}</td>
                    <td class="px-5 py-3 text-slate-700">{{ $r->activity_type_label }}</td>
                    <td class="px-5 py-3 text-slate-600">{{ $r->overtime_date->format('d/m/Y') }}</td>
                    <td class="px-5 py-3 text-slate-700">
                        {{ \Carbon\Carbon::parse($r->overtime_start_time)->format('H:i') }}
                        @if($r->overtime_end_time)
                            — {{ \Carbon\Carbon::parse($r->overtime_end_time)->format('H:i') }}
                            <span class="text-xs text-slate-400 ml-1">({{ $r->duration_label }})</span>
                        @endif
                    </td>
                    <td class="px-5 py-3 text-center">
                        <span class="badge {{ match($r->status) {
                            'signed' => 'bg-emerald-50 text-emerald-700',
                            'sent'   => 'bg-blue-50 text-blue-700',
                            default  => 'bg-amber-50 text-amber-700'
                        } }}">
                            {{ $r->status_label }}
                        </span>
                    </td>
                    <td class="px-5 py-3">
                        <div class="flex gap-2 flex-wrap">
                            {{-- PDF — selalu tersedia --}}
                            <a href="{{ route('overtime-requests.pdf', $r) }}"
                                class="text-indigo-600 hover:text-indigo-800 text-xs font-medium"
                                title="Download PDF">
                                📄 PDF
                            </a>

                            {{-- Edit — hanya untuk draft --}}
                            @if($r->status === 'draft')
                                <a href="{{ route('overtime-requests.edit', $r) }}"
                                    class="text-amber-600 hover:text-amber-800 text-xs font-medium">
                                    ✏️ Edit
                                </a>
                            @endif

                            {{-- Kirim ke client --}}
                            @if($r->status === 'draft')
                                <form method="POST" action="{{ route('overtime-requests.send', $r) }}" class="inline">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="text-blue-600 hover:text-blue-800 text-xs font-medium"
                                        onclick="return confirm('Tandai request sudah dikirim ke client?')">
                                        📤 Kirim
                                    </button>
                                </form>
                            @endif

                            {{-- Tandatangani --}}
                            @if($r->status === 'sent')
                                <form method="POST" action="{{ route('overtime-requests.sign', $r) }}" class="inline">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="text-emerald-600 hover:text-emerald-800 text-xs font-medium"
                                        onclick="return confirm('Tandai request sudah ditandatangani client?')">
                                        ✍️ TTD
                                    </button>
                                </form>
                            @endif

                            {{-- Delete — hanya draft --}}
                            @if($r->status === 'draft')
                                <form method="POST" action="{{ route('overtime-requests.destroy', $r) }}" class="inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-800 text-xs font-medium"
                                        onclick="return confirm('Hapus request ini?')">
                                        🗑️ Hapus
                                    </button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="8" class="px-5 py-10 text-center text-slate-400">Belum ada request overtime.</td></tr>
            @endforelse
            </tbody>
        </table>
        @if($requests->hasPages())
            <div class="px-5 py-3 border-t border-slate-100">{{ $requests->links() }}</div>
        @endif
    </div>
</div>
</x-app-layout>
