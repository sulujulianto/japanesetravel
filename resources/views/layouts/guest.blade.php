<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Japan Travel') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Poppins', sans-serif; }</style>
</head>
<body class="text-gray-900 antialiased">
    
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