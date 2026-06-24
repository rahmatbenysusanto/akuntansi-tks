<x-app-layout>
    <x-slot name="header">Chart of Account</x-slot>

    <div class="space-y-4">
        <div class="flex justify-between items-center">
            <div class="flex items-center gap-3">
                <p class="text-sm text-gray-500">Total: <span id="account-count">{{ $accounts->count() }}</span> akun</p>
                <div class="relative">
                    <input type="text" id="search-code" placeholder="Cari kode akun..." class="pl-8 pr-3 py-1.5 text-sm border border-gray-200 rounded-lg focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 outline-none w-52 transition-all">
                    <svg class="w-4 h-4 text-gray-400 absolute left-2.5 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
            </div>
            <a href="{{ route('accounts.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm hover:bg-blue-700">
                + Tambah Akun
            </a>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-x-auto">
            <!-- Header -->
            <div class="flex items-center px-4 py-2.5 bg-gray-50 border-b border-gray-200 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                <span class="w-28">Kode Akun</span>
                <span class="flex-1">Nama Akun</span>
                <span class="w-24 text-center">Saldo Normal</span>
                <span class="w-28 text-center">Posisi Laporan</span>
                <span class="w-24 text-right">Aksi</span>
            </div>

            <!-- Tree Content -->
            <div class="p-2" id="accounts-tree">
                @if($tree)
                    @foreach($tree as $node)
                        @include('accounts._node', ['node' => $node])
                    @endforeach
                @else
                    <p class="text-gray-400 text-sm text-center py-8">Belum ada akun.</p>
                @endif
                <div id="no-result" class="text-gray-400 text-sm text-center py-8 hidden">Tidak ada akun yang cocok dengan pencarian.</div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('search-code');
            const rows = document.querySelectorAll('[data-code-lower]');
            const totalCount = {{ $accounts->count() }};
            const countSpan = document.getElementById('account-count');
            const noResult = document.getElementById('no-result');

            if (!searchInput) return;

            searchInput.addEventListener('input', function() {
                const query = this.value.trim().toLowerCase();
                let visibleCount = 0;

                rows.forEach(row => {
                    const code = row.getAttribute('data-code-lower');
                    if (!query || code.includes(query)) {
                        row.style.display = '';
                        visibleCount++;
                    } else {
                        row.style.display = 'none';
                    }
                });

                countSpan.textContent = query ? visibleCount : totalCount;
                noResult.classList.toggle('hidden', visibleCount > 0 || !query);
            });
        });
    </script>
    @endpush
</x-app-layout>
