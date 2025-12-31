<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name', 'Japan Travel'))</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @includeIf('partials.theme-script')
</head>
<body class="font-sans antialiased bg-slate-50 text-slate-900 dark:bg-slate-950 dark:text-slate-100">
    <div class="relative min-h-screen overflow-hidden">
        <div class="pointer-events-none absolute inset-0">
            <div class="absolute -top-32 right-0 h-96 w-96 rounded-full bg-rose-200/50 blur-3xl dark:bg-rose-500/10"></div>
            <div class="absolute -bottom-40 -left-20 h-[28rem] w-[28rem] rounded-full bg-sky-200/40 blur-3xl dark:bg-sky-500/10"></div>
        </div>

        @include('partials.site-nav')

        <main class="relative pt-24">
            @yield('content')
        </main>

        @include('partials.site-footer')
    </div>

    @stack('scripts')
</body>
</html>
