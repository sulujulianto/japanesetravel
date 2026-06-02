<x-guest-layout>
    <div class="mb-8">
        <h1 class="text-2xl font-semibold tracking-tight text-[var(--auth-ink)]">{{ __('Verifikasi email Anda') }}</h1>
        <p class="mt-2 text-sm font-medium leading-6 text-[var(--auth-helper)]">{{ __('Buka tautan verifikasi yang kami kirimkan. Jika belum menerima email, Anda dapat meminta tautan baru.') }}</p>
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="mb-4 rounded-[10px] border border-[var(--auth-hairline)] bg-[var(--auth-muted-surface)] px-4 py-3 text-sm font-medium text-[var(--auth-ink)]">
            {{ __('Tautan verifikasi baru sudah dikirim ke email yang Anda gunakan saat registrasi.') }}
        </div>
    @endif

    <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf

            <div>
                <x-primary-button class="w-full py-3 sm:w-auto">
                    {{ __('Kirim ulang email') }}
                </x-primary-button>
            </div>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf

            <button type="submit" class="auth-link rounded-md text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-[var(--auth-focus)]">
                {{ __('Keluar') }}
            </button>
        </form>
    </div>
</x-guest-layout>
