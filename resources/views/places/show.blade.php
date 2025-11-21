<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $place->name }} - Japan Travel</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @includeIf('partials.theme-script')
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="font-sans antialiased bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100">

    <nav class="bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="{{ route('home') }}" class="flex items-center text-gray-500 hover:text-sky-600 dark:hover:text-red-400 transition">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                        {{ __('Kembali ke Beranda') }}
                    </a>
                </div>
                <div class="flex items-center space-x-4">
                     <div class="flex items-center space-x-2 text-sm font-medium mr-2">
                        <a href="{{ route('lang.switch', 'id') }}" class="px-3 py-1 rounded-full border border-gray-200 dark:border-gray-700 {{ App::getLocale() == 'id' ? 'bg-sky-600 text-white border-sky-600' : 'text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800' }}">ID</a>
                        <a href="{{ route('lang.switch', 'en') }}" class="px-3 py-1 rounded-full border border-gray-200 dark:border-gray-700 {{ App::getLocale() == 'en' ? 'bg-sky-600 text-white border-sky-600' : 'text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800' }}">EN</a>
                    </div>
                    <button onclick="toggleTheme()" class="w-10 h-10 rounded-full border border-gray-200 dark:border-gray-700 flex items-center justify-center text-gray-600 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 transition" title="Toggle theme">
                        <span class="text-lg" aria-hidden="true">🌗</span>
                    </button>
                    <span class="text-xl font-bold text-sky-500 dark:text-red-500">🇯🇵 Japan Travel</span>
                </div>
            </div>
        </div>
    </nav>

    <div class="relative h-96 w-full">
        @if($place->image)
            <img src="{{ asset('storage/' . $place->image) }}" class="w-full h-full object-cover" alt="{{ $place->name }}">
        @else
            <div class="w-full h-full bg-gray-300 flex items-center justify-center">No Image Available</div>
        @endif
        <div class="absolute inset-0 bg-gradient-to-t from-black/80 to-transparent flex items-end">
            <div class="max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 pb-10">
                <h1 class="text-4xl md:text-5xl font-extrabold text-white mb-2">{{ $place->name }}</h1>
                <p class="text-gray-200 text-lg">📍 {{ $place->address ?? __('Lokasi belum ditambahkan') }}</p>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10" x-data="{ tab: 'ringkasan' }">
        
        <div class="flex border-b border-gray-200 dark:border-gray-700 mb-6 overflow-x-auto">
            <button @click="tab = 'ringkasan'" :class="{ 'border-sky-500 text-sky-600 dark:border-red-500 dark:text-red-400': tab === 'ringkasan', 'border-transparent text-gray-500 hover:text-gray-700': tab !== 'ringkasan' }" class="whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm focus:outline-none">
                {{ __('Ringkasan') }}
            </button>
            <button @click="tab = 'fasilitas'" :class="{ 'border-sky-500 text-sky-600 dark:border-red-500 dark:text-red-400': tab === 'fasilitas', 'border-transparent text-gray-500 hover:text-gray-700': tab !== 'fasilitas' }" class="whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm focus:outline-none">
                {{ __('Fasilitas') }}
            </button>
            <button @click="tab = 'lokasi'" :class="{ 'border-sky-500 text-sky-600 dark:border-red-500 dark:text-red-400': tab === 'lokasi', 'border-transparent text-gray-500 hover:text-gray-700': tab !== 'lokasi' }" class="whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm focus:outline-none">
                {{ __('Lokasi & Jam Buka') }}
            </button>
            <button @click="tab = 'review'" :class="{ 'border-sky-500 text-sky-600 dark:border-red-500 dark:text-red-400': tab === 'review', 'border-transparent text-gray-500 hover:text-gray-700': tab !== 'review' }" class="whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm focus:outline-none">
                Review ({{ $place->reviews->count() }})
            </button>
        </div>

        <div x-show="tab === 'ringkasan'" class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="md:col-span-2">
                <h2 class="text-2xl font-bold mb-4">{{ __('Tentang') }} {{ $place->name }}</h2>
                <div class="prose dark:prose-invert max-w-none text-gray-700 dark:text-gray-300 leading-relaxed whitespace-pre-line">
                    {{ $place->description }}
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 h-fit">
                <h3 class="font-bold text-lg mb-4">{{ __('Informasi Singkat') }}</h3>
                <ul class="space-y-3 text-sm">
                    <li class="flex justify-between">
                        <span class="text-gray-500">{{ __('Ditambahkan Oleh') }}</span>
                        <span class="font-medium">{{ $place->author->username ?? 'Admin' }}</span>
                    </li>
                    <li class="flex justify-between">
                        <span class="text-gray-500">{{ __('Tanggal') }}</span>
                        <span class="font-medium">{{ $place->created_at->format('d M Y') }}</span>
                    </li>
                </ul>
            </div>
        </div>

        <div x-show="tab === 'fasilitas'" style="display: none;">
            <h2 class="text-2xl font-bold mb-6">{{ __('Fasilitas Tersedia') }}</h2>
            @if($place->facilities)
                <div class="flex flex-wrap gap-3">
                    @foreach(explode(',', $place->facilities) as $fac)
                        <span class="bg-sky-100 text-sky-800 dark:bg-gray-700 dark:text-gray-200 px-4 py-2 rounded-full text-sm font-medium border border-sky-200 dark:border-gray-600">
                            ✅ {{ trim($fac) }}
                        </span>
                    @endforeach
                </div>
            @else
                <p class="text-gray-500 italic">{{ __('Belum ada data fasilitas.') }}</p>
            @endif
        </div>

        <div x-show="tab === 'lokasi'" style="display: none;">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div>
                    <h3 class="font-bold text-lg mb-4">{{ __('Jam Operasional') }}</h3>
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                <tr>
                                    <td class="px-6 py-4 text-sm font-medium text-gray-500">{{ __('Hari Buka') }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">{{ $place->open_days ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="px-6 py-4 text-sm font-medium text-gray-500">{{ __('Jam Buka') }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">{{ $place->open_hours ?? '-' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div>
                    <h3 class="font-bold text-lg mb-4">{{ __('Peta Lokasi') }}</h3>
                    @if($place->address)
                        <div class="aspect-w-16 aspect-h-9 w-full h-64 bg-gray-200 rounded-lg overflow-hidden shadow-inner">
                            <iframe 
                                width="100%" 
                                height="100%" 
                                frameborder="0" 
                                style="border:0" 
                                src="https://maps.google.com/maps?q={{ urlencode($place->address) }}&output=embed" 
                                allowfullscreen>
                            </iframe>
                        </div>
                        <p class="mt-2 text-sm text-gray-500">{{ $place->address }}</p>
                    @else
                        <p class="text-gray-500 italic">{{ __('Alamat belum disetting untuk peta.') }}</p>
                    @endif
                </div>
            </div>
        </div>

        <div x-show="tab === 'review'" style="display: none;">
            <h3 class="font-bold text-lg mb-6">{{ __('Ulasan Pengunjung') }}</h3>

            @auth
                <form action="{{ route('review.store', $place->id) }}" method="POST" class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow mb-8 border border-gray-200 dark:border-gray-700">
                    @csrf
                    <h4 class="font-bold mb-4">{{ __('Tulis Pengalamanmu') }}</h4>
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Rating</label>
                        <select name="rating" class="rounded-md border-gray-300 dark:bg-gray-900 dark:text-white focus:border-sky-500 focus:ring-sky-500">
                            <option value="5">⭐⭐⭐⭐⭐ (Sempurna)</option>
                            <option value="4">⭐⭐⭐⭐ (Bagus)</option>
                            <option value="3">⭐⭐⭐ (Biasa)</option>
                            <option value="2">⭐⭐ (Buruk)</option>
                            <option value="1">⭐ (Sangat Buruk)</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Komentar</label>
                        <textarea name="comment" rows="3" class="w-full rounded-md border-gray-300 dark:bg-gray-900 dark:text-white focus:border-sky-500 focus:ring-sky-500" placeholder="Ceritakan pengalamanmu di sini..."></textarea>
                    </div>

                    <button type="submit" class="bg-sky-600 dark:bg-red-600 text-white px-4 py-2 rounded hover:bg-sky-700 transition">
                        {{ __('Kirim Ulasan') }}
                    </button>
                </form>
            @else
                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-8">
                    <p class="text-sm text-yellow-700">
                        <a href="{{ route('login') }}" class="font-bold underline">Login</a> untuk menulis ulasan.
                    </p>
                </div>
            @endauth

            <div class="space-y-6">
                @forelse($place->reviews as $review)
                    <div class="flex space-x-4 p-4 bg-white dark:bg-gray-800 rounded-lg shadow-sm">
                        <div class="flex-shrink-0">
                            <img class="h-10 w-10 rounded-full object-cover border" src="https://ui-avatars.com/api/?name={{ urlencode($review->user->username) }}&background=random" alt="">
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center justify-between mb-1">
                                <h5 class="font-bold text-sm">{{ $review->user->username }}</h5>
                                <span class="text-xs text-gray-500">{{ $review->created_at->diffForHumans() }}</span>
                            </div>
                            <div class="text-yellow-400 text-xs mb-2">
                                @for($i=0; $i<$review->rating; $i++) ★ @endfor
                                @for($i=$review->rating; $i<5; $i++) <span class="text-gray-300">★</span> @endfor
                            </div>
                            <p class="text-sm text-gray-700 dark:text-gray-300">{{ $review->comment }}</p>
                        </div>
                    </div>
                @empty
                    <p class="text-gray-500 italic text-center py-4">Belum ada ulasan. Jadilah yang pertama!</p>
                @endforelse
            </div>
        </div>

    </div> <footer class="bg-gray-800 text-white py-8 text-center mt-12 border-t border-gray-700">
        <p class="text-sm">© {{ date('Y') }} Japan Travel. All Rights Reserved.</p>
    </footer>
</body>
</html>
