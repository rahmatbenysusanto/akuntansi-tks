<x-app-layout><x-slot name="header">{{ isset($user) ? 'Edit User' : 'Tambah User' }}</x-slot>
<div class="max-w-lg bg-white rounded-xl border border-slate-200 p-6 shadow-sm">
    <form action="{{ isset($user) ? route('users.update', $user) : route('users.store') }}" method="POST">@csrf @if(isset($user)) @method('PUT') @endif
        <div class="space-y-4">
            <div><label class="block text-sm font-medium text-slate-700 mb-1.5">Nama</label><input type="text" name="name" value="{{ old('name', $user->name ?? '') }}" class="w-full rounded-lg input-modern text-sm" required></div>
            <div><label class="block text-sm font-medium text-slate-700 mb-1.5">Email</label><input type="email" name="email" value="{{ old('email', $user->email ?? '') }}" class="w-full rounded-lg input-modern text-sm" required></div>
            <div><label class="block text-sm font-medium text-slate-700 mb-1.5">Password @if(isset($user))<span class="text-slate-400 font-normal"> (biarkan kosong jika tidak diubah)</span>@endif</label><input type="password" name="password" class="w-full rounded-lg input-modern text-sm" {{ !isset($user) ? 'required' : '' }}></div>
            <div><label class="block text-sm font-medium text-slate-700 mb-1.5">Konfirmasi Password</label><input type="password" name="password_confirmation" class="w-full rounded-lg input-modern text-sm" {{ !isset($user) ? 'required' : '' }}></div>
            <div><label class="block text-sm font-medium text-slate-700 mb-1.5">Role</label>
                <select name="role" class="w-full rounded-lg input-modern text-sm" required><option value="admin">Admin</option><option value="staff">Staff</option></select></div>
        </div>
        <div class="flex gap-3 mt-6">
            <button type="submit" class="px-5 py-2.5 rounded-lg text-white text-sm font-semibold btn-primary">Simpan</button>
            <a href="{{ route('users.index') }}" class="px-5 py-2.5 rounded-lg border border-slate-200 text-slate-700 text-sm font-semibold hover:bg-slate-50 transition">Batal</a>
        </div>
    </form>
</div>
</x-app-layout>
