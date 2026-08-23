@php
    $entrypoints ??= ['resources/css/app.css', 'resources/js/legacy.js'];
    $viteHot = public_path('hot');
    $viteManifest = public_path('build/manifest.json');
    $shouldLoadVite = ! app()->runningInConsole()
        && ! app()->runningUnitTests()
        && ! app()->environment('testing')
        && (is_file($viteHot) || is_file($viteManifest));
@endphp

@if($shouldLoadVite)
    @vite($entrypoints)
@endif
