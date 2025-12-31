<x-guest-layout>
    <div class="mb-8">
        <h2 class="text-3xl font-bold text-gray-900 dark:text-white">{{ __('Selamat Datang Kembali! 👋') }}</h2>
        <p class="text-sm text-gray-600 dark:text-gray-400 mt-2">{{ __('Masuk untuk mengelola pesanan atau wishlistmu.') }}</p>
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div>
            <x-input-label for="email" :value="__('Email')" class="text-gray-700 font-semibold" />
            <x-text-input id="email" class="block mt-1 w-full border-gray-300 focus:border-sky-500 focus:ring-sky-500 rounded-lg py-3" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" placeholder="{{ __('nama@email.com') }}" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" class="text-gray-700 font-semibold" />
            <x-text-input id="password" class="block mt-1 w-full border-gray-300 focus:border-sky-500 focus:ring-sky-500 rounded-lg py-3"
                            type="password"
                            name="password"
                            required autocomplete="current-password" 
                            placeholder="{{ __('••••••••') }}" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="block mt-4 flex justify-between items-center">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-sky-600 shadow-sm focus:ring-sky-500" name="remember">
                <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">{{ __('Ingat Saya') }}</span>
            </label>

            @if (Route::has('password.request'))
                <a class="underline text-sm text-sky-600 hover:text-sky-800 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-sky-500" href="{{ route('password.request') }}">
                    {{ __('Lupa password?') }}
                </a>
            @endif
        </div>

        <div class="mt-6">
            <x-primary-button class="w-full justify-center py-3 bg-sky-600 hover:bg-sky-700 text-white font-bold rounded-lg transition transform active:scale-95">
                {{ __('Masuk Sekarang') }}
            </x-primary-button>
        </div>

        <div class="mt-6 text-center">
            <p class="text-sm text-gray-600">{{ __('Belum punya akun?') }}
                <a href="{{ route('register') }}" class="font-bold text-sky-600 hover:underline">{{ __('Daftar Gratis') }}</a>
            </p>
        </div>
    </form>
</x-guest-layout>
