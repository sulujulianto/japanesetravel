<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Toko Oleh-oleh') }} - Japan Travel</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @includeIf('partials.theme-script')
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>body { font-family: 'Manrope', system-ui, -apple-system, sans-serif; }</style>
</head>
<body class="font-sans antialiased bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100">

    <nav class="bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700 sticky top-0 z-50 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center space-x-8">
                    <a href="{{ route('home') }}" class="text-2xl font-bold tracking-tighter group">
                        <span class="text-sky-500 group-hover:text-sky-600 transition">Japan</span><span class="dark:text-white">Store</span>
                    </a>
                    <div class="hidden sm:flex space-x-6">
                        <a href="{{ route('home') }}" class="text-sm font-medium text-gray-500 hover:text-sky-600 transition">{{ __('Wisata') }}</a>
                        <a href="{{ route('shop.index') }}" class="text-sm font-bold text-sky-600 border-b-2 border-sky-600 pb-1">{{ __('Toko Oleh-oleh') }}</a>
                    </div>
                </div>
                <div class="flex items-center space-x-3">
                     <a href="{{ route('cart.index') }}" class="relative p-2 text-gray-600 hover:text-sky-600 transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                    </a>

                    <div class="flex space-x-2 text-xs font-bold">
                        <a href="{{ route('lang.switch', 'id') }}" class="px-3 py-1 rounded-full border border-gray-200 dark:border-gray-700 {{ App::getLocale() == 'id' ? 'bg-sky-600 text-white border-sky-600' : 'text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800' }}">ID</a>
                        <a href="{{ route('lang.switch', 'en') }}" class="px-3 py-1 rounded-full border border-gray-200 dark:border-gray-700 {{ App::getLocale() == 'en' ? 'bg-sky-600 text-white border-sky-600' : 'text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800' }}">EN</a>
                    </div>
                    <button onclick="toggleTheme()" class="w-10 h-10 rounded-full border border-gray-200 dark:border-gray-700 flex items-center justify-center text-gray-600 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 transition" title="Toggle theme">
                        <span class="text-lg" aria-hidden="true">🌗</span>
                    </button>
                    
                    @auth
                        <a href="{{ url('/dashboard') }}" class="bg-sky-100 text-sky-700 px-4 py-2 rounded-full text-sm font-bold hover:bg-sky-200 transition">{{ __('Dashboard') }}</a>
                    @else
                        <a href="{{ route('login') }}" class="text-sm font-bold text-gray-700 hover:text-sky-600">{{ __('Masuk') }}</a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <div class="bg-gradient-to-r from-sky-600 to-indigo-700 text-white py-16 text-center shadow-lg">
        <h1 class="text-4xl font-extrabold mb-2 tracking-tight">🛍️ {{ __('Toko Oleh-oleh') }}</h1>
        <p class="text-lg opacity-90 font-light">{{ __('Bawa pulang...') }}</p>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="flex overflow-x-auto space-x-2 mb-8 pb-2">
            <button class="px-4 py-2 bg-sky-600 text-white rounded-full text-sm font-medium shadow-md">{{ __('Semua') }}</button>
            <button class="px-4 py-2 bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-gray-700 rounded-full text-sm font-medium hover:bg-gray-50 transition">{{ __('Makanan') }}</button>
            <button class="px-4 py-2 bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-gray-700 rounded-full text-sm font-medium hover:bg-gray-50 transition">{{ __('Pakaian') }}</button>
            <button class="px-4 py-2 bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-gray-700 rounded-full text-sm font-medium hover:bg-gray-50 transition">{{ __('Aksesoris') }}</button>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
            @forelse($souvenirs as $item)
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm hover:shadow-xl transition duration-300 overflow-hidden border border-gray-100 dark:border-gray-700 group flex flex-col relative">
                
                @if($item->stock <= 5 && $item->stock > 0)
                    <span class="absolute top-3 left-3 bg-orange-500 text-white text-[10px] font-bold px-2 py-1 rounded shadow-sm z-10">{{ __('TERBATAS') }}</span>
                @endif

                <div class="relative h-56 overflow-hidden bg-gray-100">
                    @if($item->image)
                        <img src="{{ asset('storage/' . $item->image) }}" alt="{{ $item->name }}" class="w-full h-full object-cover transform group-hover:scale-105 transition duration-500">
                    @else
                        <div class="w-full h-full flex items-center justify-center text-gray-400 bg-gray-200">No Image</div>
                    @endif
                    
                    @if($item->stock <= 0)
                        <div class="absolute inset-0 bg-black/60 flex items-center justify-center z-20">
                            <span class="bg-red-600 text-white px-4 py-1 rounded-full text-sm font-bold tracking-wider shadow-lg border-2 border-white">{{ __('HABIS') }}</span>
                        </div>
                    @endif
                </div>

                <div class="p-5 flex-1 flex flex-col">
                    <h3 class="text-lg font-bold mb-1 text-gray-900 dark:text-white line-clamp-1 group-hover:text-sky-600 transition">{{ $item->name }}</h3>
                    <p class="text-gray-500 text-xs mb-4 line-clamp-2 h-8">{{ $item->description }}</p>
                    
                    <div class="mt-auto flex items-end justify-between">
                        <div>
                            <p class="text-sky-600 dark:text-sky-400 font-extrabold text-xl">Rp {{ number_format($item->price, 0, ',', '.') }}</p>
                            <p class="text-[10px] text-gray-400 font-medium mt-1">{{ __('Stok Tersedia') }}: {{ $item->stock }}</p>
                        </div>
                        
                        <form action="{{ route('cart.add', $item->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="bg-gray-900 dark:bg-white dark:text-black text-white w-10 h-10 flex items-center justify-center rounded-full hover:bg-sky-600 hover:scale-110 transition shadow-lg {{ $item->stock <= 0 ? 'opacity-20 cursor-not-allowed' : '' }}" {{ $item->stock <= 0 ? 'disabled' : '' }} title="Tambah ke Keranjang">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            @empty
            <div class="col-span-full text-center py-20">
                <div class="inline-block p-6 bg-gray-50 rounded-full mb-4 text-4xl">📦</div>
                <p class="text-gray-500 font-medium">{{ __('Belum ada barang...') }}</p>
            </div>
            @endforelse
        </div>
        
        <div class="mt-10">{{ $souvenirs->links() }}</div>
    </div>

    <footer class="bg-gray-900 text-white py-8 text-center mt-12 text-sm text-gray-400">
        <p>&copy; {{ date('Y') }} Japan Travel Store. {{ __('Selamat Berbelanja') }}</p>
    </footer>
</body>
</html>
