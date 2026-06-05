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
    <body class="min-h-dvh bg-[#FAF9F6] font-sans text-[#1F2937] antialiased dark:bg-[#0E1116] dark:text-[#F4F1ED]">
        <div class="flex min-h-dvh flex-col">
            @include('layouts.navigation')

            @isset($header)
                <header class="border-b border-[#E7E3DC] bg-white dark:border-[#2A333D] dark:bg-[#161B22]">
                    <div class="mx-auto max-w-7xl px-4 py-7 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <main class="flex-1">
                {{ $slot }}
            </main>
        </div>

        @stack('scripts')
    </body>
</html>
