<x-guest-layout>
    <div class="mb-8">
        <h1 class="text-2xl font-semibold tracking-tight text-[var(--auth-ink)]">{{ __('Masuk ke :brand', ['brand' => \App\Support\Brand::name()]) }}</h1>
        <p class="mt-2 text-sm font-medium leading-6 text-[var(--auth-helper)]">{{ __('Lanjutkan eksplorasi destinasi dan pesanan oleh-oleh Anda.') }}</p>
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="mt-2 block" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" placeholder="{{ __('nama@email.com') }}" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input id="password" class="mt-2 block"
                            type="password"
                            name="password"
                            required autocomplete="current-password" 
                            placeholder="{{ __('••••••••') }}" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="mt-4 flex items-center justify-between gap-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-[var(--auth-hairline)] text-[var(--auth-accent)] shadow-sm focus:ring-[var(--auth-focus)]" name="remember">
                <span class="ms-2 text-sm font-medium text-[var(--auth-helper)]">{{ __('Ingat saya') }}</span>
            </label>

            @if (Route::has('password.request'))
                <a class="auth-link rounded-md text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-[var(--auth-focus)]" href="{{ route('password.request') }}">
                    {{ __('Lupa password?') }}
                </a>
            @endif
        </div>

        <div class="mt-6">
            <x-primary-button class="w-full py-3">
                {{ __('Masuk') }}
            </x-primary-button>
        </div>

        <div class="mt-6 text-center">
            <p class="text-sm font-medium text-[var(--auth-helper)]">{{ __('Belum punya akun?') }}
                <a href="{{ route('register') }}" class="auth-link font-semibold">{{ __('Buat akun') }}</a>
            </p>
        </div>
    </form>
</x-guest-layout>
