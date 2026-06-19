<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-5 py-2.5 rounded-lg font-semibold text-xs text-white uppercase tracking-wider transition-all duration-200 btn-primary']) }}>
    {{ $slot }}
</button>
