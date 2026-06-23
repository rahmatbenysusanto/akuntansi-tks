<x-app-layout>
    <x-slot name="header">User Management</x-slot>

    <div class="space-y-4">
        <div class="flex justify-end">
            <a href="{{ route('users.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm hover:bg-blue-700">+ Tambah User</a>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-500 border-b border-gray-200 bg-gray-50">
                        <th class="px-5 py-3">Nama</th>
                        <th class="px-5 py-3">Email</th>
                        <th class="px-5 py-3">Role</th>
                        <th class="px-5 py-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                    <tr class="border-b border-gray-100 hover:bg-gray-50">
                        <td class="px-5 py-3">{{ $user->name }}</td>
                        <td class="px-5 py-3">{{ $user->email }}</td>
                        <td class="px-5 py-3">
                            <span class="px-2 py-0.5 {{ $user->isAdmin() ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-600' }} rounded-full text-xs">
                                {{ ucfirst($user->role) }}
                            </span>
                        </td>
                        <td class="px-5 py-3">
                            <a href="{{ route('users.edit', $user) }}" class="text-xs text-blue-600 hover:underline">Edit</a>
                            @if($user->id !== auth()->id())
                            <form action="{{ route('users.destroy', $user) }}" method="POST" class="inline ml-2">
                                @csrf @method('DELETE')
                                <button type="button" onclick="confirmAndSubmit(this, 'Hapus user ini?')" class="text-xs text-red-600 hover:underline">Hapus</button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
