<x-guest-layout>
    <div class="mb-8">
        <h1 class="text-2xl font-semibold tracking-tight text-[var(--auth-ink)]">{{ __('Atur ulang password') }}</h1>
        <p class="mt-2 text-sm font-medium leading-6 text-[var(--auth-helper)]">{{ __('Masukkan email akun Anda. Kami akan mengirim tautan untuk membuat password baru.') }}</p>
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="mt-2 block" type="email" name="email" :value="old('email')" required autofocus placeholder="{{ __('nama@email.com') }}" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="mt-6">
            <x-primary-button class="w-full py-3">
                {{ __('Kirim tautan reset') }}
            </x-primary-button>
        </div>

        <div class="mt-6 text-center">
            <a href="{{ route('login') }}" class="auth-link text-sm font-semibold">{{ __('Kembali ke login') }}</a>
        </div>
    </form>
</x-guest-layout>
