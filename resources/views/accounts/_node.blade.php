<div class="flex items-center py-1.5 px-2 rounded hover:bg-gray-50 group {{ $node['account']->is_header ? 'bg-gray-50 font-medium' : '' }} {{ !$node['account']->is_active ? 'opacity-50' : '' }}" style="padding-left: {{ 8 + ($node['account']->level - 1) * 20 }}px">
    <span class="w-28 text-xs font-mono {{ $node['account']->is_header ? 'text-gray-500' : 'text-gray-400' }}">{{ $node['account']->code }}</span>
    <span class="flex-1 text-sm {{ $node['account']->is_header ? 'text-gray-800 font-semibold' : 'text-gray-700' }}">
        {{ $node['account']->name }}
        @if($node['account']->is_header)
            <span class="text-[10px] text-gray-400 ml-1.5 font-normal">(header)</span>
        @endif
    </span>
    <span class="w-24 text-center text-xs {{ $node['account']->normal_balance === 'debit' ? 'text-orange-600' : 'text-blue-600' }}">
        {{ $node['account']->normal_balance === 'debit' ? 'Debet' : 'Kredit' }}
    </span>
    <span class="w-28 text-center text-xs {{ $node['account']->report_type === 'balance_sheet' ? 'text-purple-600' : 'text-green-600' }}">
        {{ $node['account']->report_type === 'balance_sheet' ? 'Neraca' : 'Laba Rugi' }}
    </span>
    <span class="w-24 text-right text-xs opacity-0 group-hover:opacity-100 transition-opacity">
        <a href="{{ route('accounts.edit', $node['account']) }}" class="text-blue-600 hover:underline">Edit</a>
        @if(!$node['has_children'] && !$node['account']->is_header)
        <form action="{{ route('accounts.destroy', $node['account']) }}" method="POST" class="inline ml-2" onsubmit="return confirm('Hapus akun ini?')">
            @csrf @method('DELETE')
            <button class="text-red-600 hover:underline">Hapus</button>
        </form>
        @endif
    </span>
</div>
@if($node['has_children'])
    @foreach($node['children'] as $child)
        @include('accounts._node', ['node' => $child])
    @endforeach
@endif
