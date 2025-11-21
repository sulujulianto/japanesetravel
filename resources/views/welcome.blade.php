<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Japan Travel') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @includeIf('partials.theme-script')
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>body { font-family: 'Manrope', system-ui, -apple-system, sans-serif; }</style>
</head>
<body class="font-sans antialiased bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 transition-colors duration-300">

    <nav x-data="{ scrolled: false }" @scroll.window="scrolled = (window.pageYOffset > 20)" :class="{ 'bg-white/90 dark:bg-gray-900/90 backdrop-blur-md shadow-md': scrolled, 'bg-transparent': !scrolled }" class="fixed w-full top-0 z-50 transition-all duration-300 border-b border-transparent">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-20 items-center">
                <div class="flex items-center">
                    <a href="{{ route('home') }}" class="flex items-center gap-2 group">
                        <span class="text-3xl group-hover:scale-110 transition transform duration-300">🇯🇵</span>
                        <span class="text-2xl font-extrabold text-sky-600 dark:text-white tracking-tight group-hover:text-sky-500 transition">
                            Japan<span class="text-gray-800 dark:text-gray-300 font-light">Travel</span>
                        </span>
                    </a>
                </div>

                <div class="hidden md:flex items-center space-x-6">
                    <a href="{{ route('home') }}" class="text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-sky-600 dark:hover:text-sky-400 transition">{{ __('Beranda') }}</a>
                    <a href="{{ route('shop.index') }}" class="text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-sky-600 dark:hover:text-sky-400 transition">{{ __('Oleh-oleh') }}</a>
                    
                    <div class="h-5 w-px bg-gray-300 dark:bg-gray-700"></div>

                    <div class="flex items-center gap-2 text-xs font-bold">
                        <a href="{{ route('lang.switch', 'id') }}" class="px-3 py-1 rounded-full border border-gray-200 dark:border-gray-700 {{ App::getLocale() == 'id' ? 'bg-sky-600 text-white border-sky-600' : 'text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800' }}">ID</a>
                        <a href="{{ route('lang.switch', 'en') }}" class="px-3 py-1 rounded-full border border-gray-200 dark:border-gray-700 {{ App::getLocale() == 'en' ? 'bg-sky-600 text-white border-sky-600' : 'text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800' }}">EN</a>
                    </div>

                    <button onclick="toggleTheme()" class="ml-2 w-11 h-11 rounded-full border border-gray-200 dark:border-gray-700 flex items-center justify-center text-gray-600 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 transition" title="Toggle theme">
                        <span class="text-lg" aria-hidden="true">🌗</span>
                    </button>

                    @if (Route::has('login'))
                        @auth
                            <a href="{{ url('/dashboard') }}" class="bg-sky-600 hover:bg-sky-700 text-white px-5 py-2 rounded-full font-semibold text-sm transition shadow-lg">
                                {{ __('Dashboard') }}
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="text-gray-700 dark:text-white font-semibold hover:text-sky-600 transition">{{ __('Masuk') }}</a>
                            <a href="{{ route('register') }}" class="bg-gray-900 dark:bg-white dark:text-black text-white px-5 py-2 rounded-full font-semibold text-sm transition">{{ __('Daftar') }}</a>
                        @endauth
                    @endif
                </div>
            </div>
        </div>
    </nav>

    <div class="relative h-[600px] flex items-center justify-center overflow-hidden">
        <div class="absolute inset-0 z-0">
            <img src="https://images.unsplash.com/photo-1493976040374-85c8e12f0c0e?ixlib=rb-4.0.3&auto=format&fit=crop&w=2070&q=80" alt="Japan Background" class="w-full h-full object-cover">
            <div class="absolute inset-0 bg-gradient-to-b from-black/60 via-black/40 to-gray-50 dark:to-gray-900"></div>
        </div>

        <div class="relative z-10 text-center px-4 max-w-4xl mx-auto mt-16">
            <span class="inline-block py-1 px-3 rounded-full bg-sky-500/20 border border-sky-400/30 text-sky-300 text-sm font-bold mb-4 backdrop-blur-sm animate-bounce">
                🚀 {{ __('Petualangan Menantimu!') }}
            </span>
            <h1 class="text-5xl md:text-7xl font-extrabold text-white mb-6 leading-tight drop-shadow-lg">
                {{ __('Jelajahi Keajaiban') }} <br>
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-sky-300 to-indigo-300">{{ __('Jepang') }}</span>
            </h1>
            <p class="text-lg md:text-xl text-gray-200 mb-8 max-w-2xl mx-auto font-light">
                {{ __('Temukan destinasi...') }}
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="#explore" class="bg-sky-500 hover:bg-sky-600 text-white px-8 py-4 rounded-full font-bold text-lg transition transform hover:scale-105 shadow-xl">
                    {{ __('Mulai Eksplorasi') }}
                </a>
                <a href="{{ route('shop.index') }}" class="bg-white/10 hover:bg-white/20 backdrop-blur-md border border-white/30 text-white px-8 py-4 rounded-full font-bold text-lg transition">
                    {{ __('Beli Oleh-oleh') }}
                </a>
            </div>
        </div>
    </div>

    <div class="py-16 bg-white dark:bg-gray-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 text-center">
                <div class="p-6 rounded-2xl bg-gray-50 dark:bg-gray-700 hover:shadow-lg transition">
                    <div class="text-4xl mb-4">⛩️</div>
                    <h3 class="text-xl font-bold mb-2">{{ __('Destinasi Ikonik') }}</h3>
                    <p class="text-gray-500 dark:text-gray-300 text-sm">{{ __('Akses ke tempat...') }}</p>
                </div>
                <div class="p-6 rounded-2xl bg-gray-50 dark:bg-gray-700 hover:shadow-lg transition">
                    <div class="text-4xl mb-4">🛍️</div>
                    <h3 class="text-xl font-bold mb-2">{{ __('Oleh-oleh Asli') }}</h3>
                    <p class="text-gray-500 dark:text-gray-300 text-sm">{{ __('Belanja souvenir...') }}</p>
                </div>
                <div class="p-6 rounded-2xl bg-gray-50 dark:bg-gray-700 hover:shadow-lg transition">
                    <div class="text-4xl mb-4">💬</div>
                    <h3 class="text-xl font-bold mb-2">{{ __('Komunitas Travel') }}</h3>
                    <p class="text-gray-500 dark:text-gray-300 text-sm">{{ __('Baca ulasan...') }}</p>
                </div>
            </div>
        </div>
    </div>

    <div id="explore" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
        <div class="flex justify-between items-end mb-10">
            <div>
                <h2 class="text-3xl font-bold text-gray-900 dark:text-white">{{ __('Destinasi Populer') }}</h2>
                <p class="text-gray-500 mt-2">{{ __('Jangan lewatkan...') }}</p>
            </div>
        </div>
        
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
            @forelse($places as $place)
            <a href="{{ route('place.show', $place->slug) }}" class="group block bg-white dark:bg-gray-800 rounded-2xl shadow-sm hover:shadow-2xl hover:-translate-y-2 transition duration-500 overflow-hidden border border-gray-100 dark:border-gray-700">
                <div class="relative h-64 overflow-hidden">
                    @if($place->image)
                        <img src="{{ asset('storage/' . $place->image) }}" alt="{{ $place->name }}" class="w-full h-full object-cover transform group-hover:scale-110 transition duration-700">
                    @else
                        <div class="w-full h-full bg-gray-200 flex items-center justify-center text-gray-400">No Image</div>
                    @endif
                    <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-transparent to-transparent opacity-80"></div>
                    <div class="absolute bottom-4 left-4 text-white">
                        <h3 class="text-xl font-bold group-hover:text-sky-300 transition">{{ $place->name }}</h3>
                        <p class="text-sm opacity-90 flex items-center mt-1">
                            <span>📍 {{ Str::limit($place->address, 30) }}</span>
                        </p>
                    </div>
                </div>
                <div class="p-6">
                    <p class="text-gray-600 dark:text-gray-400 text-sm mb-4 line-clamp-2 leading-relaxed">
                        {{ Str::limit($place->description, 120) }}
                    </p>
                    <div class="flex items-center justify-between pt-4 border-t border-gray-100 dark:border-gray-700">
                        <span class="text-xs font-bold text-sky-600 bg-sky-50 px-3 py-1 rounded-full dark:bg-gray-700 dark:text-sky-300">{{ __('Jepang') }}</span>
                        <span class="text-xs text-gray-400">{{ $place->created_at->diffForHumans() }}</span>
                    </div>
                </div>
            </a>
            @empty
            <div class="col-span-full flex flex-col items-center justify-center py-20 text-gray-500 bg-gray-50 dark:bg-gray-800 rounded-3xl border-2 border-dashed border-gray-300">
                <div class="text-6xl mb-4">📭</div>
                <p class="text-lg font-medium">{{ __('Belum ada destinasi...') }}</p>
            </div>
            @endforelse
        </div>
    </div>

    <footer class="bg-gray-900 text-white pt-16 pb-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-12 mb-12">
                <div class="col-span-1 md:col-span-2">
                    <span class="text-2xl font-bold text-white tracking-tight mb-4 block">
                        🇯🇵 Japan<span class="text-sky-500">Travel</span>
                    </span>
                    <p class="text-gray-400 leading-relaxed max-w-sm">
                        {{ __('Deskripsi Footer') }}
                    </p>
                </div>
                <div>
                    <h4 class="font-bold text-lg mb-4">{{ __('Navigasi') }}</h4>
                    <ul class="space-y-2 text-gray-400">
                        <li><a href="{{ route('home') }}" class="hover:text-sky-500 transition">{{ __('Beranda') }}</a></li>
                        <li><a href="{{ route('shop.index') }}" class="hover:text-sky-500 transition">{{ __('Toko Oleh-oleh') }}</a></li>
                        <li><a href="{{ route('login') }}" class="hover:text-sky-500 transition">{{ __('Masuk') }}</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-bold text-lg mb-4">{{ __('Kontak') }}</h4>
                    <ul class="space-y-2 text-gray-400">
                        <li>Tokyo, Japan</li>
                        <li>support@japantravel.com</li>
                        <li>+81 90-1234-5678</li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-gray-800 pt-8 text-center text-gray-500 text-sm">
                &copy; {{ date('Y') }} Japan Travel Project. {{ __('Dibuat dengan') }}
            </div>
        </div>
    </footer>

</body>
</html>
