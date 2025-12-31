<x-guest-layout>
    <div class="mb-8">
        <h2 class="text-3xl font-display font-semibold text-slate-900 dark:text-white">{{ __('Admin Portal') }}</h2>
        <p class="mt-2 text-sm text-slate-500 dark:text-slate-300">{{ __('Masuk sebagai admin untuk mengelola konten dan pesanan.') }}</p>
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('admin.login.store') }}">
        @csrf

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-2 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" placeholder="{{ __('admin@email.com') }}" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input id="password" class="block mt-2 w-full"
                            type="password"
                            name="password"
                            required autocomplete="current-password"
                            placeholder="••••••••" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="mt-4 flex items-center justify-between">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-slate-300 text-rose-500 focus:ring-rose-400" name="remember">
                <span class="ms-2 text-sm text-slate-600 dark:text-slate-300">{{ __('Ingat Saya') }}</span>
            </label>
        </div>

        <div class="mt-6">
            <x-primary-button class="w-full justify-center py-3">
                {{ __('Masuk Admin') }}
            </x-primary-button>
        </div>

        <div class="mt-6 text-center">
            <p class="text-sm text-slate-600 dark:text-slate-300">
                <a href="{{ route('login') }}" class="font-semibold text-rose-500 hover:underline">{{ __('Kembali ke login pengguna') }}</a>
            </p>
        </div>
    </form>
</x-guest-layout>
