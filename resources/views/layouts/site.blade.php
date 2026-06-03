<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name', 'Japan Travel'))</title>
    @includeIf('partials.theme-script')
    @includeIf('partials.vite')
</head>
<body class="m-0 min-h-dvh p-0 font-sans antialiased bg-slate-50 text-slate-900 dark:bg-slate-950 dark:text-slate-100">
    <div class="relative isolate m-0 flex min-h-dvh flex-col overflow-x-clip p-0">
        <div class="pointer-events-none fixed inset-0 -z-10 overflow-hidden">
            <div class="absolute -top-32 right-0 h-96 w-96 rounded-full bg-rose-200/50 blur-3xl dark:bg-rose-500/10"></div>
            <div class="absolute bottom-0 left-0 h-[28rem] w-[28rem] -translate-x-1/3 translate-y-1/3 rounded-full bg-sky-200/40 blur-3xl dark:bg-sky-500/10"></div>
        </div>

        @include('partials.site-nav')

        <main class="relative flex-1">
            @yield('content')
        </main>

        @include('partials.site-footer')
    </div>

    @stack('scripts')
</body>
</html>
