<footer class="relative mt-16 shrink-0 border-t border-[#E7E3DC] bg-white dark:border-[#2A333D] dark:bg-[#161B22]">
    <div class="mx-auto grid max-w-7xl gap-10 px-4 py-10 sm:px-6 lg:grid-cols-4 lg:px-8">
        <div class="lg:col-span-2">
            <div class="flex items-center gap-2 text-lg font-semibold text-[#1F2937] dark:text-[#F4F1ED]">
                <span class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-[#DDD6CC] bg-[#FAF8F3] text-xs font-semibold tracking-tight text-[#A6423A] dark:border-[#2A333D] dark:bg-[#0E1116] dark:text-[#D96B6B]">JT</span>
                <span class="font-display">Japan<span class="text-[#B33A3A] dark:text-[#D96B6B]">Travel</span></span>
            </div>
            <p class="mt-4 max-w-md text-sm leading-6 text-[#526071] dark:text-[#D8DEE8]">
                {{ __('Platform untuk menemukan destinasi Jepang, membaca ulasan, dan berbelanja oleh-oleh pilihan.') }}
            </p>
        </div>
        <div>
            <h4 class="text-sm font-semibold uppercase tracking-wider text-[#667085] dark:text-[#AEB8C7]">{{ __('Navigasi') }}</h4>
            <ul class="mt-4 space-y-2 text-sm text-[#526071] dark:text-[#D8DEE8]">
                <li><a href="{{ route('places.index') }}" class="hover:text-[#8F2E2E] dark:hover:text-[#D96B6B]">{{ __('Wisata') }}</a></li>
                <li><a href="{{ route('shop.index') }}" class="hover:text-[#8F2E2E] dark:hover:text-[#D96B6B]">{{ __('Oleh-oleh') }}</a></li>
                <li><a href="{{ route('login') }}" class="hover:text-[#8F2E2E] dark:hover:text-[#D96B6B]">{{ __('Masuk') }}</a></li>
            </ul>
        </div>
        <div>
            <h4 class="text-sm font-semibold uppercase tracking-wider text-[#667085] dark:text-[#AEB8C7]">{{ __('Kontak') }}</h4>
            <p class="mt-4 text-sm leading-6 text-[#526071] dark:text-[#D8DEE8]">
                {{ __('Kontak perjalanan dapat dikonfirmasi melalui kanal resmi yang tersedia pada halaman destinasi.') }}
            </p>
        </div>
    </div>
    <div class="mx-auto flex max-w-7xl flex-col items-center gap-2 border-t border-[#E7E3DC] px-4 py-5 text-xs text-[#667085] dark:border-[#2A333D] dark:text-[#AEB8C7] sm:px-6 lg:px-8">
        <span>© {{ date('Y') }} {{ __('Japan Travel Project.') }}</span>
        <span>{{ __('Dibuat dengan') }}</span>
    </div>
</footer>
