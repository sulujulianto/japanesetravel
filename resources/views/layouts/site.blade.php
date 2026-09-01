<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', \App\Support\Brand::name())</title>
    @includeIf('partials.theme-script')
    @includeIf('partials.vite')
</head>
<body class="public-shell m-0 min-h-dvh p-0 font-sans antialiased">
    <div data-public-shell class="relative isolate m-0 flex min-h-dvh flex-col overflow-x-clip p-0">
        @include('partials.site-nav')

        <main class="relative flex-1">
            @yield('content')
        </main>

        @include('partials.site-footer')
    </div>

    @stack('scripts')
</body>
</html>
