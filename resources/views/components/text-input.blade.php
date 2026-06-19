@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'w-full rounded-lg input-modern text-sm text-slate-700 placeholder:text-slate-400 disabled:bg-slate-50 disabled:text-slate-400']) }}>
