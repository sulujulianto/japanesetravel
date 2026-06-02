<x-guest-layout>
    <div class="mb-8">
        <h1 class="text-2xl font-semibold tracking-tight text-[var(--auth-ink)]">{{ __('Buat akun') }}</h1>
        <p class="mt-2 text-sm leading-6 text-[var(--auth-muted)]">{{ __('Simpan destinasi, tulis ulasan, dan kelola pesanan oleh-oleh Anda.') }}</p>
    </div>

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <div>
            <x-input-label for="username" :value="__('Username')" />
            <x-text-input id="username" class="mt-2 block" type="text" name="username" :value="old('username')" required autofocus autocomplete="username" placeholder="{{ __('Username unik') }}" />
            <x-input-error :messages="$errors->get('username')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="mt-2 block" type="email" name="email" :value="old('email')" required autocomplete="username" placeholder="{{ __('nama@email.com') }}" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input id="password" class="mt-2 block"
                            type="password"
                            name="password"
                            required autocomplete="new-password"
                            placeholder="{{ __('Minimal 8 karakter') }}" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Konfirmasi Password')" />
            <x-text-input id="password_confirmation" class="mt-2 block"
                            type="password"
                            name="password_confirmation"
                            required autocomplete="new-password"
                            placeholder="{{ __('Ulangi password') }}" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="mt-6">
            <x-primary-button class="w-full py-3">
                {{ __('Daftar') }}
            </x-primary-button>
        </div>

        <div class="mt-6 text-center">
            <p class="text-sm text-[var(--auth-muted)]">{{ __('Sudah punya akun?') }}
                <a href="{{ route('login') }}" class="auth-link font-semibold">{{ __('Masuk') }}</a>
            </p>
        </div>
    </form>
</x-guest-layout>
