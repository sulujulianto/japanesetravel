<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Japan Travel') }}</title>
    @includeIf('partials.theme-script')
    @includeIf('partials.vite')
</head>
<body class="font-sans antialiased bg-slate-50 text-slate-900 dark:bg-slate-950 dark:text-slate-100">
    
    <div class="absolute top-4 right-4 flex items-center gap-2">
        <div class="flex space-x-2 text-xs font-bold">
            <a href="{{ route('lang.switch', 'id') }}" class="px-3 py-1 rounded-full border border-slate-200 dark:border-slate-700 {{ App::getLocale() == 'id' ? 'bg-slate-900 text-white dark:bg-white dark:text-slate-900' : 'text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800' }}">ID</a>
            <a href="{{ route('lang.switch', 'en') }}" class="px-3 py-1 rounded-full border border-slate-200 dark:border-slate-700 {{ App::getLocale() == 'en' ? 'bg-slate-900 text-white dark:bg-white dark:text-slate-900' : 'text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800' }}">EN</a>
        </div>
        <button onclick="toggleTheme()" class="w-10 h-10 rounded-full border border-slate-200 dark:border-slate-700 flex items-center justify-center text-slate-600 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition" title="{{ __('Ganti tema') }}">
            <span class="text-lg" aria-hidden="true">🌗</span>
        </button>
    </div>

    <div class="min-h-screen flex flex-col sm:flex-row">
        
        <div class="hidden sm:flex sm:w-1/2 bg-gradient-to-br from-slate-900 via-slate-800 to-rose-900 relative">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_top,_rgba(244,63,94,0.35),_transparent_60%)]"></div>
            <div class="relative z-10 w-full flex flex-col justify-center p-12 text-white">
                <h1 class="text-5xl font-display font-semibold mb-4">🇯🇵 {{ __('Japan Travel') }}</h1>
                <p class="text-xl opacity-90">{{ __('Jelajahi keindahan Jepang dan bawa pulang kenangan manisnya.') }}</p>
            </div>
        </div>

        <div class="w-full sm:w-1/2 flex flex-col justify-center items-center bg-white/80 dark:bg-slate-950/80 p-6 sm:p-12">
            <div class="w-full max-w-md">
                <div class="sm:hidden text-center mb-8">
                    <span class="text-3xl font-display font-semibold text-slate-900 dark:text-white">🇯🇵 {{ __('Japan Travel') }}</span>
                </div>

                {{ $slot }}
                
            </div>
        </div>

    </div>
</body>
</html>
