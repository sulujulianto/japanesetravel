<button {{ $attributes->merge(['type' => 'submit', 'class' => 'auth-primary inline-flex items-center justify-center px-4 py-2 text-sm font-semibold']) }}>
    {{ $slot }}
</button>
