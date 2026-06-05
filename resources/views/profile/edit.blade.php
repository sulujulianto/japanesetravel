<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2">
            <p class="text-xs font-semibold uppercase tracking-[0.22em] text-[#B33A3A] dark:text-[#D96B6B]">{{ __('Akun') }}</p>
            <h2 class="text-2xl font-semibold text-[#1F2937] dark:text-[#F4F1ED]">{{ __('Pengaturan profil') }}</h2>
            <p class="max-w-2xl text-sm leading-6 text-[#526071] dark:text-[#AEB8C7]">{{ __('Kelola informasi dasar akun, keamanan password, dan pilihan penghapusan akun.') }}</p>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto grid max-w-7xl gap-6 px-4 sm:px-6 lg:grid-cols-[15rem_minmax(0,1fr)] lg:px-8">
            <aside class="h-fit rounded-2xl border border-[#E7E3DC] bg-white p-4 shadow-sm dark:border-[#2A333D] dark:bg-[#161B22] lg:sticky lg:top-24">
                <p class="px-3 text-xs font-semibold uppercase tracking-[0.18em] text-[#667085] dark:text-[#AEB8C7]">{{ __('Pengaturan akun') }}</p>
                <nav class="mt-3 space-y-1" aria-label="{{ __('Bagian pengaturan profil') }}">
                    <a href="#profile-information" class="block rounded-xl px-3 py-2.5 text-sm font-semibold text-[#374151] hover:bg-[#F1EEE8] dark:text-[#D8DEE8] dark:hover:bg-[#1F2630]">{{ __('Informasi Profil') }}</a>
                    <a href="#update-password" class="block rounded-xl px-3 py-2.5 text-sm font-semibold text-[#374151] hover:bg-[#F1EEE8] dark:text-[#D8DEE8] dark:hover:bg-[#1F2630]">{{ __('Update Password') }}</a>
                    <a href="#delete-account" class="block rounded-xl px-3 py-2.5 text-sm font-semibold text-[#9F2A2A] hover:bg-red-50 dark:text-[#F0A0A0] dark:hover:bg-red-950/30">{{ __('Hapus Akun') }}</a>
                </nav>
            </aside>

            <div class="min-w-0 space-y-6">
                <section id="profile-information" class="scroll-mt-24 rounded-2xl border border-[#E7E3DC] bg-white p-5 shadow-sm dark:border-[#2A333D] dark:bg-[#161B22] sm:p-7">
                    <div class="max-w-2xl">
                        @include('profile.partials.update-profile-information-form')
                    </div>
                </section>

                <section id="update-password" class="scroll-mt-24 rounded-2xl border border-[#E7E3DC] bg-white p-5 shadow-sm dark:border-[#2A333D] dark:bg-[#161B22] sm:p-7">
                    <div class="max-w-2xl">
                        @include('profile.partials.update-password-form')
                    </div>
                </section>

                <section id="delete-account" class="scroll-mt-24 rounded-2xl border border-red-200 bg-white p-5 shadow-sm dark:border-red-900/60 dark:bg-[#161B22] sm:p-7">
                    <div class="max-w-2xl">
                        @include('profile.partials.delete-user-form')
                    </div>
                </section>
            </div>
        </div>
    </div>
</x-app-layout>
