<x-app-layout><x-slot name="header">{{ isset($user) ? 'Edit User' : 'Tambah User' }}</x-slot>
<div class="max-w-3xl bg-white rounded-xl border border-slate-200 p-6 shadow-sm">
    <form action="{{ isset($user) ? route('users.update', $user) : route('users.store') }}" method="POST" data-confirm="Yakin ingin menyimpan data user ini?">@csrf @if(isset($user)) @method('PUT') @endif
        <div class="space-y-4">
            <div><label class="block text-sm font-medium text-slate-700 mb-1.5">Nama</label><input type="text" name="name" value="{{ old('name', $user->name ?? '') }}" class="w-full rounded-lg input-modern text-sm" required></div>
            <div><label class="block text-sm font-medium text-slate-700 mb-1.5">Email</label><input type="email" name="email" value="{{ old('email', $user->email ?? '') }}" class="w-full rounded-lg input-modern text-sm" required></div>
            <div><label class="block text-sm font-medium text-slate-700 mb-1.5">Password @if(isset($user))<span class="text-slate-400 font-normal"> (biarkan kosong jika tidak diubah)</span>@endif</label><input type="password" name="password" class="w-full rounded-lg input-modern text-sm" {{ !isset($user) ? 'required' : '' }}></div>
            <div><label class="block text-sm font-medium text-slate-700 mb-1.5">Konfirmasi Password</label><input type="password" name="password_confirmation" class="w-full rounded-lg input-modern text-sm" {{ !isset($user) ? 'required' : '' }}></div>
            <div><label class="block text-sm font-medium text-slate-700 mb-1.5">Role</label>
                <select name="role" id="role-select" class="w-full rounded-lg input-modern text-sm" required>
                    <option value="admin" {{ old('role', $user->role ?? '') === 'admin' ? 'selected' : '' }}>Admin</option>
                    <option value="staff" {{ old('role', $user->role ?? '') === 'staff' ? 'selected' : '' }}>Staff</option>
                </select>
            </div>

            {{-- Menu Permissions — hanya tampil untuk staff --}}
            @php
                $menus = config('menu_permissions.menus');
                $grouped = [];
                foreach ($menus as $key => [$label, $group]) {
                    $grouped[$group][] = [$key, $label];
                }
                $savedKeys = old('menu_permissions', isset($user) && $user->relationLoaded('menuPermissions')
                    ? $user->menuPermissions->pluck('menu_key')->toArray()
                    : (isset($user) ? $user->menuPermissions()->pluck('menu_key')->toArray() : []));
                $currentRole = old('role', $user->role ?? '');
            @endphp

            <div id="menu-permissions-section" class="{{ $currentRole === 'staff' ? '' : 'hidden' }} border-t border-slate-100 pt-4 mt-4">
                <label class="block text-sm font-semibold text-slate-700 mb-3">Akses Menu (untuk Staff)</label>
                <p class="text-xs text-slate-400 mb-4">Centang menu yang boleh diakses oleh staff ini. Admin otomatis bisa akses semua menu.</p>

                @foreach ($grouped as $groupName => $items)
                    <div class="mb-4">
                        @if ($groupName)
                            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">{{ $groupName }}</span>
                        @endif
                        <div class="grid grid-cols-2 gap-x-4 gap-y-0.5 mt-1">
                            @foreach ($items as [$key, $label])
                                <label class="flex items-center gap-2 text-sm text-slate-700 cursor-pointer hover:bg-indigo-50 rounded px-1.5 py-1 transition">
                                    <input type="checkbox" name="menu_permissions[]" value="{{ $key }}"
                                        class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                                        {{ in_array($key, $savedKeys) ? 'checked' : '' }}>
                                    {{ $label }}
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endforeach

                {{-- Select All / Deselect All --}}
                <div class="flex gap-2 mt-2 pt-3 border-t border-slate-100">
                    <button type="button" onclick="toggleAllMenus(true)" class="text-xs text-indigo-600 hover:underline font-medium">Pilih Semua</button>
                    <span class="text-slate-300">|</span>
                    <button type="button" onclick="toggleAllMenus(false)" class="text-xs text-slate-500 hover:underline font-medium">Hapus Semua</button>
                </div>
            </div>
        </div>
        <div class="flex gap-3 mt-6">
            <button type="submit" class="px-5 py-2.5 rounded-lg text-white text-sm font-semibold btn-primary">Simpan</button>
            <a href="{{ route('users.index') }}" class="px-5 py-2.5 rounded-lg border border-slate-200 text-slate-700 text-sm font-semibold hover:bg-slate-50 transition">Batal</a>
        </div>
    </form>
</div>

@push('scripts')
<script>
    // Show/hide menu permissions section based on role
    const roleSelect = document.getElementById('role-select');
    const menuSection = document.getElementById('menu-permissions-section');

    roleSelect.addEventListener('change', function () {
        if (this.value === 'staff') {
            menuSection.classList.remove('hidden');
        } else {
            menuSection.classList.add('hidden');
        }
    });

    // Select all / deselect all checkboxes
    function toggleAllMenus(checked) {
        menuSection.querySelectorAll('input[type="checkbox"]').forEach(cb => {
            cb.checked = checked;
        });
    }
</script>
@endpush
</x-app-layout>
