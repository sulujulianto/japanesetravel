<section>
    <header>
        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#2F5D50] dark:text-[#8AB7A4]">{{ __('Informasi Profil') }}</p>
        <h2 class="mt-2 text-xl font-semibold text-[#1F2937] dark:text-[#F4F1ED]">{{ __('Informasi akun') }}</h2>
        <p class="mt-2 text-sm leading-6 text-[var(--public-muted)]">{{ __('Perbarui identitas akun dan data pribadi yang digunakan untuk pesanan Anda.') }}</p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-5">
        @csrf
        @method('patch')

        <div>
            <x-input-label for="username" :value="__('Username')" />
            <x-text-input id="username" name="username" type="text" class="mt-2 block w-full" :value="old('username', $user->username)" required autofocus autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('username')" />
        </div>

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" name="email" type="email" class="mt-2 block w-full" :value="old('email', $user->email)" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div class="mt-3 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900 dark:border-amber-900/60 dark:bg-amber-950/30 dark:text-amber-100">
                    <p>{{ __('Your email address is unverified.') }}</p>
                    <button form="send-verification" class="mt-2 font-semibold underline decoration-amber-500 underline-offset-4 focus:outline-none focus:ring-2 focus:ring-[#B33A3A] dark:focus:ring-[#D96B6B]">
                        {{ __('Click here to re-send the verification email.') }}
                    </button>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-3 font-semibold text-emerald-700 dark:text-emerald-300">{{ __('A new verification link has been sent to your email address.') }}</p>
                    @endif
                </div>
            @endif
        </div>

        <div class="border-t border-[var(--public-border)] pt-5">
            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#2F5D50] dark:text-[#8AB7A4]">{{ __('Data pribadi') }}</p>
            <p class="mt-2 text-sm leading-6 text-[var(--public-muted)]">{{ __('Nama dan nomor telepon disimpan terenkripsi dan hanya digunakan untuk kebutuhan akun serta pengiriman.') }}</p>
        </div>

        <div>
            <x-input-label for="full_name" :value="__('Nama lengkap')" />
            <x-text-input id="full_name" name="full_name" type="text" class="mt-2 block w-full" :value="old('full_name', $profile?->full_name)" autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('full_name')" />
        </div>

        <div>
            <x-input-label for="phone" :value="__('Nomor telepon')" />
            <x-text-input id="phone" name="phone" type="tel" class="mt-2 block w-full" :value="old('phone', $profile?->phone)" autocomplete="tel" placeholder="+62 812-3456-7890" />
            <x-input-error class="mt-2" :messages="$errors->get('phone')" />
        </div>

        <div>
            <x-input-label for="preferred_locale" :value="__('Bahasa pilihan')" />
            <select id="preferred_locale" name="preferred_locale" class="auth-input mt-2 block w-full px-4 py-2.5 text-sm">
                <option value="id" @selected(old('preferred_locale', $profile?->preferred_locale ?? app()->getLocale()) === 'id')>{{ __('Bahasa Indonesia') }}</option>
                <option value="en" @selected(old('preferred_locale', $profile?->preferred_locale ?? app()->getLocale()) === 'en')>{{ __('Bahasa Inggris') }}</option>
            </select>
            <x-input-error class="mt-2" :messages="$errors->get('preferred_locale')" />
        </div>

        <div class="flex flex-wrap items-center gap-4 pt-1">
            <x-primary-button>{{ __('Simpan perubahan') }}</x-primary-button>

            @if (session('status') === 'profile-updated')
                <p data-auto-dismiss="2000" class="text-sm font-medium text-emerald-700 transition-opacity duration-200 dark:text-emerald-300">{{ __('Tersimpan.') }}</p>
            @endif
        </div>
    </form>
</section>
