<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ \App\Support\Brand::name() }}</title>

        @includeIf('partials.theme-script')
        @includeIf('partials.vite')
    </head>
    <body class="public-shell min-h-dvh font-sans antialiased">
        <div data-public-shell class="flex min-h-dvh flex-col">
            @include('partials.site-nav')

            @isset($header)
                <header class="border-b border-[#E7E3DC] bg-white dark:border-[#2A333D] dark:bg-[#161B22]">
                    <div class="mx-auto max-w-7xl px-4 py-7 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <main class="ui-reveal flex-1">
                {{ $slot }}
            </main>

            @include('partials.site-footer')
        </div>

        @stack('scripts')
    </body>
</html>
