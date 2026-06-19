@props(['active'])

@php
$classes = ($active ?? false)
            ? 'flex items-center px-3 py-2 rounded-lg text-sm font-medium nav-link-active text-white'
            : 'flex items-center px-3 py-2 rounded-lg text-sm font-medium text-white/70 hover:text-white nav-link';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
