<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Japan Travel') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @includeIf('partials.theme-script')
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>body { font-family: 'Manrope', system-ui, -apple-system, sans-serif; }</style>
</head>
<body class="text-gray-900 antialiased bg-gray-50 dark:bg-gray-900">
    
    <div class="absolute top-4 right-4 flex items-center gap-2">
        <div class="flex space-x-2 text-xs font-bold">
            <a href="{{ route('lang.switch', 'id') }}" class="px-3 py-1 rounded-full border border-gray-200 dark:border-gray-700 {{ App::getLocale() == 'id' ? 'bg-sky-600 text-white border-sky-600' : 'text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800' }}">ID</a>
            <a href="{{ route('lang.switch', 'en') }}" class="px-3 py-1 rounded-full border border-gray-200 dark:border-gray-700 {{ App::getLocale() == 'en' ? 'bg-sky-600 text-white border-sky-600' : 'text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800' }}">EN</a>
        </div>
        <button onclick="toggleTheme()" class="w-10 h-10 rounded-full border border-gray-200 dark:border-gray-700 flex items-center justify-center text-gray-600 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 transition" title="Toggle theme">
            <span class="text-lg" aria-hidden="true">🌗</span>
        </button>
    </div>

    <div class="min-h-screen flex flex-col sm:flex-row">
        
        <div class="hidden sm:flex sm:w-1/2 bg-cover bg-center relative" 
             style="background-image: url('https://images.unsplash.com/photo-1542051841857-5f90071e7989?ixlib=rb-4.0.3&auto=format&fit=crop&w=2070&q=80');">
            <div class="absolute inset-0 bg-sky-900/40 mix-blend-multiply"></div>
            <div class="relative z-10 w-full flex flex-col justify-center p-12 text-white">
                <h1 class="text-5xl font-extrabold mb-4">🇯🇵 Japan Travel</h1>
                <p class="text-xl opacity-90">Jelajahi keindahan Jepang dan bawa pulang kenangan manisnya.</p>
            </div>
        </div>

        <div class="w-full sm:w-1/2 flex flex-col justify-center items-center bg-white dark:bg-gray-900 p-6 sm:p-12">
            <div class="w-full max-w-md">
                <div class="sm:hidden text-center mb-8">
                    <span class="text-3xl font-bold text-sky-600">🇯🇵 Japan Travel</span>
                </div>

                {{ $slot }}
                
            </div>
        </div>

    </div>
</body>
</html>
