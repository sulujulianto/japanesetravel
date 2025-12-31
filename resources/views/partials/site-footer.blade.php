<footer class="relative mt-20 border-t border-slate-200/60 bg-white/80 py-12 backdrop-blur dark:border-slate-800 dark:bg-slate-950/80">
    <div class="mx-auto grid max-w-7xl gap-10 px-4 sm:px-6 lg:grid-cols-4 lg:px-8">
        <div class="lg:col-span-2">
            <div class="flex items-center gap-2 text-lg font-semibold text-slate-900 dark:text-white">
                <span class="text-2xl">⛩️</span>
                <span class="font-display">Japan<span class="text-rose-500">Travel</span></span>
            </div>
            <p class="mt-4 max-w-md text-sm text-slate-500 dark:text-slate-300">
                {{ __('Kami membantu Anda merancang perjalanan ke Jepang dengan pilihan destinasi, ulasan, dan oleh-oleh terbaik untuk dibawa pulang.') }}
            </p>
        </div>
        <div>
            <h4 class="text-sm font-semibold uppercase tracking-wider text-slate-400">{{ __('Navigasi') }}</h4>
            <ul class="mt-4 space-y-2 text-sm text-slate-500 dark:text-slate-300">
                <li><a href="{{ route('home') }}" class="hover:text-slate-900 dark:hover:text-white">{{ __('Wisata') }}</a></li>
                <li><a href="{{ route('shop.index') }}" class="hover:text-slate-900 dark:hover:text-white">{{ __('Oleh-oleh') }}</a></li>
                <li><a href="{{ route('login') }}" class="hover:text-slate-900 dark:hover:text-white">{{ __('Masuk') }}</a></li>
            </ul>
        </div>
        <div>
            <h4 class="text-sm font-semibold uppercase tracking-wider text-slate-400">{{ __('Kontak') }}</h4>
            <ul class="mt-4 space-y-2 text-sm text-slate-500 dark:text-slate-300">
                <li>Tokyo, Japan</li>
                <li>support@japantravel.com</li>
                <li>+81 90-1234-5678</li>
            </ul>
        </div>
    </div>
    <div class="mx-auto mt-10 flex max-w-7xl flex-col items-center gap-2 border-t border-slate-200/60 pt-6 text-xs text-slate-400 dark:border-slate-800">
        <span>© {{ date('Y') }} {{ __('Japan Travel Project.') }}</span>
        <span>{{ __('Dibuat dengan') }}</span>
    </div>
</footer>
