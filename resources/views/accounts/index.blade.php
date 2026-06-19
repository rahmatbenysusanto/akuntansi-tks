<x-app-layout>
    <x-slot name="header">Chart of Account</x-slot>

    <div class="space-y-4">
        <div class="flex justify-between items-center">
            <p class="text-sm text-gray-500">Total: {{ $accounts->count() }} akun</p>
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
            <div class="p-2">
                @if($tree)
                    @foreach($tree as $node)
                        @include('accounts._node', ['node' => $node])
                    @endforeach
                @else
                    <p class="text-gray-400 text-sm text-center py-8">Belum ada akun.</p>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
