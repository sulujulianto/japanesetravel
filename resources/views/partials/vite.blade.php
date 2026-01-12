@php
    $viteHot = public_path('hot');
    $viteManifest = public_path('build/manifest.json');
    $shouldLoadVite = ! app()->runningInConsole()
        && ! app()->runningUnitTests()
        && ! app()->environment('testing')
        && (is_file($viteHot) || is_file($viteManifest));
@endphp

@if($shouldLoadVite)
    @vite(['resources/css/app.css', 'resources/js/app.js'])
@endif
